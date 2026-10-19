@extends('layouts.admin')

@section('title', 'City Map Addresses')

@section('content')
<div class="container-fluid">
    <div class="mb-3 text-muted small">
        <a href="{{ route('admin.city-map.index') }}">City Map</a>
        <span class="mx-1">&rsaquo;</span>
        <strong>{{ $grade->name }}</strong>
    </div>

    <h2 class="mb-4">Addresses — {{ $grade->name }}</h2>

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Street</th><th>Number</th><th>Student</th></tr></thead>
                <tbody>
                    @forelse ($addresses as $address)
                        <tr>
                            <td>{{ $address->street->name }}</td>
                            <td>{{ $address->house_number }}</td>
                            <td>{{ $address->student->nickname ?? $address->student->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted p-3">No addresses generated for this Grade yet - the pool is created the first time a student in it needs one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
