{{-- Universal Bank's letter to a student who fell for a fraud (App\Services\FraudService::answer): what they did,
     what they lost, how to cover it and what to do next time; rendered in the reader's language when the message is
     opened (App\Models\Mailbox). $fraud: its id. Hybrid classes: the facts, without the signs and the real-life note. --}}
@php
    $f = \App\Models\Fraud::find($fraud);
    $e = $f && $f->emergency_id ? \App\Models\Emergency::find($f->emergency_id) : null;
@endphp
@if ($f)
    <p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
    <p>{{ $f->channel === 'call' ? __('mail.fraud-loss.call', ['caller' => __('fraud.callers.' . $f->script)]) : __('mail.fraud-loss.email') }}</p>
    <p><strong>{{ __('mail.fraud-loss.lost', ['amount' => zeds((float) optional($e)->amount)]) }}</strong> {{ __('mail.fraud-loss.cover', ['activity' => __('emergency.title')]) }}</p>
    @unless (\App\Support\Hybrid::on())
        <p><strong>{{ __('fraud.signs_title') }}:</strong> {{ __('fraud.signs.' . $f->script) }}</p>
    @endunless
    <p><strong>{{ __('fraud.good_title') }}:</strong> {{ __('fraud.good.' . $f->script, ['phone' => \App\Services\FraudService::BANK_PHONE]) }}</p>
    @unless (\App\Support\Hybrid::on())
        <p>{{ __('mail.fraud-loss.real') }}</p>
    @endunless
    <p><strong>{{ __('fraud.slogan') }}</strong></p>
    <br>
    <p>{{ __('mail.sincerely') }}<br><strong>Universal Bank</strong></p>
@endif
