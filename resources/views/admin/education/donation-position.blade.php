@extends('layouts.admin')

@section('title', 'Monthly Donation')

@section('content')
    <div class="adminHeading">
        <h1 class="d-flex align-items-center gap-2 theme-text-dark fw-500 fs-16 mab-20">Monthly Donation @include('admin.partials.level-badge')</h1>
    </div>
    {{-- Daniel, 2026-11-08: every level, hybrid classes too, learns to give in the bank - a charity saved
         as a beneficiary and a standing order of 4-6% of the salary that can't be cancelled, only moved
         to another charity (App\Services\DonationService). --}}
    <p class="text-muted mab-15">
        Students save a charity as a beneficiary in the bank (Pay &amp; transfer &gt; Donate to a charity) and set up a
        standing order of 4–6% of the salary, every month. It can't be cancelled, only moved to another charity, and each
        payment brings a thank-you letter. The activity is done once the standing order exists; a required one not done in
        time brings the usual City Hall fine. The standing order runs to the end of the school year: assign Monthly Donation
        again at the start of the next one, and students set it up again (last year's charity and amount are filled in).
    </p>
    <div class="row">
        <div class="themeCard card card-custom card-stretch gutter-b mab-15">
            <div class="card-body pall-30">
                <form action="{{ route('admin.education.donation-position') }}" method="post">
                    @csrf
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-25">
                                <label class="form-label" for="cid">Class</label>
                                <select class="form-select" name="cid" id="cid" required>
                                    <option value="">Select Class</option>
                                    @include('admin.partials.assignment-scope-options')
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-25">
                                <label class="form-label" for="position">Position</label>
                                <select class="form-select" name="position" id="position" required>
                                    <option value="">Select Position</option>
                                    <option value="required">Required</option>
                                    <option value="optional">Optional</option>
                                </select>
                            </div>
                        </div>
                        {{-- Date range - shown on the Calendar for admin, tutor and student alike --}}
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-25">
                                <label class="form-label" for="start_date">Start Day</label>
                                <input type="date" class="form-control" name="start_date" id="start_date">
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-25">
                                <label class="form-label" for="end_date">End Day</label>
                                <input type="date" class="form-control" name="end_date" id="end_date">
                            </div>
                        </div>
                        <div class="col-12 col-lg-6 text-end">
                            <input type="submit" class="themeBtn text-white" value="Save">
                        </div>
                    </div>
                </form>
            </div>
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
                            @forelse($list as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->school_name ?? 'Default' }}</td>
                                    <td>{{ \App\Support\ClassLabel::make($item->class_grade_name, $item->class_name) }}</td>
                                    <td>{{ $item->required ? 'Required' : 'Optional' }}</td>
                                    <td>{{ $item->start_date ?? '—' }}</td>
                                    <td>{{ $item->end_date ?? '—' }}</td>
                                    <td>
                                        <a href="javascript:void(0)" class="btn btn-sm btn-primary editBtn"
                                            data-cid="{{ $item->cid }}"
                                            data-position="{{ $item->required ? 'required' : 'optional' }}"
                                            data-start-date="{{ $item->start_date }}"
                                            data-end-date="{{ $item->end_date }}">Edit</a>
                                        <a href="javascript:void(0)" class="btn btn-sm btn-danger deleteBtn" data-id="{{ $item->id }}">Delete</a>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Edit: the form again for that class (saving updates its assignment)
        document.querySelectorAll('.editBtn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('cid').value = this.dataset.cid;
                document.getElementById('position').value = this.dataset.position;
                document.getElementById('start_date').value = this.dataset.startDate || '';
                document.getElementById('end_date').value = this.dataset.endDate || '';
                document.querySelector('.themeBtn').value = 'Update';
                window.scrollTo({ top: document.querySelector('form').offsetTop - 80, behavior: 'smooth' });
            });
        });
        // Delete: the assignment and its calendar event (MbaPositionController::mba_position_delete)
        document.querySelectorAll('.deleteBtn').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                const id = this.dataset.id;
                const randomCode = Math.floor(100000 + Math.random() * 900000);
                const { value: code } = await Swal.fire({
                    title: 'Delete this Monthly Donation assignment?',
                    html: `<p>Type this code to confirm deletion</p><h2 style="color:red">${randomCode}</h2>`,
                    input: 'text',
                    inputPlaceholder: 'Enter code',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel'
                });
                if (code == randomCode) {
                    window.location.href = "{{ url('admin/education/mba-position/delete') }}/" + id;
                } else if (code !== undefined) {
                    Swal.fire('Wrong Code!', 'Confirmation code does not match.', 'error');
                }
            });
        });
    </script>
@endsection
