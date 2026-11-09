<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\StatementGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Mailbox;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Services\CitizenActivationService;
use App\Services\NotificationService;
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view - design 3c, the skyline under a bright sky,
     * which Daniel chose from his shortlist on 2026-10-25.
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

        // The month-end fine on the LAST DAY of the month, for a student without the
        // plan of the month - once a month, the first month too (MonthEndPenaltyService
        // decides; Daniel, 2026-11-07: "el primer mes también debe haber penalización").
        $today = now();
        $isLastDayOfMonth = $today->isSameDay($today->copy()->endOfMonth());

        if (
            $user->isStudent() &&
            !config('zedville.auto_penalty') &&   // when the scheduled job is on, it does this for everyone
            $isLastDayOfMonth
        ) {
            app(\App\Http\Controllers\BankController::class)->banks_penalty(app(StatementGenerator::class), $user);
        }
        // ✅ ADD PREVIOUS MONTH STATEMENT MAIL HERE 👇👇👇
        // ----------------------------------------------

        // 📅 Previous month calculation (from the 1st: on the 31st subMonth() gave this month)
        $prevMonthDate = now()->startOfMonth()->subMonth();
        $prevMonth = $prevMonthDate->month;
        $prevYear = $prevMonthDate->year;

        $bankAccount = BankAccount::where('student_id', $user->id)->first();

        if ($bankAccount) {

            $accountOpenedBeforePrevMonth =
                Carbon::parse($bankAccount->created_at)
                    ->lessThan($prevMonthDate->copy()->endOfMonth());

            if ($accountOpenedBeforePrevMonth) {

                // A month with money on the account has a statement (bank review,
                // 2026-10-29: this waited for bank_statements rows, which only the
                // first month gets - the mail never came).
                $statementExists = \App\Models\Transaction1::where('user_id', $user->id)
                    ->whereYear('transaction_date', $prevYear)
                    ->whereMonth('transaction_date', $prevMonth)
                    ->exists();

                if ($statementExists) {
                    $subject = 'Your ' . $prevMonthDate->format('F Y') . ' Bank Statement';

                    $mailAlreadySent = Mailbox::where('student_id', $user->id)
                        ->where('subject', $subject)
                        ->exists();

                    if (!$mailAlreadySent) {

                        $prevMonthEnd = $prevMonthDate->copy()->endOfMonth();

                        $content = view('mailbox_templates.monthly-statement', [
                            'user' => $user,
                            'month' => $prevMonthDate->format('Y-m'),
                        ])->render();

                        Mailbox::create([
                            'student_id' => $user->id,
                            'subject' => 'Your ' . $prevMonthDate->format('F Y') . ' Bank Statement',
                            'content' => $content,
                            'type' => 'primary',
                            // Shown in the reader's language when opened (Mailbox model).
                            'template' => 'monthly-statement',
                            'template_data' => ['month' => $prevMonthDate->format('Y-m')],
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
