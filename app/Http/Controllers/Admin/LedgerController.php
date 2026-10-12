<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Super Admin only (route group role:super_admin): reverse a transaction and
 * reset a student's balance. Rules:
 *  - Nothing is ever deleted or edited. Every action appends a NEW ledger line,
 *    so the history stays complete and auditable.
 *  - A written reason is mandatory; it is stored in `meta` (admin-only), the
 *    student only sees a short neutral description.
 *  - A transaction can be reversed once; reversal/adjustment/Autopilot lines
 *    cannot themselves be reversed.
 *  - No account may be taken below zero.
 */
class LedgerController extends Controller
{
    private const PROTECTED_CATEGORIES = ['Reversal', 'Admin Adjustment', 'Autopilot'];

    public function index(Request $request)
    {
        $students = User::where('role', 4)->orderBy('sid')->orderBy('name')->get(['id', 'name', 'nickname', 'sid']);
        $student = null;
        $entries = collect();
        $balance = null;

        if ($request->filled('student_id')) {
            $student = $students->firstWhere('id', (int) $request->student_id);
            abort_unless($student, 404);
            $entries = Transaction1::where('user_id', $student->id)->orderByDesc('id')->limit(100)->get();
            $balance = $entries->first()->balance ?? 0;
        }

        return view('admin.ledger.index', compact('students', 'student', 'entries', 'balance'));
    }

    public function reverse(Request $request, $id)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $admin = Auth::user();

        $result = DB::transaction(function () use ($id, $data, $admin) {
            $original = Transaction1::lockForUpdate()->findOrFail($id);
            $meta = $original->meta ?? [];

            if (in_array($original->category, self::PROTECTED_CATEGORIES, true)) {
                return 'This kind of entry cannot be reversed.';
            }
            if (!empty($meta['reversed_by'])) {
                return 'This transaction has already been reversed.';
            }

            $balance = BalanceService::lockedBalance($original->user_id);
            // Reversing a credit takes money out; reversing a debit gives it back.
            $newBalance = $original->type === 'credit' ? $balance - $original->amount : $balance + $original->amount;
            if ($newBalance < 0) {
                return 'Reversing this would take the account below zero.';
            }

            $reversal = Transaction1::create([
                'user_id' => $original->user_id,
                'sid' => $original->sid,
                'bank_account_id' => $original->bank_account_id,
                'transaction_date' => now(),
                'description' => 'Reversal - ' . $original->description,
                'type' => $original->type === 'credit' ? 'debit' : 'credit',
                'category' => 'Reversal',
                'amount' => $original->amount,
                'balance' => $newBalance,
                'is_penalty' => 0,
                'meta' => ['reversal_of' => $original->id, 'by_admin' => $admin->id, 'reason' => $data['reason']],
            ]);

            $original->meta = array_merge($meta, ['reversed_by' => $reversal->id]);
            $original->save();

            return true;
        });

        return $result === true
            ? back()->with('success', 'Transaction reversed.')
            : back()->with('error', $result);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'new_balance' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);
        $admin = Auth::user();
        $student = User::where('role', 4)->findOrFail($data['student_id']);

        DB::transaction(function () use ($student, $data, $admin) {
            $balance = BalanceService::lockedBalance($student->id);
            $target = round((float) $data['new_balance'], 2);
            $diff = round($target - $balance, 2);
            if ($diff == 0.0) {
                return;
            }

            $last = Transaction1::where('user_id', $student->id)->latest('id')->first();

            Transaction1::create([
                'user_id' => $student->id,
                'sid' => $student->sid,
                'bank_account_id' => $last->bank_account_id ?? \App\Models\BankAccount::where('student_id', $student->id)->value('id'),
                'transaction_date' => now(),
                'description' => 'Balance adjustment',
                'type' => $diff > 0 ? 'credit' : 'debit',
                'category' => 'Admin Adjustment',
                'amount' => abs($diff),
                'balance' => $target,
                'is_penalty' => 0,
                'meta' => ['by_admin' => $admin->id, 'reason' => $data['reason'], 'previous_balance' => $balance],
            ]);
        });

        return back()->with('success', 'Balance updated.');
    }
}
