@extends('layouts.profile')

@section('title', 'Academy')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">Academy</h1>
</div>

@if ($categories->isEmpty())
    <div class="bg-white border border-color-[#D2DDDB] rounded p-6 text-center">
        <p class="mb-0">Nothing here yet - check back soon.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($categories as $category)
            <a href="{{ route('academy.category', $category) }}"
                class="block bg-white border border-color-[#D2DDDB] rounded p-5 hover:shadow transition">
                <h3 class="text-lg font-semibold mb-1">{{ $category->name }}</h3>
                <p class="text-sm text-gray-500 mb-0">
                    {{ $category->items_count }} {{ \Illuminate\Support\Str::plural('item', $category->items_count) }}
                </p>
            </a>
        @endforeach
    </div>
@endif
@endsection
