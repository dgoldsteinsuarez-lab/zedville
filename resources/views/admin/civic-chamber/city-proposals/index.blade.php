@extends('layouts.admin')

@section('title', 'City Proposals')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <h2 class="mb-0">City Name & Quote Proposals</h2>
        @include('components.school-badge')
    </div>
    <p class="text-muted">Students propose a city name and a city quote in the Civic Chamber. Approving one makes it live in City Hall.</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Grade</th><th>Type</th><th>Proposed value</th><th>Proposed by</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($proposals as $p)
                    <tr>
                        <td>{{ $p->grade->name ?? '—' }}</td>
                        <td>{{ ucfirst($p->type) }}</td>
                        <td>"{{ $p->value }}"</td>
                        <td>{{ $p->proposer->nickname ?? '—' }}</td>
                        <td>{{ ucfirst($p->status) }}</td>
                        <td class="d-flex gap-1">
                            @if ($p->status === 'pending')
                                <form method="POST" action="{{ route('admin.city-proposals.approve', $p->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.city-proposals.reject', $p->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted p-3">No proposals yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $proposals->links() }}
</div>
@endsection
