@extends('layouts.profile')

@section('title', 'Citizen Activation Complete')

@section('content')

<div class="grid grid-cols-1 gap-5">

    <div class="page-shell">

        {{-- Progress Bar --}}
        @include('citizen-activation.layouts.progress',['step'=>6])

        <div class="step-header">

            <div class="step-eyebrow">
                Step 6 of 6 — Complete
            </div>

            <div class="ca-step-title">
                You are all set up,
                <span>{{ Auth::user()->name }}</span>!
            </div>

            <div class="step-subtitle">
                Your <span class="term">Citizen Diary</span> below is a plain-English record of everything you just activated.
            </div>

        </div>

        <div class="step-body">

            {{-- Completion Banner --}}
            <div class="completion-banner">

                <div class="big">
                    🎉 Citizen Activation Complete
                </div>

                <div class="sub">
                    Your Zedville account is fully live.
                    Your first salary in Zeds arrives at the start of next month.
                </div>

            </div>

            {{-- Spending Snapshot --}}
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.5rem">

                <p style="font-size:.82rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem">
                    Your Monthly Spending Snapshot
                </p>

                <div class="snapshot-cat">🍖 Food & Groceries</div>

                <div class="snapshot-row">
                    <span>Weekly Groceries (Pescatarian)</span>
                    <span>Ƶ 210.11</span>
                </div>

                <div class="snapshot-row">
                    <span>School Lunch</span>
                    <span>Ƶ 20.00</span>
                </div>

                <div class="snapshot-cat">
                    🚌 Getting Around
                </div>

                <div class="snapshot-row">
                    <span>Travel to School</span>
                    <span>Ƶ 80.00</span>
                </div>

                <div class="snapshot-cat">
                    🎮 Fun & Free Time
                </div>

                <div class="snapshot-row">
                    <span>Activities & Hobbies</span>
                    <span>Ƶ 75.00</span>
                </div>

                <div class="snapshot-row">
                    <span>Eating Out</span>
                    <span>Ƶ 65.00</span>
                </div>

                <div class="snapshot-total">

                    <span class="label">
                        Total Monthly Expenses
                    </span>

                    <span class="amount">
                        Ƶ 450.11
                    </span>

                </div>

            </div>

            {{-- Citizen Diary --}}
            <div class="diary-heading">
                📖 Your Citizen Diary
            </div>

            <div class="diary-entry">

                <div class="diary-date">
                    Today — Account Setup
                </div>

                <div class="diary-text">
                    You opened a
                    <span class="term">current account</span>
                    at Universal Bank (Account No. 123000-326474).

                    A <span class="term">current account</span>
                    is the standard type of bank account used for
                    everyday money—receiving income and paying bills.
                </div>

            </div>

            <div class="diary-entry">

                <div class="diary-date">
                    Today — Salary Authorisation
                </div>

                <div class="diary-text">
                    You signed a
                    <span class="term">direct deposit authorisation</span>,
                    instructing Zedville City Hall to transfer 100%
                    of your monthly Zeds salary directly into your
                    Universal Bank account on payday.

                    This is how most workers receive wages in real life—
                    faster and safer than a paper cheque.
                </div>

            </div>

            <div class="diary-entry">

                <div class="diary-date">
                    Today — Bill Payments
                </div>

                <div class="diary-text">
                    You activated
                    <span class="term">auto-debit</span>
                    for School Fees (Ƶ1,000/month).

                    <span class="term">Auto-debit</span>
                    means the payment is pulled automatically from
                    your account every month.

                    Remember:

                    <strong>Direct Deposit</strong> = Money In

                    <strong>Auto-Debit</strong> = Money Out
                </div>

            </div>

            <div class="diary-entry">

                <div class="diary-date">
                    Today — Consumer Profile
                </div>

                <div class="diary-text">
                    You completed your
                    <span class="term">consumer profile</span>
                    survey.

                    Based on your answers, your monthly expenses have
                    been calculated at
                    <strong>Ƶ450.11</strong>
                    and will appear in your bank statement each month.
                </div>

            </div>

            <div class="btn-row" style="margin-top:1.5rem">

                <a href="{{ route('bank.index') }}"
                   class="ca-btn-primary">
                    Go to My Bank Account →
                </a>

                <a href="{{ route('CitizenActivation.consumerProfile') }}"
                   class="ca-btn-secondary">
                    ← Back
                </a>

            </div>

        </div>

    </div>

</div>

@endsection