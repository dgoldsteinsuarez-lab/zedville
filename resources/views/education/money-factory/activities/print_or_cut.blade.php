{{-- Print money or cut spending? Zedland's budget, and (not for hybrid classes) the four ways to close the gap. --}}
<section class="zm-card" aria-labelledby="zm-budget">
    <h2 id="zm-budget"><span class="zm-ico" aria-hidden="true">🏛️</span>{{ __('money_tasks.print_or_cut.budget') }}</h2>
    <div class="zm-bars">
        @foreach (['spending' => '#6250d6', 'taxes' => '#1baf7a'] as $bar => $colour)
            <div class="zm-bar-row">
                <span class="zm-bar-label">{{ __('money_tasks.print_or_cut.' . $bar) }}</span>
                <span class="zm-bar"><span style="width:{{ $stage[$bar] / $stage['spending'] * 100 }}%;background:{{ $colour }}"></span></span>
                <b>{{ __('money_tasks.print_or_cut.millions', ['amount' => $stage[$bar]]) }}</b>
            </div>
        @endforeach
    </div>
    @unless ($hybrid)
        <ul class="zm-points" style="margin-top:14px">
            <li><span aria-hidden="true">✂️</span>{{ __('money_tasks.print_or_cut.way_cut') }}</li>
            <li><span aria-hidden="true">🧾</span>{{ __('money_tasks.print_or_cut.way_taxes') }}</li>
            <li><span aria-hidden="true">📄</span>{{ __('money_tasks.print_or_cut.way_bonds') }}</li>
            <li><span aria-hidden="true">🖨️</span>{{ __('money_tasks.print_or_cut.way_print') }}</li>
        </ul>
        <a class="zm-link" href="{{ route('education.money-factory.enter') }}">{{ __('money.enter_card') }} →</a>
    @endunless
</section>
