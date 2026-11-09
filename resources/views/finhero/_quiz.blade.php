{{-- A FinHero chart-and-questions Task (App\Services\Finhero\QuizTasks: compound interest and the
     price rise, Daniel, 2026-11-08): the situation, its chart (finhero/quiz/<key>) and the student's own
     questions to choose an answer for. "Check" sends the answers to the server, which says which are right and, for those,
     why (not to hybrid classes); all right, the Task is done. Needs $activityKey (task_6...).
     A question with 'chart' => 'shift' (Task 14) draws supply and demand: the option picked moves its line
     and the chart shows where the prices go. finhero/quiz/<key>-after, if there is one, comes once the answers
     are right (Task 15: choosing the health insurance plan, check({ plan }) - 'choose' keeps the finish away). --}}
@php
    $quizKey = \App\Services\Finhero\QuizTasks::ALL[$activityKey]['key'];
    // the topic's texts: compound.php or inflation.php
    $quizText = \App\Services\Finhero\QuizTasks::ALL[$activityKey]['lang'] . '.' . $quizKey;
    $stage = \App\Services\Finhero\QuizTasks::stage($activityKey, (int) auth()->id());
    $questions = \App\Services\Finhero\QuizTasks::forPage(\App\Services\Finhero\QuizTasks::questions($activityKey, (int) auth()->id()));
@endphp
<div class="zq">
    <h1 class="zq-title"><span aria-hidden="true">{{ \App\Services\Finhero\QuizTasks::ALL[$activityKey]['icon'] }}</span> {{ __($quizText . '.title') }}</h1>

    <section class="zq-card"><p style="margin:0">{{ __($quizText . '.' . ($stage['intro_key'] ?? 'intro'), $stage['intro'] ?? []) }}</p></section>

    @includeIf('finhero.quiz.' . $quizKey)

    <section class="zq-card" aria-labelledby="zq-questions" x-data="zqQuiz(@js(['url' => route('finhero.quiz.answers', str_replace('_', '-', $activityKey)), 'questions' => $questions, 'error' => __('quiz.error'), 'chart' => $stage['chart'] ?? null]))">
        <h2 id="zq-questions">{{ __('quiz.questions') }}</h2>
        <template x-for="(q, qi) in cfg.questions" :key="qi">
            <fieldset class="zq-q" :class="result && (result.right[qi] ? 'is-right' : 'is-wrong')">
                <legend x-text="(qi + 1) + '. ' + q.text"></legend>
                <template x-if="q.chart === 'shift'">
                    {{-- supply (blue) and demand (orange); the one the option moves leaves its old place dashed --}}
                    <svg class="zq-shift" viewBox="0 0 320 226" role="img" :aria-label="cfg.chart.title + ': ' + meet(qi).p">
                        <defs><clipPath :id="'zqc' + qi"><rect x="41" y="14" width="272" height="183"></rect></clipPath></defs>
                        <line x1="40" y1="198" x2="312" y2="198" class="zq-axis"></line>
                        <line x1="40" y1="14" x2="40" y2="198" class="zq-axis"></line>
                        <text x="312" y="218" text-anchor="end" class="zq-ax" x-text="cfg.chart.amount"></text>
                        <text x="46" y="12" class="zq-ax" x-text="cfg.chart.price"></text>
                        <g :clip-path="'url(#zqc' + qi + ')'">
                            <line x1="70" y1="40" x2="270" y2="180" class="zq-ghost" x-show="moved(qi)[1] !== 0"></line>
                            <line x1="70" y1="180" x2="270" y2="40" class="zq-ghost" x-show="moved(qi)[0] !== 0"></line>
                            <line :x1="70 + moved(qi)[1]" y1="40" :x2="270 + moved(qi)[1]" y2="180" class="zq-demand"></line>
                            <line :x1="70 + moved(qi)[0]" y1="180" :x2="270 + moved(qi)[0]" y2="40" class="zq-supply"></line>
                            <line x1="40" :y1="meet(qi).y" :x2="meet(qi).x" :y2="meet(qi).y" class="zq-guide"></line>
                            <line :x1="meet(qi).x" :y1="meet(qi).y" :x2="meet(qi).x" y2="198" class="zq-guide"></line>
                            <circle :cx="meet(qi).x" :cy="meet(qi).y" r="6" class="zq-meet"></circle>
                        </g>
                        {{-- the price level where they cross; before, 100 stays faint to compare --}}
                        <text x="34" y="115" text-anchor="end" class="zq-p0" x-show="meet(qi).p !== 100">100</text>
                        <text x="34" :y="meet(qi).y + 5" text-anchor="end" class="zq-p" x-text="meet(qi).p"></text>
                        {{-- supply named at its top end, demand at its top end too: clear of the lines --}}
                        <text :x="262 + moved(qi)[0]" y="34" text-anchor="end" class="zq-lbl zq-lbl-s" x-text="cfg.chart.supply"></text>
                        <text :x="Math.max(46, 76 + moved(qi)[1])" y="34" class="zq-lbl zq-lbl-d" x-text="cfg.chart.demand"></text>
                    </svg>
                </template>
                <template x-for="(option, oi) in q.options" :key="oi">
                    <label class="zq-opt"><input type="radio" :name="'zq' + qi" :value="oi" x-model.number="answers[qi]"> <span x-text="option"></span></label>
                </template>
                <p class="zq-why" x-show="result && result.why[qi]" x-text="result ? result.why[qi] : ''"></p>
            </fieldset>
        </template>
        <p class="zq-msg" x-show="message" x-text="message" :class="result && result.done && !result.error ? 'is-done' : ''"></p>
        {{-- what a task adds once the answers are right (Task 15: choosing the plan, with check({ plan })) --}}
        @includeIf('finhero.quiz.' . $quizKey . '-after')
        <button type="button" class="themeBtn" @click="check()" x-bind:disabled="busy" x-show="!(result && result.done)">{{ __('quiz.check') }}</button>
        <div x-show="result && result.done && !result.choose" x-cloak>@include('partials.finish')</div>
    </section>
</div>

<style>
    .zq { max-width: 820px; margin: 0 auto; padding: 8px 4px 24px; color: #0F2A22; }
    .zq-title { font: 800 26px/1.25 "Manrope", sans-serif; margin: 0 0 16px; }
    .zq-card { background: #fff; border: 1px solid #D2DDDB; border-radius: 16px; padding: 18px 20px; margin-bottom: 16px; font-size: 16px; line-height: 1.5; }
    .zq-card h2 { font: 800 18px/1.3 "Manrope", sans-serif; margin: 0 0 12px; }
    .zq-bars { display: flex; flex-direction: column; gap: 10px; }
    .zq-bar-row { display: grid; grid-template-columns: minmax(110px, auto) minmax(0, 1fr) auto; align-items: center; gap: 10px; font-size: 14px; }
    .zq-bar { display: flex; height: 18px; border-radius: 6px; background: #EEF3F1; overflow: hidden; }
    .zq-bar span { display: block; height: 100%; }
    .zq-legend { margin: 12px 0 0; font-size: 14px; color: #5E7069; }
    /* a table never widens the page: if it doesn't fit, it scrolls inside its card */
    .zq-table-wrap { overflow-x: auto; width: 0; min-width: 100%; }
    .zq-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .zq-table th, .zq-table td { padding: 7px 8px; border-bottom: 1px solid #D2DDDB; text-align: left; }
    .zq-table thead th { color: #5E7069; font-weight: 700; }
    .zq-table tfoot th, .zq-table tfoot td { font-weight: 800; border-bottom: 0; }
    .zq-table .zq-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .zq-q { border: 0; padding: 0; margin: 0 0 18px; }
    .zq-q legend { font-weight: 700; font-size: 16px; margin-bottom: 8px; }
    .zq-opt { display: flex; gap: 10px; align-items: center; padding: 9px 12px; border: 2px solid #D2DDDB; border-radius: 12px; margin-bottom: 8px; cursor: pointer; font-size: 15px; }
    .zq-opt:has(input:checked) { border-color: #00A47D; background: #F0FBF7; }
    .zq-opt input { accent-color: #00A47D; width: 18px; height: 18px; }
    .zq-q.is-right legend::after { content: " ✓"; color: #0B7A55; }
    .zq-q.is-wrong legend::after { content: " ✗"; color: #8E1F25; }
    .zq-why { margin: 4px 0 0; font-size: 14px; color: #0B7A55; }
    .zq-msg { font-weight: 700; color: #8E1F25; }
    .zq-msg.is-done { color: #0B7A55; }
    /* Task 14's supply and demand */
    .zq-shift { display: block; width: 100%; max-width: 420px; height: auto; margin: 0 0 10px; }
    .zq-axis { stroke: #4F625B; stroke-width: 1.5; }
    .zq-ax { font-size: 13px; fill: #4F625B; }
    .zq-supply { stroke: #1F6FAE; stroke-width: 3; stroke-linecap: round; }
    .zq-demand { stroke: #C8541E; stroke-width: 3; stroke-linecap: round; }
    .zq-ghost { stroke: #9AA9A4; stroke-width: 2; stroke-dasharray: 5 5; }
    .zq-guide { stroke: #4F625B; stroke-width: 1; stroke-dasharray: 3 4; }
    .zq-meet { fill: #0F2A22; stroke: #fff; stroke-width: 2; }
    .zq-p { font-size: 15px; font-weight: 800; fill: #0F2A22; }
    .zq-p0 { font-size: 13px; fill: #9AA9A4; }
    .zq-lbl { font-size: 14px; font-weight: 700; }
    .zq-lbl-s { fill: #1F6FAE; }
    .zq-lbl-d { fill: #C8541E; }
    @media (max-width: 560px) {
        .zq-card { padding: 14px 12px; }
        .zq-table { font-size: 13px; }
        .zq-table th, .zq-table td { padding: 6px 4px; }
    }
</style>

<script>
    function zqQuiz(cfg) {
        return {
            cfg, answers: cfg.questions.map(() => null), result: null, message: '', busy: false,
            // Task 14: how far the option picked moves [supply, demand] - the options are always
            // supply left, supply right, demand left, demand right
            moved(qi) {
                const pick = this.answers[qi];
                return pick === null || pick === undefined ? [0, 0] : [[-40, 0], [40, 0], [0, -40], [0, 40]][pick];
            },
            // where the two lines cross, and the price level there (100 before anything moves)
            meet(qi) {
                const [s, d] = this.moved(qi);
                const y = 110 + 0.35 * (s - d);
                return { x: 170 + (s + d) / 2, y, p: Math.round(100 + (110 - y) * 5 / 14) };
            },
            async check(extra = {}) {
                this.busy = true;
                try {
                    const response = await fetch(cfg.url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                        body: JSON.stringify({ answers: this.answers, ...extra }),
                    });
                    if (!response.ok) throw new Error(response.status);
                    this.result = await response.json();
                    this.message = this.result.message;
                } catch (e) {
                    this.message = cfg.error;
                }
                this.busy = false;
            },
        };
    }
</script>
