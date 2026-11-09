{{-- Rendered in the reader's language when the message is opened (App\Models\Mailbox). --}}
<p>{!! __('mail.direct-deposit.hello', ['name' => e($user->nickname ?: $user->name)]) !!}</p>
<p>{!! __('mail.direct-deposit.intro') !!}</p>
<p><strong>{{ __('mail.direct-deposit.title') }}</strong></p>
<p>{{ __('mail.direct-deposit.text') }}</p>
<p>{{ __('mail.direct-deposit.authorize') }}</p>
<ul>
    <li>{{ __('mail.direct-deposit.item') }}</li>
</ul>
<br>
<a href="{{ route('bank.direct_deposite') }}" class="themeBtn mt-6 inline-block">{{ __('mail.direct-deposit.button') }}</a>
