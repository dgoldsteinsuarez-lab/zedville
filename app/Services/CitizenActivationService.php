<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\AutoDebitRequest;
use App\Models\ConsumerSurvey;
use App\Models\ManagePayeeBiller;
use App\Models\Transaction1;

class CitizenActivationService
{
    public function getNextRoute($user)
    {
        // STEP 1 - Bank Account
        $bankAccount = BankAccount::where('student_id', $user->id)->first();

        if (!$bankAccount) {
            return route('CitizenActivation.bankAccount');
        }

        // STEP 2 - Salary Authorization
        if (($bankAccount->primary_savings_account_amount ?? 0) <= 0) {
            return route('CitizenActivation.salaryAuthorization');
        }

        // STEP 3 - Auto Debit
        $autoDebit = AutoDebitRequest::where('user_id', $user->id)->exists();

        if (!$autoDebit) {
            return route('CitizenActivation.autoDebit');
        }

        // STEP 4 - Consumer Profile
        $consumerProfile = ConsumerSurvey::where('user_id', $user->id)->exists();

        if (!$consumerProfile) {
            return route('CitizenActivation.consumerProfile');
        }

        // All completed
        return null;
    }

    /**
     * Silently give a Tutor the same citizen account a student gets through
     * the onboarding wizard - a Tutor never sees that wizard, so this fills
     * in the same records directly from their own profile instead of a
     * submitted form. Called on every Tutor login; no-ops once a BankAccount
     * already exists, so it's safe to call repeatedly.
     */
    public function provisionForTutor($user): void
    {
        $bankAccount = BankAccount::where('student_id', $user->id)->first();

        if (!$bankAccount) {
            $salary = config('zedville.monthly_salary', 3952.40);

            $bankAccount = BankAccount::create([
                'student_id' => $user->id,
                'sid' => $user->sid,
                'student_name' => $user->name,
                'student_email' => $user->email,
                'student_cityzen_id' => $user->citizenId,
                'student_address' => $user->address,

                'bank_name' => 'Universal Bank',
                'primary_savings_account' => 'Primary Savings Account',
                'primary_savings_account_number' => '123000-' . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
                'primary_savings_account_amount' => $salary,

                'emergency_fund_account' => 'Emergency Fund Account',
                'emergency_fund_account_number' => str_pad((string) mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'emergency_fund_account_amount' => 0.00,

                'money_market_account' => 'Money Market Account',
                'money_market_account_number' => str_pad((string) mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'money_market_account_amount' => 0.00,

                'card_name' => 'Virtual Debit Card',
                'card_type' => 'DEBIT',
                'card_number' => '2000' . str_pad((string) mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT),
                'card_valid' => now()->addYear()->format('Y-m-d'),
                'card_cvv' => str_pad((string) mt_rand(0, 999), 3, '0', STR_PAD_LEFT),
                'card_iban' => 'ZV' . str_pad((string) mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'card_swift' => 'UNIB' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4)),
                'card_pin' => implode('', array_map(fn () => mt_rand(1, 9), range(1, 4))),
            ]);
        }

        if (($bankAccount->primary_savings_account_amount ?? 0) <= 0) {
            $bankAccount->update(['primary_savings_account_amount' => config('zedville.monthly_salary', 3952.40)]);
        }

        // Mandatory auto-debits only (Rent, City School) - same two every
        // student gets regardless of what they pick in the wizard.
        if (!AutoDebitRequest::where('user_id', $user->id)->where('type', 'Rent')->exists()) {
            AutoDebitRequest::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'type' => 'Rent',
                'accountno' => 'RENT-AUTO',
                'fullname' => $user->nickname,
                'email' => $user->email,
                'serviceaddress' => $bankAccount->student_address,
                'bankname' => $bankAccount->bank_name,
                'bankaccountnumber' => $bankAccount->primary_savings_account_number,
                'amount' => config('zedville.rent', 300),
                'startDate' => now(),
                'signature' => $user->nickname,
                'date' => now(),
                'tandagree' => 1,
            ]);
        }

        if (!AutoDebitRequest::where('user_id', $user->id)->where('type', 'City School')->exists()) {
            $schoolBiller = ManagePayeeBiller::where('name', 'City School')->where('status', 1)->first();

            AutoDebitRequest::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'type' => 'City School',
                'accountno' => $schoolBiller->account_number ?? 'SCHOOL-AUTO',
                'fullname' => $user->nickname,
                'email' => $user->email,
                'serviceaddress' => $bankAccount->student_address,
                'bankname' => $bankAccount->bank_name,
                'bankaccountnumber' => $bankAccount->primary_savings_account_number,
                'amount' => config('zedville.school_fees', 1000),
                'startDate' => now(),
                'signature' => $user->nickname,
                'date' => now(),
                'tandagree' => 1,
            ]);
        }

        foreach (['Rent' => config('zedville.rent', 300), 'School Fees' => config('zedville.school_fees', 1000)] as $description => $amount) {
            $alreadyPosted = Transaction1::where('user_id', $user->id)
                ->where('description', $description)
                ->whereDate('transaction_date', now()->toDateString())
                ->exists();

            if ($alreadyPosted) {
                continue;
            }

            $lastBalance = Transaction1::where('user_id', $user->id)->latest('id')->value('balance') ?? 0;

            Transaction1::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'bank_account_id' => $bankAccount->id,
                'transaction_date' => now(),
                'description' => $description,
                'type' => 'debit',
                'category' => 'Needs',
                'amount' => $amount,
                'balance' => max(0, $lastBalance - $amount),
                'is_penalty' => false,
            ]);
        }

        app(StatementGenerator::class)->generateForUser($user->id);
    }
}