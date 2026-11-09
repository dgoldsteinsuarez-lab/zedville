@extends('layouts.profile')

@section('title', __('money.title'))

{{-- The Money Factory (Daniel, 2026-11-02): Zedville, capital of Zedland, prints
     the Zed. First a picture to explore - what money is, why it's printed and how
     a banknote is made open from it (_scene) - then the currency exchange with its
     live board and the student's exchange tasks; the money supply and too little
     or too much money are pages apart. A hybrid class is informed, not told why,
     with short texts. --}}
@section('content')
@include('education.money-factory._styles')
@php
    $picked = $tasks->first(fn ($t) => (int) $t->id === $task && !$t->done);
    $config = [
        'board' => $board,
        'balance' => round($balance, 2),
        'hasAccount' => $hasAccount,
        'currency' => $picked?->currency() ?? 'USD',
        'amount' => $picked ? (float) $picked->min_amount : 100,
        'scroll' => (bool) $picked,
        'min' => \App\Services\CurrencyExchangeService::MIN,
        'max' => \App\Services\CurrencyExchangeService::MAX,
        'commission' => \App\Services\CurrencyExchangeService::COMMISSION,
        'url' => route('education.money-factory.exchange'),
        'now' => $now,
        'locale' => app()->getLocale(),
        'money' => $money,
        'done' => $tasks->where('done', true)->pluck('id')->values(),
        'history' => $history->map(fn ($e) => [
            'date' => $e->created_at->locale(app()->getLocale())->isoFormat('D MMM'),
            'zeds' => zeds($e->zeds),
            'foreign' => \App\Http\Controllers\MoneyFactoryController::foreign($e->foreign_amount, $e->currency),
        ])->values(),
        't' => [
            'range' => __('money.amount_range', ['min' => zeds(\App\Services\CurrencyExchangeService::MIN), 'max' => zeds(\App\Services\CurrencyExchangeService::MAX)]),
            'not_enough' => __('money.not_enough'),
            'error' => __('money.error'),
            'balance' => __('money.balance', ['amount' => ':amount']),
        ],
    ];
@endphp
<div class="zm" x-data="moneyFactory(@js($config))" x-init="init()">

    <section class="zm-hero zm-hero--text">
        <div>
            <p class="zm-kicker">{{ __('city.districts.financial-square') }}</p>
            <h1>{{ __('money.title') }}</h1>
            <p class="zm-lead">{{ __('money.lead') }}</p>
            <dl class="zm-facts">
                <div><dt>{{ __('money.fact_country') }}</dt><dd>{{ __('money.zedland') }}</dd></div>
                <div><dt>{{ __('money.fact_capital') }}</dt><dd>Zedville</dd></div>
                <div><dt>{{ __('money.fact_currency') }}</dt><dd>{{ __('money.zed') }}</dd></div>
            </dl>
        </div>
    </section>

    {{-- what money is, why Zedland prints it and how: in the picture (2026-11-02) --}}
    @include('education.money-factory._scene')

    {{-- the class's other Money Factory activities (2026-11-02), each on its own page --}}
    @if ($activityTasks->isNotEmpty())
        <section class="zm-card" aria-labelledby="zm-activities">
            <h2 id="zm-activities"><span class="zm-ico" aria-hidden="true">✏️</span>{{ __('money_tasks.your_activities') }}</h2>
            <div class="zm-tasks" style="margin-bottom:0">
                @foreach ($activityTasks as $t)
                    <article class="zm-task{{ $t->done ? ' is-done' : '' }}">
                        <p class="zm-task-tag">{{ __('engagement.easy_' . $t->position) }}@if ($t->done) · ✓ {{ __('money_tasks.done') }}@endif</p>
                        <h3><span aria-hidden="true">{{ \App\Services\MoneyFactory\Activities::ALL[$t->type]['icon'] }}</span> {{ $t->studentTitle() }}</h3>
                        <a class="zm-btn" href="{{ $t->studentUrl() }}">{{ __('engagement.easy_start') }} →</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="zm-card" id="exchange" aria-labelledby="zm-exchange">
        <h2 id="zm-exchange"><span class="zm-ico" aria-hidden="true">💱</span>{{ __('money.exchange_title') }}</h2>
        <p>{{ __('money.exchange_text') }}</p>

        @if ($tasks->isNotEmpty())
            <div class="zm-tasks">
                @foreach ($tasks as $t)
                    <article class="zm-task" :class="done.includes({{ $t->id }}) && 'is-done'">
                        <p class="zm-task-tag">{{ __('engagement.easy_tag') }} · {{ __('engagement.easy_' . $t->position) }} · {{ __('engagement.easy_badge', ['badge' => __('badges.engagement')]) }}</p>
                        <h3>{{ $t->studentTitle() }}</h3>
                        @if (!$hybrid && !empty($t->content['reason']))
                            <p>{{ ct('activities', $t->content['reason']) }}</p>
                        @endif
                        <p class="zm-task-goal">{{ $t->exchangeTask() }}</p>
                        <p x-show="done.includes({{ $t->id }})" class="zm-task-goal">✓ {{ __('money.task_done') }}</p>
                        <div x-show="!done.includes({{ $t->id }})" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                            <button type="button" class="zm-btn" @click="doTask(@js($t->currency()), {{ (float) $t->min_amount }})">{{ __('money.task_do') }} →</button>
                            @if ($t->until)
                                <span class="zm-small">{{ __('engagement.easy_until', ['date' => \Carbon\Carbon::parse($t->until)->locale(app()->getLocale())->isoFormat('LL')]) }}</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="zm-ex-grid">
            <div class="zm-board" aria-labelledby="zm-rates">
                <div class="zm-board-head">
                    <span class="zm-live">{{ __('money.live') }}</span>
                    <span class="zm-board-title" id="zm-rates">{{ __('money.rates_title') }}</span>
                    <span class="zm-clock" x-text="clock"></span>
                </div>
                <div class="zm-rate-row zm-rate-head" aria-hidden="true">
                    <span></span><span>{{ __('money.currency') }}</span><span>{{ __('money.one_zed') }}</span><span>{{ __('money.today') }}</span>
                </div>
                <div class="zm-rows">
                    <template x-for="row in rows" :key="row.code">
                        <button type="button" class="zm-rate-row" :class="{ 'is-up': row.flash === 'up', 'is-down': row.flash === 'down', 'is-picked': row.code === currency }" @click="pick(row.code)">
                            <span class="zm-code" x-text="row.code"></span>
                            <span class="zm-cur"><span class="zm-sym" x-text="row.symbol + ' · '"></span><span x-text="row.name"></span></span>
                            <span class="zm-val" x-text="rateText(row)"></span>
                            <span class="zm-chg" :class="change(row) >= 0 ? 'is-up' : 'is-down'" x-text="changeText(row)"></span>
                        </button>
                    </template>
                </div>
                <p class="zm-board-note">{{ __('money.rates_note', ['date' => \Carbon\Carbon::parse(\App\Services\CurrencyExchangeService::ECB_DATE)->locale(app()->getLocale())->isoFormat('LL')]) }}</p>
            </div>

            <div class="zm-form" id="zm-form">
                @if (!$hasAccount)
                    <p class="zm-error">{{ __('money.no_account') }}</p>
                    <a class="zm-btn" style="display:block;text-align:center" href="{{ route('bank.index') }}">{{ __('messages.nav_bank_account') }} →</a>
                @else
                    <div x-show="step === 'done'" x-cloak>
                        <div class="zm-done"><strong>{{ __('money.done_title') }}</strong><span x-text="result"></span></div>
                        <button type="button" class="zm-btn zm-btn--ghost" @click="step = 'form'">{{ __('money.again') }}</button>
                    </div>

                    <div x-show="step !== 'done'">
                        <p class="zm-error" x-show="error" x-text="error" x-cloak></p>
                        <div class="zm-field" x-show="step === 'form'">
                            <label for="zm-currency">{{ __('money.currency') }}</label>
                            <select id="zm-currency" x-model="currency">
                                <template x-for="row in rows" :key="row.code">
                                    <option :value="row.code" x-text="row.code + ' · ' + row.name" :selected="row.code === currency"></option>
                                </template>
                            </select>
                        </div>
                        <div class="zm-field" x-show="step === 'form'">
                            <label for="zm-amount">{{ __('money.amount') }}</label>
                            <input id="zm-amount" type="number" inputmode="decimal" step="0.01" :min="cfg.min" :max="cfg.max" x-model="amount">
                        </div>
                        <p class="zm-h2" x-show="step === 'confirm'" x-cloak style="font-size:16px">{{ __('money.confirm_title') }}</p>
                        <dl class="zm-sum">
                            <dt>{{ __('money.rate') }}</dt><dd x-text="'1 Ƶ = ' + num(shownRate(), currency === 'JPY' ? 2 : 4) + ' ' + currency"></dd>
                            <dt>{{ __('money.you_get') }}</dt><dd class="is-big" x-text="valid ? foreignText() : '—'"></dd>
                            <dt>{{ __('money.commission', ['percent' => (\App\Services\CurrencyExchangeService::COMMISSION * 100) . (app()->getLocale() === 'en' ? '%' : ' %')]) }}</dt><dd x-text="valid ? zeds(commission) : '—'"></dd>
                            <dt class="is-total">{{ __('money.you_pay') }}</dt><dd class="is-total" x-text="valid ? zeds(total) : '—'"></dd>
                        </dl>
                        <p class="zm-small" style="margin:-4px 0 12px" x-text="cfg.t.balance.split(':amount').join(zeds(balance))"></p>
                        <button type="button" class="zm-btn" x-show="step === 'form'" @click="review()">{{ __('money.exchange_button') }}</button>
                        <div class="zm-btns" x-show="step === 'confirm'" x-cloak>
                            <button type="button" class="zm-btn zm-btn--ghost" @click="step = 'form'">{{ __('money.back') }}</button>
                            <button type="button" class="zm-btn" :disabled="saving" @click="confirm()">
                                <span x-show="!saving">{{ __('money.confirm') }}</span><span x-show="saving">{{ __('money.saving') }}</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <template x-if="history.length">
            <div>
                <p class="zm-small" style="margin:16px 0 0;font-weight:700">{{ __('money.history_title') }}</p>
                <ul class="zm-history" style="margin-top:6px">
                    <template x-for="(line, i) in history" :key="i">
                        <li x-text="line.date + ' · ' + line.zeds + ' → ' + line.foreign"></li>
                    </template>
                </ul>
            </div>
        </template>
    </section>

    {{-- The banknote tracker (Daniel, 2026-11-02: "que descubran ... por qué manos ha pasado
         un billete en el último mes. Puedes darle un billete y decirles que lo pongan en una
         sección de Money Factory"): the student's own note, or any serial, and its month. --}}
    <section class="zm-card" id="tracker" aria-labelledby="zm-tracker" x-data="zmTracker(@js([
        'serial' => $serial,
        'url' => route('education.money-factory.tracker'),
        'answersUrl' => route('education.money-factory.tracker.answers'),
        'done' => (bool) ($journeyTask->done ?? false),
        't' => ['error' => __('money.error'), 'invalid' => __('money_tasks.journey.invalid')],
    ]))">
        <h2 id="zm-tracker"><span class="zm-ico" aria-hidden="true">🔎</span>{{ __('money_tasks.journey.title') }}</h2>
        @unless ($hybrid)<p>{{ __('money_tasks.journey.text') }}</p>@endunless

        @if ($journeyTask)
            <article class="zm-task{{ $journeyTask->done ? ' is-done' : '' }}" style="margin-bottom:14px" :class="done && 'is-done'">
                <p class="zm-task-tag">{{ __('engagement.easy_tag') }} · {{ __('engagement.easy_' . $journeyTask->position) }} · {{ __('engagement.easy_badge', ['badge' => __('badges.engagement')]) }}</p>
                <h3>{{ $journeyTask->studentTitle() }}</h3>
                @foreach ($journeyTask->steps() as $step)<p>{{ $step }}</p>@endforeach
                <p class="zm-task-goal" x-show="done" @if (!$journeyTask->done) x-cloak @endif>✓ {{ __('money_tasks.done') }}</p>
            </article>
        @endif

        <div class="zm-tracker">
            <div class="zm-tracker-side">
                <p class="zm-small" style="font-weight:700;margin-bottom:6px">{{ __('money_tasks.journey.your_note') }}</p>
                <div class="zm-note" aria-label="{{ __('money_tasks.journey.your_note') }}: {{ $serial }}">
                    <span class="zm-note-z" aria-hidden="true">Ƶ</span><span class="zm-note-v" aria-hidden="true">20</span>
                    <span class="zm-note-serial">{{ $serial }}</span>
                </div>
                <button type="button" class="zm-btn" style="margin-top:10px" @click="scan(cfg.serial)" :disabled="scanning">📥 {{ __('money_tasks.journey.scan') }}</button>
                <form class="zm-scan-other" @submit.prevent="scan(other)">
                    <label for="zm-serial" class="zm-small">{{ __('money_tasks.journey.serial') }}</label>
                    <div style="display:flex;gap:6px">
                        <input id="zm-serial" x-model="other" placeholder="ZV 1234 5678" autocomplete="off">
                        <button type="submit" class="zm-btn zm-btn--ghost" style="width:auto;padding:8px 14px" :disabled="scanning">{{ __('money_tasks.journey.scan_other') }}</button>
                    </div>
                </form>
                {{-- real banknote trackers (Daniel, 2026-11-02: "add the address for US America to track the money ... also from Europe") --}}
                <div class="zm-real-trackers">
                    <p class="zm-small" style="font-weight:800;color:#0F2A22;margin-bottom:4px">🌍 {{ __('money_tasks.journey.trackers_title') }}</p>
                    <p class="zm-small" style="margin-bottom:6px">{{ __('money_tasks.journey.trackers_text') }}</p>
                    <a href="https://www.wheresgeorge.com/" target="_blank" rel="noopener">💵 {{ __('money_tasks.journey.tracker_us') }}</a>
                    <a href="https://www.eurobilltracker.com/" target="_blank" rel="noopener">💶 {{ __('money_tasks.journey.tracker_eu') }}</a>
                </div>
                <p class="zm-small zm-sources" style="list-style:none;padding:0;margin-top:8px">{{ __('money.sources') }}:
                    <a href="https://tutor2u.net/economics/blog/how-often-does-a-banknote-change-hands" target="_blank" rel="noopener">The Guardian / OnStride (tutor2u)</a> ·
                    <a href="https://www.banque-france.fr/en/publications-and-statistics/publications/banknote-issuance-while-velocity-their-circulation-slowing" target="_blank" rel="noopener">Banque de France</a></p>
            </div>

            <div class="zm-tracker-main">
                <p class="zm-error" x-show="error" x-text="error" x-cloak></p>
                <div class="zm-scanning" x-show="scanning" x-cloak><span class="zm-scan-beam" aria-hidden="true"></span>{{ __('money_tasks.journey.scanning') }}</div>
                <template x-if="journey">
                    <div>
                        <h3 class="zm-journey-title" x-text="journey.title"></h3>
                        <ol class="zm-journey">
                            <template x-for="(stop, i) in journey.stops" :key="i">
                                <li :class="'is-' + stop.kind">
                                    <span class="zm-journey-ico" x-text="stop.icon" aria-hidden="true"></span>
                                    <span class="zm-journey-name" x-text="stop.name"></span>
                                    <span class="zm-journey-when"><span x-text="stop.date"></span> · <b x-text="stop.stayed"></b></span>
                                    <span class="zm-journey-passed" x-show="stop.passed" x-text="stop.passed ? '↳ ' + stop.passed : ''"></span>
                                </li>
                            </template>
                        </ol>
                        {{-- how long a hand keeps a banknote, here and in real life (2026-11-02) --}}
                        <p class="zm-journey-average">⏱️ <span x-text="journey.average"></span>
                            <span class="zm-muted">{{ __('money_tasks.journey.real_life') }}</span></p>

                        <template x-if="journey.questions.length">
                            <div class="zm-quiz">
                                <template x-for="(q, qi) in journey.questions" :key="qi">
                                    <fieldset class="zm-q" :class="result && (result.right[qi] ? 'is-right' : 'is-wrong')">
                                        <legend x-text="(qi + 1) + '. ' + q.text"></legend>
                                        <template x-for="(option, oi) in q.options" :key="oi">
                                            <label class="zm-opt"><input type="radio" :name="'zmq' + qi" :value="oi" x-model.number="answers[qi]"> <span x-text="option"></span></label>
                                        </template>
                                        <p class="zm-q-mark" x-show="result" x-text="result && (result.right[qi] ? '✓ ' + @js(__('money_tasks.right')) : '✗ ' + @js(__('money_tasks.not_right')))"></p>
                                    </fieldset>
                                </template>
                                <p class="zm-done" x-show="result && result.done" x-cloak x-text="result && result.message"></p>
                                <p class="zm-error" x-show="result && !result.done && result.message" x-cloak x-text="result && result.message"></p>
                                <button type="button" class="zm-btn" style="width:auto;padding:10px 22px" @click="check()" :disabled="checking || answers.some((a) => a === null)">
                                    <span x-show="!checking">{{ __('money_tasks.check') }}</span><span x-show="checking">{{ __('money_tasks.checking') }}</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </section>

    @unless ($hybrid)
        <section aria-labelledby="zm-more">
            <h2 class="zm-h2" id="zm-more">{{ __('money.more_title') }}</h2>
            <div class="zm-grid zm-grid--2">
                <a class="zm-more" href="{{ route('education.money-factory.circulation') }}">
                    <span class="zm-more-ico" aria-hidden="true">📊</span>
                    <span><strong>{{ __('money.circulation_card') }}</strong><span>{{ __('money.circulation_card_text') }}</span></span>
                </a>
                @if ($advanced)
                    <a class="zm-more" href="{{ route('education.money-factory.supply') }}">
                        <span class="zm-more-ico" aria-hidden="true">🏦</span>
                        <span><strong>{{ __('money.supply_card') }} <span class="zm-badge">{{ __('money.advanced') }}</span></strong><span>{{ __('money.supply_card_text') }}</span></span>
                    </a>
                    <a class="zm-more" href="{{ route('education.money-factory.enter') }}">
                        <span class="zm-more-ico" aria-hidden="true">🚪</span>
                        <span><strong>{{ __('money.enter_card') }} <span class="zm-badge">{{ __('money.advanced') }}</span></strong><span>{{ __('money.enter_card_text') }}</span></span>
                    </a>
                @endif
            </div>
        </section>
    @endunless
</div>

<script>
    // The banknote tracker: scan a serial, see its month, answer about your own note.
    window.zmTracker = function (cfg) {
        return {
            cfg,
            other: '',
            scanning: false,
            checking: false,
            journey: null,
            answers: [],
            result: null,
            error: '',
            done: cfg.done,
            scan(serial) {
                this.error = '';
                this.result = null;
                this.journey = null;
                this.scanning = true;
                fetch(cfg.url + '?serial=' + encodeURIComponent(serial || ''), { headers: { 'Accept': 'application/json' } })
                    .then((r) => r.json().then((data) => (r.ok ? data : Promise.reject(data))))
                    // a moment in the scanner, like a real one
                    .then((data) => new Promise((resolve) => setTimeout(() => resolve(data), 900)))
                    .then((data) => { this.journey = data; this.answers = data.questions.map(() => null); })
                    .catch((data) => { this.error = (data && data.message) || cfg.t.error; })
                    .finally(() => { this.scanning = false; });
            },
            check() {
                this.checking = true;
                fetch(cfg.answersUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ answers: this.answers }),
                })
                    .then((r) => r.json().then((data) => (r.ok ? data : Promise.reject(data))))
                    .then((data) => { this.result = data; if (data.done) this.done = true; })
                    .catch(() => { this.error = cfg.t.error; })
                    .finally(() => { this.checking = false; });
            },
        };
    };

    window.moneyFactory = function (cfg) {
        return {
            cfg,
            rows: cfg.board.map((row) => ({ ...row, flash: '' })),
            clock: '',
            currency: cfg.currency,
            amount: cfg.amount,
            balance: cfg.balance,
            done: cfg.done,
            history: cfg.history,
            step: 'form',
            saving: false,
            error: '',
            result: '',
            offset: 0,
            quoteAt: null,
            init() {
                // the board runs on the server's clock, so the rate confirmed is the rate paid
                this.offset = cfg.now - Math.floor(Date.now() / 1000);
                this.tick(true);
                setInterval(() => this.tick(), 2500);
                this.clockTick();
                setInterval(() => this.clockTick(), 1000);
                if (cfg.scroll) this.$nextTick(() => document.getElementById('zm-form').scrollIntoView({ behavior: 'smooth', block: 'center' }));
            },
            // the same three waves as App\Services\CurrencyExchangeService::rate()
            rateAt(row, second) {
                let move = 0;
                for (const [share, period, phase] of row.waves) move += share * Math.sin(2 * Math.PI * (second % period) / period + phase);
                return row.base * (1 + move);
            },
            second() { return Math.floor(Date.now() / 1000) + this.offset; },
            tick(first) {
                const second = this.second();
                this.rows.forEach((row) => {
                    const rate = this.rateAt(row, second);
                    row.flash = first || Math.abs(rate - row.rate) < row.rate * 1e-6 ? '' : (rate > row.rate ? 'up' : 'down');
                    row.rate = rate;
                });
                setTimeout(() => this.rows.forEach((row) => { row.flash = ''; }), 700);
            },
            clockTick() {
                this.clock = new Date().toLocaleTimeString(cfg.locale, { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            },
            num(value, decimals) {
                const [whole, cents] = Math.abs(value).toFixed(decimals).split('.');
                return (value < 0 ? '−' : '') + whole.replace(/\B(?=(\d{3})+(?!\d))/g, cfg.money.thou) + (decimals ? cfg.money.dec + cents : '');
            },
            zeds(value) {
                const number = this.num(Math.abs(value), 2);
                return (value < 0 ? '−' : '') + (cfg.money.before ? 'Ƶ ' + number : number + ' Ƶ');
            },
            row() { return this.rows.find((row) => row.code === this.currency) || this.rows[0]; },
            rateText(row) { return this.num(row.rate, row.code === 'JPY' ? 2 : 4); },
            change(row) { return (row.rate - row.open) / row.open * 100; },
            changeText(row) {
                const change = this.change(row);
                return (change >= 0 ? '▲ ' : '▼ ') + this.num(Math.abs(change), 2) + (cfg.locale === 'en' ? '%' : ' %');
            },
            get zedsIn() { const value = parseFloat(String(this.amount).replace(',', '.')); return isNaN(value) ? 0 : Math.round(value * 100) / 100; },
            get valid() { return this.zedsIn >= cfg.min && this.zedsIn <= cfg.max; },
            get commission() { return Math.round(this.zedsIn * cfg.commission * 100) / 100; },
            get total() { return Math.round((this.zedsIn + this.commission) * 100) / 100; },
            // live while choosing; held at the second of "Exchange" while confirming
            shownRate() { const row = this.row(); return this.step === 'confirm' ? this.rateAt(row, this.quoteAt) : row.rate; },
            foreignText() {
                const row = this.row(), factor = Math.pow(10, row.cents);
                return this.num(Math.round(this.zedsIn * this.shownRate() * factor) / factor, row.cents) + ' ' + row.code;
            },
            pick(code) { this.currency = code; this.step = 'form'; this.error = ''; },
            doTask(code, zeds) {
                this.pick(code);
                this.amount = zeds;
                document.getElementById('zm-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
            },
            review() {
                this.error = '';
                if (!this.valid) { this.error = cfg.t.range; return; }
                if (this.total > this.balance + 1e-9) { this.error = cfg.t.not_enough; return; }
                this.quoteAt = this.second();
                this.step = 'confirm';
            },
            confirm() {
                if (this.saving) return;
                this.saving = true;
                this.error = '';
                fetch(cfg.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ currency: this.currency, amount: this.zedsIn, at: this.quoteAt }),
                })
                    .then((r) => r.json().then((data) => (r.ok ? data : Promise.reject(data))))
                    .then((data) => {
                        this.result = data.text;
                        this.balance = data.balance;
                        this.history.unshift(data.line);
                        this.done = data.done;
                        this.step = 'done';
                    })
                    .catch((data) => { this.error = (data && data.message) || cfg.t.error; this.step = 'form'; })
                    .finally(() => { this.saving = false; });
            },
        };
    };
</script>
@endsection
