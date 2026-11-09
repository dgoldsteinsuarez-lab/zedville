{{-- The Intermediate and after (App\Services\SaverService; Daniel, 2026-11-04: "los 20% de ahorro no
     es regla fija, él deberá ajustarlo cada mes si lo necesita"): what the student has saved this month,
     in Ƶ and in % of the salary, and the share of the salary that goes to their Emergency Fund on
     payday, which they change when they need to. --}}
@php
    // "12.5%" in English, "12,5 %" in Spanish, French and German
    $pct = function ($n) {
        $s = rtrim(rtrim(number_format((float) $n, 1, '.', ''), '0'), '.');

        return app()->getLocale() === 'en' ? $s . '%' : str_replace('.', ',', $s) . ' %';
    };
@endphp
<section class="zb-card" style="margin-top:16px" aria-labelledby="zb-saver">
    <h2 id="zb-saver">{{ __('bank_ui.saver_title') }}</h2>
    <div class="zb-grid zb-grid--2" style="align-items:center">
        <div>
            <div class="zb-amount">{{ zeds($saved['amount']) }}</div>
            {{-- once Zed Directo is open (App\Services\ZedDirecto), what goes there is saved too --}}
            <p class="zb-muted" style="margin:0">{{ __(\App\Services\ZedDirecto::open($user) ? 'bank_ui.saver_saved_zd' : 'bank_ui.saver_saved', ['percent' => $pct($saved['percent'])]) }}</p>
        </div>
        <form method="POST" action="{{ route('bank.fund_percent') }}">
            @csrf
            <div class="zb-field" style="margin-bottom:8px">
                <label for="zb-fund-percent">{{ __('bank_ui.saver_share') }}</label>
                <select id="zb-fund-percent" name="percent" class="form-control">
                    @foreach (\App\Services\SaverService::OPTIONS as $option)
                        <option value="{{ $option }}" @selected((int) round($fundPercent) === $option)>{{ $pct($option) }}</option>
                    @endforeach
                </select>
                <p class="zb-help">{{ __('bank_ui.saver_share_help') }}</p>
            </div>
            <button type="submit" class="whiteBtn">{{ __('bank_ui.saver_save') }}</button>
        </form>
    </div>
</section>
