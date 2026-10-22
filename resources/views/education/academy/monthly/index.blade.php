@extends('layouts.profile')

@section('title', __('academy.monthly_title'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <a href="{{ route('academy.index') }}" class="text-sm text-gray-500">&larr; {{ __('academy.back_to_academy') }}</a>
</div>

<h1 class="text-xl font-bold mb-2">{{ __('academy.monthly_title') }}</h1>
<p class="text-gray-600 mb-6" style="max-width:70ch">{{ __('academy.monthly_intro') }}</p>

@if ($months->isEmpty())
    <div class="bg-white border border-color-[#D2DDDB] rounded p-6 text-center">
        <p class="mb-0">{{ __('academy.nothing_here_yet') }}</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($months as $n => $count)
            @php
                $open = $n <= $reachedMonth;
                $attempt = $best->get($n);
                $line = trans_choice('academy.question_count', $count, ['count' => $count]);
                if ($open) {
                    $line .= ' · ' . ($attempt ? __('academy.best_score') . ': ' . $attempt->score . '/' . $attempt->total : __('academy.not_attempted_yet'));
                }
            @endphp
            @include('education.academy.monthly._card', [
                'href' => $open ? route('academy.monthly.show', $n) : null,
                'title' => __('academy.month_n', ['n' => $n]),
                'status' => $open ? __('academy.open') : __('academy.opens_in_month', ['n' => $n]),
                'line' => $line,
            ])
        @endforeach

        @php $finalAttempt = $best->get('final'); @endphp
        @include('education.academy.monthly._card', [
            'href' => $finalReviewOpen ? route('academy.monthly.final') : null,
            'title' => __('academy.final_review'),
            'status' => $finalReviewOpen ? __('academy.open') : __('academy.opens_in_month', ['n' => $finalReviewOpensIn]),
            'line' => $finalReviewOpen
                ? __('academy.final_review_text', ['count' => $mixSize]) . ' · ' . ($finalAttempt ? __('academy.best_score') . ': ' . $finalAttempt->score . '/' . $finalAttempt->total : __('academy.not_attempted_yet'))
                : __('academy.final_review_locked', ['n' => $finalReviewOpensIn]),
        ])
    </div>
@endif
@endsection
