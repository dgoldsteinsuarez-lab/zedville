@extends('layouts.admin')

@section('title', 'Edit Street')

@section('content')
<div class="container-fluid">
    <div class="mb-3 text-muted small">
        <a href="{{ route('admin.city-map.index') }}">City Map</a>
        <span class="mx-1">&rsaquo;</span>
        <a href="{{ route('admin.city-map.streets') }}">Streets</a>
        <span class="mx-1">&rsaquo;</span>
        <strong>{{ $street->name }}</strong>
    </div>

    <h2 class="mb-4">Edit Street</h2>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <p class="text-muted small">
                Shown to students when they click this street's plaque on the City Map.
            </p>
            <form method="POST" action="{{ route('admin.city-map.streets.update', $street->id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $street->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="5" required>{{ old('bio', $street->bio) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.city-map.streets') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
