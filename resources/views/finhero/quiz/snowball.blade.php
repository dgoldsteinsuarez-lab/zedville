{{-- Task 6, the snowball: what the amount is after 1, 5, 10 and 20 years with compound interest (green)
     and, under each year, with simple interest (grey) (App\Services\Finhero\CompoundInterest\Snowball). --}}
<section class="zq-card" aria-labelledby="zq-snowball">
    <h2 id="zq-snowball">{{ __('compound.snowball.chart') }}</h2>
    <div class="zq-bars">
        @foreach ($stage['rows'] as $row)
            <div class="zq-bar-row">
                <span>{{ trans_choice('compound.snowball.years', $row['years'], ['n' => $row['years']]) }}</span>
                <span class="zq-bar"><span style="width:{{ $row['compound_pct'] }}%;background:#1baf7a"></span></span>
                <b>{{ $row['compound'] }}</b>
            </div>
            <div class="zq-bar-row">
                <span></span>
                <span class="zq-bar" style="height:10px"><span style="width:{{ $row['simple_pct'] }}%;background:#9aa9a4"></span></span>
                <span>{{ $row['simple'] }}</span>
            </div>
        @endforeach
    </div>
    <p class="zq-legend">
        <span aria-hidden="true" style="color:#1baf7a">■</span> {{ __('compound.snowball.compound') }}
        · <span aria-hidden="true" style="color:#9aa9a4">■</span> {{ __('compound.snowball.simple') }}
    </p>
</section>
