@extends('layouts.admin')

@section('title','Add Referendum')

@section('content')

<div class="container-fluid">

    <div class="row mb-3">

        <div class="col-md-6">

            <h3>Add Referendum</h3>

            <small class="text-muted">
                Create a new referendum for students.
            </small>

        </div>

        <div class="col-md-6 text-end">

            <a href="{{ url('admin/referendum') }}"
               class="btn btn-secondary">

                <i class="fa fa-arrow-left"></i>

                Back

            </a>

        </div>

    </div>

    @if ($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <div class="card">

        <div class="card-body">

            <form action="{{ url('admin/referendum/store') }}"
                  method="POST">

                @csrf

                <div class="row">

                    <div class="col-md-12 mb-3">

                        <label class="form-label">

                            Referendum Question <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="question"
                            class="form-control"
                            value="{{ old('question') }}"
                            placeholder="Enter referendum question"
                            required>

                    </div>

                    <div class="col-md-12 mb-3">

                        <label class="form-label">

                            Description

                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="5"
                            placeholder="Enter description">{{ old('description') }}</textarea>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Status

                        </label>

                        <select
                            name="status"
                            class="form-control">

                            <option value="open">
                                Open
                            </option>

                            <option value="closed">
                                Closed
                            </option>

                        </select>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Start Date

                        </label>

                        <input
                            type="date"
                            name="start_date"
                            class="form-control"
                            value="{{ old('start_date') }}">

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            End Date

                        </label>

                        <input
                            type="date"
                            name="end_date"
                            class="form-control"
                            value="{{ old('end_date') }}">

                    </div>

                    @if ($tutoredClasses->isNotEmpty())
                    <div class="col-md-6 mb-3">

                        <label class="form-label">Visible to</label>

                        <select name="scope" id="refScope" class="form-control" onchange="document.getElementById('refScopeId').style.display = this.value === 'school' ? 'none' : ''">
                            <option value="class">One of my Classes</option>
                            <option value="grade">My whole Grade</option>
                        </select>

                    </div>
                    <div class="col-md-6 mb-3" id="refScopeId">

                        <label class="form-label">Which Class / Grade</label>

                        <select name="scope_id" class="form-control">
                            @foreach ($tutoredClasses as $class)
                                <option value="{{ $class->id }}" data-grade="{{ $class->grade_id }}">
                                    {{ $class->name }} @if($class->grade)({{ $class->grade->name }})@endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">For "My whole Grade," pick any Class in that Grade — the whole Grade is used.</small>

                    </div>
                    @endif

                </div>

                <hr>

                <button
                    type="submit"
                    class="btn btn-success">

                    <i class="fa fa-save"></i>

                    Save Referendum

                </button>

                <a href="{{ url('admin/referendum') }}"
                   class="btn btn-secondary">

                    Cancel

                </a>

            </form>

        </div>

    </div>

</div>

@endsection