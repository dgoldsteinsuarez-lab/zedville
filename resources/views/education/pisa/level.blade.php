@extends('layouts.profile')

@section('title', $pisaLevel->domain . ' - ' . $pisaLevel->name)

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <div>
        <a href="{{ route('pisa.index') }}" class="text-sm text-gray-500">&larr; {{ __('pisa.title') }}</a>
        <h1 class="text-xl font-bold whitespace-nowrap mt-1">{{ $pisaLevel->domain }} - {{ $pisaLevel->name }}</h1>
    </div>
</div>

@if ($pisaLevel->description)
    <div class="bg-white border border-color-[#D2DDDB] rounded p-5 mb-5">
        <p class="text-gray-700 mb-0">{{ $pisaLevel->description }}</p>
    </div>
@endif

@if ($items->isEmpty())
    <div class="bg-white border border-color-[#D2DDDB] rounded p-6 text-center">
        <p class="mb-0">{{ __('pisa.no_activities_for_your_grade') }}</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($items as $item)
            <div>
                @include('education.academy._item-card', ['item' => $item])
                @if ($item->type === 'quiz')
                    {{-- Daniel, 2026-10-21: "a Game Option, where they play
                         alone or with other students of the same grade" -
                         the async-leaderboard answer: a timed solo run
                         through this same item, feeding the same
                         academy.item.submit it always has. --}}
                    <a href="{{ route('academy.item', $item) }}?timed=1" class="block text-center text-xs font-semibold text-indigo-600 mt-2">
                        &#9201; {{ __('pisa.timed_play') }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection
