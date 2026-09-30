@extends('layouts.admin')

@section('title','Petitions')

@section('content')

<div class="container-fluid">

    <div class="row mb-3">

        <div class="col-md-6">

            <h3 class="mb-0">
                Student Petitions
            </h3>

            <small class="text-muted">
                Review, approve or reject student petitions.
            </small>

        </div>

    </div>

    @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif

    <div class="card">

        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped align-middle">

                <thead class="table-dark">

                <tr>

                    <th width="60">ID</th>

                    <th>Title</th>

                    <th width="150">Created By</th>

                    <th width="120">Status</th>

                    <th width="120">Signatures</th>

                    <th width="160">Created</th>

                    <th width="180" class="text-center">

                        Action

                    </th>

                </tr>

                </thead>

                <tbody>

                @forelse($petitions as $petition)

                    <tr>

                        <td>

                            {{ $petition->id }}

                        </td>

                        <td>

                            <strong>

                                {{ $petition->title }}

                            </strong>

                            <br>

                            <small class="text-muted">

                                {{ Str::limit($petition->description,80) }}

                            </small>

                        </td>

                        <td>

                            @if ($petition->creator)
                                {{ $petition->creator->nickname }}
                                <br><small class="text-muted">{{ $petition->creator->name }}</small>
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif

                        </td>

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

                        <td>

                            {{ $petition->signatures()->count() }}

                        </td>

                        <td>

                            {{ $petition->created_at->format('d M Y') }}

                            <br>

                            <small>

                                {{ $petition->created_at->format('h:i A') }}

                            </small>

                        </td>

                        <td class="text-center">

                            <a href="{{ url('admin/petition/show/'.$petition->id) }}"

                               class="btn btn-info btn-sm">

                                <i class="fa fa-eye"></i>

                            </a>

                            <form

                                action="{{ url('admin/petition/delete/'.$petition->id) }}"

                                method="POST"

                                style="display:inline-block;"

                                onsubmit="return confirm('Delete this petition?')">

                                @csrf

                                @method('DELETE')

                                <button

                                    class="btn btn-danger btn-sm">

                                    <i class="fa fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="7"

                            class="text-center">

                            No Petition Found

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

            <div class="mt-3">

                {{ $petitions->links() }}

            </div>

        </div>

    </div>

</div>

@endsection