{{-- Rendered in the reader's language when the message is opened (App\Models\Mailbox).
     $month ('Y-m') is the statement's month, saved when it was sent. --}}
@php
    // "!" starts from the 1st: without it, opened on the 31st the mail pointed at the next month
    $statementMonth = !empty($month) ? \Carbon\Carbon::createFromFormat('!Y-m', $month) : \Carbon\Carbon::now()->startOfMonth()->subMonth();
    $label = $statementMonth->copy()->locale(app()->getLocale())->isoFormat(__('transactions.month_year'));
    $statementUrl = route('bank.bank_statement_show', ['month' => $statementMonth->format('Y-m')]);
@endphp

<p><strong>{{ __('mail.dear', ['name' => $user->nickname ?: $user->name]) }}</strong></p>
<p>{!! __('mail.monthly-statement.ready', ['month' => e($label)]) !!}</p>
<p>{{ __('mail.monthly-statement.view_text') }}</p>
<p>👉 <a href="{{ $statementUrl }}" target="_blank">{{ __('mail.monthly-statement.link', ['month' => $label]) }}</a></p>
<p><strong>{{ __('mail.citizen_id') }}</strong> {{ $user->citizenId }}</p>
<p>{{ __('mail.monthly-statement.anytime') }}</p>
<br>
<p>{{ __('mail.sincerely') }}<br><strong>{{ __('mail.bank_team') }}</strong></p>
