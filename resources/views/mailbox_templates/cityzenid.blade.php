{{-- Rendered in the reader's language when the message is opened (App\Models\Mailbox). --}}
<p><strong>{{ __('mail.dear', ['name' => $user->nickname ?: $user->name]) }}</strong></p>
<p>{!! __('mail.cityzenid.congrats') !!}</p>
<p><strong>{{ __('mail.citizen_id') }} {{ $user->citizenId }}</strong></p>
<p>{{ __('mail.cityzenid.check') }}</p>
<br><br>{{ __('mail.sincerely') }}
<br>{{ __('mail.city_hall') }}
