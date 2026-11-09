@extends('layouts.admin')

@section('title', 'School Domain')

@section('content')
<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">School Domain</h1>
</div>
<div class="row">
    <div class="themeCard card card-custom card-stretch gutter-b mab-15">
        <div class="card-body pall-30">
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
                                <form method="GET" action="{{ url('admin/school-domain') }}" class="mb-0">
                                    <div class="leftpanel d-flex gap-2">

                                        <div class="input-group w-300px custom-input">
                                            <span class="input-group-text bg-transparent border-0 theme-text-mute pe-1">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" class="form-control fs-14" name="search"
                                                value="{{ request('search') }}" placeholder="Search School Domain" />
                                        </div>
                                        <button type="submit" class="themeBtn text-white">Search</button>

                                    </div>
                                </form>
                                <div class="rightpanel d-flex gap-2">
                                    <button class="themeBtnGray theme-text-dark" data-bs-toggle="modal"
                                        data-bs-target="#addmodel">Add School Domain</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="themeTable">
                <div class="table-responsive">
                    @if($SchoolDomain->isEmpty())
                        <p>No Grade / Class available.</p>
                    @else
                    <table class="table w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>School Name</th>
                                <th>School Code</th>
                                <th>Phone Number</th>
                                <th>Country/Region</th>
                                <th>Time Zone</th>
                                <th>School Domain</th>
                                <th class="rightText">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $i=0;
                            @endphp
                            @foreach ($SchoolDomain as $domain)
                            @php
                            $i++;
                            @endphp
                            <tr>
                                <td valign="middle"><a class="theme-text-dark">{{$i}}</a></td>
                                <td valign="middle">{{ $domain->school_name}}</td>
                                <td valign="middle">{{ $domain->school_code}}</td>
                                <td valign="middle">{{ $domain->school_phone}}</td>
                                <td valign="middle">{{ $domain->country_region}}</td>
                                <td valign="middle">{{ $domain->timezone }}</td>
                                <td valign="middle">{{ $domain->school_domain}}</td>
                                <td valign="middle">
                                    <div class="actionBtns d-flex align-items-center justify-content-end">
                                        <a href="#" class="tableActionBtn me-3 edit-role-btn" data-bs-toggle="modal" data-bs-target="#editmodel" data-id="{{ $domain->id }}" data-school_name="{{ $domain->school_name }}" data-school_code="{{ $domain->school_code }}" data-school_phone="{{ $domain->school_phone }}" data-country_region="{{ $domain->country_region }}" data-timezone="{{ $domain->timezone }}" data-school_domain="{{ $domain->school_domain }}">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                                                <path d="M16 4.99994L19 7.99994" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M4.50001 16.4994L18.4999 2.49994L21.5004 5.49994L7.50041 19.4999L3 20.9995L4.50001 16.4994Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M5 16.9999L7 18.9999" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </a>    
                                        
                                        <a href="#" class="deleteBtn" data-bs-toggle="modal" data-bs-target="#confirmModal" data-url="{{ url('admin/school-domain/delete', $domain->id) }}">
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
<!-- Add School DOmain -->
<section class="themeModal">
    <form action="{{ url('admin/school-domain/add') }}" method="post">
         @csrf
        <div class="modal fade" id="addmodel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-460px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="exampleModalLabel">Add School Domain</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pall-30">
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">School Name</label>
                                <input class="form-control" type="text" name="school_name"  placeholder="Please School Name">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">School Code</label>
                                <input class="form-control" type="text" name="school_code" id="school_code" placeholder="Please School Code">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Phone Number</label>
                                <input class="form-control" type="text" name="school_phone"  placeholder="Please Phone Number">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Country/Region </label>
                                <input class="form-control" type="text" name="country_region" placeholder="Please Country/Region ">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Time Zone</label>
                                <select class="form-select" name="timezone" required>
                                    @foreach (\App\Support\SchoolTime::options() as $region => $zones)
                                        <optgroup label="{{ $region }}">
                                            @foreach ($zones as $zone)
                                                <option value="{{ $zone }}" @selected($zone === \App\Support\SchoolTime::DEFAULT)>{{ $zone }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <small class="text-muted">Used for the students' greeting and other school-day times.</small>
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">School Domain </label>
                                <input class="form-control" type="text" name="school_domain" placeholder="Please School Domain ">
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
        <div class="modal fade" id="editmodel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-460px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="exampleModalLabel">Edit School Domain</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pall-30">
                        <input type="hidden" name="school_id" id="school_id" />
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">School Name</label>
                                <input class="form-control" type="text" name="school_name" id="school_name"  placeholder="Please School Name">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">School Code</label>
                                <input class="form-control" type="text" name="school_code" id="school_code1" placeholder="Please School Code">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Phone Number</label>
                                <input class="form-control" type="text" name="school_phone" id="school_phone"  placeholder="Please Phone Number">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Country/Region </label>
                                <input class="form-control" type="text" name="country_region" id="country_region" placeholder="Please Country/Region ">
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">Time Zone</label>
                                <select class="form-select" name="timezone" id="timezone" required>
                                    @foreach (\App\Support\SchoolTime::options() as $region => $zones)
                                        <optgroup label="{{ $region }}">
                                            @foreach ($zones as $zone)
                                                <option value="{{ $zone }}" @selected($zone === \App\Support\SchoolTime::DEFAULT)>{{ $zone }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <small class="text-muted">Used for the students' greeting and other school-day times.</small>
                            </div>
                        </div>
                        <div class="themeForm">
                            <div class="form-group mab-20">
                                <label for="" class="form-label">School Domain </label>
                                <input class="form-control" type="text" name="school_domain" id="school_domain" placeholder="Please School Domain ">
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
    function generateSchoolCode() {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = 'SC_';
    for (let i = 0; i < 6; i++) {
        code += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return code;
}

document.addEventListener('DOMContentLoaded', function () {
    const schoolCodeInput = document.getElementById('school_code');

    // Auto-generate on load ONLY if input is empty
    if (schoolCodeInput.value.trim() === '') {
        schoolCodeInput.value = generateSchoolCode();
    }

    // Generate button functionality
    /*document.getElementById('generate_code_btn').addEventListener('click', function () {
        schoolCodeInput.value = generateSchoolCode();
    });*/
});
$(document).ready(function() {
    // Populate modal when edit button is clicked
    $('.edit-role-btn').on('click', function() {
        
        var schoolId = $(this).data('id');
        var school_name = $(this).data('school_name');
        var school_code = $(this).data('school_code');
        var school_phone = $(this).data('school_phone');
        var country_region = $(this).data('country_region');
        var school_domain = $(this).data('school_domain');
        $('#school_id').val(schoolId);
        $('#school_name').val(school_name);
        $('#school_code1').val(school_code);
        $('#school_phone').val(school_phone);
        $('#country_region').val(country_region);
        $('#timezone').val($(this).data('timezone') || 'Europe/Berlin');
        $('#school_domain').val(school_domain);
    });

     // AJAX form submit
    $('#editRoleForm').on('submit', function(e) {
        e.preventDefault();
        
        var schoolId = $('#school_id').val();
        var school_name = $('#school_name').val();
        var school_code = $('#school_code').val();
        var school_phone = $('#school_phone').val();
        var country_region = $('#country_region').val();
        var school_domain = $('#school_domain').val();
        var formData = {
            _token: $('input[name="_token"]').val(),
            _method: 'PUT',
            schoolId: $('#schooschool_idl_name').val(),
            school_name: $('#school_name').val(),
            school_code: $('#school_code1').val(),
            school_phone: $('#school_phone').val(),
            country_region: $('#country_region').val(),
            school_domain: $('#school_domain').val(),
        };
        var baseUrl = "{{ url('admin/school-domain/edit') }}/";
        $.ajax({
            //url: '/admin/role/edit/' + roleId,
            url: baseUrl + schoolId,
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#editmodel').modal('hide');
                //alert('Role updated successfully!');
                location.reload(); // Or update the row dynamically
            },
            error: function(xhr) {
                alert('Failed to update role.');
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