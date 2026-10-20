@extends('layouts.admin')

@section('title', 'Grades')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Grades</h2>
    <p class="text-muted">
        A Grade (e.g. "Grade 8") carries one curriculum Level, shared by every Class underneath it (e.g. "8A",
        "8B"). Add a Grade below, then open <strong>Manage Classes</strong> on it to add its Classes. Each
        Class gets its own student roster and Tutor - assign a Tutor from the
        <a href="{{ url('admin/staff') }}">School User</a> screen.
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Add a Grade</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/grades') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Grade 8">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Level</label>
                    <select name="grade_level_id" class="form-control">
                        <option value="">Unassigned</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level->id }}" @selected(old('grade_level_id') == $level->id)>{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Add Grade</button>
                </div>
            </form>
        </div>
    </div>

    @if (auth()->user()->isSuperAdmin())
    {{-- Managing a level writes to AdminDashboardController::addGradeLevel()/
         updateGradeLevel()/deleteGradeLevel(), all Super-Admin-only (levels
         are a shared, global set - a School Admin assigns one to their own
         Grade above, but doesn't rename/add/remove the set itself). Gated
         here to match - the old copy of this card (moved from the Classes
         screen) wasn't, and would 403 silently for a School Admin on submit. --}}
    <div class="card mb-4">
        <div class="card-header"><strong>Grade levels</strong></div>
        <div class="card-body">
            <p class="text-muted">
                A fixed set of curriculum levels (e.g. "Rookie", "Intermediate") shared by every school -
                each Grade above is assigned one.
            </p>
            <form action="{{ url('admin/grade-level/add') }}" method="post" class="d-flex gap-2 mab-15">
                @csrf
                <input class="form-control w-300px" type="text" name="name" placeholder="New level, e.g. Rookie" required maxlength="100">
                <button type="submit" class="themeBtn text-white">Add level</button>
            </form>
            @forelse ($levels as $level)
                <form action="{{ url('admin/grade-level/edit', $level->id) }}" method="post" class="d-flex gap-2 mab-10 align-items-center">
                    @csrf @method('PUT')
                    <input class="form-control w-300px" type="text" name="name" value="{{ $level->name }}" required maxlength="100">
                    <button type="submit" class="themeBtnGray theme-text-dark">Rename</button>
                    <button type="submit" form="del-level-{{ $level->id }}" class="secondaryBtn theme-text-mute"
                        onclick="return confirm('Remove this level? Grades using it become unassigned.')">Remove</button>
                </form>
                <form id="del-level-{{ $level->id }}" action="{{ url('admin/grade-level/delete', $level->id) }}" method="post">@csrf @method('DELETE')</form>
            @empty
                <p class="text-muted mb-0">No levels yet.</p>
            @endforelse
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header"><strong>All Grades</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Name &amp; Level</th><th>Classes</th><th></th></tr></thead>
                <tbody>
                @forelse ($grades as $g)
                    <tr>
                        <td>
                            <form method="POST" action="{{ url('admin/grades/' . $g->id) }}" class="d-flex gap-2 align-items-center">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $g->name }}" class="form-control form-control-sm" style="max-width:200px">
                                <select name="grade_level_id" class="form-control form-control-sm" style="max-width:160px">
                                    <option value="">Unassigned</option>
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}" @selected($g->grade_level_id == $level->id)>{{ $level->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ url('admin/grades/' . $g->id . '/general-information') }}">
                                {{ $g->classes->count() }} {{ \Illuminate\Support\Str::plural('class', $g->classes->count()) }}
                            </a>
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ url('admin/grades/' . $g->id . '/general-information') }}" class="btn btn-sm btn-primary">Manage Classes</a>
                            <form method="POST" action="{{ url('admin/grades/' . $g->id) }}" onsubmit="return confirm('Delete this Grade? Its Classes must be empty/reassigned first.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
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
