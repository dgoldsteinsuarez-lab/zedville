@extends('layouts.profile')

@section('title', __('pisa.title'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">{{ __('pisa.title') }}</h1>
    <a href="{{ route('pisa.leaderboard') }}" class="themeBtn">&#127942; {{ __('pisa.leaderboard') }}</a>
</div>

<div class="bg-white border border-color-[#D2DDDB] rounded p-6 mb-5">
    <h2 class="text-lg font-semibold mb-2">{{ __('pisa.what_is_pisa') }}</h2>
    <p class="text-gray-700 mb-2">
        {!! str_replace(':grade', '<strong>' . __('pisa.explainer_grade') . '</strong>', __('pisa.explainer_p1')) !!}
    </p>
    <p class="text-gray-700 mb-0">
        {!! str_replace(':fla', '<strong>' . __('pisa.financial_literacy_assessment') . '</strong>', __('pisa.explainer_p2')) !!}
    </p>
</div>

<h2 class="text-lg font-semibold mb-3">{{ __('pisa.proficiency_levels') }}</h2>
<p class="text-sm text-gray-500 mb-4">
    {{ __('pisa.proficiency_levels_hint') }}
</p>

@if ($levels->isEmpty())
    <div class="bg-white border border-color-[#D2DDDB] rounded p-6 text-center">
        <p class="mb-0">{{ __('pisa.no_levels_yet') }}</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($levels as $level)
            <a href="{{ route('pisa.level', $level) }}"
                class="block bg-white border border-color-[#D2DDDB] rounded p-5 hover:shadow transition">
                <h3 class="text-lg font-semibold mb-1">{{ $level->name }}</h3>
                <p class="text-xs uppercase tracking-wide text-gray-400 mb-2">{{ $level->domain }}</p>
                @if ($level->description)
                    <p class="text-sm text-gray-600 mb-2">{{ \Illuminate\Support\Str::limit($level->description, 140) }}</p>
                @endif
                <p class="text-sm {{ ($itemCounts[$level->id] ?? 0) > 0 ? 'text-green-700' : 'text-gray-500' }} mb-0">
                    {{ trans_choice('pisa.activity_count', $itemCounts[$level->id] ?? 0, ['count' => $itemCounts[$level->id] ?? 0]) }}
                </p>
            </a>
        @endforeach
    </div>
@endif
@endsection
