{{-- Task 7, who will have more? Ana's and Bea's money at 45: what each put in (purple) and the interest
     (green) (App\Services\Finhero\CompoundInterest\EarlyStart). --}}
<section class="zq-card" aria-labelledby="zq-early">
    <h2 id="zq-early">{{ __('compound.early.chart') }}</h2>
    <div class="zq-bars">
        @foreach ($stage['people'] as $person)
            <div class="zq-bar-row">
                <span><b>{{ $person['name'] }}</b> · {{ __('compound.early.starts', ['age' => $person['from'], 'monthly' => $person['monthly_text']]) }}</span>
                <span class="zq-bar">
                    <span style="width:{{ $person['put_pct'] }}%;background:#6250d6"></span>
                    <span style="width:{{ $person['interest_pct'] }}%;background:#1baf7a"></span>
                </span>
                <b>{{ $person['total_text'] }}</b>
            </div>
        @endforeach
    </div>
    <p class="zq-legend">
        <span aria-hidden="true" style="color:#6250d6">■</span> {{ __('compound.early.put') }}
        · <span aria-hidden="true" style="color:#1baf7a">■</span> {{ __('compound.early.interest') }}
    </p>
</section>
