@extends('layouts.admin')

@section('title', 'Curriculum - ' . $level->name)

@section('content')
<div class="container-fluid">
    <p class="mb-1"><a href="{{ url('admin/curriculum') }}">&larr; All Levels</a></p>
    <h2 class="mb-2">{{ $level->name }} - Categories</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Add a Category</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/curriculum/levels/' . $level->id . '/categories') }}" class="row g-3">
                @csrf
                <div class="col-md-8">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Activities">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $categories->count() + 1 }}" required min="1">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Categories</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Name</th><th>Items</th><th>Order</th><th></th><th></th></tr></thead>
                <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td colspan="5" class="p-0">
                            <form method="POST" action="{{ url('admin/curriculum/categories/' . $category->id) }}" class="row g-2 align-items-center p-3">
                                @csrf @method('PUT')
                                <div class="col-md-5">
                                    <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm" required>
                                    @if ($category->is_pisa_practice)
                                        <span class="badge bg-light text-dark border mt-1">PISA Practice</span>
                                    @endif
                                </div>
                                <div class="col-md-2">{{ $category->items_count }} {{ \Illuminate\Support\Str::plural('item', $category->items_count) }}</div>
                                <div class="col-md-2">
                                    <input type="number" name="sort_order" value="{{ $category->sort_order }}" class="form-control form-control-sm" required min="1">
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <a href="{{ url('admin/curriculum/categories/' . $category->id . '/items') }}" class="btn btn-sm btn-outline-primary">Items</a>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                                    <button type="submit" form="del-cat-{{ $category->id }}" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete this category and everything in it?')">Delete</button>
                                </div>
                            </form>
                            <form id="del-cat-{{ $category->id }}" action="{{ url('admin/curriculum/categories/' . $category->id) }}" method="post" class="d-none">@csrf @method('DELETE')</form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted p-3">No categories yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
