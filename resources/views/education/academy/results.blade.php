@extends('layouts.profile')
@section('no_page_back', '1'){{-- has its own back link at the top --}}

@section('title', ct('curriculum', $item->title) . ' - Results')

@section('content')
@php
    $backUrl = $item->category->is_pisa_practice
        ? ($item->pisaLevel ? route('pisa.level', $item->pisaLevel) : route('pisa.index'))
        : route('academy.category', $item->category);
    $backLabel = $item->category->is_pisa_practice ? __('messages.nav_pisa') : ct('curriculum', $item->category->name);
@endphp
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <a href="{{ $backUrl }}" class="zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-3 text-sm">&larr; {{ $backLabel }}</a>
</div>

<div class="max-w-[640px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded p-8">
    <h2 class="text-xl font-semibold mb-2 text-center">{{ ct('curriculum', $item->title) }}</h2>

    @if ($item->is_real_exam && empty($released))
        {{-- A real exam's mark is the tutor's until they release it (Daniel,
             2026-10-22 and 2026-10-31) - until then the student only sees
             that it was handed in. --}}
        <div class="text-center py-4">
            <div class="text-4xl mb-3">&#9989;</div>
            <p class="text-lg font-semibold mb-2">{{ __('exams.submitted_title') }}</p>
            <p class="text-gray-600 mb-2">{{ __('exams.submitted_text') }}</p>
            <p class="text-sm text-gray-500 mb-0">{{ __('exams.submitted_on', ['date' => $attempt->completed_at?->isoFormat('LLL')]) }}</p>
        </div>
    @else
        @if ($item->is_real_exam)
            <p class="text-sm text-gray-500 text-center mb-1">{{ __('exams.your_result') }}</p>
        @endif
        <p class="text-3xl font-bold text-center mb-6">{{ $attempt->score }} / {{ $attempt->total_questions }}</p>

        <div class="space-y-4">
            @foreach ($attempt->answers as $answer)
                @php
                    $question = $answer->question;
                    $rightOption = $question ? $question->options->firstWhere('is_correct', true) : null;
                @endphp
                <div class="border-b border-color-[#D2DDDB] pb-3">
                    <p class="font-semibold mb-1">{{ ct('curriculum', $question?->question_text) ?? __('academy.question_removed') }}</p>
                    <p class="text-sm mb-0 {{ $answer->is_correct ? 'text-green-700' : 'text-red-600' }}">
                        @if ($answer->is_correct)
                            {{ __('academy.correct') }}{{ $answer->option ? ' - ' . ct('curriculum', $answer->option->option_text) : '' }}
                        @else
                            {{ $answer->option ? __('academy.you_answered') . ': ' . ct('curriculum', $answer->option->option_text) : __('academy.not_answered') }}
                        @endif
                    </p>
                    {{-- Hidden until the student asks to check the right answers. --}}
                    @if ($rightOption || $question?->explanation)
                        <div class="zv-right-answer hidden mt-2 rounded bg-green-50 border border-green-200 p-3 text-sm">
                            @if ($rightOption)
                                <p class="mb-1"><strong>{{ __('exams.right_answer') }}:</strong> {{ ct('curriculum', $rightOption->option_text) }}</p>
                            @endif
                            @if ($question?->explanation)
                                <p class="mb-0 text-gray-700"><strong>{{ __('exams.why') }}:</strong> {{ ct('curriculum', $question->explanation) }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <button type="button" class="secondaryBtn" id="zvToggleAnswers"
                data-show="{{ __('exams.check_answers') }}" data-hide="{{ __('exams.hide_answers') }}">
                {{ __('exams.check_answers') }}
            </button>
        </div>
    @endif

    <div class="flex justify-center gap-4 mt-6">
        @unless ($item->is_real_exam)
            <a href="{{ route('academy.item', $item) }}" class="secondaryBtn">{{ __('academy.try_again') }}</a>
        @endunless
        @include('partials.finish', ['inline' => true, 'to' => $backUrl])
    </div>

    @if ($item->category->is_pisa_practice)
        <p class="text-xs text-gray-400 text-center mt-6 mb-0">{{ __('exams.disclaimer') }}</p>
    @endif
</div>

<script>
    (function () {
        var button = document.getElementById('zvToggleAnswers');
        if (!button) return;
        var shown = false;
        button.addEventListener('click', function () {
            shown = !shown;
            document.querySelectorAll('.zv-right-answer').forEach(function (el) { el.classList.toggle('hidden', !shown); });
            button.textContent = shown ? button.dataset.hide : button.dataset.show;
        });
    })();
</script>
@endsection
