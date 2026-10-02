@extends('layouts.admin')

@section('title', 'Autopilot')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Autopilot</h2>
    <p class="text-muted">
        Autopilot keeps a student's account going while they are away (for example an approved absence).
        At month-end the class-average activity is applied to their account for the approved period.
        Every period needs a reason and an end date, and it appears in the student's history.
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Turn Autopilot on for a student</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.autopilot.store') }}">
                @csrf
                <div class="row g-3">
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
                        <label class="form-label">From</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Until</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Reason (documented)</label>
                        <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" required minlength="5" maxlength="1000" placeholder="e.g. Hospital stay, approved by parents">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Turn on Autopilot</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Autopilot periods</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Student</th><th>From</th><th>Until</th><th>Reason</th><th>Set by</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($periods as $p)
                    <tr>
                        <td>{{ $p->student->nickname ?? '—' }} <span class="text-muted small">({{ $p->student->name ?? '' }})</span></td>
                        <td>{{ $p->start_date->format('Y-m-d') }}</td>
                        <td>{{ $p->end_date->format('Y-m-d') }}</td>
                        <td>{{ $p->reason }}</td>
                        <td>{{ $p->creator->nickname ?? '—' }}</td>
                        <td>{{ ucfirst($p->status) }}</td>
                        <td>
                            @if ($p->status === 'active')
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
@endsection
