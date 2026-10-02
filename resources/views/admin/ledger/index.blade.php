@extends('layouts.admin')

@section('title', 'Reverse / Adjust')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Reverse a transaction / reset a balance</h2>
    <p class="text-muted">
        Super Admin only. Nothing is deleted: every action adds a new line to the student's history, together with your written reason.
        A transaction can be reversed once, and an account can never go below zero.
    </p>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="GET" action="{{ route('admin.ledger.index') }}" class="row g-3 mb-4">
        <div class="col-md-5">
            <label class="form-label">Student</label>
            <select name="student_id" class="form-control" required>
                <option value="">Select student</option>
                @foreach ($students as $s)
                    <option value="{{ $s->id }}" @selected($student && $student->id == $s->id)>{{ $s->nickname }} ({{ $s->name }}) - school {{ $s->sid }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary">Show history</button></div>
    </form>

    @if ($student)
        <div class="card mb-4">
            <div class="card-header"><strong>{{ $student->nickname }}: current balance {{ number_format($balance, 2) }}</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.ledger.reset') }}" class="row g-3">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <div class="col-md-3">
                        <label class="form-label">Set balance to</label>
                        <input type="number" step="0.01" min="0" name="new_balance" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reason (required, kept in the audit trail)</label>
                        <input type="text" name="reason" class="form-control" required minlength="5" maxlength="500">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-warning" onclick="return confirm('Reset this balance? A new adjustment line will be added.')">Reset balance</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Latest 100 entries</strong></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>#</th><th>Date</th><th>Description</th><th>Type</th><th>Amount</th><th>Balance</th><th>Reverse (reason required)</th></tr></thead>
                    <tbody>
                    @foreach ($entries as $e)
                        <tr>
                            <td>{{ $e->id }}</td>
                            <td>{{ $e->transaction_date?->format('Y-m-d H:i') }}</td>
                            <td>{{ $e->description }}</td>
                            <td>{{ $e->type }}</td>
                            <td>{{ number_format($e->amount, 2) }}</td>
                            <td>{{ number_format($e->balance, 2) }}</td>
                            <td>
                                @if (!empty(($e->meta ?? [])['reversed_by']))
                                    <span class="text-muted">Reversed (line #{{ $e->meta['reversed_by'] }})</span>
                                @elseif (in_array($e->category, ['Reversal', 'Admin Adjustment', 'Autopilot']))
                                    <span class="text-muted">&mdash;</span>
                                @else
                                    <form method="POST" action="{{ route('admin.ledger.reverse', $e->id) }}" class="d-flex gap-1" onsubmit="return confirm('Reverse transaction #{{ $e->id }}?')">
                                        @csrf
                                        <input type="text" name="reason" class="form-control form-control-sm" required minlength="5" maxlength="500" placeholder="Reason">
                                        <button class="btn btn-sm btn-outline-danger">Reverse</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
