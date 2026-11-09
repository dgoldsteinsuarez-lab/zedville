@extends('layouts.profile')
@section('no_page_back', '1'){{-- has its own back link at the top --}}

@section('title', $title . ' - Results')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <a href="{{ route('academy.monthly') }}" class="zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-3 text-sm">&larr; {{ __('academy.monthly_title') }}</a>
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
        <a href="{{ $retryUrl }}" class="secondaryBtn">{{ __('academy.try_again') }}</a>
        @include('partials.finish', ['inline' => true, 'to' => route('academy.monthly')])
    </div>
</div>
@endsection
