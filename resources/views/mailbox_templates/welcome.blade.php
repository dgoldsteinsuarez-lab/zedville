{{-- Rendered in the reader's language when the message is opened (App\Models\Mailbox). --}}
@php $name = e($user->nickname ?: $user->name); @endphp
<p><strong>{!! __('mail.welcome.hello', ['name' => $name]) !!}</strong></p>
<p>{!! __('mail.welcome.newest') !!}</p>
<p><strong>{{ __('mail.welcome.citizen_id') }}</strong> {{ $user->citizenId }}</p>
<p><strong>{{ __('mail.welcome.how_title') }}</strong> {!! __('mail.welcome.how') !!}</p>
<p><strong>{{ __('mail.welcome.start_title') }}</strong></p>
<ul style="list-style: none;">
    <li> 1. {!! __('mail.welcome.step1') !!}</li>
    <li> 2. {!! __('mail.welcome.step2') !!}</li>
    <li> 3. {{ __('mail.welcome.step3') }}</li>
</ul>
<ul>
    <li>{!! __('mail.welcome.city_hall') !!}</li>
    <li>{!! __('mail.welcome.efd') !!}</li>
    <li>{!! __('mail.welcome.profile') !!}</li>
</ul>
<hr>
<p>{{ __('mail.welcome.closing') }}</p>
<br><br>{{ __('mail.regards') }}
<br>{{ __('mail.city_hall') }}
