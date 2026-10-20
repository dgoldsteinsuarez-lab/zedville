@extends('layouts.admin')

@section('title', 'FinHero Library Activity Manager')

@section('content')

    <div class="adminHeading">
        <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">
            FinHero {{ $type }} Activity Manager
        </h1>
    </div>

    <div class="themeCard card card-custom card-stretch gutter-b mab-15">
        <div class="card-body pall-30">

            <div class="tableHeadAdvance mab-15">
                <div class="row justify-content-between">
                    <div class="d-flex justify-content-end">
                        <button class="themeBtnGray theme-text-dark" data-bs-toggle="modal" data-bs-target="#activityModal">
                            Add Activity
                        </button>
                    </div>
                </div>
            </div>

            <div class="themeTable">
                <div class="table-responsive">
                    <table class="table w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Key</th>
                                <th>Name</th>
                                <th>Class</th>
                                @if($type == 'task')
                                    <th>Salary</th>
                                @endif
                                <th>Position</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>Due Date</th>
                                <th class="rightText">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $a)
                                <tr>
                                    <td>{{ $a->id }}</td>
                                    <td>{{ $a->activity_key }}</td>
                                    <td>{{ $a->activity_name }}</td>
                                    <td>{{ $a->class_name }}</td>
                                    @if($type == 'task')
                                        {{-- Show salary only for task --}}
                                        <td>{{ number_format($a->salary ?? 0, 2) }} zeds</td>
                                    @endif

                                    <td>{{ ucfirst($a->position) }}</td>
                                    <td>
                                        <span class="{{ $a->is_active ? 'text-success' : 'text-danger' }}">
                                            {{ $a->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $a->start_date ?? '—' }}</td>
                                    <td>{{ $a->end_date ?? '—' }}</td>
                                    <td>
                                        <div class="actionBtns d-flex justify-content-end">

                                            <!-- EDIT -->
                                            <a href="#" class="tableActionBtn me-3 editBtn" data-id="{{ $a->id }}"
                                                data-key="{{ $a->activity_key }}" data-name="{{ $a->activity_name }}"
                                                data-position="{{ $a->position }}"
                                                data-salary="{{ $a->salary !== null ? (float)$a->salary : '' }}" data-active="{{ $a->is_active }}"
                                                data-class="{{ $a->cid }}"
                                                data-start-date="{{ $a->start_date }}" data-end-date="{{ $a->end_date }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#activityModal">
                                                ✏️
                                            </a>

                                            <!-- DELETE -->
                                            <a href="#" class="deleteBtn" data-id="{{ $a->id }}">🗑️</a>

                                            <form id="delete-form-{{ $a->id }}" action="/admin/finhero/activities/{{ $a->id }}"
                                                method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL -->

    <form id="activityForm" method="POST">
        @csrf
        <input type="hidden" id="form_method" name="_method" value="POST">

        <div class="modal fade" id="activityModal">
            <div class="modal-dialog modal-dialog-centered mw-750px">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 id="modalTitle">Add Activity</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <!-- MODULE SELECT -->
                        <div class="form-group mb-3">
                            <label>Module</label>
                            <select id="module_select" class="form-control">
                                <option value="">Select Module</option>
                                @if($type == 'library')
                                    <option value="task_1">Introduction to Budget</option>
                                    <option value="task_2">Tracking Income & Expenses</option>
                                    <option value="task_3">Savings - Why Save?</option>
                                    <option value="task_4">Emergency Fund</option>
                                    <option value="task_5">Introduction to Banking</option>
                                    <option value="task_6">Introduction to Banking</option>
                                    <option value="task_7">Fixed vs Variable Expenses</option>
                                @else
                                    <option value="task_1">Classify Your Salary</option>
                                    <option value="task_2">Budget Calculations</option>
                                    <option value="task_3">Budget Rule</option>
                                    <option value="task_4">IT TASK 4 Budgeting</option>
                                @endif
                            </select>

                            <!-- hidden key -->
                            <input type="hidden" name="activity_key" id="activity_key">
                            <input type="hidden" name="type" value="{{ $type }}">
                        </div>
                        <div class="form-group mb-3">
                            <label>Class</label>
                            <select name="classId" id="classId" class="form-control">
                                <option value="">Select Class</option>

                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Name</label>
                            <input type="text" name="activity_name" id="activity_name" class="form-control">
                        </div>
                        {{-- Max Points removed from this form (Daniel, 2026-10-21: "i can
                             still see all the unnecesarry settings here" - redundant now
                             that points come from badges separately, per his earlier
                             statement the same day). Every activity now always uses the
                             global default point value instead - see FinheroAdminController. --}}
                        @if($type == 'task')
                            <div class="form-group  mb-3">
                                <label>Salary</label>
                                <select name="salary" id="salary" class="form-control">
                                    <option value="2000" selected>2,000</option>
                                    <option value="3200">3,200</option>
                                    <option value="4000">4,000</option>
                                    <option value="6000">6,000</option>
                                    <option value="9000">9,000</option>
                                </select>
                                <!-- <input type="number" step="0.01" name="salary" id="salary" class="form-control"> -->
                            </div>
                        @endif

                        <div class="form-group mb-3">
                            <label>Position</label>
                            <select name="position" id="position" class="form-control">
                                <option value="required">Required</option>
                                <option value="optional">Optional</option>
                                <option value="free">Free</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Status</label>
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        {{-- Daniel, 2026-10-21: "i need to be able to add start and due
                             date in all activities, including the tasks, library and so
                             on" - shown for both Task and Library, unlike Max Points/Salary
                             above which stay Task-only. --}}
                        <div class="form-group mb-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label>Due Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="themeBtn text-white">Save</button>
                    </div>

                </div>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

        // AUTO MAP MODULE → activity_key + name
        document.getElementById('module_select').addEventListener('change', function () {

            let key = this.value;
            let text = this.options[this.selectedIndex].text;

            document.getElementById('activity_key').value = key;
            document.getElementById('activity_name').value = text;
        });

        // ADD MODE
        document.querySelector('[data-bs-target="#activityModal"]').addEventListener('click', function () {

            document.getElementById('modalTitle').innerText = 'Add Activity';
            document.getElementById('activityForm').action = '/admin/finhero/activities';
            document.getElementById('form_method').value = 'POST';

            document.getElementById('activityForm').reset();
            @if($type == 'task')
                // Daniel, 2026-10-21: "salary, set one as default if not
                // picked" - this used to blank the field right back out
                // after reset(), silently undoing the 2,000 default set on
                // the <option> itself. Keep the default instead.
                let salaryEl = document.getElementById('salary');
                if (salaryEl) salaryEl.value = '2000';
            @endif
        });

        // EDIT MODE
        document.querySelectorAll('.editBtn').forEach(btn => {

            btn.addEventListener('click', function () {

                document.getElementById('modalTitle').innerText = 'Edit Activity';

                let id = this.dataset.id;

                document.getElementById('activityForm').action = '/admin/finhero/activities/' + id;
                document.getElementById('form_method').value = 'PUT';

                document.getElementById('activity_key').value = this.dataset.key;
                document.getElementById('module_select').value = this.dataset.key;

                document.getElementById('activity_name').value = this.dataset.name;
                @if($type == 'task')
                    let salaryVal = this.dataset.salary;
                    if (salaryVal !== undefined && salaryVal !== null && salaryVal !== '') {
                        salaryVal = parseFloat(salaryVal).toString();
                    }
                    let salaryEl = document.getElementById('salary');
                    if (salaryEl) {
                        salaryEl.value = salaryVal || '2000';
                    }
                @endif
                document.getElementById('position').value = this.dataset.position;
                document.getElementById('is_active').value = this.dataset.active;
                document.getElementById('classId').value = this.dataset.class;
                document.getElementById('start_date').value = this.dataset.startDate || '';
                document.getElementById('end_date').value = this.dataset.endDate || '';


            });

        });

        // DELETE
        document.querySelectorAll('.deleteBtn').forEach(btn => {

            btn.addEventListener('click', function (e) {
                e.preventDefault();

                let id = this.dataset.id;
                let randomNumber = Math.floor(1000 + Math.random() * 9000);

                Swal.fire({
                    title: 'Delete Activity?',
                    html: `
                            <p>Please enter the following number:</p>
                            <h3>${randomNumber}</h3>
                            <input type="text" id="confirmCode"
                                   class="swal2-input"
                                   placeholder="Enter number">
                        `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    preConfirm: () => {
                        let code = document.getElementById('confirmCode').value;

                        if (code != randomNumber) {
                            Swal.showValidationMessage('Incorrect confirmation number');
                            return false;
                        }

                        return true;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });

            });

        });

    </script>

@endpush