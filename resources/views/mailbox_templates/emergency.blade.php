{{-- An emergency's letter (App\Services\EmergencyService::arrive), from whoever bills it - the hospital, the repair
     shop, the family - rendered in the reader's language when the message is opened (App\Models\Mailbox). $emergency:
     its id. A health one says what the insurance left the student (Emergency::cover). Hybrid classes: the facts,
     without the why. --}}
@php
    $e = \App\Models\Emergency::find($emergency);
@endphp
@if ($e)
    @php
        $money = ['cost' => zeds((float) $e->cost), 'amount' => zeds((float) $e->amount), 'covered' => zeds((float) $e->cost - (float) $e->amount),
            'plan' => $e->plan ? __('insurance.plan_' . $e->plan) : '',
            'deductible' => $e->plan ? zeds(\App\Services\InsuranceService::PLANS[$e->plan]['deductible']) : ''];
    @endphp
    <p><strong>{{ __('mail.dear', ['name' => $user->display_name]) }}</strong></p>
    <p><strong>{{ __('emergency.kinds.' . $e->kind . '.title') }}.</strong> {{ __('emergency.kinds.' . $e->kind . '.text') }}</p>
    <p>{{ __('mail.emergency.' . (['none' => 'no_insurance', 'waiting' => 'waiting', 'deductible' => 'deductible', 'covered' => 'insured'][$e->cover() ?? ''] ?? 'cost'), $money) }}</p>
    <p>{{ __('mail.emergency.due', ['date' => $e->due_on->copy()->locale(app()->getLocale())->isoFormat('LL'), 'activity' => __('emergency.title')]) }}</p>
    @unless (\App\Support\Hybrid::on())
        <p>{{ __('mail.emergency.why') }}</p>
    @endunless
    <br>
    <p>{{ __('mail.sincerely') }}<br><strong>{{ __('mail.emergency.from.' . $e->kind) }}</strong></p>
@endif
