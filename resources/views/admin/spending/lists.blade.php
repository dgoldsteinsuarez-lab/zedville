@extends('layouts.admin')

@section('title', 'Shopping lists')

@section('content')
@php
    $groupNames = ['protein' => 'Proteins', 'carbs' => 'Carbohydrates', 'vegetables' => 'Vegetables', 'fruit' => 'Fruit', 'dairy' => 'Dairy', 'fats' => 'Fats'];
    $byShop = $products->groupBy('type');
@endphp
<div class="container-fluid">
    <h2 class="mb-1 d-flex align-items-center gap-2">Spending Activity: shopping lists @include('admin.partials.level-badge')</h2>
    <p class="text-muted">
        Each activity is a short shopping list with a budget. Students buy what is on it - nothing else - and stay within the budget;
        something off the list is refused with the reason. A line asks for a number of products: from a food group (Supermarket), from a few
        products you name (one shop), or anything from one shop. The same product may count more than once (e.g. two of the same fruit).
        A <strong>food list</strong>'s budget leaves room for what students choose: it is at least {{ \App\Services\ShoppingList::ROOM * 100 }}% of the
        way from the cheapest basket to the dearest one (rounded up to Ƶ 5) - a lower budget is raised when you save. Assign activities to
        classes in <a href="{{ url('admin/education/spending-activity') }}">Assign to classes</a>.
    </p>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @foreach ($activities->push(null) as $activity)
        @php
            $isNew = $activity === null;
            $list = $isNew ? [['qty' => 1, 'products' => []]] : ($activity->shopping_list ?? []);
            $formId = $isNew ? 'new' : $activity->id;
        @endphp
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    @if ($isNew)
                        <strong>+ New activity</strong>
                    @else
                        <span class="badge {{ $activity->type === 'low' ? 'bg-info text-dark' : 'bg-danger' }}">{{ $activity->type === 'low' ? 'Low' : 'High' }} Spending</span>
                        <strong>{{ $activity->number }}. {{ $activity->title }}</strong>
                        <span class="text-muted small ms-2">{{ str_replace(',', ', ', $activity->shop) }} → {{ $activity->destination }}</span>
                        <span class="badge bg-light text-dark border ms-1">{{ \App\Support\AdminLevel::names($activity->levels) }}</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-3">
                    @unless ($isNew)
                        @php $range = \App\Services\ShoppingList::range(\App\Services\ShoppingList::lines($activity)); @endphp
                        <span class="badge bg-warning text-dark">Budget Ƶ {{ number_format($activity->budget, 2) }}</span>
                        <span class="small text-muted" title="What the list costs bought all at the cheapest and all at the dearest">list costs Ƶ {{ number_format($range['min'], 2) }}–{{ number_format($range['max'], 2) }} · suggested budget Ƶ {{ number_format(\App\Services\ShoppingList::suggestedBudget(\App\Services\ShoppingList::lines($activity)), 0) }}</span>
                        <span class="small">{{ collect(\App\Services\ShoppingList::lines($activity))->map(fn ($l) => \App\Services\ShoppingList::label($l))->implode(' · ') }}</span>
                    @endunless
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#list-{{ $formId }}">{{ $isNew ? 'Create' : 'Edit' }}</button>
                </div>
            </div>
            <div class="collapse" id="list-{{ $formId }}">
                <form method="POST" action="{{ $isNew ? route('admin.spending.lists.store') : route('admin.spending.lists.update', $activity) }}" class="card-body">
                    @csrf
                    @unless ($isNew) @method('PUT') @endunless
                    <div class="row g-3 mb-2">
                        <div class="col-md-2"><label class="form-label">Kind</label>
                            <select name="type" class="form-select">
                                <option value="high" @selected(($activity->type ?? 'low') === 'high')>High Spending</option>
                                <option value="low" @selected(($activity->type ?? 'low') === 'low')>Low Spending</option>
                            </select></div>
                        <div class="col-md-4"><label class="form-label">Title</label><input name="title" class="form-control" required value="{{ $activity->title ?? '' }}"></div>
                        <div class="col-md-2"><label class="form-label">Delivered to</label><input name="destination" class="form-control" required value="{{ $activity->destination ?? '' }}"></div>
                        <div class="col-md-2"><label class="form-label">Budget (Ƶ)</label><input name="budget" type="number" step="0.01" min="1" class="form-control" required value="{{ $activity->budget ?? '' }}"></div>
                        <div class="col-12"><label class="form-label">Mission</label><textarea name="mission" class="form-control" rows="2" required>{{ $activity->mission ?? '' }}</textarea></div>
                        @include('admin.partials.levels-field', ['levels' => $activity->levels ?? null, 'isNew' => $isNew, 'fieldId' => 'list-levels-' . $formId])
                    </div>

                    <label class="form-label fw-semibold mt-2">Shopping list</label>
                    <div class="zv-lines" data-next="{{ count($list) }}">
                        @foreach ($list as $i => $line)
                            @include('admin.spending._list-line', ['i' => $i, 'line' => $line])
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1 zv-add-line">+ Add a line</button>

                    <details class="mt-3">
                        <summary class="small">Translations (title, mission, delivered to) - the 10 original activities already have theirs</summary>
                        <div class="row g-2 mt-1">
                            @foreach (['es' => 'Spanish', 'fr' => 'French', 'de' => 'German'] as $lang => $language)
                                <div class="col-md-4">
                                    <div class="small fw-semibold">{{ $language }}</div>
                                    <input name="translations[{{ $lang }}][title]" class="form-control form-control-sm mb-1" placeholder="Title" value="{{ $activity->translations[$lang]['title'] ?? '' }}">
                                    <textarea name="translations[{{ $lang }}][mission]" class="form-control form-control-sm mb-1" rows="2" placeholder="Mission">{{ $activity->translations[$lang]['mission'] ?? '' }}</textarea>
                                    <input name="translations[{{ $lang }}][destination]" class="form-control form-control-sm" placeholder="Delivered to" value="{{ $activity->translations[$lang]['destination'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </details>

                    <button class="btn btn-primary mt-3">Save</button>
                </form>
            </div>
        </div>
    @endforeach
</div>

{{-- one line's fields, copied by "+ Add a line" --}}
<template id="zvLineTemplate">@include('admin.spending._list-line', ['i' => '__I__', 'line' => ['qty' => 1, 'products' => []]])</template>

<script>
    // the line's value fields follow its kind (food group / products / a shop)
    function zvLineKind(row) {
        const kind = row.querySelector('.zv-kind').value;
        row.querySelectorAll('[data-for]').forEach((el) => { el.hidden = el.dataset.for !== kind; });
    }
    document.querySelectorAll('.zv-line').forEach(zvLineKind);
    document.addEventListener('change', (e) => { if (e.target.classList.contains('zv-kind')) zvLineKind(e.target.closest('.zv-line')); });
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('zv-remove-line')) {
            const lines = e.target.closest('.zv-lines');
            if (lines.querySelectorAll('.zv-line').length > 1) e.target.closest('.zv-line').remove();
        }
        if (e.target.classList.contains('zv-add-line')) {
            const lines = e.target.previousElementSibling;
            const i = Number(lines.dataset.next);
            lines.dataset.next = i + 1;
            lines.insertAdjacentHTML('beforeend', document.getElementById('zvLineTemplate').innerHTML.replaceAll('__I__', i));
            zvLineKind(lines.lastElementChild);
        }
    });
</script>
@endsection
