@extends('layouts.admin')

@section('title', 'Monthly Budget Activity')

@section('content')
    <div class="adminHeading">
        <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Monthly Budget Activity</h1>
    </div>
    <div class="row">
        <div class="themeCard card card-custom card-stretch gutter-b mab-15">
            <div class="card-body pall-30">
                <form action="{{ url('admin/education/mba-position') }}" method="post">
                    @csrf
                    <!-- Hidden field for edit -->
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row justify-content-center">

                        {{-- Class List --}}
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-25">
                                <label class="form-label">Class</label>

                                <select class="form-select" name="cid" id="cid">
                                    <option value="">Select Class</option>

                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Position --}}
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-25">
                                <label class="form-label">Position</label>

                                <select class="form-select" name="position" id="position">
                                    <option value="">
                                        Select Position
                                    </option>

                                    <option value="required">
                                        Required
                                    </option>

                                    <option value="optional">
                                        Optional
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- Date range - shown on the Calendar for admin, tutor and student alike --}}
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-25">
                                <label class="form-label">Start Day</label>
                                <input type="date" class="form-control" name="start_date" id="start_date">
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-25">
                                <label class="form-label">End Day</label>
                                <input type="date" class="form-control" name="end_date" id="end_date">
                            </div>
                        </div>

                        <div class="col-12 col-lg-6 text-end">
                            <input type="submit" class="themeBtn text-white" value="Save">
                        </div>

                    </div>
                </form>
                <div class="themeCard card card-custom mt-4">
                    <div class="card-body">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>School</th>
                                    <th>Class</th>
                                    <th>Position</th>
                                    <th>Start Day</th>
                                    <th>End Day</th>
                                    <th width="150">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($mbaList as $item)

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $item->school_name ?? 'Default' }}</td>

                                        <td>{{ $item->class_name }}</td>

                                        <td>
                                            {{ $item->required ? 'Required' : 'Optional' }}
                                        </td>

                                        <td>{{ $item->start_date ?? '—' }}</td>
                                        <td>{{ $item->end_date ?? '—' }}</td>

                                        <td>

                                            <a href="javascript:void(0)" class="btn btn-sm btn-primary editBtn"
                                                data-id="{{ $item->id }}" data-cid="{{ $item->cid }}"
                                                data-position="{{ $item->required ? 'required' : 'optional' }}"
                                                data-start-date="{{ $item->start_date }}"
                                                data-end-date="{{ $item->end_date }}">
                                                Edit
                                            </a>

                                            <a href="javascript:void(0)" class="btn btn-sm btn-danger deleteBtn"
                                                data-id="{{ $item->id }}">
                                                Delete
                                            </a>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="7" class="text-center">No Records Found</td>
                                    </tr>

                                @endforelse

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.editBtn').forEach(function (btn) {

            btn.addEventListener('click', function () {

                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('cid').value = this.dataset.cid;
                document.getElementById('position').value = this.dataset.position;
                document.getElementById('start_date').value = this.dataset.startDate || '';
                document.getElementById('end_date').value = this.dataset.endDate || '';

                document.querySelector('.themeBtn').value = 'Update';

                window.scrollTo({
                    top: document.querySelector('form').offsetTop - 80,
                    behavior: 'smooth'
                });

            });

        });
        //Delete code
        document.querySelectorAll('.deleteBtn').forEach(function (btn) {

            btn.addEventListener('click', async function () {

                let id = this.dataset.id;

                let randomCode = Math.floor(100000 + Math.random() * 900000);

                const { value: code } = await Swal.fire({
                    title: 'Delete MBA Position?',
                    html: `
                            <p>Type this code to confirm deletion</p>
                            <h2 style="color:red">${randomCode}</h2>
                        `,
                    input: 'text',
                    inputPlaceholder: 'Enter code',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel'
                });

                if (code == randomCode) {

                    window.location.href = "{{ url('admin/education/mba-position/delete') }}/" + id;

                } else if (code !== undefined) {

                    Swal.fire(
                        'Wrong Code!',
                        'Confirmation code does not match.',
                        'error'
                    );

                }

            });

        });
    </script>
@endsection