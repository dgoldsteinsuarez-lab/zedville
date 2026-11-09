{{-- A real exam before the student may take it (Daniel, 2026-10-31): not
     opened for their class today, or opened and waiting for the password
     their tutor gives them. --}}
@extends('layouts.profile')
@section('no_page_back', '1'){{-- has its own back link at the top --}}

@section('title', ct('curriculum', $item->title))

@section('content')
@php
    $backUrl = $item->category->is_pisa_practice ? route('pisa.index') : route('academy.category', $item->category);
    $backLabel = $item->category->is_pisa_practice ? __('messages.nav_pisa') : ct('curriculum', $item->category->name);
@endphp
<div class="flex items-start pb-2">
    <a href="{{ $backUrl }}" class="zvBackBtn inline-flex items-center gap-2 py-2 px-4 text-sm">&larr; {{ $backLabel }}</a>
</div>

<div class="max-w-[520px] mx-auto w-full bg-white border border-color-[#D2DDDB] rounded p-8 mt-2 text-center">
    <h2 class="text-xl font-semibold mb-1">{{ ct('curriculum', $item->title) }}</h2>
    <span class="inline-block text-xs font-semibold bg-red-100 text-red-700 rounded px-2 py-1 mb-5">{{ __('academy.real_exam') }}</span>

    @if (session('error'))
        <p class="text-sm text-red-700 mb-4">{{ session('error') }}</p>
    @endif

    @if ($askPassword)
        <div class="text-4xl mb-3" aria-hidden="true">&#128273;</div>
        <p class="text-lg font-semibold mb-1">{{ __('exams.password_title') }}</p>
        <p class="text-gray-600 mb-4">{{ __('exams.password_text') }}</p>
        <form method="POST" action="{{ route('academy.item.unlock', $item) }}" class="flex flex-col items-center gap-3">
            @csrf
            <label for="examPassword" class="sr-only">{{ __('exams.password_label') }}</label>
            <input id="examPassword" name="password" type="text" autocomplete="off" autocapitalize="characters" spellcheck="false" required autofocus
                placeholder="{{ __('exams.password_label') }}"
                class="w-56 text-center text-xl tracking-[.3em] uppercase rounded-lg border border-[#D2DDDB] px-3 py-2">
            @error('password')
                <p class="text-sm text-red-700 mb-0">{{ $message }}</p>
            @enderror
            <button type="submit" class="themeBtn">{{ __('exams.password_button') }}</button>
        </form>
        <p class="text-sm font-semibold text-red-700 mt-5 mb-0">{{ __('exams.real_exam_intro') }}</p>
    @else
        <div class="text-4xl mb-3" aria-hidden="true">&#128274;</div>
        <p class="text-lg font-semibold mb-1">{{ __('exams.not_open_title') }}</p>
        <p class="text-gray-600 mb-0">{{ __('exams.not_open_text') }}</p>
        @if ($session && $session->closed_at === null && $session->exam_date->isFuture())
            <p class="text-gray-600 mt-2 mb-0">{{ __('exams.opens_on', ['date' => $session->exam_date->locale(app()->getLocale())->isoFormat('LL')]) }}</p>
        @endif
    @endif
</div>
@endsection
