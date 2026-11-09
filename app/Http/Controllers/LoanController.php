<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Services\LoanService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * The bank's Loans page, from the Intermediate (App\Services\LoanService): a
 * simulator (the instalment, the total and the interest before asking), the
 * request with the bank's check, and each loan with its table - what each
 * instalment pays of interest and of capital - with the 14 days to change one's
 * mind and the button to pay it all off.
 */
class LoanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless(LoanService::offered($user), 404);

        return view('bank.loans', [
            'user' => $user,
            'loans' => Loan::where('user_id', $user->id)->with('instalments')->orderByDesc('id')->get(),
            'load' => LoanService::monthlyLoad($user->id),
            'maxLoad' => LoanService::maxLoad($user->id),
            'blocked' => LoanService::blocked($user->id),
            'monthlyRate' => LoanService::monthlyRate(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:' . LoanService::MIN . '|max:' . LoanService::MAX,
            'months' => 'required|integer|in:' . implode(',', LoanService::TERMS),
            'accept' => 'accepted',
        ]);
        $loan = LoanService::grant(auth()->user(), (float) $data['amount'], (int) $data['months']);
        if (is_string($loan)) {
            return back()->with('error', $this->message($loan, auth()->id()));
        }
        $first = $loan->instalments()->first();

        return back()->with('success', e(__('flash.loan_granted', ['amount' => zeds((float) $loan->amount), 'instalment' => zeds((float) $loan->instalment),
            'date' => Carbon::parse($first->due_on)->locale(app()->getLocale())->isoFormat('LL')])));
    }

    public function cancel(Loan $loan)
    {
        $error = LoanService::cancel(auth()->user(), $loan);

        return $error ? back()->with('error', $this->message($error, auth()->id())) : back()->with('success', e(__('flash.loan_cancelled')));
    }

    public function payOff(Loan $loan)
    {
        $error = LoanService::payOff(auth()->user(), $loan);

        return $error ? back()->with('error', $this->message($error, auth()->id())) : back()->with('success', e(__('flash.loan_paid_off')));
    }

    /** Why not, in the student's language. */
    private function message(string $error, int $userId): string
    {
        return match ($error) {
            'loan_amount' => __('flash.loan_amount', ['min' => zeds(LoanService::MIN), 'max' => zeds(LoanService::MAX)]),
            'loan_too_much' => __('flash.loan_too_much', ['max' => zeds(LoanService::maxLoad($userId))]),
            default => __('flash.' . $error),
        };
    }
}
