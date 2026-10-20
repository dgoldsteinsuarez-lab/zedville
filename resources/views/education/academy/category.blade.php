@extends('layouts.profile')

@section('title', $category->name)

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <div>
        <a href="{{ route('academy.index') }}" class="text-sm text-gray-500">&larr; Academy</a>
        <h1 class="text-xl font-bold whitespace-nowrap mt-1">{{ $category->name }}</h1>
    </div>
</div>

@if ($items->isEmpty())
    <div class="bg-white border border-color-[#D2DDDB] rounded p-6 text-center">
        <p class="mb-0">Nothing here yet - check back soon.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($items as $item)
            <a href="{{ route('academy.item', $item) }}"
                class="block bg-white border border-color-[#D2DDDB] rounded p-5 hover:shadow transition">
                <h3 class="text-lg font-semibold mb-1">{{ $item->title }}</h3>
                @if ($item->description)
                    <p class="text-sm text-gray-600 mb-2">{{ $item->description }}</p>
                @endif
                @if ($item->pisaLevel)
                    <span class="inline-block text-xs font-semibold bg-gray-100 rounded px-2 py-1 mb-2">{{ $item->pisaLevel->name }}</span>
                @endif
                @if ($item->best_score)
                    <p class="text-sm text-green-700 mb-0">Best score: {{ $item->best_score->score }}/{{ $item->best_score->total_questions }}</p>
                @elseif ($item->type === 'quiz')
                    <p class="text-sm text-gray-500 mb-0">Not attempted yet</p>
                @endif
            </a>
        @endforeach
    </div>
@endif
@endsection
