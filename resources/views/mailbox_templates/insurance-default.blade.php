{{-- The City Hall's letter to whoever had no health insurance when their first month of the level ended: it gives
     them the Basic plan (App\Services\InsuranceService::assignDefault), rendered in the reader's language when the
     message is opened (App\Models\Mailbox). $plan. Hybrid classes: the facts, without the why. --}}
<p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
<p>{{ __('mail.insurance-default.given', ['plan' => __('insurance.plan_' . $plan), 'premium' => zeds(\App\Services\InsuranceService::PLANS[$plan]['premium'])]) }}</p>
@unless (\App\Support\Hybrid::on())
    <p>{{ __('mail.insurance-default.why') }}</p>
@endunless
<p>{{ __('mail.insurance-default.next', ['task' => __('insurance.choose.title')]) }}</p>
<br>
<p>{{ __('mail.sincerely') }}<br><strong>{{ __('mail.city_hall') }}</strong></p>
