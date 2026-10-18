@extends('layouts.admin')

@section('title', 'FinHero Point Defaults')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">FinHero — Global Point Defaults</h2>
    <p class="text-muted">
        One default point value per activity, shared by every school. When an activity is assigned anywhere
        without a specific point value typed in, this default applies. Editing a default here changes it for
        every school that has not manually overridden that activity - it does not touch schools that already have
        their own value set.
    </p>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Defaults</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Activity key</th><th>Name</th><th>Type</th><th>Default points</th><th>Default salary</th><th></th></tr></thead>
                <tbody>
                @forelse ($defaults as $default)
                    <tr>
                        <td><code>{{ $default->activity_key }}</code></td>
                        <td>{{ $default->activity_name ?? '—' }}</td>
                        <td>{{ ucfirst($default->type ?? '—') }}</td>
                        <td colspan="3">
                            <form method="POST" action="{{ route('finhero.point-defaults.update', $default->id) }}" class="d-flex gap-2 align-items-center">
                                @csrf
                                @method('PUT')
                                <input type="number" name="max_points" min="1" max="100" class="form-control form-control-sm" style="width:90px"
                                       value="{{ $default->max_points }}" placeholder="points">
                                @if ($default->type === 'task')
                                    <input type="number" step="0.01" min="0" name="salary" class="form-control form-control-sm" style="width:110px"
                                           value="{{ $default->salary }}" placeholder="salary">
                                @endif
                                <button class="btn btn-sm btn-primary">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted p-3">No activities registered yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Add a default for a new activity key</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('finhero.point-defaults.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Activity key</label>
                    <input type="text" name="activity_key" class="form-control" placeholder="task_5" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Display name</label>
                    <input type="text" name="activity_name" class="form-control" placeholder="Match Needs and Wants">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control" required>
                        <option value="task">Task</option>
                        <option value="library">Library</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Default points</label>
                    <input type="number" name="max_points" min="1" max="100" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Default salary</label>
                    <input type="number" step="0.01" min="0" name="salary" class="form-control">
                </div>
                <div class="col-md-12">
                    <button class="btn btn-primary">Add default</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
