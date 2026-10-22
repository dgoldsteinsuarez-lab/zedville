@extends('layouts.profile')

@section('title', $item->title)

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    @if ($item->category->is_pisa_practice)
        <a href="{{ $item->pisaLevel ? route('pisa.level', $item->pisaLevel) : route('pisa.index') }}" class="text-sm text-gray-500">&larr; {{ __('messages.nav_pisa') }}</a>
    @else
        <a href="{{ route('academy.category', $item->category) }}" class="text-sm text-gray-500">&larr; {{ $item->category->name }}</a>
    @endif
</div>

<div class="grid grid-cols-1 gap-5 mt-2">
    <div class="themeTabspills">
        <div class="w-full">
            <div class="max-w-[600px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded">
                @if ($questions->isEmpty())
                    <div class="p-10 text-center">
                        <p class="mb-0">{{ __('academy.no_questions_yet') }}</p>
                    </div>
                @else
                <form action="{{ route('academy.item.submit', $item) }}" method="post">
                    @csrf
                    <div class="quizeIntroSteps p-10" id="quizeIntroText">
                        <h2 class="text-xl font-semibold mb-4 text-center">{{ $item->title }}</h2>
                        @if ($item->image)
                            <img src="{{ asset('uploads/curriculum/' . $item->image) }}" alt="" class="mx-auto mb-4 rounded" style="max-width:100%;max-height:260px;">
                        @endif
                        @if ($item->description)
                            <p class="text-center text-gray-600 mb-4">{{ $item->description }}</p>
                        @endif
                        <div class="introText text-center mt-4">
                            <p class="mb-2 text-lg">{{ trans_choice('academy.question_count_ready', $questions->count(), ['count' => $questions->count()]) }}</p>
                        </div>
                        <div class="tab-nav flex justify-center gap-4 mt-8">
                            <button type="button" class="themeBtn startQuiz" id="startQuiz">{{ __('academy.start') }}</button>
                        </div>
                    </div>

                    <div class="quizeSecQuestion hidden" id="quizQuestions">
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
                                            <h2 class="questionTitle text-2xl font-semibold">{{ $question->question_text }}</h2>
                                        </div>
                                        @if ($question->image)
                                            <div class="px-4 pt-4 flex flex-wrap justify-center gap-4">
                                                <img src="{{ asset('uploads/curriculum/' . $question->image) }}" alt="" class="rounded" style="max-width:100%;max-height:220px;">
                                                @if ($question->image_2)
                                                    <img src="{{ asset('uploads/curriculum/' . $question->image_2) }}" alt="" class="rounded" style="max-width:100%;max-height:220px;">
                                                @endif
                                            </div>
                                        @endif
                                        <div class="questionLists p-4">
                                            @foreach ($question->options as $option)
                                                <div class="quizItems">
                                                    <input type="radio" class="hidden" name="answers[{{ $question->id }}]" value="{{ $option->id }}" id="opt{{ $option->id }}">
                                                    <label for="opt{{ $option->id }}" class="quizLabel">{{ $option->option_text }}</label>
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
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

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
