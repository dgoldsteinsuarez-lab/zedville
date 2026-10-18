@extends('layouts.admin')

@section('title', 'Supermarket Stores')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Supermarket Stores</h2>
    <p class="text-muted">
        A product is assigned to one of these stores (its "type"). The consumer profile survey's diet step reads
        this to show groceries for a diet - it only finds anything for a diet whose store is named exactly
        <strong>Omnivore</strong>, <strong>Vegetarian</strong>, <strong>Pescatarian</strong>, or <strong>Vegan</strong>
        (capitalization doesn't matter, the spelling does). Other store names work fine for regular Supermarket
        shopping, just not for that survey.
    </p>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Add a store</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.admin.education.supermarket-store-names.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Omnivore" required maxlength="100">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary">Add store</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Stores</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Name</th><th></th></tr></thead>
                <tbody>
                @forelse ($storeNames as $store)
                    <tr>
                        <td>{{ $store->name }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.admin.education.supermarket-store-names.delete', $store->id) }}" onsubmit="return confirm('Remove this store? Products already assigned to it will keep the old name as plain text.')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-muted p-3">No stores yet. Add one above, then manage its products from the <a href="{{ route('admin.admin.education.supermarket') }}">Supermarket Products</a> screen.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
