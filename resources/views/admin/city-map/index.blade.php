@extends('layouts.admin')

@section('title', 'City Map')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">City Map</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>City Map Image</strong></div>
        <div class="card-body">
            <p class="text-muted small">
                One shared image used by every school/Grade's City Map page. Upload the exported render from
                your 3D model here - an angled/isometric view works best so each building stays recognizable
                (a straight top-down view loses the architecture).
            </p>

            @if ($setting->image)
                <img src="{{ asset('uploads/city/' . $setting->image) }}" class="img-fluid mb-3 rounded border" style="max-height: 320px;">
            @else
                <div class="alert alert-warning">No image uploaded yet - the student-facing City Map page shows a placeholder until one is set.</div>
            @endif

            <form method="POST" action="{{ route('admin.city-map.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" name="image" accept="image/*" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Streets</strong>
            <a href="{{ route('admin.city-map.streets') }}" class="btn btn-sm btn-primary">Manage Streets</a>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-0">Edit the name or economist bio shown when a student clicks a street plaque on the City Map.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Addresses by Grade</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Grade</th><th>School</th><th></th></tr></thead>
                <tbody>
                    @forelse ($grades as $grade)
                        <tr>
                            <td>{{ $grade->name }}</td>
                            <td>{{ $grade->sid }}</td>
                            <td><a href="{{ route('admin.city-map.addresses', $grade->id) }}" class="btn btn-sm btn-outline-secondary">View addresses</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted p-3">No Grades yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
