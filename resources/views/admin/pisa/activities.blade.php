@extends('layouts.admin')

@section('title', 'PISA - ' . $pisaLevel->domain . ' - ' . $pisaLevel->name)

@section('content')
<div class="container-fluid">
    <p class="mb-1"><a href="{{ route('admin.pisa.index') }}">&larr; PISA levels</a></p>
    <h2 class="mb-1">{{ $pisaLevel->domain }} - {{ $pisaLevel->name }}</h2>
    <p class="text-muted mb-1">
        @if ($pisaLevel->min_score || $pisaLevel->max_score)
            Score range: {{ $pisaLevel->min_score ?? '—' }} to {{ $pisaLevel->max_score ?? '—' }}.
        @endif
        {{ $pisaLevel->description }}
    </p>
    <p class="text-muted">
        {{-- Daniel, 2026-10-21: "for PISA i don't need to divide in
             Zedville levels, only the PISA levels... independent from if
             a student is Junior, Rookie or else, they will have the same
             activities." One global activity bank per PISA level now -
             every student sees these, regardless of Grade Level. --}}
        One activity bank for this PISA level, shared by every student on the platform.
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if (!$pisaCategory)
        <div class="alert alert-warning">No global PISA Practice category exists yet.</div>
    @else
        <div class="card mb-4">
            <div class="card-header"><strong>Activities at this level</strong></div>
            <div class="card-body">
                @if ($items->isEmpty())
                    <p class="text-muted mb-0">No activities at this level yet.</p>
                @else
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th></th><th>Title</th><th>Type</th><th>Questions</th><th>Active</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td style="width:40px">
                                        @if ($item->image)
                                            <img src="{{ asset('uploads/curriculum/' . $item->image) }}" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->title }}
                                        @if ($item->is_real_exam)
                                            <span class="badge bg-danger ms-1">Real exam</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($item->type) }}</td>
                                    <td>
                                        @if ($item->type === 'quiz')
                                            <a href="{{ url('admin/curriculum/items/' . $item->id . '/questions') }}">
                                                {{ $item->questions_count }} {{ \Illuminate\Support\Str::plural('question', $item->questions_count) }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="{{ $item->is_active ? 'text-success' : 'text-danger' }}">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ url('admin/curriculum/categories/' . $pisaCategory->id . '/items') }}" class="btn btn-sm btn-outline-secondary">
                                            Edit details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <details>
            <summary class="mb-2" style="cursor:pointer;"><strong>Add an activity at this level</strong></summary>
            <form method="POST" action="{{ url('admin/curriculum/categories/' . $pisaCategory->id . '/items') }}" class="row g-2 mt-2" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pisa_proficiency_level_id" value="{{ $pisaLevel->id }}">
                <div class="col-md-4">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Stimulus / scenario</label>
                    <input type="text" name="description" class="form-control" placeholder="The real-life situation this activity is built around">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control">
                        <option value="quiz">Quiz (scored)</option>
                        <option value="content">Content (no scoring)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stimulus image (optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/webp">
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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        </details>
    @endif
</div>
@endsection
