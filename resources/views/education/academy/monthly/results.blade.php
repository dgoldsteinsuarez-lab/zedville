@extends('layouts.profile')

@section('title', $title . ' - Results')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <a href="{{ route('academy.monthly') }}" class="text-sm text-gray-500">&larr; {{ __('academy.monthly_title') }}</a>
</div>

<div class="max-w-[600px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded p-8">
    <h2 class="text-xl font-semibold mb-2 text-center">{{ $title }}</h2>
    <p class="text-3xl font-bold text-center mb-6">{{ $score }} / {{ $rows->count() }}</p>

    <div class="space-y-4">
        @foreach ($rows as $row)
            <div class="border-b border-color-[#D2DDDB] pb-3">
                <p class="font-semibold mb-1">{{ $row['question'] }}</p>
                @if ($row['is_correct'])
                    <p class="text-sm mb-0 text-green-700">{{ __('academy.correct') }} - {{ $row['chosen'] }}</p>
                @else
                    <p class="text-sm mb-0 text-red-600">{{ $row['chosen'] ? __('academy.you_answered') . ': ' . $row['chosen'] : __('academy.not_answered') }}</p>
                    @if ($row['correct'])
                        <p class="text-sm mb-0 text-green-700">{{ __('academy.correct_answer') }}: {{ $row['correct'] }}</p>
                    @endif
                @endif
            </div>
        @endforeach
    </div>

    <div class="flex justify-center gap-4 mt-6">
        <a href="{{ $retryUrl }}" class="themeBtn">{{ __('academy.try_again') }}</a>
        <a href="{{ route('academy.monthly') }}" class="secondaryBtn">{{ __('academy.back_to') }} {{ __('academy.monthly_title') }}</a>
    </div>
</div>
@endsection
