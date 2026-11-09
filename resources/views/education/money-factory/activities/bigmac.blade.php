{{-- The Big Mac index: The Economist's prices (July 2026), and in Zeds with the board's rates - one left for the student. --}}
<section class="zm-card" aria-labelledby="zm-bigmac">
    <h2 id="zm-bigmac"><span class="zm-ico" aria-hidden="true">🍔</span>{{ __('money_tasks.bigmac.table') }}</h2>
    <div style="overflow-x:auto">
        <table class="zm-table">
            <thead>
                <tr>
                    <th>{{ __('money_tasks.bigmac.country') }}</th>
                    <th class="num">{{ __('money_tasks.bigmac.price') }}</th>
                    <th class="num">{{ __('money.one_zed') }}</th>
                    <th class="num">{{ __('money_tasks.bigmac.in_zeds') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background:#F2FBF7">
                    <td><strong>Zedville</strong></td>
                    <td class="num">–</td>
                    <td class="num">–</td>
                    <td class="num"><strong>{{ $stage['zedville'] }}</strong></td>
                </tr>
                @foreach ($stage['rows'] as $row)
                    <tr>
                        <td>{{ $row['country'] }}</td>
                        <td class="num">{{ $row['price'] }}</td>
                        <td class="num">{{ $row['rate'] }}</td>
                        <td class="num">@if ($row['zeds']){{ $row['zeds'] }}@else<strong class="zm-ask">?</strong>@endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @unless ($hybrid)
        <p class="zm-muted" style="margin:10px 0 0">{{ __('money_tasks.bigmac.how') }}</p>
    @endunless
</section>
