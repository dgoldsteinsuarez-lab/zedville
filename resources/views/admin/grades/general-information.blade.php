@extends('layouts.admin')

@section('title', 'General Information')

@section('content')
<div class="container-fluid">
    {{-- Admin Structure Proposal doc §2.3: breadcrumb of School > Grade > Level --}}
    <div class="mb-3 text-muted small">
        <a href="{{ url('admin/dashboard') }}">{{ $school->school_name ?? 'School' }}</a>
        <span class="mx-1">&rsaquo;</span>
        <strong>{{ $grade->name }}</strong>
        @if ($grade->level)
            <span class="mx-1">&rsaquo;</span>
            <span>{{ $grade->level->name }}</span>
        @endif
    </div>

    <h2 class="mb-4">General Information — {{ $grade->name }}</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>School</strong>
                    <a href="{{ url('admin/school-domain') }}" class="small">Edit school &rarr;</a>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> {{ $school->school_name ?? '—' }}</p>
                    <p class="mb-1"><strong>Domain:</strong> {{ $school->school_domain ?? '—' }}</p>
                    <p class="mb-1"><strong>Country/Region:</strong> {{ $school->country_region ?? '—' }}</p>
                    <p class="mb-0"><strong>Phone:</strong> {{ $school->school_phone ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header"><strong>Grade &amp; Level</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ url('admin/grades/' . $grade->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Grade name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $grade->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Level</label>
                            <select name="grade_level_id" class="form-control">
                                <option value="">Unassigned</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}" @selected($grade->grade_level_id == $level->id)>{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>School Month Settings</strong></div>
        <div class="card-body">
            <p class="text-muted small">
                Defines which month this school's academic-year progression starts — moved here from Education
                per the Admin Structure Proposal. Applies to the whole school, not just this Grade.
            </p>
            <div class="d-flex align-items-end gap-2">
                <div>
                    <label class="form-label">Start month</label>
                    <select id="sms-start-month" class="form-control">
                        <option value="">Select month</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" @selected(($monthSetting->start_month ?? null) == $i)>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="saveSchoolMonthSetting({{ $grade->sid }})">Save</button>
            </div>
            <div id="sms-msg" class="mt-2"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Classes in this Grade</strong>
            <a href="{{ url('admin/grade?add_to_grade=' . $grade->id) }}" class="btn btn-sm btn-primary">Add Class</a>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Class Code</th><th>Name</th><th>Assigned Tutor</th><th>Students</th></tr></thead>
                <tbody>
                    @forelse ($grade->classes as $class)
                        <tr>
                            <td>{{ $class->classCode }}</td>
                            <td>{{ $class->name }}</td>
                            <td>{{ $class->tutors->pluck('nickname')->filter()->implode(', ') ?: 'None assigned' }}</td>
                            <td>{{ $class->students_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted p-3">No Classes under this Grade yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            One Grade can contain several Classes (e.g. "8A", "8B") — each with its own Tutor(s) and its own
            student roster — while sharing this Grade's curriculum and Level. Manage Tutor assignment and rosters
            from the <a href="{{ url('admin/grade') }}">Classes</a> screen.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function saveSchoolMonthSetting(schoolId) {
    const startMonth = document.getElementById('sms-start-month').value;
    if (!startMonth) {
        document.getElementById('sms-msg').innerHTML = '<div class="alert alert-danger">Select a month first.</div>';
        return;
    }

    fetch('{{ url('admin/login-question/bulkSaveSMS') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ data: [{ school_id: schoolId, start_month: startMonth }] })
    })
        .then(r => r.json())
        .then(data => {
            document.getElementById('sms-msg').innerHTML = data.status
                ? '<div class="alert alert-success">' + data.message + '</div>'
                : '<div class="alert alert-danger">Something went wrong.</div>';
        });
}
</script>
@endpush
