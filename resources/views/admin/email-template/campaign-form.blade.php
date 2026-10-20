@extends('layouts.admin')

@section('title', 'New Campaign')

@section('content')
<style>
.multi-select-dropdown { position: relative; margin-bottom: 1rem; }
.multi-select-dropdown #multiSelectButton { background-color: #fff; cursor: pointer; padding: 0.5rem 0.75rem; }
.multi-select-dropdown #multiSelectButton:hover { background-color: #f8f9fa; }
.dropdown-menu-custom { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); z-index: 1000; margin-top: 0.25rem; }
.user-list-scroll { max-height: 250px; overflow-y: auto; overflow-x: hidden; }
.user-item { padding: 0.5rem 0.75rem; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
.user-item:last-child { border-bottom: none; }
.user-item:hover { background-color: #f8f9fa; }
.user-item .form-check { margin-bottom: 0; display: flex; align-items: center; }
.user-item label { cursor: pointer; margin-bottom: 0; margin-left: 0.5rem; flex-grow: 1; }
</style>

<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">New Campaign</h1>
</div>

<p class="text-muted mab-20">Pick a template, choose who receives it, and choose when it sends.</p>

@if (count($templates) === 0)
    <div class="alert alert-warning">
        No templates exist yet. <a href="{{ url('admin/email-templates/create') }}">Create one first</a>.
    </div>
@else
<div class="row">
    <div class="col-12">
        <div class="themeCard card card-custom card-stretch gutter-b">
            <div class="card-body pall-30">
                <form action="{{ url('admin/email-campaigns') }}" method="POST" class="themeForm">
                    @csrf

                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-20">
                                <label class="form-label">Campaign name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-20">
                                <label class="form-label">Template</label>
                                <select class="form-select" name="template_id" required>
                                    <option value="" disabled selected>Select a template</option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="mab-20">
                    <h5 class="mab-15">Who should receive it?</h5>

                    <div class="form-group mab-20">
                        <select class="form-select" name="scope_type" id="scopeType" required>
                            <option value="" disabled selected>Select recipients</option>
                            <option value="all">All students (every school)</option>
                            <option value="school">An entire school</option>
                            <option value="grade">A specific grade within a school</option>
                            <option value="selected">Hand-picked students</option>
                        </select>
                    </div>

                    <div id="scopeSchoolWrap" class="form-group mab-20" style="display:none;">
                        <label class="form-label">School</label>
                        <select class="form-select" name="scope_school_id" id="scopeSchoolId">
                            <option value="" disabled selected>Select a school</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="scopeGradeWrap" class="form-group mab-20" style="display:none;">
                        <label class="form-label">Grade</label>
                        <select class="form-select" name="scope_grade_id">
                            <option value="" disabled selected>Select a grade</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="scopeSelectedWrap" style="display:none;">
                        <label class="mb-2 d-block fw-bold">Select students</label>
                        <div class="multi-select-dropdown">
                            <button type="button" class="form-control text-start d-flex justify-content-between align-items-center" id="multiSelectButton">
                                <span id="selectedCount">Select students...</span>
                            </button>
                            <div class="dropdown-menu-custom" id="dropdownMenu" style="display:none;">
                                <div class="p-2 border-bottom">
                                    <input type="text" class="form-control form-control-sm" placeholder="Search by name or email..." id="userSearch">
                                </div>
                                <div class="p-2 border-bottom d-flex gap-2">
                                    <button type="button" class="themeBtnGray theme-text-dark" id="selectAll" style="padding:.375rem .75rem;font-size:.875rem;">Select All</button>
                                    <button type="button" class="secondaryBtn theme-text-mute" id="deselectAll" style="padding:.375rem .75rem;font-size:.875rem;">Deselect All</button>
                                </div>
                                <div class="user-list-scroll" id="userList">
                                    @foreach ($users as $user)
                                        <div class="user-item" data-user-id="{{ $user->id }}">
                                            <div class="form-check">
                                                <input class="form-check-input user-checkbox" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="user{{ $user->id }}">
                                                <label class="form-check-label" for="user{{ $user->id }}">{{ $user->name }} <small class="text-muted">({{ $user->email }})</small></label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mab-20">
                    <h5 class="mab-15">When should it send?</h5>

                    <div class="form-group mab-20">
                        <select class="form-select" name="trigger_type" id="triggerType" required>
                            <option value="" disabled selected>Select a schedule</option>
                            <option value="immediate">Send now</option>
                            <option value="fixed_date">On a fixed date</option>
                            <option value="relative_to_school_start">N days into the school's start month (every year)</option>
                            <option value="recurring_monthly_day">Every month, on a specific day</option>
                        </select>
                    </div>

                    <div id="triggerFixedDateWrap" class="form-group mab-20" style="display:none;">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="fixed_date">
                    </div>

                    <div id="triggerRelativeWrap" class="form-group mab-20" style="display:none;">
                        <label class="form-label">Days into the school's start month</label>
                        <input type="number" class="form-control" name="relative_days_offset" min="0" max="364" value="0">
                        <small class="text-muted">E.g. 0 = the 1st day of the month the school's academic year starts in. Needs a School or School + Grade recipient scope above, so there's exactly one school's setting to use.</small>
                    </div>

                    <div id="triggerRecurringWrap" class="form-group mab-20" style="display:none;">
                        <label class="form-label">Day of the month</label>
                        <input type="number" class="form-control" name="recurring_day_of_month" min="1" max="31" value="1">
                    </div>

                    <button type="submit" class="themeBtn text-white mt-2">Create Campaign</button>
                    <a href="{{ url('admin/email-campaigns') }}" class="secondaryBtn theme-text-dark mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    const scopeType = document.getElementById('scopeType');
    const scopeSchoolWrap = document.getElementById('scopeSchoolWrap');
    const scopeGradeWrap = document.getElementById('scopeGradeWrap');
    const scopeSelectedWrap = document.getElementById('scopeSelectedWrap');

    scopeType.addEventListener('change', function () {
        scopeSchoolWrap.style.display = (this.value === 'school' || this.value === 'grade') ? 'block' : 'none';
        scopeGradeWrap.style.display = this.value === 'grade' ? 'block' : 'none';
        scopeSelectedWrap.style.display = this.value === 'selected' ? 'block' : 'none';
    });

    const triggerType = document.getElementById('triggerType');
    const triggerFixedDateWrap = document.getElementById('triggerFixedDateWrap');
    const triggerRelativeWrap = document.getElementById('triggerRelativeWrap');
    const triggerRecurringWrap = document.getElementById('triggerRecurringWrap');

    triggerType.addEventListener('change', function () {
        triggerFixedDateWrap.style.display = this.value === 'fixed_date' ? 'block' : 'none';
        triggerRelativeWrap.style.display = this.value === 'relative_to_school_start' ? 'block' : 'none';
        triggerRecurringWrap.style.display = this.value === 'recurring_monthly_day' ? 'block' : 'none';
    });

    // Student multi-select (same pattern as the old communication.blade.php)
    const multiSelectButton = document.getElementById('multiSelectButton');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const selectedCountSpan = document.getElementById('selectedCount');
    const userSearch = document.getElementById('userSearch');
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');

    if (multiSelectButton) {
        multiSelectButton.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.multi-select-dropdown')) {
                dropdownMenu.style.display = 'none';
            }
        });

        dropdownMenu.addEventListener('click', function (e) { e.stopPropagation(); });

        function updateSelectedCount() {
            const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
            selectedCountSpan.textContent = checkedCount === 0 ? 'Select students...' : checkedCount + ' student(s) selected';
        }

        userCheckboxes.forEach(cb => cb.addEventListener('change', updateSelectedCount));

        selectAllBtn.addEventListener('click', function () {
            document.querySelectorAll('.user-item:not([style*="display: none"]) .user-checkbox').forEach(cb => cb.checked = true);
            updateSelectedCount();
        });

        deselectAllBtn.addEventListener('click', function () {
            document.querySelectorAll('.user-item:not([style*="display: none"]) .user-checkbox').forEach(cb => cb.checked = false);
            updateSelectedCount();
        });

        userSearch.addEventListener('keyup', function () {
            const search = this.value.toLowerCase();
            document.querySelectorAll('.user-item').forEach(function (item) {
                item.style.display = item.textContent.toLowerCase().includes(search) ? 'block' : 'none';
            });
        });
    }
</script>
@endpush
