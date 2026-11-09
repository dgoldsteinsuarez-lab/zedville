@extends('layouts.admin')

@section('title', 'Loans')

{{-- Admin > Bank > Loans (LoanAdminController): the students' personal loans, from the Intermediate
     (App\Services\LoanService), with how many instalments are paid and how many came late. Read-only. --}}
@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Loans</h2>
    <p class="text-muted">
        Personal loans from the Intermediate on: {{ $rules['min'] }} to {{ $rules['max'] }} Ƶ over {{ implode(', ', $rules['terms']) }} months
        at {{ $rules['tae'] }} % APR, equal instalments taken on the 7th with the bills. The bank only lends while all of a student's
        instalments stay within {{ $rules['share'] }} % of the salary; {{ $rules['cancel'] }} days to change their mind; an instalment
        not paid in its month is late, and {{ $rules['late'] }} late ones in {{ $rules['block'] }} months stop new loans for
        {{ $rules['block'] }} months. Students ask for them in the bank's Loans tab; nothing to do here.
    </p>

    <div class="card">
        <div class="card-body table-responsive">
            @if ($loans->isEmpty())
                <p class="text-muted mb-0">No loans yet.</p>
            @else
                <table class="table table-sm align-middle">
                    <thead>
                        <tr><th>Student</th><th>Class</th><th>Granted</th><th class="text-end">Amount</th><th>Term</th><th class="text-end">Instalment</th><th class="text-end">Interest</th><th>Paid</th><th>Late</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            @php
                                $student = $students[$loan->user_id] ?? null;
                                $paid = $loan->instalments->whereIn('status', ['paid', 'paid_late', 'paid_early'])->count();
                                $late = $loan->instalments->whereIn('status', ['late', 'paid_late'])->count();
                            @endphp
                            <tr>
                                <td>{{ $student ? ($student->nickname ?: $student->name) : '—' }}</td>
                                <td>{{ $student ? ($classes[$student->grade] ?? '—') : '—' }}</td>
                                <td>{{ $loan->created_at->format('d M Y') }}</td>
                                <td class="text-end">{{ number_format((float) $loan->amount, 2) }} Ƶ</td>
                                <td>{{ $loan->months }} months</td>
                                <td class="text-end">{{ number_format((float) $loan->instalment, 2) }} Ƶ</td>
                                <td class="text-end">{{ number_format((float) $loan->interest_total, 2) }} Ƶ</td>
                                <td>{{ $paid }} / {{ $loan->months }}</td>
                                <td>@if ($late)<span class="badge bg-danger">{{ $late }}</span>@else 0 @endif</td>
                                <td>{{ ucfirst($loan->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
