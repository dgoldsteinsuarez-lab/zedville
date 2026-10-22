@extends('layouts.profile')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h2 class="mb-1">{{ $module->icon }} {{ $module->title }}</h2>
    <p class="text-muted mb-4">{{ $questions->count() }} {{ Str::plural('question', $questions->count()) }}</p>

    @isset($score)
        <div class="alert {{ $score['correct'] === $score['total'] ? 'alert-success' : 'alert-warning' }}">
            Done! You got {{ $score['correct'] }}/{{ $score['total'] }} correct.
            @if ($score['first_attempt'])
                You earned {{ $score['points'] }} FinHero {{ Str::plural('point', $score['points']) }}.
            @else
                FinHero points only count on your first try, so this one was practice.
            @endif
        </div>
    @endisset

    @if (!empty($module->lesson))
        <div class="card mb-4">
            <div class="card-body">
                @foreach ($module->lesson as $section)
                    <h4 class="{{ $loop->first ? '' : 'mt-4' }} mb-2">{{ $section['title'] }}</h4>
                    @php $inList = false; @endphp
                    @foreach ($section['lines'] as $line)
                        @if (str_starts_with($line, '• '))
                            @if (!$inList)<ul class="mb-2">@php $inList = true; @endphp@endif
                            <li>{{ mb_substr($line, 2) }}</li>
                        @else
                            @if ($inList)</ul>@php $inList = false; @endphp@endif
                            <p class="mb-2">{{ $line }}</p>
                        @endif
                    @endforeach
                    @if ($inList)</ul>@endif
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('library.submit', str_replace('_', '-', $activity->activity_key)) }}">
        @csrf
        @foreach ($questions as $index => $question)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">{{ $index + 1 }}. {{ $question->question_text }}</h5>
                    @if ($question->image)
                        <img src="{{ asset('uploads/finhero/' . $question->image) }}" alt="" class="img-fluid rounded mb-3" style="max-height:220px;">
                    @endif
                    @foreach ($question->options as $option)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio"
                                name="answers[{{ $question->id }}]" value="{{ $option->id }}"
                                id="opt{{ $option->id }}" required>
                            <label class="form-check-label" for="opt{{ $option->id }}">{{ $option->option_text }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if ($questions->count() > 0)
            <button type="submit" class="btn btn-primary">Submit</button>
        @else
            <p class="text-muted">No questions have been added for this module yet.</p>
        @endif
    </form>
</div>
@endsection
