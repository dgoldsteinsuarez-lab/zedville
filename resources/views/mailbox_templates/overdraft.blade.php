{{-- Universal Bank's letter when something it had to pay put the current account below zero (App\Services\Overdraft),
     rendered in the reader's language when the message is opened (App\Models\Mailbox). $balance: the balance then;
     $line: what was paid (a bank line). Hybrid classes: the facts and what to do, without the why. --}}
@php
    $what = \Illuminate\Support\Facades\Lang::has('transactions.lines.' . $line) ? __('transactions.lines.' . $line) : $line;
@endphp

<p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
<p>{{ __('mail.overdraft.what', ['what' => $what, 'balance' => zeds((float) $balance)]) }}</p>
<p>{{ __('mail.overdraft.cost', ['tae' => \App\Services\PriceRise::pct(\App\Services\Overdraft::TAE * 100, false), 'fee' => zeds(\App\Services\Overdraft::FEE)]) }}</p>
@unless (\App\Support\Hybrid::on())
    <p>{{ __('mail.overdraft.why') }}</p>
@endunless
<p>{{ __('mail.overdraft.next') }}</p>
<br>
<p>{{ __('mail.sincerely') }}<br><strong>Universal Bank</strong></p>
