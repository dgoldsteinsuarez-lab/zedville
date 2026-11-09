{{-- Zed Directo's letter in the Intermediate's third month (App\Services\ZedDirecto::announce), rendered in the
     reader's language when the message is opened (App\Models\Mailbox): a new bank opens, what it pays, compare
     before choosing. $month ('Y-m'): the month it opens. --}}
@php
    // "!" starts from the 1st: without it, opened on the 31st the month moved on
    $label = !empty($month) ? \Carbon\Carbon::createFromFormat('!Y-m', $month)->locale(app()->getLocale())->isoFormat('MMMM') : '';
    $pct = fn (string $key) => \App\Services\PriceRise::pct(\App\Services\SavingsLedger::rate($key) * 100, false);
@endphp

<p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
<p>{{ __('mail.zed-directo-open.what', ['month' => $label]) }}</p>
<p>{{ __('mail.zed-directo-open.offer', ['savings' => $pct('zd_savings'), 'deposit' => $pct('zd_deposit')]) }}</p>
<p>{{ __('mail.zed-directo-open.compare') }} <a href="{{ route('zeddirecto.index') }}">{{ __('mail.zed-directo-open.link') }}</a></p>
<br>
<p>{{ __('mail.sincerely') }}<br><strong>{{ __('mail.zed-directo-open.sign') }}</strong></p>
