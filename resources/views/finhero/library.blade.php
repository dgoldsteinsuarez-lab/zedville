@extends('layouts.profile')
@section('no_page_back', '1'){{-- Back is beside the lesson --}}

@section('title', ct('library', $module->title))

{{-- A FinHero Library module: its lesson, then its quiz one question at a
     time with Back / Continue, then - after submitting - the
     acknowledgement and the result with every answer reviewed (Daniel,
     2026-10-26). The result comes from FinheroStudentController::
     submitLibrary() through a redirect, so a reload never resubmits. --}}
@section('content')
@php
    $total = $questions->count();
    $pageKey = str_replace('_', '-', $activity->activity_key);
@endphp
{{-- Back in its own column on the left, so the lesson starts at the top of
     the page instead of under it (Daniel, 2026-10-28: "maximize the space ...
     move up and start the lesson box"); on a narrow page Back sits above it. --}}
<div class="zl-outer">
<div class="zl-page">
<div class="zl-back">
    @include('partials.page-back', ['fallback' => route('education.educational_finance_department'), 'label' => __('layout.back'),
        'buttonClass' => 'zvBackBtn inline-flex items-center gap-2 py-2 px-4 text-sm font-semibold'])
</div>
<div class="zl-wrap">
    <div class="zl-card" id="zlCard">
        <header class="zl-head">
            <span class="zl-icon" aria-hidden="true">{!! \App\Support\ModuleIcon::html($module->icon) !!}</span>
            <div class="min-w-0">
                <p class="zl-kicker">{{ __('finhero.library.lesson') }}</p>
                <h1 class="zl-title">{{ ct('library', $module->title) }}</h1>
                <p class="zl-sub">{{ trans_choice('finhero.library.question_count', $total, ['count' => $total]) }}</p>
            </div>
        </header>

        @if ($result)
            @php
                $correct = $result['correct'];
                $cheer = $correct === $total ? 'cheer_perfect' : ($total && $correct / $total >= 0.7 ? 'cheer_good' : 'cheer_try');
            @endphp
            <section class="zl-result">
                <div class="zl-ack" role="status">
                    <svg class="zl-ack-icon" viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="24"/><path d="M15 27l7 7 15-16"/></svg>
                    <h2>{{ __('finhero.library.submitted') }}</h2>
                </div>

                <div class="zl-score">
                    <span class="zl-score-num">{{ $correct }}<small>/{{ $total }}</small></span>
                    <span class="zl-score-text">{{ __('finhero.library.score', ['correct' => $correct, 'total' => $total]) }}</span>
                </div>
                <p class="zl-cheer">{{ __('finhero.library.' . $cheer) }}</p>
                <p class="zl-points {{ $result['first_attempt'] ? 'is-earned' : '' }}">
                    @if ($result['first_attempt'])
                        ⭐ {{ trans_choice('finhero.library.earned', $result['points'], ['points' => $result['points']]) }}
                    @else
                        {{ __('finhero.library.practice') }}
                    @endif
                </p>

                <h3 class="zl-review-title">{{ __('finhero.library.review') }}</h3>
                <ol class="zl-review">
                    @foreach ($questions as $index => $question)
                        @php
                            $chosen = $question->options->firstWhere('id', $result['answers'][$question->id] ?? null);
                            $right = $question->options->firstWhere('is_correct', true);
                            $ok = $chosen && $right && $chosen->id === $right->id;
                        @endphp
                        <li class="zl-rev {{ $ok ? 'is-ok' : 'is-wrong' }}">
                            <span class="zl-mark" aria-hidden="true">{{ $ok ? '✓' : '✗' }}</span>
                            <div class="min-w-0">
                                <p class="zl-rev-q">{{ $index + 1 }}. {{ ct('library', $question->question_text) }}</p>
                                @if ($ok)
                                    <p class="zl-rev-a">{{ __('finhero.library.correct') }} · {{ ct('library', $chosen->option_text) }}</p>
                                @else
                                    <p class="zl-rev-a">{{ $chosen ? __('finhero.library.your_answer', ['answer' => ct('library', $chosen->option_text)]) : __('finhero.library.not_answered') }}</p>
                                    @if ($right)
                                        <p class="zl-rev-right">{{ __('finhero.library.right_answer', ['answer' => ct('library', $right->option_text)]) }}</p>
                                    @endif
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>

                <div class="zl-actions">
                    <a href="{{ route('library.page', $pageKey) }}" class="secondaryBtn">{{ __('finhero.library.try_again') }}</a>
                    @include('partials.finish', ['inline' => true])
                </div>
            </section>
        @else
            <form method="POST" action="{{ route('library.submit', $pageKey) }}" id="zlQuiz">
                @csrf
                {{-- Step 0: the lesson. --}}
                <section class="zl-step" data-step>
                    @if (!empty($module->lesson))
                        {{-- its name, over the lesson when it stays beside the questions --}}
                        <p class="zl-ref-title" aria-hidden="true"><span class="zl-ref-icon">{!! \App\Support\ModuleIcon::html($module->icon) !!}</span>{{ ct('library', $module->title) }}</p>
                        <div class="zl-lesson">
                            @foreach ($module->lesson as $section)
                                <div class="zl-part">
                                <h2 class="zl-lesson-title">{{ ct('library', $section['title']) }}</h2>
                                @php $inList = false; @endphp
                                @foreach ($section['lines'] as $line)
                                    @if (str_starts_with($line, '• '))
                                        @if (!$inList)<ul>@php $inList = true; @endphp@endif
                                        <li>{{ mb_substr(ct('library', $line), 2) }}</li>
                                    @else
                                        @if ($inList)</ul>@php $inList = false; @endphp@endif
                                        <p>{{ ct('library', $line) }}</p>
                                    @endif
                                @endforeach
                                @if ($inList)</ul>@endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="zl-start">
                        @if ($total > 0)
                            <button type="button" class="themeBtn" data-go="1">{{ __('finhero.library.start') }} →</button>
                        @else
                            <p class="zl-sub">{{ __('finhero.library.no_questions') }}</p>
                        @endif
                    </div>
                </section>

                {{-- Steps 1..n: one question each. --}}
                @foreach ($questions as $index => $question)
                    <section class="zl-step" data-step hidden>
                        <div class="zl-progress">
                            <span>{{ __('finhero.library.question_of', ['current' => $index + 1, 'total' => $total]) }}</span>
                            <span class="zl-bar" aria-hidden="true"><span style="width: {{ round(($index + 1) / $total * 100) }}%"></span></span>
                        </div>
                        <h2 class="zl-question" tabindex="-1">{{ ct('library', $question->question_text) }}</h2>
                        @if ($question->image)
                            <img src="{{ asset('uploads/finhero/' . $question->image) }}" alt="" class="zl-image">
                        @endif
                        {{-- The app's answer cards (theme_style.css styles them inside .quizQuestionSecItem .questionLists). --}}
                        <div class="quizQuestionSecItem">
                            <div class="questionLists" role="radiogroup" aria-label="{{ ct('library', $question->question_text) }}">
                                @foreach ($question->options as $option)
                                    <div class="quizItems">
                                        <input type="radio" class="zl-radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" id="opt{{ $option->id }}">
                                        <label for="opt{{ $option->id }}" class="quizLabel">{{ ct('library', $option->option_text) }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach

                <footer class="zl-nav" data-nav hidden>
                    <button type="button" class="secondaryBtn" data-back>← {{ __('finhero.library.back') }}</button>
                    <p class="zl-hint" data-hint>{{ __('finhero.library.choose') }}</p>
                    <button type="button" class="themeBtn" data-next>{{ __('finhero.library.continue') }} →</button>
                    <button type="submit" class="themeBtn" data-submit data-busy="{{ __('finhero.library.submitting') }}" hidden>{{ __('finhero.library.submit') }} ✓</button>
                </footer>
            </form>
        @endif
    </div>
</div>
</div>
</div>
@endsection

@push('styles')
<style>
    main:has(.zl-outer) { padding-top: 20px; }
    .zl-outer { container-type: inline-size; }
    /* Back keeps its own column beside the card while the card still has 740px or so
       (2026-10-29), so the card starts at the top of the page */
    .zl-page { display: grid; grid-template-columns: minmax(max-content, 1fr) minmax(0, 1040px) minmax(0, 1fr); column-gap: 16px; align-items: start; }
    .zl-page > .zl-wrap { grid-column: 2; }
    @container (max-width: 880px) {
        .zl-page { grid-template-columns: minmax(0, 1fr); }
        .zl-back { margin-bottom: 16px; }
        .zl-page > .zl-wrap { grid-column: 1; }
    }
    .zl-wrap { width: 100%; max-width: 1040px; margin: 0 auto; padding: 0 0 24px; }
    .zl-card { background: #fff; border: 1px solid #D2DDDB; border-radius: 16px; padding: clamp(20px, 4vw, 36px); scroll-margin-top: 20px; }
    .zl-head { display: flex; gap: 16px; align-items: center; padding-bottom: 20px; margin-bottom: 24px; border-bottom: 1px solid #E6ECEA; }
    /* while answering, less padding, so the question, its answers and Next fit the
       screen (Daniel, 2026-10-28) */
    .zl-card.is-quiz { padding-top: 18px; padding-bottom: 20px; }
    .zl-icon { font-size: 40px; line-height: 1; flex: none; }
    .zl-kicker { font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #00A47D; margin: 0 0 4px; }
    .zl-title { font: 800 clamp(22px, 3vw, 28px)/1.2 "Manrope", sans-serif; margin: 0; color: #0F2A22; }
    .zl-sub { margin: 4px 0 0; color: #5E7069; font-size: 15px; }
    .zl-step[hidden], .zl-nav[hidden], [data-submit][hidden], [data-next][hidden], .zl-hint[hidden] { display: none !important; }

    .zl-lesson { color: #1F2D28; font-size: 16px; line-height: 1.65; }
    .zl-lesson-title { font: 700 19px/1.3 "Manrope", sans-serif; margin: 22px 0 8px; color: #016950; }
    .zl-lesson-title:first-child { margin-top: 0; }
    .zl-lesson p { margin: 0 0 10px; }
    .zl-lesson ul { margin: 0 0 12px; padding-left: 22px; list-style: disc; }
    .zl-lesson li { margin: 4px 0; }
    .zl-start { margin-top: 28px; display: flex; justify-content: flex-end; }

    /* the lesson on one screen with Start at its bottom right (Daniel, 2026-10-29:
       "squeeze this introduction to budget box ... so I would not need to scroll"):
       a header of one line - the title, then Lesson and the number of questions at
       its right - the lesson in two columns, and a little less space between lines */
    main:has(.zl-outer) { padding-bottom: 8px; }
    .zl-outer .zl-wrap { padding-bottom: 0; }
    .zl-card:not(.is-quiz):has(.zl-lesson) { padding: 18px 32px 16px; }
    .zl-card:not(.is-quiz):has(.zl-lesson) .zl-head { padding-bottom: 12px; margin-bottom: 14px; }
    .zl-card:not(.is-quiz):has(.zl-lesson) .zl-head > .min-w-0 { flex: 1; display: flex; flex-wrap: wrap; align-items: baseline; column-gap: 10px; }
    .zl-card:not(.is-quiz):has(.zl-lesson) .zl-kicker { order: 2; margin: 0 0 0 auto; }
    .zl-card:not(.is-quiz):has(.zl-lesson) .zl-sub { order: 3; margin: 0; font-size: 14px; }
    .zl-card:not(.is-quiz):has(.zl-lesson) .zl-sub::before { content: "·"; margin-right: 10px; color: #B7C4BF; }
    .zl-card:not(.is-quiz):has(.zl-lesson) .zl-title { order: 1; font-size: 26px; }
    .zl-lesson { line-height: 1.5; }
    .zl-lesson .zl-lesson-title { font-size: 18px; margin: 0 0 6px; }
    .zl-lesson p { margin: 0 0 8px; }
    .zl-lesson ul { margin: 0 0 8px; }
    .zl-lesson li { margin: 1px 0; }
    .zl-part + .zl-part { margin-top: 14px; }
    /* two columns the text flows through and that end level - not one part a column,
       where the longer part decides the height (in French Tracking Income & Expenses
       needed 110px more) - a heading never left alone at a column's foot */
    @container (min-width: 760px) {
        .zl-lesson { columns: 2; column-gap: 56px; column-rule: 1px solid #E6ECEA; }
        .zl-lesson .zl-lesson-title { break-after: avoid; }
        .zl-lesson li { break-inside: avoid; }
    }
    /* a smaller laptop: the lines a little smaller, so the longest lessons still fit */
    @container (min-width: 760px) and (max-width: 1080px) {
        .zl-lesson { font-size: 15px; }
    }
    .zl-lesson + .zl-start { margin-top: 10px; padding-top: 12px; border-top: 1px solid #E6ECEA; }

    /* While answering, the lesson stays in view (Daniel, 2026-10-29: "keep the context
       of 1st page on the left side ... make it all fit ... no scrolling up and down.
       Then each question" beside it; 2026-11-02: "Show also the notes (1st page) when
       its minimized"): on the left from a 600px page - half and half, 3:2 from 900px -
       its text made just small enough to fit (fitLesson, 15px down to 12px); on a
       phone under the question, so the question still comes first. The card's header
       gives way to the lesson's name over it. */
    .zl-ref-title { display: none; }
    .zl-card.is-quiz .zl-head, .zl-card.is-quiz .zl-start { display: none; }
    .zl-card.is-quiz #zlQuiz > .zl-step:first-of-type { padding: 12px 16px; background: #F7FAF9; border: 1px solid #E6ECEA; border-radius: 12px; }
    /* a module without a lesson: the questions on their own */
    .zl-card.is-quiz #zlQuiz:not(:has(.zl-lesson)) { display: block; }
    .zl-card.is-quiz #zlQuiz > .zl-step:first-of-type:not(:has(.zl-lesson)) { display: none; }
    .zl-card.is-quiz .zl-ref-title { display: flex; align-items: center; gap: 8px; margin: 0 0 8px; padding-bottom: 8px; border-bottom: 1px solid #E3ECE8;
        font: 800 16px/1.25 "Manrope", sans-serif; color: #0F2A22; }
    .zl-ref-icon { font-size: 20px; line-height: 1; }
    .zl-card.is-quiz .zl-lesson { columns: auto; font-size: 15px; line-height: 1.42; }
    .zl-card.is-quiz .zl-lesson .zl-lesson-title { font-size: 1.1em; margin: 0 0 .3em; }
    .zl-card.is-quiz .zl-lesson p { margin: 0 0 .45em; }
    .zl-card.is-quiz .zl-lesson ul { margin: 0 0 .45em; padding-left: 1.3em; }
    .zl-card.is-quiz .zl-lesson li { margin: 0; }
    .zl-card.is-quiz .zl-part + .zl-part { margin-top: .7em; }
    /* in a column half the card wide or less, the hint goes over the buttons */
    .zl-card.is-quiz .zl-hint { order: -1; flex-basis: 100%; min-width: 0; font-size: 13px; }
    @container (max-width: 599px) {
        .zl-card.is-quiz #zlQuiz { display: flex; flex-direction: column; }
        .zl-card.is-quiz #zlQuiz > .zl-step:first-of-type { order: 1; margin-top: 20px; }
    }
    @container (min-width: 600px) {
        .zl-card.is-quiz { padding-left: 20px; padding-right: 20px; }
        .zl-card.is-quiz #zlQuiz { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); grid-template-rows: 1fr auto; column-gap: 20px; }
        .zl-card.is-quiz #zlQuiz > .zl-step:first-of-type { grid-column: 1; grid-row: 1 / 3; }
        .zl-card.is-quiz #zlQuiz > .zl-step:not(:first-of-type) { grid-column: 2; grid-row: 1; }
        .zl-card.is-quiz #zlQuiz > .zl-nav { grid-column: 2; grid-row: 2; align-self: end; }
        .zl-card.is-quiz .zl-question { font-size: 18px; }
    }
    @container (min-width: 900px) {
        .zl-card.is-quiz { padding-left: 24px; padding-right: 24px; }
        .zl-card.is-quiz #zlQuiz { grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); column-gap: 24px; }
        .zl-card.is-quiz .zl-question { font-size: 20px; }
        .zl-card.is-quiz .zl-hint { order: 0; flex-basis: 0%; }
    }

    .zl-progress { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; font-size: 13px; font-weight: 700; color: #5E7069; }
    .zl-bar { display: block; height: 6px; border-radius: 99px; background: #E6ECEA; overflow: hidden; }
    .zl-bar span { display: block; height: 100%; background: #00A47D; border-radius: 99px; transition: width .3s; }
    .zl-question { font: 700 clamp(19px, 2.6vw, 23px)/1.35 "Manrope", sans-serif; color: #0F2A22; margin: 0 0 18px; outline: none; }
    .zl-image { display: block; max-width: 100%; max-height: 240px; border-radius: 10px; margin: 0 auto 18px; }
    .zl-radio { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .quizItems { position: relative; }
    .quizItems .zl-radio:focus-visible + label { outline: 3px solid #00A47D; outline-offset: 2px; }

    .zl-nav { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 18px; padding-top: 18px; border-top: 1px solid #E6ECEA; }
    .zl-hint { flex: 1; margin: 0; text-align: center; font-size: 14px; color: #5E7069; min-width: 140px; }
    .zl-nav [data-next], .zl-nav [data-submit] { margin-left: auto; }
    .zl-nav button:disabled { opacity: .45; cursor: not-allowed; transform: none; }

    .zl-ack { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; margin-bottom: 18px; }
    .zl-ack h2 { font: 800 22px/1.3 "Manrope", sans-serif; color: #016950; margin: 0; }
    .zl-ack-icon { width: 64px; height: 64px; }
    .zl-ack-icon circle { fill: #E2F6EF; stroke: #00A47D; stroke-width: 3; }
    .zl-ack-icon path { fill: none; stroke: #00A47D; stroke-width: 4; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 40; stroke-dashoffset: 40; animation: zlTick .5s .2s ease-out forwards; }
    @keyframes zlTick { to { stroke-dashoffset: 0; } }
    .zl-score { display: flex; flex-direction: column; align-items: center; gap: 2px; }
    .zl-score-num { font: 800 48px/1 "Manrope", sans-serif; color: #0F2A22; font-variant-numeric: tabular-nums; }
    .zl-score-num small { font-size: 24px; color: #5E7069; }
    .zl-score-text { font-size: 15px; color: #5E7069; }
    .zl-cheer { text-align: center; font-size: 17px; font-weight: 600; margin: 14px 0 6px; color: #1F2D28; }
    .zl-points { text-align: center; font-size: 14px; color: #5E7069; margin: 0 0 26px; }
    .zl-points.is-earned { color: #A0570B; font-weight: 700; }
    .zl-review-title { font: 700 17px "Manrope", sans-serif; margin: 0 0 12px; color: #0F2A22; }
    .zl-review { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
    .zl-rev { display: flex; gap: 12px; padding: 12px 14px; border-radius: 10px; border: 1px solid #E6ECEA; }
    .zl-rev.is-ok { background: #F1FAF6; border-color: #BFE6D6; }
    .zl-rev.is-wrong { background: #FFF6F5; border-color: #F3CFCB; }
    .zl-mark { flex: none; width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; font-weight: 800; color: #fff; background: #00A47D; font-size: 14px; }
    .zl-rev.is-wrong .zl-mark { background: #D9534F; }
    .zl-rev-q { margin: 0 0 4px; font-weight: 700; color: #0F2A22; }
    .zl-rev-a, .zl-rev-right { margin: 0; font-size: 14px; color: #45564F; }
    .zl-rev-right { color: #016950; font-weight: 600; }
    .zl-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 26px; }
    @media (prefers-reduced-motion: reduce) { .zl-ack-icon path { animation: none; stroke-dashoffset: 0; } .zl-bar span { transition: none; } }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var form = document.getElementById('zlQuiz');
        if (!form) return;
        var steps = Array.prototype.slice.call(form.querySelectorAll('[data-step]'));
        var nav = form.querySelector('[data-nav]');
        var back = form.querySelector('[data-back]');
        var next = form.querySelector('[data-next]');
        var submit = form.querySelector('[data-submit]');
        var hint = form.querySelector('[data-hint]');
        var current = 0;

        function answered(step) { return !!step.querySelector('input[type=radio]:checked'); }

        // Beside the questions the whole lesson has to fit above the bottom of the
        // screen: its text goes down from 15px to at most 12px until it does. One
        // that still couldn't would scroll in its own place, never the page. Under
        // the question (a phone) it just follows it.
        function fitLesson() {
            var lesson = steps[0], text = lesson.querySelector('.zl-lesson');
            if (!text) return;
            text.style.fontSize = lesson.style.maxHeight = lesson.style.overflowY = '';
            if (current === 0 || !lesson.offsetParent || getComputedStyle(form).display !== 'grid') return;
            var main = form.closest('main'), mainTop = main.getBoundingClientRect().top - main.scrollTop;
            var limit = main.clientHeight - parseFloat(getComputedStyle(main).paddingBottom)
                - parseFloat(getComputedStyle(document.getElementById('zlCard')).paddingBottom) - 6;
            var end = function () { return text.getBoundingClientRect().bottom - mainTop + parseFloat(getComputedStyle(lesson).paddingBottom) + 1; };
            for (var size = 15; size >= 12; size -= 0.5) {
                text.style.fontSize = size + 'px';
                if (end() <= limit) return;
            }
            lesson.style.maxHeight = Math.max(200, limit - (lesson.getBoundingClientRect().top - mainTop)) + 'px';
            lesson.style.overflowY = 'auto';
        }

        function show(n, moved) {
            current = Math.max(0, Math.min(n, steps.length - 1));
            // the lesson (step 0) stays beside the questions
            steps.forEach(function (step, i) { step.hidden = i !== current && !(i === 0 && current > 0); });
            nav.hidden = current === 0;
            document.getElementById('zlCard').classList.toggle('is-quiz', current > 0);
            fitLesson();
            if (current > 0) {
                var last = current === steps.length - 1;
                var ok = answered(steps[current]);
                next.hidden = last;
                submit.hidden = !last;
                next.disabled = !ok;
                submit.disabled = !ok;
                hint.hidden = ok;
            }
            if (moved) {
                document.getElementById('zlCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
                var title = steps[current].querySelector('.zl-question');
                if (title) title.focus({ preventScroll: true });
            }
        }

        form.addEventListener('change', function () { show(current); });
        // the page area also changes size when the sidebar opens or closes
        if (window.ResizeObserver) new ResizeObserver(function () { fitLesson(); }).observe(form.closest('main'));
        else window.addEventListener('resize', fitLesson);
        form.querySelectorAll('[data-go]').forEach(function (button) {
            button.addEventListener('click', function () { show(Number(button.dataset.go), true); });
        });
        next.addEventListener('click', function () { if (answered(steps[current])) show(current + 1, true); });
        back.addEventListener('click', function () { show(current - 1, true); });
        form.addEventListener('submit', function (event) {
            if (!steps.slice(1).every(answered)) { event.preventDefault(); return; }
            submit.disabled = true;
            submit.textContent = submit.dataset.busy;
        });
        show(0);
    })();
</script>
@endpush
