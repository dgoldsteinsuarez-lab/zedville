@extends('layouts.profile')

@section('title', 'Bank Account')

@section('content')

<div class="grid grid-cols-1 gap-5">

    <div class="page-shell">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress', ['step' => 2])

        <div class="step-header">

            <div class="step-eyebrow">
                {{ __('citizen.eyebrow', ['step' => 2, 'total' => 6, 'name' => __('citizen.bank.step')]) }}
            </div>

            <div class="ca-step-title">
                {{ __('citizen.bank.title') }}
            </div>

            <div class="step-subtitle">
                {!! __('citizen.bank.subtitle') !!}
            </div>

        </div>

        <div class="step-body">

            <div class="narrative">
                {!! __('citizen.bank.narrative') !!}
            </div>

            <form action="{{ route('CitizenActivation.store') }}" method="post">
                @csrf
                <div class="form-grid">

                    <div class="field-group">
                        <label class="field-label">{{ __('citizen.bank.full_name') }}</label>
                        <input
                            class="field-input readonly"
                            type="text"
                            value="{{ Auth::user()->name }}"
                            readonly>
                        <small class="field-hint">{!! __('citizen.bank.typo_hint', ['settings' => '<a href="' . e(route('profile.edit')) . '">' . e(__('citizen.bank.settings')) . '</a>']) !!}</small>
                    </div>

                    <div class="field-group">
                        <label class="field-label">{{ __('citizen.bank.citizen_id') }}</label>
                        <input
                            class="field-input readonly"
                            type="text"
                            name="studentId"
                            value="{{ $user->citizenId }}"
                            readonly>
                    </div>

                </div>

                <div class="form-grid single">

                    <div class="field-group">
                        <label class="field-label">{{ __('citizen.bank.email') }}</label>
                        <input
                            class="field-input"
                            type="email"
                            name="email"
                            value="{{ Auth::user()->email }}"
                            placeholder="{{ __('citizen.bank.email_placeholder') }}">
                    </div>

                </div>

                <div class="form-grid single">

                    <div class="field-group">
                        @if ($cityAddress)
                            <label class="field-label">{{ __('citizen.bank.address_auto') }}</label>
                            <input
                                class="field-input readonly"
                                type="text"
                                name="homeAddress"
                                value="{{ $cityAddress }}"
                                readonly>
                            <small class="field-hint">{{ __('citizen.bank.address_hint') }}</small>
                        @else
                            <label class="field-label">{{ __('citizen.bank.address') }}</label>

                            <textarea
                                class="field-textarea"
                                name="homeAddress"
                                placeholder="{{ __('citizen.bank.address') }}">{{ $user->address }}</textarea>
                        @endif

                    </div>

                </div>

                <div class="checkbox-group" style="margin-top:0.5rem">

                    <label class="check-row">

                        <input type="checkbox"
                               name="rcvaccemail"
                               value="1"
                               checked
                               hidden>

                        <div class="check-box">
                            ✓
                        </div>

                        <span class="check-text">
                            {{ __('citizen.bank.agree_statements') }}
                        </span>

                    </label>

                    <label class="check-row">

                        <input type="checkbox"
                               name="tandc"
                               value="1"
                               hidden>

                        <div class="check-box">
                            ✓
                        </div>

                        <span class="check-text">
                            {{ __('citizen.bank.agree_terms') }}
                        </span>

                    </label>

                </div>

                <div class="btn-row">

                    <a href="{{ route('CitizenActivation.index') }}"
                       class="ca-btn-secondary">
                        {{ __('citizen.back') }}
                    </a>

                    <button type="submit"
                            class="ca-btn-primary">
                        {{ __('citizen.bank.submit') }}
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection