@extends('layouts.admin')

@section('title', 'PISA')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">PISA</h2>
    <p class="text-muted">
        PISA's own official proficiency/difficulty levels (an international standard, the same for every school
        and every Grade Level) - not tied to Rookie/Intermediate/Advance/Advance Economist. Shown here as a
        reference catalog to build PISA-practice assignments against later.
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Add a Level</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/pisa') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Domain</label>
                    <input type="text" name="domain" class="form-control" value="{{ old('domain', 'Financial Literacy') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Level 3">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order') }}" required min="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Min score</label>
                    <input type="number" step="0.01" name="min_score" class="form-control" value="{{ old('min_score') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Max score</label>
                    <input type="number" step="0.01" name="max_score" class="form-control" value="{{ old('max_score') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Add Level</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>All Levels</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Domain</th><th>Name</th><th>Order</th><th>Score range</th><th>Description</th><th></th></tr></thead>
                <tbody>
                @forelse ($levels as $level)
                    <tr>
                        <td colspan="6" class="p-0">
                            <form method="POST" action="{{ url('admin/pisa/' . $level->id) }}" class="row g-2 align-items-start p-3">
                                @csrf @method('PUT')
                                <div class="col-md-2">
                                    <input type="text" name="domain" value="{{ $level->domain }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="name" value="{{ $level->name }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-1">
                                    <input type="number" name="sort_order" value="{{ $level->sort_order }}" class="form-control form-control-sm" required min="1">
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex gap-1">
                                        <input type="number" step="0.01" name="min_score" value="{{ $level->min_score }}" class="form-control form-control-sm" placeholder="Min">
                                        <input type="number" step="0.01" name="max_score" value="{{ $level->max_score }}" class="form-control form-control-sm" placeholder="Max">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <textarea name="description" class="form-control form-control-sm" rows="2">{{ $level->description }}</textarea>
                                </div>
                                <div class="col-md-1 d-flex flex-column gap-1">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="pt-0 pb-2 ps-3">
                            <form method="POST" action="{{ url('admin/pisa/' . $level->id) }}" onsubmit="return confirm('Delete this PISA level?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted p-3">No PISA levels yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
