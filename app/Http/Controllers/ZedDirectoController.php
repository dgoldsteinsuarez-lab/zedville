<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\SavingsTransaction;
use App\Services\SavingsLedger;
use App\Services\ZedDirecto;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Zed Directo's page, the Intermediate's second bank from its third month
 * (App\Services\ZedDirecto): its rates next to the Money Market's, with the
 * real interest; the savings account (open it, bring money from the current
 * account, send it back); the 6-month deposit (make one, take it out early
 * without the interest); and its movements. Before the third month the page
 * says when it opens; for the levels before the Intermediate there is none.
 */
class ZedDirectoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $open = ZedDirecto::open($user);
        $opensOn = ZedDirecto::opensOn($user);
        abort_unless($open || $opensOn, 404);
        $account = BankAccount::where('student_id', $user->id)->first();
        if (!$account) {
            return redirect()->route('bank.index');
        }
        if ($open) {
            // a deposit that ended today is paid before the page shows it
            ZedDirecto::endDue($user->id);
            $account->refresh();
        }

        return view('zeddirecto.index', [
            'open' => $open,
            'opensOn' => $opensOn,
            'account' => $account,
            'rates' => [
                'mm' => SavingsLedger::rate('money_market', $user) * 100,
                'savings' => SavingsLedger::rate(ZedDirecto::SAVINGS) * 100,
                'deposit' => SavingsLedger::rate(ZedDirecto::DEPOSIT) * 100,
            ],
            'savingsOpen' => SavingsLedger::isOpen($account, ZedDirecto::SAVINGS),
            'depositOpen' => SavingsLedger::isOpen($account, ZedDirecto::DEPOSIT),
            'movements' => SavingsTransaction::where('user_id', $user->id)->whereIn('account', [ZedDirecto::SAVINGS, ZedDirecto::DEPOSIT])
                ->orderByDesc('transaction_date')->orderByDesc('id')->limit(20)->get(),
        ]);
    }

    public function open()
    {
        $result = app(ZedDirecto::class)->openSavings(auth()->user());

        return match ($result) {
            'opened' => back()->with('success', e(__('flash.zd_opened'))),
            'open' => back()->with('success', e(__('flash.zd_already_open'))),
            default => back()->with('error', __('flash.zd_closed')),
        };
    }

    public function move(Request $request)
    {
        $data = $request->validate(['way' => 'required|in:in,out', 'amount' => 'required|numeric|min:0.01|max:1000000']);
        $error = app(ZedDirecto::class)->move(auth()->user(), $data['way'], (float) $data['amount']);
        if ($error) {
            return back()->with('error', $this->message($error));
        }

        return back()->with('success', e(__($data['way'] === 'in' ? 'flash.zd_moved_in' : 'flash.zd_moved_out', ['amount' => zeds((float) $data['amount'])])));
    }

    public function deposit(Request $request)
    {
        $data = $request->validate(['amount' => 'required|numeric|min:0.01|max:1000000']);
        $error = app(ZedDirecto::class)->openDeposit(auth()->user(), (float) $data['amount']);
        if ($error) {
            return back()->with('error', $this->message($error));
        }
        $ends = Carbon::parse(BankAccount::where('student_id', auth()->id())->value('zd_deposit_matures_on'));

        return back()->with('success', e(__('flash.zd_deposit_made', ['amount' => zeds((float) $data['amount']),
            'date' => $ends->locale(app()->getLocale())->isoFormat('LL')])));
    }

    public function cancel()
    {
        $error = app(ZedDirecto::class)->cancelDeposit(auth()->user());

        return $error ? back()->with('error', $this->message($error)) : back()->with('success', e(__('flash.zd_deposit_cancelled')));
    }

    /** Why it didn't happen, in the student's language. */
    private function message(string $error): string
    {
        return match ($error) {
            'insufficient_balance' => __('flash.zd_no_money'),
            'zd_deposit_min' => __('flash.zd_deposit_min', ['min' => zeds(ZedDirecto::MIN_DEPOSIT)]),
            default => __('flash.' . $error),
        };
    }
}
