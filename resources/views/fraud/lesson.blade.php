@extends('layouts.profile')

@section('title', $lesson['title'])

{{-- What happened with the fake email (FraudController::lesson; App\Services\FraudService::lesson): well spotted,
     or what falling for it cost - with the fraud's name, its signs (not for hybrid classes), what to do and the way
     to cover the loss. --}}
@section('content')
<div class="zl">
    <section class="zl-card {{ $lesson['status'] === 'safe' ? 'is-safe' : 'is-fell' }}">
        <p class="zl-icon" aria-hidden="true">{{ $lesson['status'] === 'safe' ? '🛡️' : '🎣' }}</p>
        <h1>{{ $lesson['title'] }}</h1>
        <p>{{ $lesson['text'] }}</p>
        <p>{{ $lesson['name'] }}</p>
        @if ($lesson['sign'])
            <p><strong>{{ __('fraud.signs_title') }}:</strong> {{ $lesson['sign'] }}</p>
        @endif
        <p><strong>{{ __('fraud.good_title') }}:</strong> {{ $lesson['good'] }}</p>
        <p class="zl-slogan">{{ $lesson['slogan'] }}</p>
        <div class="zl-actions">
            @if ($lesson['resolve'])
                <a href="{{ $lesson['resolve'] }}" class="themeBtn">{{ __('fraud.go_resolve') }}</a>
            @endif
            <a href="{{ route('profile.mailbox') }}" class="whiteBtn">{{ __('fraud.back') }}</a>
        </div>
    </section>
</div>
<style>
    .zl { max-width: 680px; margin: 0 auto; padding: 8px 4px 24px; color: #0F2A22; }
    .zl-card { background: #fff; border: 1px solid #D2DDDB; border-radius: 16px; padding: 20px; font-size: 16px; line-height: 1.5; }
    .zl-card.is-safe { border-color: #9FD9C3; background: #F0FBF7; }
    .zl-card.is-fell { border-color: #F2B8A0; background: #FFF8F4; }
    .zl-card h1 { font: 800 24px/1.25 "Manrope", sans-serif; margin: 0 0 10px; }
    .zl-card p { margin: 0 0 10px; }
    .zl-icon { font-size: 36px; line-height: 1; margin: 0 0 8px; }
    .zl-slogan { font-weight: 800; }
    .zl-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
</style>
@endsection
