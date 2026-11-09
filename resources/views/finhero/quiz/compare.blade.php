{{-- Task 16, two loans side by side: the term, the APR and the instalment the bank gives; the student works out
     the totals (App\Services\Finhero\Credit\CompareLoans). --}}
<section class="zq-card" aria-labelledby="zq-compare">
    <h2 id="zq-compare">{{ __('credit.compare.chart') }}</h2>
    <div class="zq-table-wrap">
        <table class="zq-table">
            <thead><tr><td></td><th scope="col">{{ __('credit.compare.offer_a') }}</th><th scope="col">{{ __('credit.compare.offer_b') }}</th></tr></thead>
            <tbody>
                <tr><th scope="row">{{ __('credit.compare.row_months') }}</th><td>{{ $stage['offers']['a']['months'] }}</td><td>{{ $stage['offers']['b']['months'] }}</td></tr>
                <tr><th scope="row">{{ __('credit.compare.row_tae') }}</th><td>{{ $stage['offers']['a']['tae'] }}</td><td>{{ $stage['offers']['b']['tae'] }}</td></tr>
                <tr><th scope="row">{{ __('credit.compare.row_instalment') }}</th><td style="white-space:nowrap">{{ $stage['offers']['a']['instalment'] }}</td><td style="white-space:nowrap">{{ $stage['offers']['b']['instalment'] }}</td></tr>
            </tbody>
        </table>
    </div>
    <p class="zq-legend">{{ __('credit.compare.hint') }}</p>
</section>
