@extends('layouts.profile')

@section('title', $item->title)

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <a href="{{ route('academy.category', $item->category) }}" class="text-sm text-gray-500">&larr; {{ $item->category->name }}</a>
</div>

<div class="max-w-[600px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded p-8">
    <h2 class="text-xl font-semibold mb-4 text-center">{{ $item->title }}</h2>
    <p class="mb-0">{{ $item->description }}</p>
</div>
@endsection
