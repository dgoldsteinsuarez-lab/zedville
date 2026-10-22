@extends('layouts.profile')

@section('title', __('pisa.leaderboard_title'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <div>
        <a href="{{ route('pisa.index') }}" class="text-sm text-gray-500">&larr; {{ __('pisa.title') }}</a>
        <h1 class="text-xl font-bold whitespace-nowrap mt-1">&#127942; {{ __('pisa.leaderboard_title') }}</h1>
    </div>
</div>

<div class="bg-white border border-color-[#D2DDDB] rounded p-5 mb-5">
    <p class="text-gray-700 mb-0">
        {{ __('pisa.leaderboard_explainer') }}
    </p>
</div>

@if ($leaderboard->isEmpty())
    <div class="bg-white border border-color-[#D2DDDB] rounded p-6 text-center">
        <p class="mb-0">{{ __('pisa.no_one_played_yet') }}</p>
    </div>
@else
    <div class="bg-white border border-color-[#D2DDDB] rounded overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs uppercase text-gray-500">
                    <th class="px-5 py-3">{{ __('pisa.rank') }}</th>
                    <th class="px-5 py-3">{{ __('pisa.student') }}</th>
                    <th class="px-5 py-3 text-right">{{ __('pisa.score') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaderboard as $i => $row)
                    <tr class="border-t border-color-[#D2DDDB] {{ $row['student']->id === auth()->id() ? 'bg-green-50' : '' }}">
                        <td class="px-5 py-3 font-semibold">
                            @if ($i === 0) &#129351;
                            @elseif ($i === 1) &#129352;
                            @elseif ($i === 2) &#129353;
                            @else {{ $i + 1 }}
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            {{ $row['student']->nickname }}
                            @if ($row['student']->id === auth()->id())
                                <span class="text-xs text-green-700 font-semibold">({{ __('pisa.you') }})</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right font-semibold">{{ $row['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($myRank)
        <p class="text-sm text-gray-500 mt-3 mb-0">{{ __('pisa.ranked_this_month', ['rank' => $myRank]) }}</p>
    @else
        <p class="text-sm text-gray-500 mt-3 mb-0">{{ __('pisa.join_leaderboard_hint') }}</p>
    @endif
@endif
@endsection
