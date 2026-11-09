@extends('layouts.admin')

@section('title', 'Substitute Goods')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <h2 class="mb-0 d-flex align-items-center gap-2">Engagement: Substitute Goods (My Shopping Basket) @include('admin.partials.level-badge')</h2>
        @include('components.school-badge')
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>What the students do</strong></div>
        <div class="card-body">
            <p class="mb-2">
                While the activity runs for a class, <strong>3 items</strong> of each student's monthly shopping basket
                (from their Consumer Profile survey) become "out of stock". In the Supermarket - only in the section of
                their own diet - the student swaps each of them for a <strong>substitute good</strong>: another product
                of the same diet that does a similar job, at a similar or better price.
            </p>
            <ul class="mb-2">
                <li>It teaches substitute goods (chicken &harr; tofu, butter &harr; margarine) and comparing value for money.</li>
                <li>When the last out-of-stock item is swapped, the activity is completed and the student earns an <strong>Engagement</strong> point.</li>
                <li>Swapping changes the student's basket for the rest of the month. Their diet itself only changes with the survey (once a month).</li>
                <li>Students find the instructions on the activity page and in the Supermarket.</li>
            </ul>
            <a href="{{ route('engagement.substitute-goods') }}" target="_blank" class="small">See the student instructions page &rarr;</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Assign to a class</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.engagement.substitute-goods.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select name="cid" class="form-control" required>
                            <option value="">Select class</option>
                            @include('admin.partials.assignment-scope-options')
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('cid') == $class->id)>{{ $class->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Position</label>
                        <select name="position" class="form-control" required>
                            <option value="required" @selected(old('position') === 'required')>Required</option>
                            <option value="optional" @selected(old('position') === 'optional')>Optional</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Until</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', now()->endOfMonth()->toDateString()) }}" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Assign</button>
                <span class="text-muted small ms-2">Assigning a class again updates its dates.</span>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Classes with this activity</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Class</th><th>Position</th><th>From</th><th>Until</th><th></th></tr></thead>
                <tbody>
                @forelse ($assignments as $a)
                    <tr>
                        <td>{{ \App\Support\ClassLabel::make($a->class_grade_name, $a->class_name) }}</td>
                        <td>{{ $a->required ? 'Required' : 'Optional' }}</td>
                        <td>{{ $a->start_date }}</td>
                        <td>{{ $a->end_date }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.engagement.substitute-goods.delete', $a->id) }}" onsubmit="return confirm('Remove Substitute Goods from {{ \App\Support\ClassLabel::make($a->class_grade_name, $a->class_name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted p-3">Not assigned to any class yet - until it is, no student gets out-of-stock items.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
