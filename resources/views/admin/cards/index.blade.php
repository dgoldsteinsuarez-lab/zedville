@extends('layouts.admin')

@section('title', 'Credit Cards')

{{-- Admin > Bank > Credit Cards (CreditCardAdminController): the students' credit cards, from the Intermediate
     (App\Services\CreditCardService), with what they owe and how they pay. Read-only. --}}
@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Credit Cards</h2>
    <p class="text-muted">
        From the Intermediate on, students can ask for a credit card in the bank's Credit card tab and pay with it in the City
        Mall and the spending activities' shops. Limit {{ number_format($rules['limit'], 2) }} Ƶ. Each month's purchases make a
        statement, paid on the 7th with the bills as the student chose: all of it (free), the minimum ({{ $rules['min'] }} %, at
        least {{ number_format($rules['floor'], 2) }} Ƶ) or a fixed amount; what is deferred pays {{ $rules['tae'] }} % APR. A statement
        not paid by the end of its month is taken anyway, into overdraft. Nothing to do here.
    </p>

    <div class="card">
        <div class="card-body table-responsive">
            @if ($cards->isEmpty())
                <p class="text-muted mb-0">No credit cards yet.</p>
            @else
                <table class="table table-sm align-middle">
                    <thead>
                        <tr><th>Student</th><th>Class</th><th>Since</th><th class="text-end">Owed</th><th>Pays</th><th class="text-end">Interest paid</th><th>Late</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($cards as $card)
                            @php $student = $students[$card->user_id] ?? null; @endphp
                            <tr>
                                <td>{{ $student ? ($student->nickname ?: $student->name) : '—' }}</td>
                                <td>{{ $student ? ($classes[$student->grade] ?? '—') : '—' }}</td>
                                <td>{{ $card->created_at->format('d M Y') }}</td>
                                <td class="text-end">{{ number_format($card->owed, 2) }} Ƶ</td>
                                <td>{{ ['full' => 'All', 'minimum' => 'The minimum', 'fixed' => 'Fixed: ' . number_format((float) $card->fixed_amount, 2) . ' Ƶ'][$card->mode] ?? $card->mode }}</td>
                                <td class="text-end">{{ number_format($card->interest, 2) }} Ƶ</td>
                                <td>@if ($card->late)<span class="badge bg-danger">{{ $card->late }}</span>@else 0 @endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
