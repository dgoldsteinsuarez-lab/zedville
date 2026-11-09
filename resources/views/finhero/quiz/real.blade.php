{{-- Task 13, what is my money worth? In a year: the money with its interest, and what the same things cost
     then (App\Services\Finhero\Inflation\RealInterest). --}}
<section class="zq-card" aria-labelledby="zq-real">
    <h2 id="zq-real">{{ __('inflation.real.chart') }}</h2>
    <div class="zq-bars">
        @foreach ($stage['bars'] as $bar)
            <div class="zq-bar-row">
                <span>{{ $bar['label'] }}</span>
                <span class="zq-bar"><span style="width:{{ $bar['pct'] }}%;background:{{ $bar['kind'] === 'have' ? '#1F6FAE' : '#C8541E' }}"></span></span>
                <b>{{ $bar['value'] }}</b>
            </div>
        @endforeach
    </div>
</section>
