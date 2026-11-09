@extends('layouts.admin')

@section('title', 'Run the exam')

@section('content')
<div class="container-fluid">
    <h2 class="mb-1">{{ $item->title }}</h2>
    <p class="text-muted mb-3">
        Open the exam for your class on the day of the exam and give the students the password - they need it to start.
        The system marks it: you see the marks at once in <a href="{{ route('admin.exam-results.index', ['exam' => $item->id]) }}">Exam results</a>,
        the students when you release them. Doing it on paper instead?
        <a href="{{ route('admin.exam-results.print', $item) }}" target="_blank">Print the exam</a>.
    </p>
    @unless ($item->is_active)
        <div class="alert alert-warning">This exam is switched off in Curriculum, so students can't take it online even when it is open for their class.</div>
    @endunless

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr><th>Class</th><th>Status</th><th>Handed in</th><th style="min-width: 380px">Open on the day of the exam</th><th class="text-end">Marks</th></tr>
                </thead>
                <tbody>
                    @forelse ($classes as $class)
                        @php $s = $sessions[$class->id] ?? null; @endphp
                        <tr>
                            <td><strong>{{ \App\Support\ClassLabel::make($class->grade->name ?? null, $class->name) }}</strong></td>
                            <td>
                                @if (!$s)
                                    <span class="text-muted">Not opened</span>
                                @elseif ($s->isOpen())
                                    <span class="badge bg-success">Open today</span>
                                    <div class="small">Password: <strong class="font-monospace fs-6">{{ $s->password }}</strong></div>
                                @elseif ($s->closed_at === null && $s->exam_date->isFuture())
                                    <span class="badge bg-info text-dark">Planned for {{ $s->exam_date->format('d M Y') }}</span>
                                    <div class="small">Password: <strong class="font-monospace">{{ $s->password }}</strong></div>
                                @else
                                    <span class="badge bg-secondary">Closed</span>
                                    <div class="small text-muted">{{ $s->exam_date->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td>{{ $class->handed_in }} / {{ $class->students_count }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.exam-results.open', [$item, $class]) }}" class="d-flex gap-2 align-items-center">
                                    @csrf
                                    <input type="date" name="exam_date" class="form-control form-control-sm" style="max-width: 150px"
                                        value="{{ $s && $s->exam_date->gte(today()) ? $s->exam_date->toDateString() : today()->toDateString() }}" min="{{ today()->toDateString() }}" required>
                                    <input type="text" name="password" class="form-control form-control-sm font-monospace text-uppercase" style="max-width: 110px"
                                        value="{{ $s->password ?? \App\Models\ExamSession::newPassword() }}" required minlength="4" maxlength="20" aria-label="Password">
                                    <button class="btn btn-sm btn-primary text-nowrap">{{ $s ? 'Save' : 'Open' }}</button>
                                </form>
                                @if ($s && $s->isOpen())
                                    <form method="POST" action="{{ route('admin.exam-results.close', $s) }}" class="mt-1" onsubmit="return confirm('Close the exam now? Students who haven\'t handed it in can\'t any more.')">
                                        @csrf
                                        <button class="btn btn-sm btn-link text-danger p-0">Close now</button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($s && $s->results_released_at)
                                    <span class="text-success small">Released {{ $s->results_released_at->format('d M Y') }}</span>
                                @elseif ($s && $class->handed_in > 0)
                                    <form method="POST" action="{{ route('admin.exam-results.release', $s) }}" onsubmit="return confirm('Show the students of this class their marks and the right answers? This also closes the exam.')">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success text-nowrap">Release results</button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">You have no class in this exam's level.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
