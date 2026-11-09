@extends('layouts.admin')

@section('title', 'Activity Fines')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Activity Fines</h2>
    <p class="text-muted mb-1">
        Every Required activity on a class calendar has a last day. The day after it, each student of the class who
        didn't do it is fined once, automatically. Students see the fine on their bank statement and get a notification.
    </p>
    <p class="text-muted">
        Never fined: Optional activities, Academy exams, a student with an extension still running (Extensions),
        a student on approved Autopilot during the activity, or one who joined after it started.
        Only deadlines from {{ $setting->active_since->format('d M Y') }} on.
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Fine per activity not done</strong></div>
        <div class="card-body">
            @if ($canEdit)
                <form method="POST" action="{{ route('admin.activity-fines.settings') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label" for="fine-amount">Amount (zeds) - 0 turns fines off</label>
                        <input id="fine-amount" type="number" step="0.01" min="0" max="10000" name="amount" class="form-control"
                            value="{{ old('amount', number_format($setting->amount, 2, '.', '')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            @else
                <p class="mb-0">
                    {{ $setting->amount > 0 ? number_format($setting->amount, 2) . ' zeds for each Required activity not done in time.' : 'Fines are off.' }}
                    Only a Super Admin can change it.
                </p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Fines charged</strong> <span class="text-muted">({{ $fines->count() }}, newest first)</span></div>
        <div class="card-body p-0">
            @if ($fines->isEmpty())
                <p class="text-muted p-3 mb-0">No fines yet.</p>
            @else
                <table class="table mb-0">
                    <thead><tr><th>Charged</th><th>Student</th><th>Activity</th><th>Last day</th><th class="text-end">Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($fines as $fine)
                            <tr>
                                <td>{{ $fine->created_at->format('d M Y') }}</td>
                                <td>{{ optional($fine->student)->nickname }} ({{ optional($fine->student)->name }})</td>
                                <td>{{ \App\Support\CalendarTitle::forStudent($fine->activity_title) }}</td>
                                <td>{{ $fine->lastDay()->format('d M Y') }}</td>
                                <td class="text-end">{{ number_format($fine->amount, 2) }}</td>
                                <td>
                                    @if (!$fine->transaction_id)
                                        <span class="text-muted">Nothing charged: no money in the account</span>
                                    @elseif (!empty(optional($fine->transaction)->meta['reversed_by']))
                                        <span class="text-success">Given back</span>
                                    @else
                                        Charged
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @if ($canEdit)
        <p class="text-muted mt-3">To give a fine back, reverse its line in Bank &gt; Reverse / Adjust.</p>
    @endif
</div>
@endsection
