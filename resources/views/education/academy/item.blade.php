@extends('layouts.profile')
@section('no_page_back', '1'){{-- has its own back link at the top --}}

@section('title', ct('curriculum', $item->title))

@section('content')
<div class="flex flex-col items-start justify-between pb-2 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    @if ($item->category->is_pisa_practice)
        <a href="{{ $item->pisaLevel ? route('pisa.level', $item->pisaLevel) : route('pisa.index') }}" class="zvBackBtn inline-flex items-center gap-2 py-2 px-4 text-sm">&larr; {{ __('messages.nav_pisa') }}</a>
    @else
        <a href="{{ route('academy.category', $item->category) }}" class="zvBackBtn inline-flex items-center gap-2 py-2 px-4 text-sm">&larr; {{ ct('curriculum', $item->category->name) }}</a>
    @endif
</div>

{{-- The item's picture and text (the PISA "stimulus": a fare table, a
     chart...) stay on screen while the student answers, like the real test
     (Daniel, 2026-10-22) - beside the questions on a wide screen, above them
     (and foldable) on a narrow one. --}}
@php $hasStimulus = $item->image || $item->description; @endphp
<div class="grid grid-cols-1 gap-5 mt-2">
    <div class="themeTabspills">
        <div class="w-full">
            <div class="zv-item-card mx-auto w-full bg-white border border-color-[#D2DDDB] rounded" id="itemCard">
                @if ($questions->isEmpty())
                    <div class="p-10 text-center">
                        <p class="mb-0">{{ __('academy.no_questions_yet') }}</p>
                    </div>
                @else
                <form action="{{ route('academy.item.submit', $item) }}" method="post">
                    @csrf
                    <div class="quizeIntroSteps p-10" id="quizeIntroText">
                        <h2 class="text-xl font-semibold mb-4 text-center">{{ ct('curriculum', $item->title) }}</h2>
                        @if ($item->image)
                            <img src="{{ ct_image($item->image) }}" alt="" class="mx-auto mb-4 rounded" style="max-width:100%;max-height:260px;">
                        @endif
                        @if ($item->description)
                            <p class="text-center text-gray-600 mb-4">{{ ct('curriculum', $item->description) }}</p>
                        @endif
                        @if ($item->is_real_exam)
                            <p class="text-center text-sm font-semibold text-red-700 mb-2">{{ __('exams.real_exam_intro') }}</p>
                        @endif
                        <div class="introText text-center mt-4">
                            <p class="mb-2 text-lg">{{ trans_choice('academy.question_count_ready', $questions->count(), ['count' => $questions->count()]) }}</p>
                        </div>
                        <div class="tab-nav flex justify-center gap-4 mt-8">
                            <button type="button" class="themeBtn startQuiz" id="startQuiz">{{ __('academy.start') }}</button>
                        </div>
                    </div>

                    <div class="quizeSecQuestion hidden" id="quizQuestions">
                        @if ($hasStimulus)
                        <div class="zv-split">
                        <details class="zv-stimulus" open>
                            <summary>{{ __('academy.stimulus_title') }}</summary>
                            <h3 class="text-lg font-semibold mt-3 mb-3">{{ ct('curriculum', $item->title) }}</h3>
                            @if ($item->image)
                                <img src="{{ ct_image($item->image) }}" alt="" class="rounded mb-3" style="max-width:100%;">
                            @endif
                            @if ($item->description)
                                <p class="text-gray-700 mb-0">{{ ct('curriculum', $item->description) }}</p>
                            @endif
                        </details>
                        @endif
                        <div class="quizQuestionSecItem" id="questionansSet">
                            <div class="quizQuestionSec">
                                @foreach ($questions as $index => $question)
                                    <div class="singleQuiz @if ($index === 0) active @endif">
                                        <div class="quesHeading p-4 border-b border-color-[#D2DDDB] shadow-sm">
                                            <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                                                <h4 class="text-xl font-semibold">{{ __('academy.question') }} {{ $index + 1 }}</h4>
                                                <span class="flex items-center gap-3">
                                                    @if (request()->boolean('timed'))
                                                        <span id="timerDisplay" class="text-sm font-semibold text-red-600">&#9201; 20s</span>
                                                    @endif
                                                    <span class="text-sm font-semibold">{{ $index + 1 }}/{{ $questions->count() }}</span>
                                                </span>
                                            </div>
                                            <h2 class="questionTitle text-2xl font-semibold">{{ ct('curriculum', $question->question_text) }}</h2>
                                        </div>
                                        @if ($question->image)
                                            <div class="px-4 pt-4 flex flex-wrap justify-center gap-4">
                                                <img src="{{ ct_image($question->image) }}" alt="" class="rounded" style="max-width:100%;max-height:220px;">
                                                @if ($question->image_2)
                                                    <img src="{{ ct_image($question->image_2) }}" alt="" class="rounded" style="max-width:100%;max-height:220px;">
                                                @endif
                                            </div>
                                        @endif
                                        <div class="questionLists p-4">
                                            @foreach ($question->options as $option)
                                                <div class="quizItems">
                                                    <input type="radio" class="hidden" name="answers[{{ $question->id }}]" value="{{ $option->id }}" id="opt{{ $option->id }}">
                                                    <label for="opt{{ $option->id }}" class="quizLabel">{{ ct('curriculum', $option->option_text) }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="quizeBtns flex flex-wrap justify-end items-center gap-4 mt-4 p-4">
                                <button type="button" class="secondaryBtn qs-prev hidden">{{ __('academy.previous') }}</button>
                                <button type="button" class="themeBtn qs-next @if ($questions->count() <= 1) hidden @endif">{{ __('academy.next') }}</button>
                                <button type="submit" class="themeBtn submitBtn @if ($questions->count() > 1) hidden @endif">{{ __('academy.submit') }}</button>
                            </div>
                        </div>
                        @if ($hasStimulus)
                        </div>
                        @endif
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* a question with its answers and Next on one screen (Daniel, 2026-10-28: "no
       scrolling up and down"): less white above the card, and a long "information"
       panel scrolls in its own place beside the question instead of the page */
    main:has(#itemCard) { padding-top: 24px; padding-bottom: 16px; }
    .zv-item-card { max-width: 600px; }
    .zv-item-card.is-answering { max-width: 1120px; }
    .zv-split { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); align-items: start; }
    .zv-stimulus {
        position: sticky; top: 0; max-height: calc(100vh - 170px); overflow-y: auto;
        padding: 16px 20px; background: #F7FAF9; border-right: 1px solid #D2DDDB;
    }
    .zv-stimulus summary {
        cursor: pointer; font-size: 12px; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #00A47D;
    }
    @media (max-width: 1023px) {
        .zv-split { grid-template-columns: 1fr; }
        .zv-stimulus { position: static; max-height: none; border-right: 0; border-bottom: 1px solid #D2DDDB; }
    }
</style>
@endpush

@push('scripts')
<script>
    jQuery(document).ready(function() {
        // Daniel, 2026-10-21: "a Game Option, where they play alone..." -
        // the "play timed" solo mode. No change to how answers are
        // submitted (still the normal academy.item.submit form below);
        // this only adds a visible countdown that advances the student
        // along the same Next/Submit buttons the untimed flow already has.
        var TIMED = {{ request()->boolean('timed') ? 'true' : 'false' }};
        var SECONDS_PER_QUESTION = 20;
        var timerInterval = null;

        function startTimer() {
            if (!TIMED) return;
            clearInterval(timerInterval);
            var remaining = SECONDS_PER_QUESTION;
            $("#timerDisplay").text('⏱ ' + remaining + 's');
            timerInterval = setInterval(function() {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    $("#timerDisplay").text('⏱ 0s');
                    var next = $(".qs-next:visible");
                    if (next.length) {
                        next.click();
                    } else {
                        $(".submitBtn:visible").click();
                    }
                } else {
                    $("#timerDisplay").text('⏱ ' + remaining + 's');
                }
            }, 1000);
        }

        $("#startQuiz").click(function() {
            $("#quizeIntroText").addClass("hidden");
            $("#quizQuestions").removeClass("hidden");
            // Wider only when there is information to show beside the questions.
            if ($("#quizQuestions .zv-split").length) $("#itemCard").addClass("is-answering");
            startTimer();
        });

        jQuery(".qs-next").click(function() {
            let qescur = $(this).closest("#questionansSet").find(".singleQuiz.active");
            if (jQuery(qescur).next().length > 0) {
                $(".qs-prev").removeClass("hidden");
                $(this).parent('.quizeBtns').addClass("justify-between");
                jQuery(".singleQuiz").removeClass("active");
                jQuery(qescur).next().addClass("active");
            }
            if (jQuery(qescur).next().next().length == 0) {
                $(".qs-next").addClass("hidden");
                $(".submitBtn").removeClass("hidden");
            }
            startTimer();
        });

        $(".qs-prev").click(function() {
            let qescur = $(this).closest("#questionansSet").find(".singleQuiz.active");
            if (jQuery(qescur).prev().length > 0) {
                $(".submitBtn").addClass("hidden");
                $(".qs-next").removeClass("hidden");
                jQuery(".singleQuiz").removeClass("active");
                jQuery(qescur).prev().addClass("active");
            }
            if (jQuery(qescur).prev().prev().length == 0) {
                $(".qs-prev").addClass("hidden");
                $(this).parent('.quizeBtns').removeClass("justify-between");
            }
        });
    });
</script>
@endpush
