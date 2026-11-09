{{-- Universal Bank's letter in the Intermediate's third month (App\Services\ZedDirecto::announce), rendered in the
     reader's language when the message is opened (App\Models\Mailbox): the central bank raised interest rates, so
     the Money Market now pays 6 % a year instead of 5 %. $month ('Y-m'): the month it starts. Hybrid classes: the
     facts, without the why. --}}
@php
    // "!" starts from the 1st: without it, opened on the 31st the month moved on
    $label = !empty($month) ? \Carbon\Carbon::createFromFormat('!Y-m', $month)->locale(app()->getLocale())->isoFormat('MMMM') : '';
    $pct = fn ($n) => \App\Services\PriceRise::pct($n, false);
    $rate = \App\Services\ZedDirecto::MONEY_MARKET_RAISED * 100;
    $old = \App\Services\ZedDirecto::MONEY_MARKET * 100;
@endphp

<p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
<p>{{ __('mail.rates-up.what', ['month' => $label, 'rate' => $pct($rate), 'old' => $pct($old)]) }}</p>
@unless (\App\Support\Hybrid::on())
    <p>{{ __('mail.rates-up.why') }}</p>
@endunless
<p>{{ __('mail.rates-up.real', ['index' => $pct(\App\Services\PriceRise::INDEX), 'real' => $pct($rate - \App\Services\PriceRise::INDEX)]) }}</p>
<br>
<p>{{ __('mail.sincerely') }}<br><strong>Universal Bank</strong></p>
