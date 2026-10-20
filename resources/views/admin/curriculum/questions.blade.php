@extends('layouts.admin')

@section('title', 'Curriculum - ' . $item->title)

@section('content')
<div class="container-fluid">
    <p class="mb-1"><a href="{{ url('admin/curriculum/categories/' . $item->category_id . '/items') }}">&larr; Items</a></p>
    <h2 class="mb-2">{{ $item->title }} - Questions</h2>
    <p class="text-muted">
        Up to 4 options per question - mark which one is correct. A question needs at least 2 filled-in options
        and one marked correct before it can be saved.
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Add a Question</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/curriculum/items/' . $item->id . '/questions/save') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Question</label>
                    <textarea name="question_text" class="form-control" rows="2" required></textarea>
                </div>
                <input type="hidden" name="sort_order" value="{{ $questions->count() + 1 }}">
                <div class="row g-2">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <input type="radio" name="correct" value="{{ $i }}" required aria-label="Correct answer">
                                </span>
                                <input type="text" name="options[{{ $i }}]" class="form-control" placeholder="Option {{ $i + 1 }}{{ $i < 2 ? ' (required)' : ' (optional)' }}">
                            </div>
                        </div>
                    @endfor
                </div>
                <button type="submit" class="btn btn-primary mt-3">Add Question</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Existing Questions</strong></div>
        <div class="card-body p-0">
            @forelse ($questions as $question)
                <form method="POST" action="{{ url('admin/curriculum/items/' . $item->id . '/questions/save') }}" class="p-3 border-bottom">
                    @csrf
                    <input type="hidden" name="id" value="{{ $question->id }}">
                    <input type="hidden" name="sort_order" value="{{ $question->sort_order }}">
                    <div class="mb-2">
                        <textarea name="question_text" class="form-control" rows="2" required>{{ $question->question_text }}</textarea>
                    </div>
                    <div class="row g-2">
                        @for ($i = 0; $i < 4; $i++)
                            @php $option = $question->options[$i] ?? null; @endphp
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input type="radio" name="correct" value="{{ $i }}" @checked($option && $option->is_correct) aria-label="Correct answer">
                                    </span>
                                    @if ($option)
                                        <input type="hidden" name="option_ids[{{ $i }}]" value="{{ $option->id }}">
                                    @endif
                                    <input type="text" name="options[{{ $i }}]" class="form-control" value="{{ $option->option_text ?? '' }}" placeholder="Option {{ $i + 1 }}">
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div class="mt-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                        <button type="submit" form="del-q-{{ $question->id }}" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Delete this question?')">Delete</button>
                    </div>
                </form>
                <form id="del-q-{{ $question->id }}" action="{{ url('admin/curriculum/questions/' . $question->id) }}" method="post" class="d-none">@csrf @method('DELETE')</form>
            @empty
                <p class="text-muted p-3 mb-0">No questions yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
