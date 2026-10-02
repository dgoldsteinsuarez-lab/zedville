@extends('layouts.admin')

@section('title', 'School Users')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">School Users</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('temporary_password'))
        <div class="alert alert-warning">
            <strong>Temporary password (shown once — copy it now and give it to the user):</strong>
            <code>{{ session('temporary_password') }}</code>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Add account</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.staff.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Full name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nickname (shown to students)</label>
                        <input type="text" name="nickname" class="form-control" value="{{ old('nickname') }}" required minlength="2" maxlength="30">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control" required>
                            <option value="3" @selected(old('role') == 3)>Tutor</option>
                            @if (auth()->user()->isSuperAdmin())
                                <option value="2" @selected(old('role') == 2)>School Admin</option>
                            @endif
                        </select>
                    </div>
                    @if (auth()->user()->isSuperAdmin())
                        <div class="col-md-2">
                            <label class="form-label">School</label>
                            <select name="sid" class="form-control" required>
                                <option value="">Select school</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" @selected(old('sid') == $school->id)>{{ $school->school_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label">Classes (Tutors only — pick the school's classes this Tutor looks after)</label>
                        <div>
                            @foreach ($classes as $class)
                                <label class="me-3">
                                    <input type="checkbox" name="class_ids[]" value="{{ $class->id }}"> {{ $class->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Create account</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Accounts</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr><th>Name</th><th>Nickname</th><th>Email</th><th>Role</th><th>Classes</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        <tr>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->nickname }}</td>
                            <td>{{ $member->email }}</td>
                            <td>{{ $member->role_name }}</td>
                            @if ($member->isTutor())
                                <td colspan="2">
                                    <form method="POST" action="{{ route('admin.staff.classes', $member->id) }}">
                                        @csrf
                                        @foreach ($classes->where('sid', $member->sid) as $class)
                                            <label class="me-2">
                                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}"
                                                    @checked($member->tutoredClasses->contains('id', $class->id))> {{ $class->name }}
                                            </label>
                                        @endforeach
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Save classes</button>
                                    </form>
                                </td>
                            @else
                                <td colspan="2" class="text-muted">—</td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted p-3">No accounts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
