@extends('layouts.admin')

@section('title', 'Real exam result')

@section('content')
<div class="container-fluid">
    <p class="mb-1"><a href="{{ route('admin.exam-results.index') }}">&larr; Real exam results</a></p>
    <h2 class="mb-1">{{ $attempt->item->title ?? 'Exam' }}</h2>
    <p class="text-muted">
        {{ $attempt->student->name ?? '—' }} ({{ $attempt->student->nickname ?? '—' }})
        · {{ $attempt->student?->gradeRelation?->name ?? '—' }}
        · submitted {{ $attempt->completed_at?->format('d M Y H:i') }}
        · <strong>{{ $attempt->score }} / {{ $attempt->total_questions }}</strong>
    </p>

    <div class="card">
        <ul class="list-group list-group-flush">
            @foreach ($attempt->answers as $answer)
                @php $right = $answer->question?->options->firstWhere('is_correct', true); @endphp
                <li class="list-group-item">
                    <p class="fw-semibold mb-1">{{ $loop->iteration }}. {{ $answer->question->question_text ?? '(question removed)' }}</p>
                    <p class="mb-0 {{ $answer->is_correct ? 'text-success' : 'text-danger' }}">
                        {{ $answer->is_correct ? 'Correct' : 'Wrong' }} -
                        answered: {{ $answer->option->option_text ?? 'not answered' }}
                        @unless ($answer->is_correct)
                            · right answer: {{ $right->option_text ?? '—' }}
                        @endunless
                    </p>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
