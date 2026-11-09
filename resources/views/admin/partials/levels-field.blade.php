{{-- The levels an activity is for (App\Support\AdminLevel, 2026-11-02): one checkbox
     per level; none checked means every level. A new activity ($isNew) starts with the
     level being worked on; an existing one shows its own. Expects $levels (the
     activity's, or null); optional $isNew, $fieldId. --}}
@php
    $fieldId = $fieldId ?? 'levels-' . uniqid();
    $default = $levels ?? (($isNew ?? false) && \App\Support\AdminLevel::id() ? [\App\Support\AdminLevel::id()] : []);
    $checked = array_map('intval', (array) old('levels', $default));
@endphp
<div class="col-12">
    <label class="form-label small mb-1">For which levels</label>
    <div class="d-flex flex-wrap gap-3">
        @foreach (\App\Support\AdminLevel::all() as $level)
            <div class="form-check">
                <input type="checkbox" class="form-check-input" style="appearance: auto; -webkit-appearance: checkbox" name="levels[]" value="{{ $level->id }}" id="{{ $fieldId }}-{{ $level->id }}" @checked(in_array($level->id, $checked, true))>
                <label class="form-check-label small" for="{{ $fieldId }}-{{ $level->id }}">{{ $level->name }}</label>
            </div>
        @endforeach
    </div>
    <div class="form-text">None checked: every level.</div>
</div>
