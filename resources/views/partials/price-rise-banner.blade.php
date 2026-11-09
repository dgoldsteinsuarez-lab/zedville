{{-- The shops' notice in the Intermediate, from the month the prices rose (App\Services\PriceRise): the
     prices shown are the new ones; the Statistics Office says what rose and why. --}}
@php $priceRiseSince = \App\Services\PriceRise::since(auth()->user()); @endphp
@if ($priceRiseSince)
    <div class="zp-banner" role="note">
        <span aria-hidden="true">📈</span>
        <span>{{ __('prices.banner', ['month' => $priceRiseSince->copy()->locale(app()->getLocale())->isoFormat('MMMM'), 'index' => \App\Services\PriceRise::pct(\App\Services\PriceRise::INDEX, false)]) }}</span>
        <a href="{{ route('prices.index') }}">{{ __('prices.banner_link') }}</a>
    </div>
    <style>
        .zp-banner { display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; margin: 0 0 14px; padding: 10px 14px;
            border: 1px solid #F1C9A9; border-radius: 12px; background: #FFF6EE; color: #0F2A22; font-size: 15px; line-height: 1.4; }
        .zp-banner a { font-weight: 700; color: #9A3B10; text-decoration: underline; }
    </style>
@endif
