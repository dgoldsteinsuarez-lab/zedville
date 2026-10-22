@extends('layouts.profile')

@section('title', $title)

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <a href="{{ route('academy.monthly') }}" class="text-sm text-gray-500">&larr; {{ __('academy.monthly_title') }}</a>
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
                <form action="{{ $action }}" method="post">
                    @csrf
                    @if ($sendQuestionIds)
                        @foreach ($questions as $question)
                            <input type="hidden" name="question_ids[]" value="{{ $question->id }}">
                        @endforeach
                    @endif
                    <div class="quizeIntroSteps p-10" id="quizeIntroText">
                        <h2 class="text-xl font-semibold mb-4 text-center">{{ $title }}</h2>
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
                                                <span class="text-sm font-semibold">{{ $index + 1 }}/{{ $questions->count() }}</span>
                                            </div>
                                            <h2 class="questionTitle text-2xl font-semibold">{{ $question->question }}</h2>
                                        </div>
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
        $("#startQuiz").click(function() {
            $("#quizeIntroText").addClass("hidden");
            $("#quizQuestions").removeClass("hidden");
        });

        jQuery(".qs-next").click(function() {
            let current = $(this).closest("#questionansSet").find(".singleQuiz.active");
            if (jQuery(current).next().length > 0) {
                $(".qs-prev").removeClass("hidden");
                $(this).parent('.quizeBtns').addClass("justify-between");
                jQuery(".singleQuiz").removeClass("active");
                jQuery(current).next().addClass("active");
            }
            if (jQuery(current).next().next().length == 0) {
                $(".qs-next").addClass("hidden");
                $(".submitBtn").removeClass("hidden");
            }
        });

        $(".qs-prev").click(function() {
            let current = $(this).closest("#questionansSet").find(".singleQuiz.active");
            if (jQuery(current).prev().length > 0) {
                $(".submitBtn").addClass("hidden");
                $(".qs-next").removeClass("hidden");
                jQuery(".singleQuiz").removeClass("active");
                jQuery(current).prev().addClass("active");
            }
            if (jQuery(current).prev().prev().length == 0) {
                $(".qs-prev").addClass("hidden");
                $(this).parent('.quizeBtns').removeClass("justify-between");
            }
        });
    });
</script>
@endpush
