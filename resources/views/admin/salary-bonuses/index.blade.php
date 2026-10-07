@extends('layouts.admin')

@section('title', 'Salary Bonuses')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Salary Bonuses</h2>
    <p class="text-muted">
        One-time or recurring bonuses on top of a student's regular salary. Every bonus needs a reason,
        and can be paused without being deleted. Applied automatically once a day.
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Grant a bonus</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.salary-bonuses.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-control" required>
                            <option value="">Select student</option>
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id', request('student_id')) == $s->id)>{{ $s->nickname }} ({{ $s->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Amount (zeds)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" class="form-control">
                            <option value="">One-time</option>
                            <option value="weekly" @selected(old('frequency') == 'weekly')>Weekly</option>
                            <option value="monthly" @selected(old('frequency') == 'monthly')>Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End date (recurring only)</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason (required)</label>
                        <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" required minlength="5" maxlength="1000" placeholder="e.g. Class President role, Outstanding activity completion">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Grant bonus</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Bonuses</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Student</th><th>Amount</th><th>Frequency</th><th>Dates</th><th>Reason</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($bonuses as $bonus)
                    <tr>
                        <td>{{ $bonus->student->nickname ?? '—' }}</td>
                        <td>{{ number_format($bonus->amount, 2) }} zeds</td>
                        <td>{{ $bonus->frequency ? ucfirst($bonus->frequency) : 'One-time' }}</td>
                        <td>
                            {{ $bonus->start_date->format('d M Y') }}
                            @if ($bonus->end_date) &rarr; {{ $bonus->end_date->format('d M Y') }} @endif
                        </td>
                        <td>{{ $bonus->reason }}</td>
                        <td>{{ ucfirst($bonus->status) }}</td>
                        <td class="d-flex gap-1">
                            @if ($bonus->status === 'active')
                                <form method="POST" action="{{ route('admin.salary-bonuses.pause', $bonus->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">Pause</button>
                                </form>
                            @elseif ($bonus->status === 'paused')
                                <form method="POST" action="{{ route('admin.salary-bonuses.resume', $bonus->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">Resume</button>
                                </form>
                            @endif
                            @if ($bonus->status !== 'cancelled')
                                <form method="POST" action="{{ route('admin.salary-bonuses.cancel', $bonus->id) }}" onsubmit="return confirm('Cancel this bonus?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted p-3">No bonuses granted yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
