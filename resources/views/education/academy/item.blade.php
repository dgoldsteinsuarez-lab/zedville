@extends('layouts.profile')

@section('title', $item->title)

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <a href="{{ route('academy.category', $item->category) }}" class="text-sm text-gray-500">&larr; {{ $item->category->name }}</a>
</div>

<div class="grid grid-cols-1 gap-5 mt-2">
    <div class="themeTabspills">
        <div class="w-full">
            <div class="max-w-[600px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded">
                @if ($questions->isEmpty())
                    <div class="p-10 text-center">
                        <p class="mb-0">This item has no questions yet - check back soon.</p>
                    </div>
                @else
                <form action="{{ route('academy.item.submit', $item) }}" method="post">
                    @csrf
                    <div class="quizeIntroSteps p-10" id="quizeIntroText">
                        <h2 class="text-xl font-semibold mb-4 text-center">{{ $item->title }}</h2>
                        @if ($item->description)
                            <p class="text-center text-gray-600 mb-4">{{ $item->description }}</p>
                        @endif
                        <div class="introText text-center mt-4">
                            <p class="mb-2 text-lg">{{ $questions->count() }} {{ \Illuminate\Support\Str::plural('question', $questions->count()) }} - ready when you are.</p>
                        </div>
                        <div class="tab-nav flex justify-center gap-4 mt-8">
                            <button type="button" class="themeBtn startQuiz" id="startQuiz">Start</button>
                        </div>
                    </div>

                    <div class="quizeSecQuestion hidden" id="quizQuestions">
                        <div class="quizQuestionSecItem" id="questionansSet">
                            <div class="quizQuestionSec">
                                @foreach ($questions as $index => $question)
                                    <div class="singleQuiz @if ($index === 0) active @endif">
                                        <div class="quesHeading p-4 border-b border-color-[#D2DDDB] shadow-sm">
                                            <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                                                <h4 class="text-xl font-semibold">Question {{ $index + 1 }}</h4>
                                                <span class="text-sm font-semibold">{{ $index + 1 }}/{{ $questions->count() }}</span>
                                            </div>
                                            <h2 class="questionTitle text-2xl font-semibold">{{ $question->question_text }}</h2>
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
                                <button type="button" class="secondaryBtn qs-prev hidden">Previous</button>
                                <button type="button" class="themeBtn qs-next @if ($questions->count() <= 1) hidden @endif">Next</button>
                                <button type="submit" class="themeBtn submitBtn @if ($questions->count() > 1) hidden @endif">Submit</button>
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
