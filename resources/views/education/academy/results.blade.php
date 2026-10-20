@extends('layouts.profile')

@section('title', $item->title . ' - Results')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <a href="{{ route('academy.category', $item->category) }}" class="text-sm text-gray-500">&larr; {{ $item->category->name }}</a>
</div>

<div class="max-w-[600px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded p-8">
    <h2 class="text-xl font-semibold mb-2 text-center">{{ $item->title }}</h2>
    <p class="text-3xl font-bold text-center mb-6">{{ $attempt->score }} / {{ $attempt->total_questions }}</p>

    <div class="space-y-4">
        @foreach ($attempt->answers as $answer)
            <div class="border-b border-color-[#D2DDDB] pb-3">
                <p class="font-semibold mb-1">{{ $answer->question->question_text ?? '(question removed)' }}</p>
                <p class="text-sm mb-0 {{ $answer->is_correct ? 'text-green-700' : 'text-red-600' }}">
                    @if ($answer->is_correct)
                        Correct{{ $answer->option ? ' - ' . $answer->option->option_text : '' }}
                    @else
                        {{ $answer->option ? 'You answered: ' . $answer->option->option_text : 'Not answered' }}
                    @endif
                </p>
            </div>
        @endforeach
    </div>

    <div class="flex justify-center gap-4 mt-6">
        <a href="{{ route('academy.item', $item) }}" class="themeBtn">Try again</a>
        <a href="{{ route('academy.category', $item->category) }}" class="secondaryBtn">Back to {{ $item->category->name }}</a>
    </div>
</div>
@endsection
