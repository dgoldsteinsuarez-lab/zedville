@extends('layouts.admin')

@section('title', 'Classes')

@section('content')
    <div class="adminHeading">
        <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Classes</h1>
    </div>
    <p class="text-muted">
        Every Class across every Grade, for quick lookup or a fix without opening a specific Grade. To add a
        Class, it's usually easier from inside its Grade - open <a href="{{ url('admin/grades') }}">Grades</a>
        and use "Manage Classes" there. Assign a Tutor to a Class from the <a href="{{ url('admin/staff') }}">School
        User</a> screen.
    </p>
    <div class="row">
        <div class="themeCard card card-custom card-stretch gutter-b mab-15">
            <div class="card-body pall-30">
                @if(session('error'))
                    <div class="alert alert-danger" style="margin-bottom: 20px;">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" style="margin-bottom: 20px;">{{ $errors->first() }}</div>
                @endif
                @if(session('success'))
                    <div id="success-message" class="alert alert-success" style="margin-bottom: 20px;">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="tableHeadAdvance themeForm">
                    <div class="dataTableAdvan">
                        <div class="tableHeadAdvance mab-15">
                            <div class="row gap-2 justify-content-between">
                                <div class="form-group gap-2 d-flex justify-content-between">
                                    <form method="GET" action="{{ url('admin/grade') }}" class="mb-0">
                                        <div class="leftpanel d-flex gap-2">

                                            <div class="input-group w-300px custom-input">
                                                <span class="input-group-text bg-transparent border-0 theme-text-mute pe-1">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                                <input type="text" class="form-control fs-14" name="search"
                                                    value="{{ request('search') }}" placeholder="Search class" />
                                            </div>
                                            <button type="submit" class="themeBtn text-white">Search</button>

                                        </div>
                                    </form>
                                    <div class="rightpanel d-flex gap-2">
                                        <button class="themeBtnGray theme-text-dark" data-bs-toggle="modal"
                                            data-bs-target="#addmodel">Add Class</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="themeTable">
                    <div class="table-responsive">
                        @if($grade->isEmpty())
                            <p>No Grade / Class available.</p>
                        @else
                        <table class="table w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Grade</th>
                                    <th>Class</th>
                                    <th>Tutor(s)</th>
                                    <th>Students</th>
                                    <th>Class Code</th>
                                    <th class="rightText">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $i=0;
                                @endphp
                                @foreach ($grade as $gradelist)
                                @php
                                $i++;
                                @endphp
                                <tr>
                                    <td valign="middle"><a class="theme-text-dark">{{$i}}</a></td>
                                    <td valign="middle">{{ $gradelist->grade->name ?? '— not assigned —' }}</td>
                                    <td valign="middle">{{ $gradelist->name }}</td>
                                    <td valign="middle">{{ $gradelist->tutors->pluck('nickname')->filter()->implode(', ') ?: '—' }}</td>
                                    <td valign="middle">{{ $gradelist->students_count }}</td>
                                    <td valign="middle">{{ $gradelist->classCode }}</td>
                                    <td valign="middle">
                                        <div class="actionBtns d-flex align-items-center justify-content-end">
                                            <a href="#" class="tableActionBtn me-3 edit-role-btn" data-bs-toggle="modal" data-bs-target="#gradeEditModal" data-id="{{ $gradelist->id }}" data-name="{{ $gradelist->name }}" data-classCode="{{ $gradelist->classCode }}" data-grade-id="{{ $gradelist->grade_id }}">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                                                    <path d="M16 4.99994L19 7.99994" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M4.50001 16.4994L18.4999 2.49994L21.5004 5.49994L7.50041 19.4999L3 20.9995L4.50001 16.4994Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M5 16.9999L7 18.9999" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </a>    
                                            
                                            <a href="#" class="deleteBtn" data-bs-toggle="modal" data-bs-target="#confirmModal" data-url="{{ url('admin/grade/delete', $gradelist->id) }}">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete">
                                                    <path d="M4 5.5H20" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.5H15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M6 8.5H18V20C18 20.8285 17.3285 21.5 16.5 21.5H7.5C6.67155 21.5 6 20.8285 6 20V8.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Grade / Class -->
<section class="themeModal">
    <form action="{{ url('admin/grade/add') }}" method="post">
         @csrf
        <div class="modal fade" id="addmodel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-460px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="exampleModalLabel">Add Class</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pall-30">
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Grade / Class Name</label>
                                <input class="form-control" type="text" name="name" placeholder="Please Grade / Class Name">
                            </div>
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Grade</label>
                                <select class="form-control" name="grade_id" id="add_grade_id">
                                    <option value="">— none —</option>
                                    @foreach ($grades as $g)
                                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-center justify-content-center">
                        <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="themeBtn text-white">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
<!-- Edit Grade / Class -->
 <section class="themeModal">
    <form id="editRoleForm" method="post">
         @csrf
          @method('PUT')
        <div class="modal fade" id="gradeEditModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-460px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="exampleModalLabel">Edit Class</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pall-30">
                        <div class="themeForm">
                            <input type="hidden" id="class_row_id" />
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Grade / Class Name</label>
                                <input class="form-control" type="text" name="name" id="grade_name" placeholder="Please Grade / Class Name">
                            </div>
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Grade</label>
                                <select class="form-control" name="grade_id" id="grade_id_edit">
                                    <option value="">— none —</option>
                                    @foreach ($grades as $g)
                                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Class Code</label>
                                <input class="form-control" type="text" name="classCode" id="classCode" placeholder="Please Enter Class code">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-center justify-content-center">
                        <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="themeBtn text-white">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
 <!-- Delete Grade / Class -->
  <section class="themeModal">
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-350px">
            <form id="modalDeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" id="delete_code" name="delete_code">
                <div class="modal-content">
                    <div class="modal-body text-center pall-60">
                        <h4 class="fs-18 fw-700 mab-20">Are you sure?</h4>
                        <p>You won't be able to revert this!</p>
                        <div class="alert alert-warning">
                            Please enter this number to confirm deletion:
                            <h3 id="randomNumber" class="mt-2"></h3>
                        </div>

                        <div class="mb-3">
                            <input type="text"
                                name="confirm_code"
                                class="form-control"
                                placeholder="Enter the number shown above"
                                required>
                        </div>
                        <div class="text-center mat-40">
                            
                            <button type="submit" class="themeBtn text-white">Yes Delete it!</button>
                            <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    // Deep-link from a Grade's General Information page: pre-select the Grade and open the Add Class modal.
    var addToGrade = new URLSearchParams(window.location.search).get('add_to_grade');
    if (addToGrade) {
        $('#add_grade_id').val(addToGrade);
        $('#addmodel').modal('show');
    }

    // Populate modal when edit button is clicked
    $('.edit-role-btn').on('click', function() {
        
        var classRowId = $(this).data('id');
        var gradeName = $(this).data('name');
        var classCode = $(this).data('classcode');
        $('#class_row_id').val(classRowId);
        $('#grade_name').val(gradeName);
        $('#classCode').val(classCode);
        $('#grade_id_edit').val($(this).data('grade-id') || '');
    });

    // AJAX form submit
    $('#editRoleForm').on('submit', function(e) {
        e.preventDefault();

        var classRowId = $('#class_row_id').val();
        var formData = {
            _token: $('input[name="_token"]').val(),
            _method: 'PUT',
            name: $('#grade_name').val(),
            classCode: $('#classCode').val(),
            grade_id: $('#grade_id_edit').val()
        };
        var baseUrl = "{{ url('admin/grade/edit') }}/";
        $.ajax({
            //url: '/admin/role/edit/' + roleId,
            url: baseUrl + classRowId,
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#gradeEditModal').modal('hide');
                //alert('Role updated successfully!');
                location.reload(); // Or update the row dynamically
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.errors) ? Object.values(xhr.responseJSON.errors)[0][0] : 'Failed to update class.';
                alert(msg);
                console.log(xhr.responseText);
            }
        });
    });
    //Delete security
    $('.deleteBtn').on('click', function() {
    let randomNumber = Math.floor(1000 + Math.random() * 9000);

    $('#randomNumber').text(randomNumber);
    $('#delete_code').val(randomNumber);
});
});
</script>

@endpush