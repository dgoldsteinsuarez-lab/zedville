@extends('layouts.admin')

@section('title', 'My Students')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">My Students</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @forelse ($levels as $levelName => $classes)
        <h4 class="mt-4 mb-3">{{ $levelName !== '' ? $levelName : 'Classes without a grade level' }}</h4>
        @foreach ($classes as $class)
        <div class="card mb-4" id="class-{{ $class->id }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ $class->name }}</strong>
                <span class="text-muted small">
                    Tutor{{ $class->tutors->count() > 1 ? 's' : '' }}:
                    {{ $class->tutors->pluck('nickname')->filter()->implode(', ') ?: 'None assigned' }}
                </span>
            </div>
            {{-- The class language starts as the Grade's choice; the Tutor can
                 change it for this class (Daniel, 2026-10-22). --}}
            <details class="border-bottom px-3 py-2">
                <summary class="small">
                    <strong>Language:</strong>
                    {{ config('zedville.languages')[$class->default_language ?? 'en'] ?? strtoupper($class->default_language ?? 'en') }}
                    @if (($class->language_mode ?? 'free') === 'locked')
                        <span class="badge bg-warning text-dark">&#128274; locked</span>
                    @else
                        <span class="text-muted">(students can switch)</span>
                    @endif
                    @if (($class->language_level ?? 'standard') === 'beginner')
                        <span class="badge bg-info text-dark">Beginner</span>
                    @elseif (($class->language_level ?? 'standard') === 'hybrid')
                        <span class="badge bg-success">Hybrid + English</span>
                    @endif
                    <span class="text-primary ms-1">Change</span>
                </summary>
                <form method="POST" action="{{ route('admin.tutor.class-language', $class->id) }}" class="pt-3" style="max-width: 520px">
                    @csrf
                    @method('PUT')
                    @include('admin.partials.language-fields', [
                        'settings' => ['language_mode' => $class->language_mode, 'default_language' => $class->default_language, 'language_level' => $class->language_level],
                        'idPrefix' => 'class' . $class->id,
                    ])
                    <button type="submit" class="btn btn-sm btn-primary mb-2">Save language for {{ $class->name }}</button>
                </form>
            </details>
            <div class="card-body p-0">
                @if ($class->students->isEmpty())
                    <p class="text-muted p-3 mb-0">No students in this class yet.</p>
                @else
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Nickname</th>
                                <th>Real name</th>
                                <th>Citizen ID</th>
                                <th>Balance</th>
                                <th>Activities this month</th>
                                <th>Engagement</th>
                                <th>FinHero</th>
                                <th>Mood</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($class->students as $student)
                                @php $s = $student->summary; @endphp
                                <tr>
                                    <td>{{ $student->nickname }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->citizenId }}</td>
                                    <td>{{ number_format($s['balance'], 2) }} zeds</td>
                                    <td>{{ $s['activities_this_month'] }}</td>
                                    <td>
                                        <span class="badge" style="background:{{ $s['engagement_meta']['color'] }}; color:{{ $s['engagement_meta']['text'] }};">
                                            {{ $s['engagement_meta']['emoji'] }} {{ $s['engagement_meta']['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:{{ $s['finhero_meta']['color'] }}; color:{{ $s['finhero_meta']['text'] }};">
                                            {{ $s['finhero_meta']['emoji'] }} {{ $s['finhero_meta']['label_en'] }}
                                        </span>
                                    </td>
                                    <td>{{ $s['mood'] ?? '—' }}</td>
                                    <td>
                                        <a href="{{ url('admin/my-students/' . $student->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    </td>
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
