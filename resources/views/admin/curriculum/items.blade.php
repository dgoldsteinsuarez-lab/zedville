@extends('layouts.admin')

@section('title', 'Curriculum - ' . $category->name)

@section('content')
<div class="container-fluid">
    <p class="mb-1"><a href="{{ url('admin/curriculum/levels/' . $category->grade_level_id . '/categories') }}">&larr; Categories</a></p>
    <h2 class="mb-2">{{ $category->name }} - Items</h2>
    <p class="text-muted">
        "Quiz" items have their own question bank (students answer and get a score) - add questions after creating
        one. "Content" items are a simple title + description shown to students, no scoring (for Graphics).
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Add an Item</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/curriculum/categories/' . $category->id . '/items') }}" class="row g-3" enctype="multipart/form-data">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control">
                        <option value="quiz">Quiz (scored)</option>
                        <option value="content">Content (no scoring)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">PISA proficiency level (optional)</label>
                    <select name="pisa_proficiency_level_id" class="form-control">
                        <option value="">None</option>
                        @foreach ($pisaLevels as $pisaLevel)
                            <option value="{{ $pisaLevel->id }}">{{ $pisaLevel->domain }} - {{ $pisaLevel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $items->count() + 1 }}" required min="1">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="newItemActive" checked>
                        <label class="form-check-label" for="newItemActive">Active</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Image (stimulus/chart, optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/webp">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start date (optional)</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End date (optional)</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_real_exam" value="1" class="form-check-input" id="newItemRealExam">
                        <label class="form-check-label" for="newItemRealExam">Real exam</label>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Items</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Title</th><th>Type</th><th>PISA level</th><th>Questions</th><th>Active</th><th></th></tr></thead>
                <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td colspan="6" class="p-0">
                            <form method="POST" action="{{ url('admin/curriculum/items/' . $item->id) }}" class="row g-2 align-items-center p-3" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="col-md-3">
                                    <input type="text" name="title" value="{{ $item->title }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-2">
                                    <select name="type" class="form-control form-control-sm">
                                        <option value="quiz" @selected($item->type === 'quiz')>Quiz</option>
                                        <option value="content" @selected($item->type === 'content')>Content</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="pisa_proficiency_level_id" class="form-control form-control-sm">
                                        <option value="">None</option>
                                        @foreach ($pisaLevels as $pisaLevel)
                                            <option value="{{ $pisaLevel->id }}" @selected($item->pisa_proficiency_level_id == $pisaLevel->id)>{{ $pisaLevel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    @if ($item->type === 'quiz')
                                        <a href="{{ url('admin/curriculum/items/' . $item->id . '/questions') }}" class="btn btn-sm btn-outline-primary">
                                            {{ $item->questions_count }} {{ \Illuminate\Support\Str::plural('question', $item->questions_count) }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                                <div class="col-md-1 form-check">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($item->is_active)>
                                </div>
                                <div class="col-md-2 d-flex gap-2">
                                    <input type="hidden" name="description" value="{{ $item->description }}">
                                    <input type="hidden" name="sort_order" value="{{ $item->sort_order }}">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                                    <button type="submit" form="del-item-{{ $item->id }}" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete this item and all its questions/results?')">Delete</button>
                                </div>

                                <div class="col-md-2 d-flex align-items-center gap-2">
                                    @if ($item->image)
                                        <img src="{{ asset('uploads/curriculum/' . $item->image) }}" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
                                    @endif
                                    <input type="file" name="image" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp" title="Replace image">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $item->start_date?->format('Y-m-d') }}" title="Start date">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $item->end_date?->format('Y-m-d') }}" title="End date">
                                </div>
                                <div class="col-md-2 form-check">
                                    <input type="checkbox" name="is_real_exam" value="1" class="form-check-input" @checked($item->is_real_exam)>
                                    <label class="form-check-label">Real exam</label>
                                </div>
                            </form>
                            <form id="del-item-{{ $item->id }}" action="{{ url('admin/curriculum/items/' . $item->id) }}" method="post" class="d-none">@csrf @method('DELETE')</form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted p-3">No items yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
