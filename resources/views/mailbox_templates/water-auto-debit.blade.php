{{-- Rendered in the reader's language when the message is opened (App\Models\Mailbox). --}}
<p>{{ __('mail.hi') }}</p>
<p>{{ __('mail.utility.intro') }}</p>
<p><strong>{{ __('mail.utility.steps') }}</strong></p>
<ul>
    <li>{{ __('mail.utility.step1') }}</li>
    <li>{{ __('mail.utility.step2') }}</li>
    <li>{{ __('mail.utility.step3') }}</li>
    <li>{{ __('mail.utility.step4') }}</li>
</ul>
<br>
<a href="{{ route('bank.water_auto_debit') }}" class="themeBtn mt-6 inline-block">{{ __('mail.utility.button') }}</a>
