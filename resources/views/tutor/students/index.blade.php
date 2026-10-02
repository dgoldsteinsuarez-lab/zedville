@extends('layouts.admin')

@section('title', 'My Students')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">My Students</h2>

    @forelse ($levels as $levelName => $classes)
        <h4 class="mt-4 mb-3">{{ $levelName !== '' ? $levelName : 'Classes without a grade level' }}</h4>
        @foreach ($classes as $class)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ $class->name }}</strong>
                <span class="text-muted small">
                    Tutor{{ $class->tutors->count() > 1 ? 's' : '' }}:
                    {{ $class->tutors->pluck('nickname')->filter()->implode(', ') ?: 'None assigned' }}
                </span>
            </div>
            <div class="card-body p-0">
                @if ($class->students->isEmpty())
                    <p class="text-muted p-3 mb-0">No students in this class yet.</p>
                @else
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Nickname</th>
                                <th>Real name</th>
                                <th>Citizen ID</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($class->students as $student)
                                <tr>
                                    <td>{{ $student->nickname }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->citizenId }}</td>
                                    <td>{{ $student->created_at?->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
        @endforeach
    @empty
        <p class="text-muted">You haven't been assigned to any classes yet.</p>
    @endforelse
</div>
@endsection
