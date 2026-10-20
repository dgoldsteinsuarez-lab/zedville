@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">
        Home
    </h1>
</div>

@if (auth()->user()->isTutor())
    @php
        $tutorActivitiesThisMonth = \Illuminate\Support\Facades\DB::table('activity_completions')
            ->where('student_id', auth()->id())
            ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $autopilotOn = auth()->user()->autopilot_enabled;
    @endphp
    <div class="alert {{ $autopilotOn ? 'alert-info' : 'alert-warning' }} mab-15 d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <strong>
                @if (!$autopilotOn)
                    Autopilot is OFF for your account.
                @elseif ($tutorActivitiesThisMonth > 0)
                    Autopilot is not needed this month - you have completed {{ $tutorActivitiesThisMonth }} {{ \Illuminate\Support\Str::plural('activity', $tutorActivitiesThisMonth) }}.
                @else
                    You are on Autopilot this month.
                @endif
            </strong>
            <div class="mt-1">
                @if ($autopilotOn)
                    <em>What is Autopilot?</em> Your own citizen account runs on Autopilot by default. If you do not
                    complete any activities during a month, the system applies your class's average activity to your
                    account at month-end, so it works as a passive benchmark of how your class is doing. If you
                    complete activities yourself, Autopilot is not applied for that month.
                @else
                    You switched Autopilot off, so your account will <strong>not</strong> receive your class's average
                    activity even if you don't log any activity this month. Turn it back on any time.
                @endif
            </div>
        </div>
        <form action="{{ url('admin/autopilot/toggle-self') }}" method="POST" class="flex-shrink-0">
            @csrf
            <input type="hidden" name="autopilot_enabled" value="{{ $autopilotOn ? 0 : 1 }}">
            <button type="submit" class="btn btn-sm {{ $autopilotOn ? 'btn-outline-secondary' : 'btn-primary' }}">
                {{ $autopilotOn ? 'Switch to manual' : 'Turn Autopilot back on' }}
            </button>
        </form>
    </div>
@endif

{{-- Admin Structure Proposal doc §2.3: Grade/Class drill-down for the
     currently selected School. Which school that is, and picking a
     different one, is now shown by the "Managing: X" / "No school
     selected" banner at the very top of every admin page (layouts/admin
     .blade.php) - not repeated here (bugs.md, 2026-10-20). --}}
@if ($selectedSchool)
    <div class="row">
        @forelse ($grades as $grade)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-1">{{ $grade->name }}</h5>
                            @if ($grade->level)
                                <span class="badge bg-light text-dark border">{{ $grade->level->name }}</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-2">{{ $grade->classes->count() }} {{ \Illuminate\Support\Str::plural('class', $grade->classes->count()) }}: {{ $grade->classes->pluck('name')->implode(', ') ?: '—' }}</p>
                        <p class="small mb-1">
                            <strong>Tutor{{ $grade->tutor_names->count() > 1 ? 's' : '' }}:</strong>
                            {{ $grade->tutor_names->implode(', ') ?: 'None assigned' }}
                        </p>
                        <p class="small mb-3"><strong>Students:</strong> {{ $grade->students_count }}</p>
                        <a href="{{ url('admin/grades/' . $grade->id . '/general-information') }}" class="btn btn-sm btn-primary">
                            General Information
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted">No Grades yet for this school. <a href="{{ url('admin/grades') }}">Add one</a>.</p>
            </div>
        @endforelse
    </div>
@endif
@endsection
