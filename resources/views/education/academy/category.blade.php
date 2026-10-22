@extends('layouts.profile')

@section('title', $category->name)

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <div>
        <a href="{{ route('academy.index') }}" class="text-sm text-gray-500">&larr; {{ __('academy.back_to_academy') }}</a>
        <h1 class="text-xl font-bold whitespace-nowrap mt-1">{{ $category->name }}</h1>
    </div>
</div>

@if ($items->isEmpty())
    <div class="bg-white border border-color-[#D2DDDB] rounded p-6 text-center">
        <p class="mb-0">{{ __('academy.nothing_here_yet') }}</p>
    </div>
@else
    @php
        $realExams = $items->where('is_real_exam', true);
        $practiceItems = $items->where('is_real_exam', false);
        $hasMix = $realExams->isNotEmpty() && $practiceItems->isNotEmpty();
    @endphp

    @if ($hasMix)
        {{-- Daniel, 2026-10-21: "Exams, should be one for practice exams...
             add a section of practice exams and real exams." Grouped by
             the is_real_exam flag, not the category name - categories
             have no stable "this is the Exams one" signal, and this way
             the split only ever appears where it's actually meaningful. --}}
        <h2 class="text-base font-semibold mb-3">{{ __('academy.real_exam_section') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
            @foreach ($realExams as $item)
                @include('education.academy._item-card', ['item' => $item])
            @endforeach
        </div>
        <h2 class="text-base font-semibold mb-3">{{ __('academy.practice_exams_section') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($practiceItems as $item)
                @include('education.academy._item-card', ['item' => $item])
            @endforeach
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($items as $item)
                @include('education.academy._item-card', ['item' => $item])
            @endforeach
        </div>
    @endif
@endif
@endsection
