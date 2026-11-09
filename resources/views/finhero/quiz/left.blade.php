{{-- Task 12, what is left to save: the month's spending at the old prices and the new ones, and what is
     left of the salary (App\Services\Finhero\Inflation\LeftToSave). --}}
<section class="zq-card" aria-labelledby="zq-left">
    <h2 id="zq-left">{{ __('inflation.left.chart') }}</h2>
    <div class="zq-table-wrap">
        <table class="zq-table">
            <thead><tr><th scope="col"></th><th scope="col" class="zq-num">{{ __('prices.before') }}</th><th scope="col" class="zq-num">{{ __('prices.now') }}</th></tr></thead>
            <tbody>
                @foreach ($stage['rows'] as $row)
                    <tr><td>{{ $row['name'] }}</td><td class="zq-num">{{ $row['before'] }}</td><td class="zq-num">{{ $row['after'] }}</td></tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><th scope="row">{{ __('inflation.left.spent') }}</th><td class="zq-num">{{ $stage['spent']['before'] }}</td><td class="zq-num">{{ $stage['spent']['after'] }}</td></tr>
                <tr><th scope="row">{{ __('inflation.left.left') }}</th><td class="zq-num">{{ $stage['left']['before'] }}</td><td class="zq-num">{{ $stage['left']['after'] }}</td></tr>
            </tfoot>
        </table>
    </div>
</section>
