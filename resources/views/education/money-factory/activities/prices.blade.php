{{-- How much is it in other currencies? The Zedville price and the board's rates the student needs. --}}
<section class="zm-card">
    <div class="zm-price-grid">
        <div class="zm-item">
            <span class="zm-item-ico" aria-hidden="true">{{ $stage['icon'] }}</span>
            <div>
                <strong>{{ $stage['name'] }}</strong>
                <span class="zm-item-price">{{ $stage['price'] }}</span>
            </div>
        </div>
        <div class="zm-board">
            <div class="zm-board-head"><span class="zm-board-title">{{ __('money.rates_title') }}</span></div>
            @foreach ($stage['rates'] as $rate)
                <div class="zm-mini-row">
                    <span class="zm-code">{{ $rate['code'] }}</span>
                    <span class="zm-cur">{{ $rate['name'] }}</span>
                    <span class="zm-val">{{ __('money.one_zed') }} {{ $rate['rate'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
