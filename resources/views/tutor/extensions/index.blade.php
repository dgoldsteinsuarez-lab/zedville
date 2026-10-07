@extends('layouts.admin')

@section('title', 'Assignment Extensions')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Assignment Extensions</h2>
    <p class="text-muted">Grant a student extra time on an assignment, with a reason.</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Grant an extension</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tutor.extensions.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Assignment</label>
                        <select name="calendar_event_id" class="form-control" required>
                            <option value="">Select assignment</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}" @selected(old('calendar_event_id') == $event->id)>
                                    {{ $event->title }} (due {{ $event->end?->format('d M Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-control" required>
                            <option value="">Select student</option>
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->nickname }} ({{ $s->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">New due date</label>
                        <input type="date" name="extended_to" class="form-control" value="{{ old('extended_to') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reason</label>
                        <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" required minlength="5" maxlength="1000" placeholder="e.g. Family emergency">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Grant extension</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Granted extensions</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Student</th><th>Assignment</th><th>Original due</th><th>Extended to</th><th>Reason</th></tr></thead>
                <tbody>
                @forelse ($extensions as $ext)
                    <tr>
                        <td>{{ $ext->student->nickname ?? '—' }}</td>
                        <td>{{ $ext->calendarEvent->title ?? '—' }}</td>
                        <td>{{ $ext->original_due?->format('d M Y') }}</td>
                        <td>{{ $ext->extended_to->format('d M Y') }}</td>
                        <td>{{ $ext->reason }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted p-3">No extensions granted yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
