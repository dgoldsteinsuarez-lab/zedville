@extends('layouts.admin')

@section('title', 'Real exam results')

@section('content')
<div class="container-fluid">
    <h2 class="mb-1">Real exam results</h2>
    <p class="text-muted">
        The system marks each real exam: the marks are here at once, for the tutor and the school; the students see theirs when their tutor releases them.
        @if (auth()->user()->isTutor()) You see the students in your own classes. @endif
    </p>

    {{-- The real exams themselves: online in the Academy, or printed (Daniel, 2026-10-31) --}}
    <div class="card mb-4">
        <div class="card-header"><strong>Real exams</strong></div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr><th>Exam</th><th>Questions</th><th>Online for students</th><th>On the calendar</th><th>Handed in</th><th class="text-end">Online or on paper</th></tr>
                </thead>
                <tbody>
                    @forelse ($examCards as $exam)
                        <tr>
                            <td><strong>{{ $exam->title }}</strong></td>
                            <td>{{ $exam->questions_count }}</td>
                            <td>
                                @if ($exam->is_active)
                                    <span class="badge bg-success">Available</span> <span class="text-muted small">the tutor opens it for a class on the day, with a password</span>
                                @else
                                    <span class="badge bg-secondary">Hidden</span>
                                @endif
                            </td>
                            <td>
                                @if ($exam->start_date)
                                    {{ $exam->start_date->format('d M Y') }}@if ($exam->end_date && !$exam->end_date->equalTo($exam->start_date)) – {{ $exam->end_date->format('d M Y') }}@endif
                                @else
                                    <span class="text-muted">No dates</span>
                                @endif
                            </td>
                            <td>{{ $exam->handed_in }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.exam-results.classes', $exam) }}">Open for a class</a>
                                <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('admin.exam-results.print', ['item' => $exam, 'lang' => app()->getLocale()]) }}">Print exam</a>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ route('admin.exam-results.print', ['item' => $exam, 'lang' => app()->getLocale(), 'key' => 1]) }}">Answer key</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">There is no real exam for your classes yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form method="GET" class="d-flex gap-2 align-items-end mb-3">
        <div>
            <label class="form-label mb-1">Exam</label>
            <select name="exam" class="form-select" onchange="this.form.submit()">
                <option value="">All real exams</option>
                @foreach ($exams as $exam)
                    <option value="{{ $exam->id }}" @selected($examId === $exam->id)>{{ $exam->title }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Exam</th>
                        <th>Score</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attempts as $attempt)
                        @php $pct = $attempt->total_questions ? round($attempt->score / $attempt->total_questions * 100) : 0; @endphp
                        <tr>
                            <td>{{ $attempt->student->name ?? '—' }} <span class="text-muted">({{ $attempt->student->nickname ?? '—' }})</span></td>
                            <td>{{ $attempt->student?->gradeRelation?->name ?? '—' }}</td>
                            <td>{{ $attempt->item->title ?? '—' }}</td>
                            <td><strong>{{ $attempt->score }} / {{ $attempt->total_questions }}</strong> <span class="text-muted">({{ $pct }}%)</span></td>
                            <td>{{ $attempt->completed_at?->format('d M Y H:i') }}</td>
                            <td class="text-end"><a href="{{ route('admin.exam-results.show', $attempt) }}">Answers</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No real exam has been handed in yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $attempts->links() }}</div>
</div>
@endsection
