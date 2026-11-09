{{-- A fake email from the "Education Finance Department" (App\Services\FraudService, proposal «Fraudes»): its
     address has a 1 for an l, it doesn't say the student's name, it is in a hurry and its link opens a fake page.
     "Report as fraud" is the right answer. $fraud: its id; $script: verify, refund, prize, parcel or job. Once
     answered, it says how the student answered and links what happened. --}}
@php $f = \App\Models\Fraud::find($fraud); @endphp
@if ($f)
    @php $t = \App\Services\FraudService::amounts(); @endphp
    <p style="font-size:14px;color:#5C5C5C">{{ __('fraud.email_from') }}: {{ __('fraud.email_dept') }} &lt;{{ \App\Services\FraudService::FAKE_SENDER }}&gt;</p>
    <p><strong>{{ __('fraud.emails.' . $script . '.hello') }}</strong></p>
    <p>{{ __('fraud.emails.' . $script . '.text', $t) }}</p>
    @if ($f->status === 'open')
        <p><a href="{{ route('fraud.fake', $f->token) }}" style="display:inline-block;padding:10px 18px;border-radius:8px;background:#1F4FB8;color:#fff;font-weight:700;text-decoration:none">{{ __('fraud.emails.' . $script . '.button', $t) }}</a></p>
        <form method="POST" action="{{ route('fraud.report', $f->token) }}" style="margin-top:24px">
            @csrf
            <button type="submit" class="dangerBtn">⚠ {{ __('fraud.email_report') }}</button>
        </form>
    @else
        <p style="margin-top:20px"><a href="{{ route('fraud.lesson', $f->token) }}"><strong>{{ __($f->status === 'safe' ? 'fraud.safe_title' : 'fraud.fell_title') }}</strong> →</a></p>
    @endif
@endif
