@extends('layouts.profile')

@section('title', 'Salary Authorization')

@section('content')

<div class="grid grid-cols-1 gap-5">

    <div class="page-shell">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress', ['step' => 3])

        <div class="step-header">

            <div class="step-eyebrow">
                {{ __('citizen.eyebrow', ['step' => 3, 'total' => 6, 'name' => __('citizen.salary.step')]) }}
            </div>

            <div class="ca-step-title">
                {{ __('citizen.salary.title') }}
            </div>

            <div class="step-subtitle">
                {!! __('citizen.salary.subtitle') !!}
            </div>

        </div>

        <div class="step-body">

            <div class="narrative">
                {!! __('citizen.salary.narrative') !!}
            </div>

            <div class="definition">

                <span class="definition-icon">💡</span>

                <div>
                    <strong>{{ __('citizen.financial_literacy') }}</strong>
                    {!! __('citizen.salary.definition') !!}
                </div>

            </div>

            <div class="info-box">

                <div class="info-row">
                    <span class="info-label">{{ __('citizen.salary.account_holder') }}</span>
                    <span>{{ $bankAccount->student_name ?? $user->name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">{{ __('citizen.salary.bank') }}</span>
                    <span>{{ $bankAccount->bank_name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">{{ __('citizen.salary.account_number') }}</span>
                    <span>{{ $bankAccount->primary_savings_account_number }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">{{ __('citizen.salary.deposit_amount') }}</span>
                    <span>{{ __('citizen.salary.deposit_value') }}</span>
                </div>

            </div>

            <div class="legal-block">
                {{ __('citizen.salary.legal') }}
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
                                {{ __('citizen.salary.deposit_check') }}
                            </span>

                        </div>

                    </label>

                </div>

                <div class="btn-row">

                    <a href="{{ route('CitizenActivation.bankAccount') }}"
                       class="ca-btn-secondary">
                        {{ __('citizen.back') }}
                    </a>

                    <button type="submit"
                            class="ca-btn-primary">
                        {{ __('citizen.salary.submit') }}
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection