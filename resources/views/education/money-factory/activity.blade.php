@extends('layouts.profile')

@section('title', $title)

{{-- A Money Factory activity (Daniel, 2026-11-02: the ideas "en Admin, bajo Money Factory,
     pero los alumnos lo harán en Educational Finance Department"): the task from the board,
     what to look at (App\Services\MoneyFactory\<activity>::stage) and the student's own
     questions. Done when every answer is right while it runs for the class. A hybrid class
     isn't told why ("no es necesario explicar, solo informar"). --}}
@section('content')
@include('education.money-factory._styles')
<div class="zm">
    <div class="zm-page-head">
        <a class="zm-crumb" href="{{ route('education.money-factory') }}">← {{ __('money.back_to_factory') }}</a>
    </div>
    <div class="zm-page-head">
        <h1><span aria-hidden="true">{{ $icon }}</span> {{ $title }}</h1>
        @if ($advanced)<span class="zm-badge">{{ __('money.advanced') }}</span>@endif
    </div>

    @if ($task)
        <article class="zm-task{{ $task->done ? ' is-done' : '' }}" id="zm-task">
            <p class="zm-task-tag">{{ __('engagement.easy_tag') }} · {{ __('engagement.easy_' . $task->position) }} · {{ __('engagement.easy_badge', ['badge' => __('badges.engagement')]) }}</p>
            @foreach ($task->steps() as $step)<p>{{ $step }}</p>@endforeach
            <p class="zm-task-goal" @if (!$task->done) style="display:none" @endif data-done>✓ {{ __('money_tasks.done') }}</p>
        </article>
    @else
        <p class="zm-muted" style="margin:0">{{ __($ownCounts ? 'money_tasks.not_assigned_own' : 'money_tasks.not_assigned') }}</p>
    @endif

    <section class="zm-card">
        <p style="margin:0">{{ __('money_tasks.' . $key . '.intro', $stage['intro'] ?? []) }}</p>
    </section>

    @includeIf('education.money-factory.activities.' . $key)

    <section class="zm-card" aria-labelledby="zm-questions" x-data="zmQuiz(@js([
        'url' => route('education.money-factory.activity.answers', $slug),
        'questions' => $questions,
        't' => ['error' => __('money.error'), 'right' => __('money_tasks.right'), 'not_right' => __('money_tasks.not_right')],
    ]))">
        <h2 id="zm-questions"><span class="zm-ico" aria-hidden="true">✏️</span>{{ __('money_tasks.questions') }}</h2>
        <div class="zm-quiz" style="border-top:0;padding-top:0">
            <template x-for="(q, qi) in cfg.questions" :key="qi">
                <fieldset class="zm-q" :class="result && (result.right[qi] ? 'is-right' : 'is-wrong')">
                    <legend x-text="(qi + 1) + '. ' + q.text"></legend>
                    <p class="zm-q-lead" x-show="q.lead" x-text="q.lead"></p>
                    <div class="zm-chips" x-show="q.facts">
                        <template x-for="(fact, fi) in (q.facts || [])" :key="fi">
                            <span class="zm-chip"><span x-text="fact[0]"></span> <b x-text="fact[1]"></b></span>
                        </template>
                    </div>

                    {{-- one option --}}
                    <template x-if="q.kind === 'choice'">
                        <div>
                            <template x-for="(option, oi) in q.options" :key="oi">
                                <label class="zm-opt"><input type="radio" :name="'zmq' + qi" :value="oi" x-model.number="answers[qi]"> <span x-text="option"></span></label>
                            </template>
                        </div>
                    </template>

                    {{-- put the cards in order: tap them one after the other --}}
                    <template x-if="q.kind === 'order'">
                        <div>
                            <div class="zm-order">
                                <template x-for="(card, ci) in q.cards" :key="ci">
                                    <button type="button" class="zm-order-card" :class="place(qi, ci) && 'is-placed'" @click="toggle(qi, ci)" :aria-pressed="place(qi, ci) ? 'true' : 'false'">
                                        <span class="zm-order-num" x-text="place(qi, ci) || '·'"></span>
                                        <span class="zm-order-ico" aria-hidden="true" x-text="card.icon"></span>
                                        <strong x-text="card.name"></strong>
                                        <span class="zm-order-text" x-text="card.text"></span>
                                        <em class="zm-order-when" x-show="result" x-text="card.when"></em>
                                    </button>
                                </template>
                            </div>
                            <button type="button" class="zm-btn zm-btn--ghost" style="width:auto;padding:6px 14px;margin-top:8px;font-size:14px" @click="answers[qi] = []" x-show="answers[qi].length">↺ {{ __('money_tasks.start_again') }}</button>
                        </div>
                    </template>

                    <p class="zm-q-mark" x-show="result" x-text="result && (result.right[qi] ? '✓ ' + cfg.t.right : '✗ ' + cfg.t.not_right)"></p>
                    <p class="zm-q-why" x-show="result && result.why && result.why[qi]" x-text="result && result.why && result.why[qi]"></p>
                </fieldset>
            </template>

            <p class="zm-error" x-show="error" x-cloak x-text="error"></p>
            <p class="zm-done" x-show="result && !result.right.includes(false)" x-cloak x-text="result && result.message"></p>
            <p class="zm-error" x-show="result && result.right.includes(false)" x-cloak x-text="result && result.message"></p>
            <button type="button" class="zm-btn" style="width:auto;padding:10px 22px" @click="check()" :disabled="checking || !ready()">
                <span x-show="!checking">{{ __('money_tasks.check') }}</span><span x-show="checking" x-cloak>{{ __('money_tasks.checking') }}</span>
            </button>
        </div>
    </section>

    @if (!empty(\App\Services\MoneyFactory\Activities::SOURCES[$type]))
        <section class="zm-card">
            <h2 style="font-size:15px">{{ __('money.sources') }}</h2>
            <ul class="zm-sources">
                @foreach (\App\Services\MoneyFactory\Activities::SOURCES[$type] as [$label, $url])
                    <li><a href="{{ $url }}" target="_blank" rel="noopener">{{ $label }}</a></li>
                @endforeach
            </ul>
        </section>
    @endif
</div>

<script>
    // The questions of a Money Factory activity: one option each, or cards to put in order.
    window.zmQuiz = function (cfg) {
        return {
            cfg,
            answers: cfg.questions.map((q) => (q.kind === 'order' ? [] : null)),
            result: null,
            checking: false,
            error: '',
            // a card's place in the order the student is making (1, 2... or 0)
            place(qi, ci) { return this.answers[qi].indexOf(ci) + 1; },
            toggle(qi, ci) {
                const order = this.answers[qi];
                const at = order.indexOf(ci);
                if (at >= 0) order.splice(at, 1); else order.push(ci);
            },
            ready() {
                return this.cfg.questions.every((q, qi) => (q.kind === 'order' ? this.answers[qi].length === q.options.length : this.answers[qi] !== null));
            },
            check() {
                this.checking = true;
                this.error = '';
                fetch(cfg.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ answers: this.answers }),
                })
                    .then((r) => r.json().then((data) => (r.ok ? data : Promise.reject(data))))
                    .then((data) => {
                        this.result = data;
                        const task = document.querySelector('#zm-task');
                        if (data.done && task) { task.classList.add('is-done'); task.querySelector('[data-done]').style.display = ''; }
                    })
                    .catch(() => { this.error = cfg.t.error; })
                    .finally(() => { this.checking = false; });
            },
        };
    };
</script>
@endsection
