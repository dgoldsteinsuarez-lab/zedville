@extends('layouts.admin')

@section('title', $grade->name)

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

    <h2 class="mb-4">{{ $grade->name }}</h2>

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

    @if ($canSeeCityProposals)
    <div class="card mb-4">
        <div class="card-header"><strong>City Proposals</strong></div>
        <div class="card-body p-0">
            <p class="text-muted small px-3 pt-3 mb-2">
                Students in this Grade vote on a new city name/quote; Tutors agree here on whether it goes live in City Hall.
            </p>
            <table class="table mb-0 align-middle">
                <thead><tr><th>Type</th><th>Proposed value</th><th>Proposed by</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($cityProposals as $p)
                    <tr>
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
                    <tr><td colspan="5" class="text-muted p-3">No proposals yet for this Grade.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Classes in this Grade</strong>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">Add Class</button>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Class Code</th><th>Name</th><th>Assigned Tutor</th><th>Students</th><th>Language</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse ($grade->classes as $class)
                        <tr>
                            <td>{{ $class->classCode }}</td>
                            <td>{{ $class->name }}</td>
                            <td>{{ $class->tutors->pluck('nickname')->filter()->implode(', ') ?: 'None assigned' }}</td>
                            <td>{{ $class->students_count }}</td>
                            <td>
                                @if (($class->language_mode ?? 'free') === 'locked')
                                    <span class="badge bg-warning text-dark">&#128274; {{ strtoupper($class->default_language ?? 'en') }} only</span>
                                @else
                                    <span class="text-muted small">Free ({{ strtoupper($class->default_language ?? 'en') }} default)</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-class-btn"
                                    data-bs-toggle="modal" data-bs-target="#editClassModal"
                                    data-id="{{ $class->id }}" data-name="{{ $class->name }}" data-code="{{ $class->classCode }}"
                                    data-language-mode="{{ $class->language_mode ?? 'free' }}" data-default-language="{{ $class->default_language ?? 'en' }}">
                                    Edit
                                </button>
                                <form method="POST" action="{{ url('admin/grade/delete/' . $class->id) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this Class? Students must be moved out first.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted p-3">No Classes under this Grade yet - add the first one above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            One Grade can contain several Classes (e.g. "8A", "8B") — each with its own Tutor(s) and its own
            student roster — while sharing this Grade's curriculum and Level. Assign a Tutor to a Class from the
            <a href="{{ url('admin/staff') }}">School User</a> screen. Looking for a Class from a different Grade?
            The <a href="{{ url('admin/grade') }}">Classes</a> screen lists every Class across every Grade.
        </div>
    </div>
</div>

{{-- Add Class - posts to the same admin/grade/add endpoint the Classes
     screen's own Add modal uses (AdminDashboardController::addGrade()) -
     grade_id is fixed to this Grade, so there's no dropdown to pick wrong. --}}
<section class="themeModal">
    <form action="{{ url('admin/grade/add') }}" method="post">
        @csrf
        <input type="hidden" name="grade_id" value="{{ $grade->id }}">
        <div class="modal fade" id="addClassModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Class to {{ $grade->name }}</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Class name</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. 8A" required>
                        </div>
                        <hr>
                        <p class="text-muted small mb-2">
                            Language (bugs.md, 2026-10-21) - let students switch freely, or lock everyone in this
                            class to one language (e.g. to force immersion for a language-learning class).
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Default language</label>
                            <select name="default_language" class="form-control">
                                <option value="en">English</option>
                                <option value="es">Spanish (Español)</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="language_mode" value="locked" id="newClassLocked">
                            <label class="form-check-label" for="newClassLocked">Lock to this language (students can't switch)</label>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

{{-- Edit Class - posts to the same admin/grade/edit/{id} endpoint
     (AdminDashboardController::updateGrade()); grade_id stays this Grade.
     action is set by JS below, per row clicked. --}}
<section class="themeModal">
    <form id="editClassForm" method="post">
        @csrf
        @method('PUT')
        <input type="hidden" name="grade_id" value="{{ $grade->id }}">
        <div class="modal fade" id="editClassModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Class</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Class name</label>
                            <input type="text" class="form-control" name="name" id="editClassName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Class Code</label>
                            <input type="text" class="form-control" name="classCode" id="editClassCode">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Default language</label>
                            <select name="default_language" id="editClassDefaultLanguage" class="form-control">
                                <option value="en">English</option>
                                <option value="es">Spanish (Español)</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="language_mode" value="locked" id="editClassLocked">
                            <label class="form-check-label" for="editClassLocked">Lock to this language (students can't switch)</label>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.edit-class-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('editClassForm').action = '{{ url('admin/grade/edit') }}/' + this.dataset.id;
        document.getElementById('editClassName').value = this.dataset.name;
        document.getElementById('editClassCode').value = this.dataset.code;
        document.getElementById('editClassDefaultLanguage').value = this.dataset.defaultLanguage || 'en';
        document.getElementById('editClassLocked').checked = this.dataset.languageMode === 'locked';
    });
});

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
