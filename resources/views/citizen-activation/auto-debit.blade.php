@extends('layouts.profile')

@section('title', 'Auto Debit')

@section('content')

<div class="grid grid-cols-1 gap-5">

    <div class="page-shell">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress',['step'=>4])

        <div class="step-header">

            <div class="step-eyebrow">
                Step 4 of 6 — Bill Payments
            </div>

            <div class="ca-step-title">
                Pay your bills automatically
            </div>

            <div class="step-subtitle">
                Set up <span class="term">auto-debit</span> so your monthly services are paid on time —
                every month, without doing anything.
            </div>

        </div>

        <div class="step-body">

            <div class="narrative">
                An <strong>auto-debit</strong> (also called a direct debit) is an instruction
                that allows a company to pull an agreed amount from your bank account on a set
                date each month.

                Most adults use auto-debits for regular bills—rent, utilities and subscriptions—
                so they never miss a payment or receive a late fee.
            </div>

            <div class="definition">

                <span class="definition-icon">💡</span>

                <div>
                    <strong>Financial literacy:</strong>

                    <strong>Auto-debit</strong> and
                    <strong>direct deposit</strong> are different.

                    Direct deposit = money comes <em>into</em> your account (salary).

                    Auto-debit = money goes <em>out</em> of your account (bills).

                    Both are automatic.
                </div>

            </div>

            <form action="{{ route('CitizenActivation.store-auto-debit-authorization-form') }}" method="post">
                 @csrf
                <div class="checkbox-group">

                    <label class="check-row required">

                        <input type="checkbox"
                                name="services[school]"
                                value="{{ $billers['City School']->account_number ?? '' }}"
                                checked
                               disabled
                               hidden>

                        <div class="check-box">✓</div>

                        <div class="check-text">

                            <span class="check-title">
                                School Fees — Ƶ1,000/month
                                <span class="required-tag">Required</span>
                            </span>

                            <div class="check-desc">
                                Account No.:{{ $billers[3]->account_number ?? '__________' }}
                            </div>
                            <input type="hidden"
                            name="services[school]"
                            value="{{ $billers[3]->account_number ?? '' }}">

                            <input type="hidden"
                                name="services_meta[school][name]"
                                value="City School">

                            <input type="hidden"
                                name="services_meta[school][account]"
                                value="{{ $billers[3]->account_number ?? '' }}">

                            <input type="hidden"
                                name="services_meta[school][amount]"
                                value="{{ $billers[3]->amount ?? '' }}"></div>
                        </div>

                    </label>

                    <label class="check-row">

                        <input type="checkbox"
                               name="services[internet]" 
                               value="{{ $billers['Internet Service Provider']->account_number ?? '' }}"
                               hidden>

                        <div class="check-box">✓</div>

                        <div class="check-text">

                            <span class="check-title">
                                Internet Service — ABC Communications
                            </span>

                            <div class="check-desc">
                                Account No.: {{  $billers[12]->account_number ?? '__________' }}
                            </div>
                            <input type="hidden" name="services_meta[internet][name]" value="Internet">
                            <input type="hidden" name="services_meta[internet][account]" value="{{ $billers[12]->account_number ?? '' }}">
                            <input type="hidden" name="services_meta[internet][amount]" value="{{ $billers[12]->amount ?? '' }}">
                        </div>

                    </label>

                    <label class="check-row">

                        <input type="checkbox"
                               name="services[electricity]" 
                               value="{{ $billers['Utility - Electricity']->account_number ?? '' }}"
                               hidden>

                        <div class="check-box">✓</div>

                        <div class="check-text">

                            <span class="check-title">
                                Electricity — ABC Electric Utility Co.
                            </span>

                            <div class="check-desc">
                                Account No.: {{ $billers[10]->account_number ?? '__________' }}
                            </div>
                            <input type="hidden" name="services_meta[electricity][name]" value="Utility - Electricity">
                            <input type="hidden" name="services_meta[electricity][account]" value="{{ $billers[10]->account_number ?? '' }}">
                            <input type="hidden" name="services_meta[electricity][amount]" value="{{ $billers[10]->amount ?? '' }}">
                        </div>

                    </label>

                    <label class="check-row">

                        <input type="checkbox"
                               name="services[water]" 
                               value="{{ $billers['Utility - Water']->account_number ?? '' }}"
                               hidden>

                        <div class="check-box">✓</div>

                        <div class="check-text">

                            <span class="check-title">
                                Water — ABC Water Utility Co.
                            </span>

                            <div class="check-desc">
                                Account No.: {{ $billers[11]->account_number ?? '__________' }}
                            </div>
                            <input type="hidden" name="services_meta[water][name]" value="Utility - Water">
                            <input type="hidden" name="services_meta[water][account]" value="{{ $billers[11]->account_number ?? '' }}">
                            <input type="hidden" name="services_meta[water][amount]" value="{{ $billers[11]->amount ?? '' }}">
                        </div>

                    </label>

                </div>

                <div class="legal-block">

                    "I hereby authorise ABC Group Companies to initiate debit entries to
                    my bank account for the bill payments of the services I have selected
                    above.

                    This authorisation can be cancelled at any time by notifying the
                    respective company in writing."

                </div>
                <input type="hidden" name="fullname" value="{{ $user->nickname }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                <input type="hidden" name="serviceaddress" value="{{ $user->student_address }}">
                <input type="hidden" name="bankname" value="{{ $bankAccount->bank_name }}">
                <input type="hidden" name="bankaccountnumber" value="{{ $bankAccount->primary_savings_account_number }}"></a>
                <div class="btn-row">

                    <a href="{{ route('CitizenActivation.salaryAuthorization') }}"
                       class="ca-btn-secondary">
                        ← Back
                    </a>

                    <button type="submit"
                            class="ca-btn-primary">
                        Confirm & Activate Auto-Pay →
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection