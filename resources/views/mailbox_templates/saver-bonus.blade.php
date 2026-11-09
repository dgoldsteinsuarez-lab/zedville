{{-- The City Hall's letter with a saver bonus (App\Services\SaverBonus), rendered in the reader's language when the
     message is opened (App\Models\Mailbox). $kind: 'welcome' (5 % of the Emergency Fund brought from the Rookie) or
     'quarter' (5 % of what was saved in the last three months); $amount. Hybrid classes: the facts, without the why. --}}
@php
    $pct = \App\Services\PriceRise::pct(\App\Services\SaverBonus::SHARE * 100, false);
    $max = zeds($kind === 'welcome' ? \App\Services\SaverBonus::WELCOME_MAX : \App\Services\SaverBonus::QUARTER_MAX);
@endphp

<p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
<p>{{ __('mail.saver-bonus.' . $kind, ['amount' => zeds((float) $amount), 'pct' => $pct, 'max' => $max]) }}</p>
@unless (\App\Support\Hybrid::on())
    <p>{{ __('mail.saver-bonus.why') }}</p>
@endunless
<p>{{ __('mail.saver-bonus.next', ['pct' => $pct]) }}</p>
<br>
<p>{{ __('mail.sincerely') }}<br><strong>{{ __('mail.city_hall') }}</strong></p>
