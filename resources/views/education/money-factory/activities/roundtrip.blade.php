{{-- Back from the trip: the exchange office's two prices for one unit of the student's currency, and their trip. --}}
<section class="zm-card">
    <div class="zm-price-grid">
        <div class="zm-board" aria-label="{{ __('money_tasks.roundtrip.office') }}">
            <div class="zm-board-head"><span class="zm-board-title">💱 {{ __('money_tasks.roundtrip.office') }}</span></div>
            <div class="zm-bureau">
                <span></span>
                <span class="zm-bureau-head">{{ __('money_tasks.roundtrip.we_sell') }}</span>
                <span class="zm-bureau-head">{{ __('money_tasks.roundtrip.we_buy') }}</span>
                <span class="zm-code">{{ $stage['code'] }}</span>
                <span class="zm-val">{{ $stage['sell'] }} Ƶ</span>
                <span class="zm-val">{{ $stage['buy'] }} Ƶ</span>
            </div>
            <p class="zm-board-note">{{ __('money_tasks.roundtrip.per_unit', ['code' => $stage['code'], 'name' => $stage['name']]) }}</p>
        </div>
        <div>
            <p>🧳 {{ $stage['story'] }}</p>
            @unless ($hybrid)
                <p class="zm-muted" style="margin:0">{{ __('money_tasks.roundtrip.note') }}</p>
            @endunless
        </div>
    </div>
</section>
