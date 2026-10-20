@extends('layouts.admin')

@section('title', 'Curriculum')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Curriculum</h2>
    <p class="text-muted">
        Activities, Exams and Graphics for each Grade Level - a new, separate area from the per-Grade Education
        settings. Pick a Level to manage its categories.
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @foreach ($levels as $level)
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $level->name }}</h5>
                        <p class="text-muted small mb-3">
                            {{ $categoryCounts[$level->id] ?? 0 }} {{ \Illuminate\Support\Str::plural('category', $categoryCounts[$level->id] ?? 0) }}
                        </p>
                        <a href="{{ url('admin/curriculum/levels/' . $level->id . '/categories') }}" class="btn btn-sm btn-primary mt-auto">
                            Manage categories
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
