{{-- A charity's thank-you for the monthly donation (App\Services\DonationService), rendered in the
     reader's language when the message is opened (App\Models\Mailbox). $npo: the charity's group
     (animals, children...); $name: its name; $amount: the donation; $story: which donation to it this
     is, so each month a different animal or child writes (lang/<locale>/npo_letters.php). Hybrid
     classes: the thanks and what to do, without what a charity is. --}}
@php
    $stories = trans('npo_letters.' . ($npo ?? 'community'));
    $stories = is_array($stories) && $stories ? array_values($stories) : array_values((array) trans('npo_letters.community'));
    $letter = $stories[max(0, (int) ($story ?? 1) - 1) % count($stories)];
@endphp

<p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
<p>{{ str_replace(':amount', zeds((float) ($amount ?? 0)), $letter['text']) }}</p>
@unless (\App\Support\Hybrid::on())
    <p>{{ __('npo_letters.what') }}</p>
@endunless
<p>{{ __('npo_letters.next') }}</p>
<br>
<p>{{ __('npo_letters.signed') }}<br><strong>{{ $letter['from'] }}</strong><br>{{ ct('activities', $name ?? '') }}</p>
