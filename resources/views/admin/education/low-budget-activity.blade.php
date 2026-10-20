@extends('layouts.admin')

@section('title', 'Low Budget Activity')

@section('content')
<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Low Budget Activity</h1>
</div>
<div class="row">
    <div class="themeCard card card-custom card-stretch gutter-b mab-15">
        <div class="card-body pall-30">
            <form action="{{ url('admin/education/lba-position') }}" method="post">
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
                                    <option value="{{ $class->id }}" {{ isset($position) && $position->cid == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="form-group mab-25">
                            <label class="form-label">Position</label>
                            <select class="form-select" aria-label="Default select example" name="position" id="position">
                                <option value="" disabled {{ empty($position) ? 'selected' : '' }}>Select Position</option>
                                <option value="required"
                                    {{ isset($position) && $position->required == 1 ? 'selected' : '' }}>
                                    Required
                                </option>
                                <option value="optional"
                                    {{ isset($position) && $position->optional == 1 ? 'selected' : '' }}>
                                    Optional
                                </option>
                            </select>
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

                                <td>

                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary editBtn"
                                        data-id="{{ $item->id }}" data-cid="{{ $item->cid }}"
                                        data-position="{{ $item->required ? 'required' : 'optional' }}">
                                        Edit
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center">No Records Found</td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('.editBtn').forEach(function (btn) {

        btn.addEventListener('click', function () {

            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('cid').value = this.dataset.cid;
            document.getElementById('position').value = this.dataset.position;

            document.querySelector('.themeBtn').value = 'Update';

            window.scrollTo({
                top: document.querySelector('form').offsetTop - 80,
                behavior: 'smooth'
            });

        });

    });
</script>
@endsection
