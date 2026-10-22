@extends('layouts.profile')

@section('title', $item->title . ' - Results')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    @if ($item->category->is_pisa_practice)
        <a href="{{ $item->pisaLevel ? route('pisa.level', $item->pisaLevel) : route('pisa.index') }}" class="text-sm text-gray-500">&larr; {{ __('messages.nav_pisa') }}</a>
    @else
        <a href="{{ route('academy.category', $item->category) }}" class="text-sm text-gray-500">&larr; {{ $item->category->name }}</a>
    @endif
</div>

<div class="max-w-[600px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded p-8">
    <h2 class="text-xl font-semibold mb-2 text-center">{{ $item->title }}</h2>
    <p class="text-3xl font-bold text-center mb-6">{{ $attempt->score }} / {{ $attempt->total_questions }}</p>

    <div class="space-y-4">
        @foreach ($attempt->answers as $answer)
            <div class="border-b border-color-[#D2DDDB] pb-3">
                <p class="font-semibold mb-1">{{ $answer->question->question_text ?? __('academy.question_removed') }}</p>
                <p class="text-sm mb-0 {{ $answer->is_correct ? 'text-green-700' : 'text-red-600' }}">
                    @if ($answer->is_correct)
                        {{ __('academy.correct') }}{{ $answer->option ? ' - ' . $answer->option->option_text : '' }}
                    @else
                        {{ $answer->option ? __('academy.you_answered') . ': ' . $answer->option->option_text : __('academy.not_answered') }}
                    @endif
                </p>
            </div>
        @endforeach
    </div>

    @if ($item->is_real_exam)
        <p class="text-center text-sm text-gray-500 mt-4 mb-0">{{ __('academy.real_exam_final_note') }}</p>
    @endif

    <div class="flex justify-center gap-4 mt-6">
        @unless ($item->is_real_exam)
            <a href="{{ route('academy.item', $item) }}" class="themeBtn">{{ __('academy.try_again') }}</a>
        @endunless
        @if ($item->category->is_pisa_practice)
            <a href="{{ $item->pisaLevel ? route('pisa.level', $item->pisaLevel) : route('pisa.index') }}" class="secondaryBtn">{{ __('academy.back_to') }} {{ __('messages.nav_pisa') }}</a>
        @else
            <a href="{{ route('academy.category', $item->category) }}" class="secondaryBtn">{{ __('academy.back_to') }} {{ $item->category->name }}</a>
        @endif
    </div>
</div>
@endsection
