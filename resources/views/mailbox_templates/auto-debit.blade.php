{{-- Rendered in the reader's language when the message is opened (App\Models\Mailbox). --}}
<p>{{ __('mail.auto-debit.intro') }}</p>
<p>{{ __('mail.auto-debit.authorize') }}</p>
<ul>
    <li>{{ __('mail.auto-debit.item') }}</li>
</ul>
<br>
<a href="{{ route('bank.auto_debit_authorization_form') }}" class="themeBtn mt-6 inline-block">{{ __('mail.auto-debit.button') }}</a>
