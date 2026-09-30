@extends('layouts.admin')

@section('title','Petition Details')

@section('content')

<div class="container-fluid">

    <div class="row mb-3">

        <div class="col-md-6">

            <h3>Petition Details</h3>

            <small class="text-muted">

                Review and manage this petition.

            </small>

        </div>

        <div class="col-md-6 text-end">

            <a href="{{ url('admin/petition') }}"
               class="btn btn-secondary">

                <i class="fa fa-arrow-left"></i>

                Back

            </a>

        </div>

    </div>

    @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif

    @if($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <div class="card">

        <div class="card-header bg-primary text-white">

            Petition Information

        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <tr>

                    <th width="220">

                        Petition Title

                    </th>

                    <td>

                        {{ $petition->title }}

                    </td>

                </tr>

                <tr>

                    <th>

                        Description

                    </th>

                    <td>

                        {!! nl2br(e($petition->description)) !!}

                    </td>

                </tr>

                <tr>

                    <th>

                        Created By

                    </th>

                    <td>

                        @if ($petition->creator)
                            {{ $petition->creator->nickname }} ({{ $petition->creator->name }})
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif

                    </td>

                </tr>

                <tr>

                    <th>

                        Total Signatures

                    </th>

                    <td>

                        {{ $petition->signatures()->count() }}

                    </td>

                </tr>

                <tr>

                    <th>

                        Status

                    </th>

                    <td>

                        @if($petition->status=='pending')

                            <span class="badge bg-warning text-dark">

                                Pending

                            </span>

                        @elseif($petition->status=='approved')

                            <span class="badge bg-success">

                                Approved

                            </span>

                        @elseif($petition->status=='rejected')

                            <span class="badge bg-danger">

                                Rejected

                            </span>

                        @else

                            <span class="badge bg-secondary">

                                Closed

                            </span>

                        @endif

                    </td>

                </tr>

                <tr>

                    <th>

                        Created Date

                    </th>

                    <td>

                        {{ $petition->created_at->format('d M Y h:i A') }}

                    </td>

                </tr>

            </table>

        </div>

    </div>

    <br>

    <div class="card">

        <div class="card-header bg-info text-white">

            Tutor Feedback

        </div>

        <div class="card-body">

            <form action="{{ url('admin/petition/update-feedback/'.$petition->id) }}"
                  method="POST">

                @csrf

                <div class="mb-3">

                    <label class="form-label">

                        Feedback

                    </label>

                    <textarea
                        name="tutor_feedback"
                        rows="6"
                        class="form-control"
                        placeholder="Write tutor feedback...">{{ old('tutor_feedback',$petition->tutor_feedback) }}</textarea>

                </div>

                <button
                    class="btn btn-primary">

                    <i class="fa fa-save"></i>

                    Save Feedback

                </button>

            </form>

        </div>

    </div>

    <br>

    <div class="card">

        <div class="card-header bg-warning">

            Petition Action

        </div>

        <div class="card-body">

            <div class="d-flex gap-2 flex-wrap">

                @if($petition->status!='approved')

                <form
                    action="{{ url('admin/petition/approve/'.$petition->id) }}"
                    method="POST">

                    @csrf

                    <input
                        type="hidden"
                        name="tutor_feedback"
                        value="{{ $petition->tutor_feedback }}">

                    <button
                        class="btn btn-success">

                        <i class="fa fa-check"></i>

                        Approve

                    </button>

                </form>

                @endif

                @if($petition->status!='rejected')

                <form
                    action="{{ url('admin/petition/reject/'.$petition->id) }}"
                    method="POST">

                    @csrf

                    <input
                        type="hidden"
                        name="tutor_feedback"
                        value="{{ $petition->tutor_feedback }}">

                    <button
                        class="btn btn-danger">

                        <i class="fa fa-times"></i>

                        Reject

                    </button>

                </form>

                @endif

                @if($petition->status!='closed')

                <form
                    action="{{ url('admin/petition/close/'.$petition->id) }}"
                    method="POST">

                    @csrf

                    <button
                        class="btn btn-secondary">

                        <i class="fa fa-lock"></i>

                        Close Petition

                    </button>

                </form>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection