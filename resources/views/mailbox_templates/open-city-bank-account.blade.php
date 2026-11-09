{{-- Rendered in the reader's language when the message is opened (App\Models\Mailbox). --}}
<p>{{ __('mail.open-city-bank-account.dear') }}</p>
<p>{{ __('mail.open-city-bank-account.why') }}</p>
<ul>
    @foreach (__('mail.open-city-bank-account.reasons') as $line)<li>{{ $line }}</li>@endforeach
</ul>
<p>{{ __('mail.open-city-bank-account.benefits_title') }}</p>
<ul>
    @foreach (__('mail.open-city-bank-account.benefits') as $line)<li>{{ $line }}</li>@endforeach
</ul>
<p>{{ __('mail.open-city-bank-account.start_title') }}</p>
<ul style="list-style: none;">
    @foreach (__('mail.open-city-bank-account.start') as $i => $line)<li>{{ $i + 1 }}. {{ $line }}</li>@endforeach
</ul>
<p>{{ __('mail.open-city-bank-account.questions') }}</p>
<br><br>{{ __('mail.regards') }}
<br>{{ __('mail.open-city-bank-account.signature') }}
