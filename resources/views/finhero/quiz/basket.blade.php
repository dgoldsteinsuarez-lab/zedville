{{-- Task 10, my shopping before and after: each line of the month's shopping at the old prices and the
     new ones (App\Services\Finhero\Inflation\MyBasket). --}}
<section class="zq-card" aria-labelledby="zq-basket">
    <h2 id="zq-basket">{{ __('inflation.basket.chart') }}</h2>
    <div class="zq-table-wrap">
        <table class="zq-table">
            <thead><tr><th scope="col"></th><th scope="col" class="zq-num">{{ __('prices.before') }}</th><th scope="col" class="zq-num">{{ __('prices.now') }}</th><th scope="col" class="zq-num">{{ __('prices.difference') }}</th></tr></thead>
            <tbody>
                @foreach ($stage['rows'] as $row)
                    <tr><td>{{ $row['name'] }}</td><td class="zq-num">{{ $row['before'] }}</td><td class="zq-num">{{ $row['after'] }}</td><td class="zq-num">{{ $row['diff'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
