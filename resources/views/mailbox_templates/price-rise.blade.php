{{-- The City Hall's letter about the Intermediate's price rise (App\Services\PriceRise::announce), rendered in the
     reader's language when the message is opened (App\Models\Mailbox). $month ('Y-m'): the month the prices rose;
     $before, $after, $extra: the student's bills and monthly shopping at the old and the new prices. Hybrid
     classes: the facts and where to look, without the why. --}}
@php
    // "!" starts from the 1st: without it, opened on the 31st the month moved on
    $label = !empty($month) ? \Carbon\Carbon::createFromFormat('!Y-m', $month)->locale(app()->getLocale())->isoFormat('MMMM') : '';
    $index = \App\Services\PriceRise::pct(\App\Services\PriceRise::INDEX, false);
@endphp

<p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
<p>{{ __('mail.price-rise.what', ['month' => $label, 'index' => $index]) }}</p>
@unless (\App\Support\Hybrid::on())
    <p>{{ __('mail.price-rise.why', ['oil' => \App\Services\PriceRise::pct(\App\Services\PriceRise::GROUPS['oil'], false)]) }}</p>
@endunless
<p>{{ __('mail.price-rise.you', ['before' => zeds((float) ($before ?? 0)), 'after' => zeds((float) ($after ?? 0)), 'extra' => zeds((float) ($extra ?? 0))]) }}</p>
<p>{{ __('mail.price-rise.next') }} <a href="{{ route('prices.index') }}">{{ __('mail.price-rise.link') }}</a></p>
<br>
<p>{{ __('mail.sincerely') }}<br><strong>{{ __('mail.city_hall') }}</strong></p>
