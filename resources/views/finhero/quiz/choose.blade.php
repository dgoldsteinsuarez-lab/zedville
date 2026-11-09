{{-- Task 15, the two health insurance plans side by side (App\Services\InsuranceService::PLANS) and the
     student's own plan, if they have one (App\Services\Finhero\Insurance\ChoosePlan::stage). --}}
@php
    $current = $stage['current'];
    $day = fn ($d) => \App\Services\Finhero\Insurance\ChoosePlan::date($d);
@endphp
<section class="zq-card" aria-labelledby="zq-plans">
    <h2 id="zq-plans">{{ __('insurance.choose.chart') }}</h2>
    <div class="zq-table-wrap">
        <table class="zq-table zq-plans-table">
            <thead>
                <tr><td></td>
                    @foreach ($stage['plans'] as $key => $plan)
                        <th scope="col">{{ __('insurance.plan_' . $key) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach (['premium', 'deductible', 'copay', 'wait'] as $row)
                    <tr>
                        {{-- the term, and under it what it means --}}
                        <th scope="row">{{ __('insurance.row_' . $row) }}
                            @if (\Illuminate\Support\Facades\Lang::has('insurance.note_' . $row))
                                <span class="zq-note">{{ __('insurance.note_' . $row) }}</span>
                            @endif
                        </th>
                        @foreach ($stage['plans'] as $plan)
                            <td class="{{ $row === 'wait' ? '' : 'zq-amount' }}">{{ $plan[$row] }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($current)
        <p class="zq-legend">
            @if ($current['auto'])
                {{ __('insurance.choose.have_auto', ['plan' => __('insurance.plan_' . $current['plan']), 'date' => $day($current['since'])]) }}
            @else
                {{ __('insurance.choose.have', ['plan' => __('insurance.plan_' . $current['plan']), 'date' => $day($current['since'])]) }}
                {{ $stage['can_choose'] ? __('insurance.choose.change_now') : __('insurance.choose.change_from', ['date' => $day($current['from'])]) }}
            @endif
        </p>
    @endif
</section>

<style>
    .zq-note { display: block; font-weight: 400; font-size: 13px; color: #5E7069; }
    /* long words (Komplettschutz, Gesundheitsproblem) break at their soft hyphen instead of widening the
       table; an amount stays on one line */
    .zq-plans-table th, .zq-plans-table td { -webkit-hyphens: manual; hyphens: manual; }
    .zq-plans-table .zq-amount { white-space: nowrap; }
</style>
