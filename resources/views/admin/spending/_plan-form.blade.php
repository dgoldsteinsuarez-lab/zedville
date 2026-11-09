{{-- A plan of the month, new or edited (admin/spending/plans). --}}
<form method="POST" enctype="multipart/form-data" action="{{ $plan ? route('admin.spending.plans.update', $plan) : route('admin.spending.plans.store') }}">
    @csrf
    @if ($plan) @method('PUT') @endif
    <div class="row g-2">
        <div class="col-md-2"><label class="form-label small">Kind</label>
            <select name="kind" class="form-select form-select-sm">
                <option value="low" @selected(($plan->kind ?? 'low') === 'low')>Low Spending 4</option>
                <option value="high" @selected(($plan->kind ?? 'low') === 'high')>High Spending 4</option>
                <option value="mid" @selected(($plan->kind ?? 'low') === 'mid')>Intermediate (about 15%)</option>
            </select></div>
        <div class="col-md-3"><label class="form-label small">Name (English, says what it is)</label><input name="name" class="form-control form-control-sm" required value="{{ $plan->name ?? '' }}"></div>
        <div class="col-md-2"><label class="form-label small">Cost each time (Ƶ)</label><input name="cost" type="number" step="0.01" min="1" class="form-control form-control-sm" required value="{{ $plan->cost ?? '' }}"></div>
        <div class="col-md-2"><label class="form-label small">How often</label>
            <select name="times_per_month" class="form-select form-select-sm">
                @foreach ($frequencies as $value => $label)
                    <option value="{{ $value }}" @selected(($plan->times_per_month ?? 1) == $value)>{{ $label }}</option>
                @endforeach
            </select></div>
        <div class="col-md-1"><label class="form-label small">Order</label><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ $plan->sort_order ?? 0 }}"></div>
        <div class="col-md-2"><label class="form-label small">Picture</label><input name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control form-control-sm"></div>
        <div class="col-12"><label class="form-label small">What it is (English) - say what it costs and why</label><textarea name="description" class="form-control form-control-sm" rows="2" required>{{ $plan->description ?? '' }}</textarea></div>
        @foreach (['es' => 'Spanish', 'fr' => 'French', 'de' => 'German'] as $lang => $language)
            <div class="col-md-4">
                <label class="form-label small">{{ $language }}</label>
                <input name="translations[{{ $lang }}][name]" class="form-control form-control-sm mb-1" placeholder="Name" value="{{ $plan->translations[$lang]['name'] ?? '' }}">
                <textarea name="translations[{{ $lang }}][description]" class="form-control form-control-sm" rows="3" placeholder="What it is">{{ $plan->translations[$lang]['description'] ?? '' }}</textarea>
            </div>
        @endforeach
    </div>
    <button class="btn btn-sm btn-primary mt-2">Save</button>
</form>
