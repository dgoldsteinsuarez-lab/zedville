@extends('layouts.admin')

@section('title', 'Autopilot')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <h2 class="mb-0">Autopilot for Students</h2>
        @include('components.school-badge')
    </div>
    <p class="text-muted">
        Autopilot keeps a student's account going while they are away (an approved absence, or the school holidays
        for a whole grade). At month-end the class-average activity is applied to their account for the approved
        period. Every period needs a reason and an end date, and it appears in the student's history.
        Only Tutors, School Admins and Admins can turn it on - students can't.
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @php $target = old('target', 'student'); @endphp
    <div class="card mb-4">
        <div class="card-header"><strong>Schedule Autopilot</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.autopilot.store') }}">
                @csrf
                <div class="mb-3">
                    <div class="btn-group" role="group" aria-label="Who goes on Autopilot">
                        <input type="radio" class="btn-check" name="target" id="targetStudent" value="student" @checked($target === 'student')>
                        <label class="btn btn-outline-primary" for="targetStudent">One student</label>
                        <input type="radio" class="btn-check" name="target" id="targetGrade" value="grade" @checked($target === 'grade')>
                        <label class="btn btn-outline-primary" for="targetGrade">Whole grade</label>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-3" id="pickStudent" @if ($target !== 'student') hidden @endif>
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-control">
                            <option value="">Select student</option>
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->nickname }} ({{ $s->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3" id="pickGrade" @if ($target !== 'grade') hidden @endif>
                        <label class="form-label">Grade</label>
                        <select name="grade_id" class="form-control">
                            <option value="">Select grade</option>
                            @foreach ($grades as $g)
                                <option value="{{ $g->id }}" @selected(old('grade_id') == $g->id)>{{ $g->name }} ({{ $g->classes_count }} {{ \Illuminate\Support\Str::plural('class', $g->classes_count) }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Every student in the grade{{ auth()->user()->isTutor() ? ' that you tutor' : '' }}.</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', now()->toDateString()) }}"
                            min="{{ $earliest->toDateString() }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Until</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Reason (documented)</label>
                        <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" required minlength="5" maxlength="1000" placeholder="e.g. Summer holidays / Hospital stay, approved by parents">
                    </div>
                </div>
                <div class="form-text mt-2">
                    Forgot to set it in time? It can start up to {{ \App\Services\AutopilotService::MAX_MONTHS_BACK }} months back
                    (from {{ $earliest->toDateString() }}). For the months already gone, the activity penalty is given back
                    and the month appears in the student's history, as if it had been set in time.
                </div>
                <button type="submit" class="btn btn-primary mt-3">Schedule Autopilot</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Autopilot periods</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Who</th><th>From</th><th>Until</th><th>Reason</th><th>Set by</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($rows as $p)
                    <tr>
                        <td>
                            @if ($p->batch_id)
                                <strong>{{ $p->grade->name ?? 'Grade' }}</strong>
                                <span class="badge bg-info text-dark">Whole grade</span>
                                <div class="text-muted small">{{ $p->student_count }} {{ \Illuminate\Support\Str::plural('student', $p->student_count) }}</div>
                            @else
                                {{ $p->student->nickname ?? '—' }} <span class="text-muted small">({{ $p->student->name ?? '' }})</span>
                            @endif
                        </td>
                        <td>
                            {{ $p->start_date->format('Y-m-d') }}
                            @if ($p->created_at && $p->created_at->toDateString() > $p->start_date->toDateString())
                                <div class="text-muted small">set later, on {{ $p->created_at->format('Y-m-d') }}</div>
                            @endif
                        </td>
                        <td>{{ $p->end_date->format('Y-m-d') }}</td>
                        <td>{{ $p->reason }}</td>
                        <td>{{ $p->creator->nickname ?? '—' }}</td>
                        <td>
                            @if ($p->batch_id && $p->active_count > 0 && $p->active_count < $p->student_count)
                                Active ({{ $p->active_count }} of {{ $p->student_count }})
                            @else
                                {{ $p->batch_id ? ($p->active_count > 0 ? 'Active' : 'Cancelled') : ucfirst($p->status) }}
                            @endif
                        </td>
                        <td>
                            @if ($p->batch_id && $p->active_count > 0)
                                <form method="POST" action="{{ route('admin.autopilot.cancel-batch', $p->batch_id) }}" onsubmit="return confirm('Cancel Autopilot for the whole grade?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                            @elseif (!$p->batch_id && $p->status === 'active')
                                <form method="POST" action="{{ route('admin.autopilot.cancel', $p->id) }}" onsubmit="return confirm('Cancel this Autopilot period?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted p-3">No Autopilot periods yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Show the Student or the Grade picker for the chosen target.
    document.querySelectorAll('input[name="target"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.getElementById('pickStudent').hidden = this.value !== 'student';
            document.getElementById('pickGrade').hidden = this.value !== 'grade';
        });
    });
</script>
@endsection
