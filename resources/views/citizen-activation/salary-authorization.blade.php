@extends('layouts.profile')

@section('title', 'Salary Authorization')

@section('content')

<div class="grid grid-cols-1 gap-5">

    <div class="page-shell">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress', ['step' => 3])

        <div class="step-header">

            <div class="step-eyebrow">
                Step 3 of 6 — Salary Setup
            </div>

            <div class="ca-step-title">
                Get paid — set up your salary
            </div>

            <div class="step-subtitle">
                Authorise a <span class="term">direct deposit</span> so your monthly Zeds salary goes
                straight into your Universal Bank account on payday.
            </div>

        </div>

        <div class="step-body">

            <div class="narrative">
                A <strong>direct deposit</strong> means the city automatically transfers your salary
                into your bank account on payday — no action needed from you each month.
                This is how most workers receive their wages in real life.

                You are authorising <strong>100% of your salary</strong> to be deposited directly.
            </div>

            <div class="definition">

                <span class="definition-icon">💡</span>

                <div>
                    <strong>Financial literacy:</strong>

                    A <strong>direct deposit authorisation</strong> is a signed instruction
                    from you to your employer, giving them permission to transfer your wages
                    electronically into your bank account.

                    It is faster and safer than a paper cheque.
                </div>

            </div>

            <div class="info-box">

                <div class="info-row">
                    <span class="info-label">Account Holder</span>
                    <span>{{ $user->nickname }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Bank</span>
                    <span>{{ $bankAccount->bank_name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Account Number</span>
                    <span>{{ $bankAccount->primary_savings_account_number }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Deposit Amount</span>
                    <span>100% of Monthly Salary</span>
                </div>

            </div>

            <div class="legal-block">
                "I hereby authorise Zedville City Hall to initiate electronic deposits
                of my salary into the designated bank account.

                I understand I may cancel this authorisation at any time.

                I confirm that the bank information provided is accurate."
            </div>

            <form action="{{ route('CitizenActivation.authorize-direct-deposit') }}" method="post">
                 @csrf
                <div class="checkbox-group">

                    <label class="check-row checked required">

                        <input type="checkbox"
                               name="bankdeposite"
                               value="1"
                               checked
                               hidden>

                        <div class="check-box">
                            ✓
                        </div>

                        <div class="check-text">

                            <span class="check-title">
                                Deposit 100% of my salary into the account above
                            </span>

                        </div>

                    </label>

                </div>

                <div class="btn-row">

                    <a href="{{ route('CitizenActivation.bankAccount') }}"
                       class="ca-btn-secondary">
                        ← Back
                    </a>

                    <button type="submit"
                            class="ca-btn-primary">
                        Authorise Direct Deposit →
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection