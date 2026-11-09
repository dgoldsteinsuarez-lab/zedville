@extends('layouts.admin')

@section('title', 'Health Insurance')

{{-- Admin > Bank > Health Insurance (InsuranceAdminController): the two plans (App\Services\InsuranceService)
     and each student's current plan, with the premiums paid so far. Read-only. --}}
@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Health Insurance</h2>
    <p class="text-muted">
        From the Intermediate on, every student has health insurance: Zedville has no public health care. They compare the
        two plans and choose one in FinHero Task 15 "Choose your health insurance" (assign it as Required in the level's first
        month); whoever has none when their first month ends gets the Basic plan from the City Hall, with a letter, and may
        still choose once. After that they can change plan once a year. The premium is paid on the 7th with the bills and
        doesn't rise with prices. In a health emergency, the plan decides how much the student pays.
    </p>

    <div class="card mb-3">
        <div class="card-body table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr><th></th><th>Basic</th><th>Complete</th></tr>
                </thead>
                <tbody>
                    <tr><td>Premium, every month</td><td>{{ number_format($plans['basic']['premium'], 2) }} Ƶ</td><td>{{ number_format($plans['complete']['premium'], 2) }} Ƶ</td></tr>
                    <tr><td>Deductible: what the student pays first for each health problem</td><td>{{ number_format($plans['basic']['deductible'], 2) }} Ƶ</td><td>{{ number_format($plans['complete']['deductible'], 2) }} Ƶ</td></tr>
                    <tr><td>Co-payment: the student's share of the rest</td><td>{{ $plans['basic']['copay'] * 100 }} %</td><td>{{ $plans['complete']['copay'] * 100 }} %</td></tr>
                    <tr><td>Waiting period</td><td>{{ $plans['basic']['dentist_wait'] }} months for the dentist; emergencies always covered</td><td>None</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            @if ($policies->isEmpty())
                <p class="text-muted mb-0">No student has a plan yet.</p>
            @else
                <table class="table table-sm align-middle">
                    <thead>
                        <tr><th>Student</th><th>Class</th><th>Plan</th><th>Since</th><th>Chosen by</th><th class="text-end">Premiums paid</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($policies as $policy)
                            @php $student = $students[$policy->user_id] ?? null; @endphp
                            <tr>
                                <td>{{ $student ? ($student->nickname ?: $student->name) : '—' }}</td>
                                <td>{{ $student ? ($classes[$student->grade] ?? '—') : '—' }}</td>
                                <td>{{ ucfirst($policy->plan) }}</td>
                                <td>{{ $policy->started_on->format('d M Y') }}</td>
                                <td>@if ($policy->auto)<span class="badge bg-warning text-dark">City Hall</span>@else Student @endif</td>
                                <td class="text-end">{{ number_format((float) ($premiums[$policy->user_id] ?? 0), 2) }} Ƶ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
