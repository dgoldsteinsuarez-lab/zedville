{{-- The City Hall's letter for the month-end fine (App\Services\MonthEndPenaltyService), rendered in the
     reader's language when the message is opened (App\Models\Mailbox). $amount: the fine; $month ('Y-m'):
     the month it was for. Hybrid classes: the facts and what to do, without the why. --}}
@php
    // "!" starts from the 1st: without it, opened on the 31st the month moved on
    $label = !empty($month) ? \Carbon\Carbon::createFromFormat('!Y-m', $month)->locale(app()->getLocale())->isoFormat('MMMM') : '';
@endphp

<p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
<p>{{ __('mail.city-hall-fine.what', ['month' => $label, 'amount' => zeds((float) ($amount ?? 0))]) }}</p>
@unless (\App\Support\Hybrid::on())
    <p>{{ __('mail.city-hall-fine.why') }}</p>
@endunless
<p>{{ __('mail.city-hall-fine.next') }}</p>
<br>
<p>{{ __('mail.sincerely') }}<br><strong>{{ __('mail.city_hall') }}</strong></p>
