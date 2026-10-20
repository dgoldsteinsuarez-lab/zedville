@extends('layouts.admin')

@section('title', 'Student')

@section('content')
    <div class="adminHeading">
        <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Student</h1>
    </div>
    <!-- Body Widget -->
    <div class="themeCard card card-custom card-stretch gutter-b mab-15">
        <div class="card-body pall-30">
            <div class="tableHeadAdvance themeForm">
                <div class="dataTableAdvan">
                    <div class="tableHeadAdvance mab-15">
                        <div class="row gap-2 justify-content-between">
                            <div class="form-group gap-2 d-flex justify-content-between">
                                <form method="GET" action="{{ url('admin/student') }}" class="mb-0">
                                    <div class="leftpanel d-flex gap-2">

                                        <div class="input-group w-300px custom-input">
                                            <span class="input-group-text bg-transparent border-0 theme-text-mute pe-1">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" class="form-control fs-14"
                                                placeholder="Please enter Citizen ID, Name, Email" name="search"
                                                value="{{ request('search') }}" />
                                        </div>
                                        <select name="class_id" class="form-select fs-14 w-auto" onchange="this.form.submit()">
                                            <option value="">All Classes</option>
                                            @foreach ($grades as $class)
                                                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="themeBtn text-white">Search</button>

                                        <!-- <div class="daterangePickerField rangePicker haveicon text-nowrap" style="display: flex;">12 May 2025 - 24 May 2025</div> -->
                                    </div>
                                </form>
                                <div class="rightpanel d-flex gap-2">
                                    <button class="secondaryBtn" data-bs-toggle="modal"
                                        data-bs-target="#invitestudent">Invite Student</button>
                                    <button class="themeBtnGray theme-text-dark" data-bs-toggle="modal"
                                        data-bs-target="#addstudent">Add Student</button>
                                </div>
                            </div>
                        </div>
                        <div class="row gap-2 justify-content-between filterSection mat-15 d-none">
                            <div class="col-auto">
                                <div class="topField">
                                    <div class="filterSection">
                                        <div class="btn-group">
                                            <button class="addFilterBtn" type="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <span class="icon"></span>
                                                <span class="txt">Add Filters</span>
                                            </button>
                                            <ul class="dropdown-menu filterDropdown themeForm">
                                                <li>
                                                    <a href="javascript:void(0);" id="date-wrp1">
                                                        <div class="notInput customChekbox">
                                                            <label class="form-check-label lh-14">Order time</label>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0);" id="date-wrp2">
                                                        <div class="notInput customChekbox">
                                                            <label class="form-check-label lh-14">Shop</label>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="clearBtn" style="display: block;"><a href="javascript:void(0);">Clear</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="themeTable">
                <div class="table-responsive">
                    <table class="table w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Age</th>
                                <th>Grade level</th>
                                <th>Class</th>
                                <th>Tutor(s)</th>
                                <th>Mascot</th>
                                <th class="rightText">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                <tr>
                                    <td>{{ $student->citizenId }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->email }}</td>
                                    <td>{{ $student->age }}</td>
                                    <td>{{ optional(optional($student->gradeRelation)->level)->name ?? '—' }}</td>
                                    <td>{{ optional($student->gradeRelation)->name ?? ' ' }}</td>
                                    <td>{{ $student->gradeRelation ? ($student->gradeRelation->tutors->pluck('nickname')->filter()->implode(', ') ?: '—') : '—' }}</td>
                                    <td>{{ optional($student->mascotRelation)->name ?? ' ' }}</td>
                                    <td valign="middle">
                                        <div class="actionBtns d-flex align-items-center justify-content-end">
                                            <a href="{{ url('admin/student/details/' . $student->id) }}"
                                                class="tableActionBtn me-3" data-bs-toggle="tooltip" data-bs-placement="top"
                                                aria-label="Details" data-bs-original-title="Detail">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M19 2H5C4.44772 2 4 2.44772 4 3V21C4 21.5523 4.44772 22 5 22H19C19.5523 22 20 21.5523 20 21V3C20 2.44772 19.5523 2 19 2Z"
                                                        stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path>
                                                    <path d="M10.5 7H16.5" stroke="currentColor" stroke-width="1.4"
                                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M10.5 12H16.5" stroke="currentColor" stroke-width="1.4"
                                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M10.5 17H16.5" stroke="currentColor" stroke-width="1.4"
                                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M7.5 8C8.0523 8 8.5 7.5523 8.5 7C8.5 6.4477 8.0523 6 7.5 6C6.9477 6 6.5 6.4477 6.5 7C6.5 7.5523 6.9477 8 7.5 8Z"
                                                        fill="currentColor"></path>
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M7.5 13C8.0523 13 8.5 12.5523 8.5 12C8.5 11.4477 8.0523 11 7.5 11C6.9477 11 6.5 11.4477 6.5 12C6.5 12.5523 6.9477 13 7.5 13Z"
                                                        fill="currentColor"></path>
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M7.5 18C8.0523 18 8.5 17.5523 8.5 17C8.5 16.4477 8.0523 16 7.5 16C6.9477 16 6.5 16.4477 6.5 17C6.5 17.5523 6.9477 18 7.5 18Z"
                                                        fill="currentColor"></path>
                                                </svg>
                                            </a>
                                            @if (auth()->user()->isSuperAdmin())
                                                <form action="{{ url('admin/impersonate/' . $student->id) }}" method="post" class="me-3 d-inline">
                                                    @csrf
                                                    <button type="submit" class="tableActionBtn border-0 bg-transparent p-0" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" aria-label="View as" data-bs-original-title="View as this student">
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M2.03993 12.3453C1.9039 12.1216 1.9039 11.8784 2.03993 11.6547C3.42372 9.38938 7.32967 4 12.0001 4C16.6705 4 20.5765 9.38938 21.9603 11.6547C22.0963 11.8784 22.0963 12.1216 21.9603 12.3453C20.5765 14.6106 16.6705 20 12.0001 20C7.32967 20 3.42372 14.6106 2.03993 12.3453Z" stroke="currentColor" stroke-width="1.4"/>
                                                            <path d="M15 12C15 13.6569 13.6569 15 12 15C10.3431 15 9 13.6569 9 12C9 10.3431 10.3431 9 12 9C13.6569 9 15 10.3431 15 12Z" stroke="currentColor" stroke-width="1.4"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="#" class="deleteBtn">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" data-bs-title="Delete">
                                                    <path d="M4 5.5H20" stroke="currentColor" stroke-width="1.4"
                                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M9 2.5H15" stroke="currentColor" stroke-width="1.4"
                                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path
                                                        d="M6 8.5H18V20C18 20.8285 17.3285 21.5 16.5 21.5H7.5C6.67155 21.5 6 20.8285 6 20V8.5Z"
                                                        stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pagination-wrapper d-none">
                    <div class="container-fluid p-0">
                        <div class="row gap-2 g-0 justify-content-end align-items-center">
                            <div class="col-auto total-wrp">
                                <span>Total 4</span>
                            </div>
                            <div class="col-auto">
                                <ul class="list-pagination">
                                    <li class="pagbtn prev dissable"><a href="#"><i class="fa fa-angle-left"
                                                aria-hidden="true"></i></a></li>
                                    <li><a href="#">1</a></li>
                                    <li><a href="#">2</a></li>
                                    <li><a href="#">3</a></li>
                                    <li><a href="#">4</a></li>
                                    <li><a href="#">5</a></li>
                                    <li><a href="#">...</a></li>
                                    <li><a href="#">10</a></li>
                                    <li class="pagbtn next"><a href="#"><i class="fa fa-angle-right"
                                                aria-hidden="true"></i></a></li>
                                </ul>
                            </div>
                            <div class="col-auto">
                                <div class="pagination-box">
                                    <div class="row g-0 gap-3">
                                        <div class="col-auto">
                                            <label for="inputPassword6" class="col-form-label">Go to</label>
                                        </div>
                                        <div class="col-auto align-self-center">
                                            <input type="text" id="inputnumber" class="form-control" placeholder="1">
                                        </div>
                                        <div class="col-auto page-panel align-self-center pal-0">
                                            <select class="form-select">
                                                <option value="">15 / page</option>
                                                <option value="">30 / page</option>
                                                <option value="">45 / page</option>
                                                <option value="">60 / page</option>
                                                <option value="">75 / page</option>
                                                <option value="">100 / page</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="themeModal rightSide rightsideSticky">

        <form action="{{ url('admin/student/add-student') }}" method="post">
            @csrf
            <div class="modal fade" id="addstudent" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-750px w-100 m-0 h-100">
                    <div class="modal-content h-100">
                        <div class="modal-header">
                            <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="exampleModalLabel">Add Student</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="themeForm">
                                <div class="row">

                                    <div class="col-xl-12 col-lg-12 col-md-12 mt-4">
                                        <div class="form">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group mab-20">
                                                        <label for="fullName" class="form-label">Full Name</label>
                                                        <input type="text" class="form-control" id="fullName"
                                                            placeholder="Full Name" name="name" value="">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group mab-20">
                                                        <label for="email" class="form-label">Email</label>
                                                        <input type="text" class="form-control mb-3" id="email" name="email"
                                                            placeholder="Write your email here">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group mab-20">
                                                        <label for="school" class="form-label">Schools</label>
                                                        <select class="form-select" id="school" name="school">
                                                            <option value="">Select School</option>
                                                            @foreach($schools as $school)
                                                                <option value="{{ $school->id }}">{{ $school->school_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group mab-20">
                                                        <label for="profession" class="form-label">Grade Level</label>
                                                        <select class="form-select" id="country" name="grade">
                                                            <option value="">Select Grade</option>
                                                            @foreach($grades as $grade)
                                                                <option value="{{ $grade->id }}" {{ @$student->gradeRelation && @$student->gradeRelation->id == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4" style="display: none;">
                                                    <div class="form-group mab-20">
                                                        <label for="profession" class="form-label">Mascot</label>
                                                        <select class="form-select" id="country" name="mascot">
                                                            <option value="">Select Mascot</option>
                                                            @foreach($mascots as $mascot)
                                                                <option value="{{ $mascot->id }}" {{ @$student->mascotRelation && @$student->mascotRelation->id == $mascot->id ? 'selected' : '' }}>{{ $mascot->name }} </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>



                                                <div class="col-lg-12">
                                                    <div class="form-group mab-20">
                                                        <label for="email" class="form-label">Address</label>
                                                        <input type="tel" class="form-control mb-3" id=""
                                                            placeholder="Write your address..">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group mab-20">
                                                        <label for="addStudentPassword" class="form-label">Password</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="addStudentPassword"
                                                                name="password" placeholder="********" minlength="8" required>
                                                            <button class="btn btn-outline-secondary" type="button"
                                                                onclick="generateAddStudentPassword()">Generate</button>
                                                        </div>
                                                        <div class="form-text">Shown in plain text so you can copy it for the student - write it down, it won't be shown again.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer text-center justify-content-center">
                            <button type="button" class="secondaryBtn theme-text-mute"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="themeBtn text-white">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <section class="themeModal">
        <div class="modal fade" id="invitestudent" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-460px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="exampleModalLabel">Invite</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pall-30">
                        <form action="#" method="post">
                            <div class="themeForm">
                                <div class="form-group mab-20">
                                    <label for="" class="form-label">Email ID</label>
                                    <input class="form-control" type="email" placeholder="Please enter email">
                                </div>
                                <div class="form-group mab-20">
                                    <label for="" class="form-label">Select Role</label>
                                    <select name="" class="form-select" id="">
                                        <option value="">Select Role</option>
                                        <option value="">Student</option>
                                        <option value="">School Admin</option>
                                        <option value="">School Tutor</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer text-center justify-content-center">
                        <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="themeBtn text-white">Invite</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    function generateAddStudentPassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        let pwd = '';
        for (let i = 0; i < 10; i++) {
            pwd += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('addStudentPassword').value = pwd;
    }
</script>
@endpush