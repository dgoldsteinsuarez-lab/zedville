@extends('layouts.profile')

@section('title', __('academy.title'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">{{ __('academy.title') }} @if($gradeLevel)&mdash; {{ $gradeLevel->name }} {{ __('academy.level_suffix') }} @endif</h1>
</div>

@if ($categories->isEmpty() && !$hasMonthlyQuestions)
    <div class="bg-white border border-color-[#D2DDDB] rounded p-6 text-center">
        <p class="mb-0">{{ __('academy.nothing_here_yet') }}</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @if ($hasMonthlyQuestions)
            <a href="{{ route('academy.monthly') }}"
                class="block bg-white border border-color-[#D2DDDB] rounded p-5 hover:shadow transition">
                <h3 class="text-lg font-semibold mb-1">{{ __('academy.monthly_title') }}</h3>
                <p class="text-sm text-gray-500 mb-0">{{ __('academy.monthly_card_text') }}</p>
            </a>
        @endif
        @foreach ($categories as $category)
            <a href="{{ route('academy.category', $category) }}"
                class="block bg-white border border-color-[#D2DDDB] rounded p-5 hover:shadow transition">
                <h3 class="text-lg font-semibold mb-1">{{ $category->name }}</h3>
                <p class="text-sm text-gray-500 mb-0">
                    {{ trans_choice('academy.item_count', $category->items_count, ['count' => $category->items_count]) }}
                </p>
            </a>
        @endforeach
    </div>
@endif
@endsection
