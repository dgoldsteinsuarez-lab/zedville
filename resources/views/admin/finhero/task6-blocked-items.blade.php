@extends('layouts.admin')

@section('title', 'Task 6 — Blocked Items')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Task 6 — Blocked Items</h2>
    <p class="text-muted">
        Mark which Supermarket items are "temporarily unavailable" for a Class while Task 6 (Substitute Goods) is
        active for them. A student in this Class with one of these in their basket must swap it for a substitute
        to complete the task.
    </p>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finhero.task6-blocked-items.index') }}" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label">Class</label>
                    <select name="classId" class="form-control" onchange="this.form.submit()">
                        <option value="">Select a class&hellip;</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if ($classId && $items->isNotEmpty())
    <div class="card">
        <div class="card-body p-0">
            <form method="POST" action="{{ route('finhero.task6-blocked-items.update') }}">
                @csrf
                <input type="hidden" name="classId" value="{{ $classId }}">
                <table class="table mb-0 align-middle">
                    <thead><tr><th style="width:40px"></th><th>Item</th><th>Diet</th><th>Price</th></tr></thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td><input type="checkbox" name="blocked[]" value="{{ $item->id }}" @checked($blockedIds->contains($item->id))></td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ ucfirst($item->type) }}</td>
                                <td>{{ number_format($item->price, 2) }} Zed</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    @elseif ($classId)
        <div class="alert alert-warning">No Supermarket items found.</div>
    @endif
</div>
@endsection
