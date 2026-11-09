{{-- Task 11, what is my salary worth? What the same salary buys before the rise (100%) and now
     (App\Services\Finhero\Inflation\MySalary). --}}
<section class="zq-card" aria-labelledby="zq-salary">
    <h2 id="zq-salary">{{ __('inflation.salary.chart') }}</h2>
    <div class="zq-bars">
        <div class="zq-bar-row">
            <span>{{ __('inflation.salary.before') }}</span>
            <span class="zq-bar"><span style="width:100%;background:#1F6FAE"></span></span>
            <b>{{ $stage['full_text'] }}</b>
        </div>
        <div class="zq-bar-row">
            <span>{{ __('inflation.salary.now') }}</span>
            <span class="zq-bar"><span style="width:{{ $stage['part'] }}%;background:#C8541E"></span></span>
            <b>{{ $stage['part_text'] }}</b>
        </div>
    </div>
</section>
