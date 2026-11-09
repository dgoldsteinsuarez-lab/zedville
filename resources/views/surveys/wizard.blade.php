{{--
    Consumer Profile survey - "Your first month in Zedville" (Daniel,
    2026-10-23). Ms. Rodriguez from the Finance Department walks the
    student through a normal day: the supermarket (diet + basket), school
    (lunch), getting around, free time and the weekend. A running total
    shows the month against their salary, every answer is tagged Need or
    Want, and the last screen gives them a consumer type
    (ConsumerSurvey::profile()). Saves through ConsumerSurveyController::
    storesurvey - same answers as before, so nothing downstream changes.

    $context: 'profile' (Settings / Supermarket pop-up) or 'activation'
    (Citizen Activation step 5, which then continues to step 6).
--}}
@php
    $context = $context ?? 'profile';
    $userId = auth()->id();
    $surveyBankOk = \App\Models\BankAccount::where('student_id', $userId)->exists();
    $surveyDone = \App\Models\ConsumerSurvey::doneThisMonth($userId);
    $surveySalary = \App\Models\ConsumerSurvey::salaryFor($userId);
    $w = __('surveys.wizard');
    $options = [
        'diets' => collect(__('surveys.diets'))->map(fn ($d, $key) => ['value' => $key, 'title' => $d['title'], 'text' => $d['text']])->values(),
        'lunch' => collect(__('surveys.lunch'))->map(fn ($o, $v) => ['value' => (int) $v, 'title' => $o['title'], 'text' => $o['text'], 'want' => false])->values(),
        'transport' => collect(__('surveys.transport'))->map(fn ($o, $v) => ['value' => (int) $v, 'title' => $o['title'], 'text' => $o['text'], 'want' => (int) $v === \App\Models\ConsumerSurvey::TAXI])->values(),
        'activities' => collect(__('surveys.activities'))->map(fn ($label, $v) => ['value' => (int) $v, 'title' => $label, 'text' => '', 'want' => true])->values(),
        'restaurants' => collect(__('surveys.restaurants'))->map(fn ($label, $v) => ['value' => (int) $v, 'title' => $label, 'text' => '', 'want' => true])->values(),
    ];
@endphp

<div class="zvq" x-data="surveyWizard(@js([
        'salary' => $surveySalary,
        'options' => $options,
        'texts' => $w,
        'lang' => app()->getLocale(),
        'context' => $context,
        'store' => url('/profile/storesurvey'),
        'groceries' => url('/api/groceries'),
        'after' => $context === 'activation' ? route('CitizenActivation.activationComplete') : null,
    ]))">

    {{-- Header (title + progress) and Ms. Rodriguez: on a wide survey one row, her question
         and then the title over the progress (_styles: .zvq-top) --}}
    <div class="zvq-top">
        <div class="zvq-head">
            <div class="zvq-title">{{ $w['title'] }}</div>
            <div class="zvq-progress" x-show="step <= 7">
                <span x-text="t('step_of', { step: step, total: 7 })"></span>
                <div class="zvq-bar"><div class="zvq-bar-fill" :style="'width:' + Math.round(step / 7 * 100) + '%'"></div></div>
            </div>
        </div>

        <div class="zvq-guide">
            <div class="zvq-avatar" aria-hidden="true">👩🏽‍💼</div>
            <div class="zvq-bubble">
                <div class="zvq-guide-name">{{ $w['guide_name'] }} <span>· {{ $w['guide_role'] }}</span></div>
                <p class="zvq-guide-text" x-html="guideText()"></p>
            </div>
        </div>
    </div>

    <div class="zvq-error" x-show="error" x-text="error" x-cloak></div>

    {{-- 1. Welcome --}}
    <section x-show="step === 1">
        <h2 class="zvq-step-title">{{ $w['intro_title'] }}</h2>
        @if (!$surveyBankOk)
            <div class="zvq-note">
                <strong>{{ __('surveys.bank_required_title') }}</strong><br>
                {{ __('surveys.bank_required_text') }}
                <a href="{{ route('bank.index') }}" class="zvq-link">{{ __('surveys.open_bank_account') }} →</a>
            </div>
        @elseif ($surveyDone && $context !== 'activation')
            <div class="zvq-note">{{ __('surveys.wizard.already_done', ['date' => now()->startOfMonth()->addMonth()->translatedFormat('j F')]) }}</div>
        @else
            <label class="zvq-promise">
                <input type="checkbox" x-model="promised">
                <span>{{ $w['promise'] }}</span>
            </label>
        @endif
    </section>

    {{-- 2. Diet --}}
    <section x-show="step === 2" x-cloak>
        <h2 class="zvq-step-title">{{ $w['diet_title'] }}</h2>
        <div class="zvq-options zvq-options-2">
            <template x-for="o in opts.diets" :key="o.value">
                <button type="button" class="zvq-option" :class="{ 'is-on': answers.diet === o.value }" @click="pickDiet(o.value)">
                    <span class="zvq-option-title" x-text="o.title"></span>
                    <span class="zvq-option-text" x-text="o.text"></span>
                </button>
            </template>
        </div>
    </section>

    {{-- 3. Groceries --}}
    <section x-show="step === 3" x-cloak>
        <div class="zvq-step-row">
            <h2 class="zvq-step-title">{{ $w['groceries_title'] }}</h2>
            <span class="zvq-count" :class="{ 'is-done': picked.length === 10 }" x-text="t('picked', { count: picked.length, total: 10 })"></span>
        </div>
        <p class="zvq-muted" x-show="loadingItems">{{ $w['loading'] }}</p>
        <template x-for="group in groupedItems()" :key="group.key">
            <div class="zvq-group">
                <div class="zvq-group-title" x-text="group.title"></div>
                <div class="zvq-items">
                    <template x-for="item in group.items" :key="item.id">
                        <button type="button" class="zvq-item"
                                :class="{ 'is-on': isPicked(item) || item.oil, 'is-locked': item.oil, 'is-full': !isPicked(item) && !item.oil && picked.length >= 10 }"
                                :disabled="item.oil || (!isPicked(item) && picked.length >= 10)"
                                @click="toggleItem(item)">
                            <span class="zvq-item-check" aria-hidden="true" x-text="isPicked(item) || item.oil ? '✓' : '+'"></span>
                            <span class="zvq-item-name" x-text="item.label || item.product_name"></span>
                            <span class="zvq-item-price" x-text="item.oil ? texts.oil_included : money(item.total) + ' ' + texts.per_month"></span>
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </section>

    {{-- 4-7. One choice each --}}
    <template x-for="q in questions" :key="q.key">
        <section x-show="step === q.step" x-cloak>
            <h2 class="zvq-step-title" x-text="texts[q.key + '_title']"></h2>
            <div class="zvq-options">
                <template x-for="o in opts[q.key]" :key="o.value">
                    <button type="button" class="zvq-option zvq-option-row" :class="{ 'is-on': answers[q.key] === o.value }" @click="answers[q.key] = o.value">
                        <span class="zvq-option-main">
                            <span class="zvq-option-title" x-text="o.title"></span>
                            <span class="zvq-option-text" x-show="o.text" x-text="o.text"></span>
                        </span>
                        <span class="zvq-option-side">
                            <span class="zvq-tag" :class="o.want ? 'is-want' : 'is-need'" x-text="o.want ? texts.want : texts.need"></span>
                            <span class="zvq-option-price" x-text="money(o.value) + ' ' + texts.per_month"></span>
                        </span>
                    </button>
                </template>
            </div>
        </section>
    </template>

    {{-- 8. Your consumer profile --}}
    <section x-show="step === 8" x-cloak>
        <h2 class="zvq-step-title">{{ $w['results_title'] }}</h2>
        <template x-if="result">
            <div>
                <div class="zvq-type">
                    <div class="zvq-type-title" x-text="result.title"></div>
                    <p class="zvq-type-text" x-text="result.text"></p>
                    <p class="zvq-type-tip" x-text="result.tip"></p>
                </div>
                <div class="zvq-summary">
                    <div class="zvq-summary-title">{{ $w['your_month'] }}</div>
                    <template x-for="row in summaryRows()" :key="row.label">
                        <div class="zvq-summary-row">
                            <span x-text="row.label"></span>
                            <span class="zvq-summary-right">
                                <span class="zvq-tag" :class="row.want ? 'is-want' : 'is-need'" x-text="row.want ? texts.want : texts.need"></span>
                                <strong x-text="money(row.amount)"></strong>
                            </span>
                        </div>
                    </template>
                    <div class="zvq-summary-row zvq-summary-total">
                        <span>{{ $w['total'] }}</span><strong x-text="money(result.total)"></strong>
                    </div>
                    <div class="zvq-split">
                        <div class="zvq-split-needs" :style="'width:' + split(result).needsPct + '%'"></div>
                        <div class="zvq-split-wants" :style="'width:' + split(result).wantsPct + '%'"></div>
                    </div>
                    <div class="zvq-split-legend">
                        <span><i class="is-need"></i> {{ $w['needs'] }} <b x-text="split(result).needsPct + '%'"></b></span>
                        <span><i class="is-want"></i> {{ $w['wants'] }} <b x-text="split(result).wantsPct + '%'"></b></span>
                    </div>
                </div>
            </div>
        </template>
    </section>

    {{-- Running total of the month --}}
    <div class="zvq-footer" x-show="step >= 3 && step <= 7" x-cloak>
        <div>
            <div class="zvq-footer-label">{{ $w['footer_month'] }}</div>
            <div class="zvq-footer-total" x-text="money(totals().total)"></div>
        </div>
        <div class="zvq-footer-right">
            <div class="zvq-footer-pct" x-text="t('footer_salary', { pct: salaryPct() })"></div>
            <div class="zvq-footer-split">
                <span class="zvq-tag is-need" x-text="texts.needs + ' ' + money(totals().needs)"></span>
                <span class="zvq-tag is-want" x-text="texts.wants + ' ' + money(totals().wants)"></span>
            </div>
        </div>
    </div>

    {{-- One pair of buttons --}}
    <div class="zvq-nav">
        @if ($context === 'activation')
            <a href="{{ route('CitizenActivation.autoDebit') }}" class="whiteBtn" x-show="step === 1">{{ $w['back'] }}</a>
        @endif
        <button type="button" class="whiteBtn" x-show="step > 1 && step <= 7" @click="go(step - 1)" x-cloak>{{ $w['back'] }}</button>
        {{-- keeps the next button on the right when there's no Back --}}
        <span x-show="step === 8 || (step === 1 && {{ $context === 'activation' ? 'false' : 'true' }})"></span>

        @if ($surveyBankOk && !($surveyDone && $context !== 'activation'))
            <button type="button" class="themeBtn" x-show="step === 1" :disabled="!promised" @click="go(2)">{{ $w['start'] }}</button>
        @endif
        <button type="button" class="themeBtn" x-show="step >= 2 && step <= 6" :disabled="!canNext()" @click="go(step + 1)" x-cloak>{{ $w['next'] }}</button>
        <button type="button" class="themeBtn" x-show="step === 7" :disabled="!canNext() || saving" @click="save()" x-cloak>
            <span x-text="saving ? texts.saving : texts.finish"></span>
        </button>
        <a :href="after" class="themeBtn" x-show="step === 8 && after" x-cloak>{{ $w['continue_activation'] }} →</a>
        <button type="button" class="themeBtn" x-show="step === 8 && !after" @click="window.location.reload()" x-cloak>{{ $w['done'] }}</button>
    </div>

    @if ($context === 'activation')
        {{-- Skipping is a quiet link, gone once the survey is saved. The
             survey can be done later in Settings. --}}
        <form action="{{ route('CitizenActivation.consumerProfile.skip') }}" method="POST" class="text-center mt-3" x-show="step < 8">
            @csrf
            <button type="submit" class="text-sm text-gray-500 underline">{{ $w['skip'] }}</button>
        </form>
    @endif
</div>

@include('surveys._styles')
@once
<script>
function surveyWizard(config) {
    const GROUPS = {
        proteins: ['beef', 'chicken', 'fish', 'seafood', 'egg', 'tofu', 'lentil', 'chickpea', 'burger', 'hamburger'],
        dairy: ['milk', 'cheese', 'yogurt'],
        fresh: ['vegetable', 'fruit', 'lettuce', 'spinach', 'tomato', 'potato'],
    };
    return {
        step: 1,
        promised: false,
        texts: config.texts,
        opts: config.options,
        salary: Number(config.salary) || 0,
        after: config.after,
        questions: [
            { key: 'lunch', step: 4 },
            { key: 'transport', step: 5 },
            { key: 'activities', step: 6 },
            { key: 'restaurants', step: 7 },
        ],
        answers: { diet: null, lunch: null, transport: null, activities: null, restaurants: null },
        items: [],
        picked: [],
        loadingItems: false,
        saving: false,
        error: '',
        result: null,
        savedMessage: '',

        t(key, params) {
            let text = this.texts[key] || key;
            Object.keys(params || {}).forEach(name => { text = text.split(':' + name).join(params[name]); });
            return text;
        },
        money(amount) {
            return 'Ƶ' + Number(amount || 0).toLocaleString(config.lang, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        guideText() {
            const key = { 1: 'intro_text', 2: 'diet_guide', 3: 'groceries_guide', 4: 'lunch_guide', 5: 'transport_guide', 6: 'activities_guide', 7: 'restaurants_guide' }[this.step];
            if (this.step === 8) return this.savedMessage || this.texts.results_title;
            return key === 'groceries_guide' ? this.t(key, { count: 10 }) : this.texts[key];
        },

        pickDiet(diet) {
            if (this.answers.diet !== diet) {
                this.answers.diet = diet;
                this.items = [];
                this.picked = [];
            }
        },
        loadItems() {
            if (this.items.length || !this.answers.diet) return;
            this.loadingItems = true;
            this.error = '';
            fetch(config.groceries + '/' + this.answers.diet, { headers: { 'Accept': 'application/json' } })
                .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(list => {
                    this.items = list.map(i => ({
                        ...i,
                        total: Math.round(Number(i.price) * Number(i.quantity || 1) * 100) / 100,
                        oil: String(i.product_name).toLowerCase().includes('cooking oil'),
                    }));
                })
                .catch(() => { this.error = this.texts.groceries_failed; })
                .finally(() => { this.loadingItems = false; });
        },
        groupOf(name) {
            const lower = String(name).toLowerCase();
            return Object.keys(GROUPS).find(g => GROUPS[g].some(word => lower.includes(word))) || 'basics';
        },
        groupedItems() {
            return ['proteins', 'dairy', 'fresh', 'basics']
                .map(key => ({ key, title: this.texts.groups[key], items: this.items.filter(i => this.groupOf(i.product_name) === key) }))
                .filter(g => g.items.length);
        },
        isPicked(item) { return this.picked.includes(item.id); },
        toggleItem(item) {
            if (item.oil) return;
            if (this.isPicked(item)) {
                this.picked = this.picked.filter(id => id !== item.id);
            } else if (this.picked.length < 10) {
                this.picked.push(item.id);
            }
        },
        chosenItems() { return this.items.filter(i => i.oil || this.isPicked(i)); },
        basket() { return this.chosenItems().reduce((sum, i) => sum + i.total, 0); },
        totals() {
            const transport = this.answers.transport || 0;
            const wants = (this.answers.activities || 0) + (this.answers.restaurants || 0) + (transport === 240 ? transport : 0);
            const total = this.basket() + (this.answers.lunch || 0) + transport + (this.answers.activities || 0) + (this.answers.restaurants || 0);
            return { total, wants, needs: total - wants };
        },
        salaryPct() {
            return this.salary ? (this.totals().total / this.salary * 100).toLocaleString(config.lang, { maximumFractionDigits: 1 }) : '0';
        },
        split(r) {
            const needsPct = r.total ? Math.round(r.needs / r.total * 100) : 0;
            return { needsPct, wantsPct: r.total ? 100 - needsPct : 0 };
        },
        summaryRows() {
            const a = this.answers;
            return [
                { label: this.texts.groceries, amount: this.basket(), want: false },
                { label: this.texts.school_lunch, amount: a.lunch, want: false },
                { label: this.texts.transport, amount: a.transport, want: a.transport === 240 },
                { label: this.texts.activities, amount: a.activities, want: true },
                { label: this.texts.eating_out, amount: a.restaurants, want: true },
            ];
        },

        canNext() {
            switch (this.step) {
                case 2: return !!this.answers.diet;
                case 3: return this.picked.length === 10;
                case 4: return this.answers.lunch !== null;
                case 5: return this.answers.transport !== null;
                case 6: return this.answers.activities !== null;
                case 7: return this.answers.restaurants !== null;
                default: return true;
            }
        },
        go(step) {
            this.error = '';
            this.step = step;
            if (step === 3) this.loadItems();
            this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        save() {
            this.saving = true;
            this.error = '';
            const groceries = this.chosenItems().map(i => ({
                id: i.id, name: i.product_name, unit_price: Number(i.price), quantity: Number(i.quantity || 1), total_price: i.total,
            }));
            const t = this.totals();
            fetch(config.store, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    diet: this.answers.diet,
                    groceries,
                    basketCost: Math.round(this.basket() * 100) / 100,
                    lunch: this.answers.lunch,
                    transport: this.answers.transport,
                    activities: this.answers.activities,
                    restaurants: this.answers.restaurants,
                    totalCost: Math.round(t.total * 100) / 100,
                }),
            })
                .then(async r => ({ ok: r.ok, data: await r.json().catch(() => ({})) }))
                .then(({ ok, data }) => {
                    if (!ok || !data.status) {
                        this.error = data.message || this.texts.try_again;
                        return;
                    }
                    this.result = data.profile;
                    this.savedMessage = data.starts_next_month ? data.message : this.texts.results_saved_now;
                    this.go(8);
                })
                .catch(() => { this.error = this.texts.try_again; })
                .finally(() => { this.saving = false; });
        },
    };
}
</script>
@endonce
