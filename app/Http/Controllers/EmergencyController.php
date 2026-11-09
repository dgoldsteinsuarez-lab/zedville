<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Emergency;
use App\Services\BankTransferService;
use App\Services\EmergencyService;
use App\Services\LoanService;
use App\Services\SavingsLedger;
use App\Support\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * «Resuelve tu imprevisto» (proposal «El imprevisto», step 13): the student's
 * emergency - what happened, what it costs them (a health one, after their
 * insurance), the ways to pay it with what each costs in all, and once paid
 * the two short questions (App\Services\EmergencyService).
 */
class EmergencyController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        abort_unless(Level::saver($user), 404);
        EmergencyService::arrive($user);
        EmergencyService::chargeDue($user);

        $emergency = EmergencyService::latest($user->id);
        $options = $emergency ? EmergencyService::options($emergency, $user) : [];
        $question = $emergency && $emergency->status !== 'pending' ? EmergencyService::question($emergency) : null;
        $history = Emergency::where('user_id', $user->id)->when($emergency, fn ($q) => $q->whereKeyNot($emergency->id))->latest('id')->get();
        $account = BankAccount::where('student_id', $user->id)->first();

        return view('emergency.show', [
            'user' => $user,
            'emergency' => $emergency,
            'options' => $options,
            // what the student has now, for what each way leaves them
            'balances' => [
                'fund' => $account && SavingsLedger::isOpen($account, BankTransferService::EMERGENCY) ? SavingsLedger::balance($account, BankTransferService::EMERGENCY) : 0.0,
                'current' => $account ? BankTransferService::balance($account, BankTransferService::CURRENT) : 0.0,
            ],
            'question' => $question ? \App\Services\MoneyFactory\Activities::forPage([$question])[0] : null,
            'why' => $question['why'] ?? null,
            'history' => $history,
        ]);
    }

    public function pay(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate(['emergency' => ['required', 'integer'], 'option' => ['required', 'string', 'max:20']]);
        $emergency = Emergency::where('user_id', $user->id)->findOrFail($data['emergency']);

        if ($why = EmergencyService::pay($user, $emergency, $data['option'])) {
            return redirect()->route('emergency.show')->with('error', match ($why) {
                'loan_too_much' => __('flash.loan_too_much', ['max' => zeds(LoanService::maxLoad($user->id))]),
                'loan_amount' => __('flash.loan_amount', ['min' => zeds(LoanService::MIN), 'max' => zeds(LoanService::MAX)]),
                default => __('flash.' . $why),
            });
        }

        return redirect()->route('emergency.show')->with('success', e(__('flash.emergency_paid')));
    }

    public function answers(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate(['emergency' => ['required', 'integer'], 'cost' => ['nullable', 'integer'], 'change' => ['nullable', 'string', 'max:30']]);
        $emergency = Emergency::where('user_id', $user->id)->findOrFail($data['emergency']);

        if ($why = EmergencyService::answer($user, $emergency, isset($data['cost']) ? (int) $data['cost'] : null, $data['change'] ?? null)) {
            return redirect()->route('emergency.show')->withInput()->with('error', __('emergency.' . $why));
        }

        return redirect()->route('emergency.show')->with('success', e(__('emergency.done')));
    }
}
