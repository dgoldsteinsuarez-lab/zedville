{{-- Rendered in the reader's language when the message is opened (App\Models\Mailbox). --}}
@php
    $bankAccount = \App\Models\BankAccount::find($bank_account_id ?? null) ?? \App\Models\BankAccount::where('student_id', $user->id)->first();
@endphp
<p>{{ __('mail.bank-account-ready.intro') }}</p>
<p>{{ __('mail.bank-account-ready.visit') }}</p>
<ul style="list-style: none;">
    @foreach (__('mail.bank-account-ready.features') as $feature)
        <li> - {{ $feature }}</li>
    @endforeach
</ul>
<p>{{ __('mail.bank-account-ready.starts') }}</p>
<p><strong>{{ __('mail.bank-account-ready.online') }}</strong></p>
<p><strong>{{ __('mail.bank-account-ready.welcome', ['name' => $user->nickname ?: $user->name]) }}</strong></p>
<p><strong>{{ __('mail.bank-account-ready.account') }}</strong> {{ __('mail.bank-account-ready.student') }}</p>
@if ($bankAccount)
    <p><strong>{{ __('mail.bank-account-ready.summary') }}</strong></p>
    <ul>
        <li><strong>{{ __('mail.bank-account-ready.balance') }}</strong> Ƶ{{ number_format((float) $bankAccount->primary_savings_account_amount, 2) }}</li>
        <li><strong>{{ __('mail.bank-account-ready.number') }}</strong> XXXX-XXXX-XXXX-{{ substr((string) $bankAccount->primary_savings_account_number, -4) }}</li>
        <li><strong>{{ __('mail.bank-account-ready.card') }}</strong> XXXX-XXXX-XXXX-{{ substr((string) $bankAccount->card_number, -4) }}</li>
        <li><strong>{{ __('mail.bank-account-ready.latest') }}</strong> {{ __('mail.bank-account-ready.none') }}</li>
    </ul>
@endif
<p><strong>{{ __('mail.bank-account-ready.available') }}</strong></p>
<p><strong>{{ __('mail.bank-account-ready.statement_title') }}</strong></p>
<ul><li>{{ __('mail.bank-account-ready.statement') }}</li></ul>
<p><strong>{{ __('mail.bank-account-ready.card_title') }}</strong></p>
<ul>
    @foreach (__('mail.bank-account-ready.card_items') as $line)<li>{{ $line }}</li>@endforeach
</ul>
<p><strong>{{ __('mail.bank-account-ready.transfers_title') }}</strong></p>
<ul>
    @foreach (__('mail.bank-account-ready.transfers') as $line)<li>{{ $line }}</li>@endforeach
</ul>
<p><strong>{{ __('mail.bank-account-ready.savings_title') }}</strong></p>
<ul>
    @foreach (__('mail.bank-account-ready.savings') as $line)<li>{{ $line }}</li>@endforeach
</ul>
<p><strong>{{ __('mail.bank-account-ready.alerts_title') }}</strong></p>
<ul><li>{{ __('mail.bank-account-ready.alerts') }}</li></ul>
<p>{{ __('mail.bank-account-ready.questions') }}</p>
<p><strong>{{ __('mail.bank-account-ready.thanks') }}</strong></p>
