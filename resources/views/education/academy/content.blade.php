@extends('layouts.profile')
@section('no_page_back', '1'){{-- has its own back link at the top --}}

@section('title', ct('curriculum', $item->title))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    @if ($item->category->is_pisa_practice)
        <a href="{{ $item->pisaLevel ? route('pisa.level', $item->pisaLevel) : route('pisa.index') }}" class="zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-3 text-sm">&larr; {{ __('messages.nav_pisa') }}</a>
    @else
        <a href="{{ route('academy.category', $item->category) }}" class="zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-3 text-sm">&larr; {{ ct('curriculum', $item->category->name) }}</a>
    @endif
</div>

<div class="max-w-[600px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded p-8">
    <h2 class="text-xl font-semibold mb-4 text-center">{{ ct('curriculum', $item->title) }}</h2>
    @if ($item->image)
        <img src="{{ ct_image($item->image) }}" alt="" class="mx-auto mb-4 rounded d-block" style="max-width:100%;max-height:320px;">
    @endif
    <p class="mb-0">{{ ct('curriculum', $item->description) }}</p>
</div>
@include('partials.finish', ['to' => $item->category->is_pisa_practice
    ? ($item->pisaLevel ? route('pisa.level', $item->pisaLevel) : route('pisa.index'))
    : route('academy.category', $item->category)])
@endsection
