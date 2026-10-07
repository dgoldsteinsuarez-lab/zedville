@extends('layouts.admin')

@section('title', 'Grades')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Grades</h2>
    <p class="text-muted">
        A Grade (e.g. "Grade 8") carries one curriculum Level, shared by every Class underneath it.
        Manage Classes (Tutor, roster, class code) on the <a href="{{ url('admin/grade') }}">Classes</a> screen.
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
                            <a href="{{ url('admin/grades/' . $g->id . '/general-information') }}" class="btn btn-sm btn-outline-secondary">General Information</a>
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
