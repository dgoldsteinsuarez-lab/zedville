@extends('layouts.profile')

@section('title', 'Bank Account')

@section('content')

<div class="grid grid-cols-1 gap-5">

    <div class="page-shell">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress', ['step' => 2])

        <div class="step-header">

            <div class="step-eyebrow">
                Step 2 of 6 — Bank Account
            </div>

            <div class="ca-step-title">
                Open your city bank account
            </div>

            <div class="step-subtitle">
                In banking, a <span class="term">current account</span> is used for everyday money —
                receiving your salary and paying bills. Open yours at Universal Bank now.
            </div>

        </div>

        <div class="step-body">

            <div class="narrative">
                Every Zedville citizen banks at <strong>Universal Bank</strong> — the city's official
                bank. This is where your monthly salary in Zeds will land and where your expenses
                will be paid from. It only takes 60 seconds to open.
            </div>

            <form action="{{ route('CitizenActivation.store') }}" method="post">
                @csrf
                <div class="form-grid">

                    <div class="field-group">
                        <label class="field-label">Full Name</label>
                        <input
                            class="field-input readonly"
                            type="text"
                            value="{{ Auth::user()->name }}"
                            readonly>
                        <small class="field-hint">Made a typo? Fix it in <a href="{{ route('profile.edit') }}">Settings</a>.</small>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Citizen ID (Auto-filled)</label>
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
                        <label class="field-label">Email Address</label>
                        <input
                            class="field-input"
                            type="email"
                            name="email"
                            value="{{ Auth::user()->email }}"
                            placeholder="Your Email">
                    </div>

                </div>

                <div class="form-grid single">

                    <div class="field-group">
                        <label class="field-label">Home Address</label>

                        <textarea
                            class="field-textarea"
                            name="homeAddress"
                            placeholder="Home Address">{{ $user->address }}</textarea>

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
                            I agree to receive account statements via email
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
                            I agree to the Terms & Conditions
                        </span>

                    </label>

                </div>

                <div class="btn-row">

                    <a href="{{ route('CitizenActivation.index') }}"
                       class="ca-btn-secondary">
                        ← Back
                    </a>

                    <button type="submit"
                            class="ca-btn-primary">
                        Submit Application →
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection