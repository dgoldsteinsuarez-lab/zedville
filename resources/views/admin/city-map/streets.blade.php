@extends('layouts.admin')

@section('title', 'City Map Streets')

@section('content')
<div class="container-fluid">
    <div class="mb-3 text-muted small">
        <a href="{{ route('admin.city-map.index') }}">City Map</a>
        <span class="mx-1">&rsaquo;</span>
        <strong>Streets</strong>
    </div>

    <h2 class="mb-4">Streets</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Name</th><th>Bio</th><th></th></tr></thead>
                <tbody>
                    @foreach ($streets as $street)
                        <tr>
                            <td class="text-nowrap"><strong>{{ $street->name }}</strong></td>
                            <td>{{ \Illuminate\Support\Str::limit($street->bio, 120) }}</td>
                            <td><a href="{{ route('admin.city-map.streets.edit', $street->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
