@extends('layouts.profile')

@section('title', 'Auto Debit')

@section('content')

<div class="grid grid-cols-1 gap-5">

    <div class="page-shell">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress',['step'=>4])

        <div class="step-header">

            <div class="step-eyebrow">
                {{ __('citizen.eyebrow', ['step' => 4, 'total' => 6, 'name' => __('citizen.bills.step')]) }}
            </div>

            <div class="ca-step-title">
                {{ __('citizen.bills.title') }}
            </div>

            <div class="step-subtitle">
                {!! __('citizen.bills.subtitle') !!}
            </div>

        </div>

        <div class="step-body">

            <div class="narrative">
                {!! __('citizen.bills.narrative') !!}
            </div>

            <div class="definition">

                <span class="definition-icon">💡</span>

                <div>
                    <strong>{{ __('citizen.financial_literacy') }}</strong>
                    {!! __('citizen.bills.definition') !!}
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
                                {{ __('citizen.bills.school', ['amount' => 'Ƶ' . number_format(config('zedville.school_fees', 1000))]) }}
                                <span class="required-tag">{{ __('citizen.bills.required') }}</span>
                            </span>

                            <div class="check-desc">
                                {{ __('citizen.bills.account_no', ['number' => $billers[3]->account_number ?? '__________']) }}
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
                                {{ __('citizen.bills.internet') }}
                            </span>

                            <div class="check-desc">
                                {{ __('citizen.bills.account_no', ['number' => $billers[12]->account_number ?? '__________']) }}
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
                                {{ __('citizen.bills.electricity') }}
                            </span>

                            <div class="check-desc">
                                {{ __('citizen.bills.account_no', ['number' => $billers[10]->account_number ?? '__________']) }}
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
                                {{ __('citizen.bills.water') }}
                            </span>

                            <div class="check-desc">
                                {{ __('citizen.bills.account_no', ['number' => $billers[11]->account_number ?? '__________']) }}
                            </div>
                            <input type="hidden" name="services_meta[water][name]" value="Utility - Water">
                            <input type="hidden" name="services_meta[water][account]" value="{{ $billers[11]->account_number ?? '' }}">
                            <input type="hidden" name="services_meta[water][amount]" value="{{ $billers[11]->amount ?? '' }}">
                        </div>

                    </label>

                </div>

                <div class="legal-block">

                    {{ __('citizen.bills.legal') }}

                </div>
                <input type="hidden" name="fullname" value="{{ $user->display_name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                <input type="hidden" name="serviceaddress" value="{{ $bankAccount->student_address }}">
                <input type="hidden" name="bankname" value="{{ $bankAccount->bank_name }}">
                <input type="hidden" name="bankaccountnumber" value="{{ $bankAccount->primary_savings_account_number }}"></a>
                <div class="btn-row">

                    <a href="{{ route('CitizenActivation.salaryAuthorization') }}"
                       class="ca-btn-secondary">
                        {{ __('citizen.back') }}
                    </a>

                    <button type="submit"
                            class="ca-btn-primary">
                        {{ __('citizen.bills.submit') }}
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection