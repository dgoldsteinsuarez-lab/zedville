<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankController;
use App\Services\StatementGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Mailbox;
use App\Helpers\MailboxHelper;
use App\Helpers\MailboxScheduler;
use App\Models\Transaction1;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Services\CitizenActivationService;
use App\Services\NotificationService;
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, CitizenActivationService $activationService, NotificationService $notifications): \Illuminate\Http\RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Real "New Device Login" notification — hashes ip+user-agent,
        // fires only the first time this device is seen for this user.
        $notifications->checkNewDevice($user, $request);

        // ✅ Store SID in session
        session([
            'sid' => $user->sid,        // if SID exists in DB
            'user_id' => $user->id,     // optional but useful
        ]);

        /*if (is_null($user->loginTime)) {
            // Insert welcome message into mailbox
            $content = MailboxHelper::renderMailboxTemplate('cityzenid', ['user' => $user]);
            $mailbox =Mailbox::create([
                'student_id' => $user->id,
                'subject' => $user->citizenId,
                'content' => $content,
                'type' => 'primary',
                'read' => 0, // mark as unread
            ]);
        }*/
        //MailboxScheduler::scheduleForEvent('login', $user->id);
        $now = now();
        $accountCreatedAt = Carbon::parse($user->created_at);

        // Check if user is NOT a new account
        $isEligible =
            !($accountCreatedAt->year === $now->year &&
                $accountCreatedAt->month === $now->month);

        // ✅ Only run logic if eligible
        /*if ($isEligible && $now->day >= 6) {
            app(StatementGenerator::class)->generateForUser($user->id);
        }

        if ($isEligible && $now->day >= 7) {
            $this->ensureMonthlyBills($user);
        }*/

        // Penalty on LAST DAY of month
        $today = now();
        $isLastDayOfMonth = $today->isSameDay($today->copy()->endOfMonth());

        $accountCreatedBeforeThisMonth =
            $user->created_at->lessThan(now()->startOfMonth());

        $penaltyExists = \App\Models\Transaction1::where('user_id', $user->id)
            ->where('category', 'Penalty')
            ->whereYear('transaction_date', $today->year)
            ->whereMonth('transaction_date', $today->month)
            ->exists();

        if (
            $user->isStudent() &&
            !config('zedville.auto_penalty') &&   // when the scheduled job is on, it does this for everyone
            $isLastDayOfMonth &&
            !$penaltyExists &&
            $accountCreatedBeforeThisMonth
        ) {
            app(\App\Http\Controllers\BankController::class)->banks_penalty(app(StatementGenerator::class), $user);
        }
        // ✅ ADD PREVIOUS MONTH STATEMENT MAIL HERE 👇👇👇
        // ----------------------------------------------

        // 📅 Previous month calculation
        $prevMonthDate = now()->subMonth();
        $prevMonth = $prevMonthDate->month;
        $prevYear = $prevMonthDate->year;

        $bankAccount = BankAccount::where('student_id', $user->id)->first();

        if ($bankAccount) {

            $accountOpenedBeforePrevMonth =
                Carbon::parse($bankAccount->created_at)
                    ->lessThan($prevMonthDate->startOfMonth());

            if ($accountOpenedBeforePrevMonth) {

                $statementExists = BankStatement::where('user_id', $user->id)
                    ->where('month', $prevMonth)
                    ->where('year', $prevYear)
                    ->exists();

                if ($statementExists) {
                    $subject = 'Your ' . $prevMonthDate->format('F Y') . ' Bank Statement';
                    /*$mailAlreadySent = Mailbox::where('student_id', $user->id)
                         ->where('subject', $subject)
                        ->whereYear('created_at', now()->year)
                        ->whereMonth('created_at', now()->month)
                        ->exists();*/
                    $subject = 'Your ' . $prevMonthDate->format('F Y') . ' Bank Statement';

                    $mailAlreadySent = Mailbox::where('student_id', $user->id)
                        ->where('subject', $subject)
                        ->exists();

                    if (!$mailAlreadySent) {

                        $prevMonthEnd = now()->subMonth()->endOfMonth();

                        $content = view('mailbox_templates.monthly-statement', [
                            'user' => $user,
                            'month' => $prevMonthDate->format('F'),
                            'year' => $prevYear,
                        ])->render();

                        Mailbox::create([
                            'student_id' => $user->id,
                            'subject' => 'Your ' . $prevMonthDate->format('F Y') . ' Bank Statement',
                            'content' => $content,
                            'type' => 'primary',
                            'read' => 0,
                            'created_at' => $prevMonthEnd,
                            'updated_at' => $prevMonthEnd,
                        ]);
                    }
                }
            }
        }

        // ----------------------------------------------
        // Save login time
        $user->loginTime = now();
        $user->save();
        // 1. Student (Role 4): Check activation steps then go to student dashboard
        if ($user->role == 4 || $user->role == '4') {
            $nextRoute = $activationService->getNextRoute($user);
            if ($nextRoute) {
                return redirect($nextRoute);
            }
            return redirect('/dashboard');
        }

        // 2. Super Admin (1), School Admin (2), or Legacy Admin (0)
        if (in_array($user->role, [0, 1, 2, '0', '1', '2', 'admin'])) {
            return redirect('/admin/dashboard');
        }

        // 3. Tutor (Role 3) - silently give them the same citizen account a
        // student gets through onboarding (bank account, mandatory bills,
        // first statement) since a Tutor never sees that wizard. Safe to
        // call on every login - no-ops once already provisioned.
        if ($user->role == 3 || $user->role == '3' || $user->role === 'tutor') {
            $activationService->provisionForTutor($user);
            return redirect('/admin/dashboard');
        }

        return redirect('/dashboard'); // fallback
    }


    private function ensureMonthlyBills(\App\Models\User $user)
    {
        $fixedTransactions = [
            ['day' => 7, 'description' => 'Utility - Electricity', 'amount' => rand(60, 90)],
            ['day' => 7, 'description' => 'Utility - Water', 'amount' => rand(30, 60)],
            ['day' => 7, 'description' => 'Internet', 'amount' => 20],
            ['day' => 7, 'description' => 'Rent', 'amount' => 300],
            ['day' => 7, 'description' => 'School Fees', 'amount' => 1000],
        ];

        $today = now();

        foreach ($fixedTransactions as $item) {

            $exists = Transaction1::where('user_id', $user->id)
                ->where('description', $item['description'])
                ->whereYear('transaction_date', $today->year)
                ->whereMonth('transaction_date', $today->month)
                ->exists();

            if ($exists) {
                continue;
            }

            $txnDate = now()->startOfMonth()->addDays($item['day'] - 1);

            $latestTxn = Transaction1::where('user_id', $user->id)
                ->latest('id')
                ->first();

            $lastBalance = $latestTxn ? $latestTxn->balance : 0;
            $newBalance = max(0, $lastBalance - $item['amount']);
            $bankAccount = BankAccount::where('student_id', $user->id)->first();
            Transaction1::create([
                'user_id' => $user->id,
                'bank_account_id' => $bankAccount->id ?? null,
                'transaction_date' => $txnDate,
                'description' => $item['description'],
                'type' => 'debit',
                'category' => 'Wants',
                'amount' => $item['amount'],
                'balance' => $newBalance,
                'is_penalty' => false,
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
