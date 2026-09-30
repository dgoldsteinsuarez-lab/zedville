@php
    use Carbon\Carbon;

    // Previous month dates
    $prevMonth = Carbon::now()->subMonth();
    $fromDate  = $prevMonth->copy()->startOfMonth()->format('Y-m-d');
    $toDate    = $prevMonth->copy()->endOfMonth()->format('Y-m-d');

    $statementUrl = url('/bank-account/banks-statement') .
        '?from_date=' . $fromDate .
        '&to_date=' . $toDate;
@endphp

<p><strong>Dear {{ $user->nickname }},</strong></p>

<p>
    Your <strong>{{ $prevMonth->format('F Y') }}</strong> bank statement is now ready.
</p>

<p>
    You can view your complete statement, including income, expenses, and balances, by clicking the link below:
</p>

<p>
    👉 <a href="{{ $statementUrl }}" target="_blank">
        View {{ $prevMonth->format('F Y') }} Bank Statement
    </a>
</p>

<p>
    <strong>Official Citizen ID:</strong> {{ $user->citizenId }}
</p>

<p>
    (You can also access this anytime from your Bank Account section.)
</p>

<br>

<p>
    Sincerely,<br>
    <strong>The Zedville Bank Team</strong>
</p>
