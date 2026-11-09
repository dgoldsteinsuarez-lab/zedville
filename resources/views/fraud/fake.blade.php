@extends('layouts.profile')

@section('title', __('fraud.fake_title'))

{{-- The fake page the fake email's link opens (FraudController::fake; App\Services\FraudService): it looks like the
     Education Finance Department's, its address is the fake one, and it asks for nothing real - pressing its button
     is falling for it, and the lesson starts. --}}
@section('content')
<div class="zf">
    <div class="zf-browser" aria-hidden="true">
        <span class="zf-dot"></span><span class="zf-dot"></span><span class="zf-dot"></span>
        <span class="zf-url">https://educacion-financiera.zedvi11e.zv/verificar</span>
    </div>
    <section class="zf-card">
        <p class="zf-brand">🏛 {{ __('fraud.email_dept') }}</p>
        <h1>{{ __('fraud.fake_title') }}</h1>
        <p>{{ __('fraud.emails.' . $fraud->script . '.text', \App\Services\FraudService::amounts()) }}</p>
        <p class="zf-small">{{ __('fraud.fake_text') }}</p>
        <form method="POST" action="{{ route('fraud.verify', $fraud->token) }}">
            @csrf
            <button type="submit" class="zf-btn">{{ __('fraud.emails.' . $fraud->script . '.button', \App\Services\FraudService::amounts()) }}</button>
        </form>
    </section>
</div>
<style>
    .zf { max-width: 640px; margin: 0 auto; padding: 8px 4px 24px; }
    .zf-browser { display: flex; align-items: center; gap: 6px; background: #E9EDF2; border: 1px solid #CDD5DF; border-bottom: 0;
        border-radius: 12px 12px 0 0; padding: 8px 12px; }
    .zf-dot { width: 10px; height: 10px; border-radius: 50%; background: #B8C2CF; }
    .zf-url { margin-left: 8px; font: 13px/1.3 monospace; color: #3B4552; background: #fff; border-radius: 6px; padding: 4px 8px;
        overflow-wrap: anywhere; flex: 1; }
    .zf-card { background: #fff; border: 1px solid #CDD5DF; border-radius: 0 0 12px 12px; padding: 22px 20px; font-size: 16px; line-height: 1.5; }
    .zf-card h1 { font: 800 24px/1.25 "Manrope", sans-serif; margin: 8px 0 12px; }
    .zf-brand { font-weight: 700; color: #1F4FB8; margin: 0; }
    .zf-small { font-size: 14px; color: #5E6B78; }
    .zf-btn { padding: 12px 22px; border-radius: 8px; background: #1F4FB8; color: #fff; font-weight: 700; border: 0; cursor: pointer; font-size: 16px; }
</style>
@endsection
