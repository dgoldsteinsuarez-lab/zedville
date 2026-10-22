@extends('layouts.profile')

@section('title', $item->title)

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    @if ($item->category->is_pisa_practice)
        <a href="{{ $item->pisaLevel ? route('pisa.level', $item->pisaLevel) : route('pisa.index') }}" class="text-sm text-gray-500">&larr; {{ __('messages.nav_pisa') }}</a>
    @else
        <a href="{{ route('academy.category', $item->category) }}" class="text-sm text-gray-500">&larr; {{ $item->category->name }}</a>
    @endif
</div>

<div class="max-w-[600px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded p-8">
    <h2 class="text-xl font-semibold mb-4 text-center">{{ $item->title }}</h2>
    @if ($item->image)
        <img src="{{ asset('uploads/curriculum/' . $item->image) }}" alt="" class="mx-auto mb-4 rounded d-block" style="max-width:100%;max-height:320px;">
    @endif
    <p class="mb-0">{{ $item->description }}</p>
</div>
@endsection
