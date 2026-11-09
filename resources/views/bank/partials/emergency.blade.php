{{-- An unexpected expense still to pay (App\Services\EmergencyService): what it is, what the student pays and by
     when, with the way to «Resuelve tu imprevisto». --}}
@php $pendingEmergency = \App\Services\EmergencyService::open($user->id); @endphp
@if ($pendingEmergency && $pendingEmergency->status === 'pending')
    <section class="zb-card zb-emergency" role="alert" style="margin:0 0 14px">
        <p style="margin:0 0 8px"><span aria-hidden="true">{{ \App\Services\EmergencyService::KINDS[$pendingEmergency->kind]['icon'] }}</span>
            {{ __('emergency.bank_notice', ['title' => __('emergency.kinds.' . $pendingEmergency->kind . '.title'), 'amount' => zeds((float) $pendingEmergency->amount),
                'date' => $pendingEmergency->due_on->copy()->locale(app()->getLocale())->isoFormat('LL')]) }}</p>
        <div class="zb-actions"><a href="{{ route('emergency.show') }}" class="themeBtn">{{ __('emergency.bank_go') }}</a></div>
    </section>
    <style>.zb-emergency { border-color: #F2B8A0; background: #FFF8F4; }</style>
@endif
