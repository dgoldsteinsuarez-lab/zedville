@extends('layouts.admin')

@section('title', 'Login Question')

@section('content')
<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Login Question</h1>
</div>
<!-- Body Widget -->
<div class="themeCard card card-custom card-stretch gutter-b mab-15">
    <div class="card-body pall-30">
        <div class="tableHeadAdvance themeForm">
            <div class="dataTableAdvan">
                <div class="tableHeadAdvance mab-15">
                    <div class="row gap-2 justify-content-between">
                        <div class="form-group gap-2 d-flex justify-content-between">
                            <form method="GET" action="{{ url('admin/login-question') }}" class="mb-0">
                            <div class="leftpanel d-flex gap-2">
                                 
                                <div class="input-group w-300px custom-input">
                                    <span class="input-group-text bg-transparent border-0 theme-text-mute pe-1">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control fs-14" placeholder="Enter Month Or Question Or School Name" name="search" value="{{ request('search') }}" />
                                </div>
                                <button type="submit" class="themeBtn text-white">Search</button>
                               
                                <!-- <div class="daterangePickerField rangePicker haveicon text-nowrap" style="display: flex;">12 May 2025 - 24 May 2025</div> -->
                            </div>
                              </form>
                            <div class="rightpanel d-flex gap-2">
                                <!-- <button class="secondaryBtn" data-bs-toggle="modal" data-bs-target="#invitestudent">Invite Student</button> -->
                                <button  id="addBtn" class="themeBtnGray theme-text-dark" data-bs-toggle="modal" data-bs-target="#addstudent">Add Question</button>
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
                            <th>Level</th>
                            <th>Month</th>
                            <th>Question</th>
                            <!-- <th>School Name</th> -->
                            <th class="rightText">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($questions as $q)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ optional($q->level)->name ?? '—' }}</td>
                            <td>{{ optional($q->month)->name ?? '—' }}</td>
                            <td>{{ $q->question }}</td>
                            <!-- <td>{{ optional($q->school)->school_name ?? '-' }}</td> -->
                            <td valign="middle">
                                <div class="actionBtns d-flex align-items-center justify-content-end">
                                    @php
                                        $options = $q->options ?? collect();

                                        $correctIndex = $options->search(function($item){
                                            return $item->is_correct == 1;
                                        });
                                    @endphp
                                    <a href="#" class="tableActionBtn me-3 editBtn"  data-id="{{ $q->id }}" data-sid="{{ $q->sid }}" data-name="{{ $q->month_name }}" data-month="{{ $q->month_id }}" data-level="{{ $q->grade_level_id }}" data-question="{{ $q->question }}" data-type="{{ $q->type }}"  data-options='@json($options->pluck("option_text")->values())' data-option-ids='@json($options->pluck("id")->values())' data-option-index="{{ $correctIndex }}" data-bs-toggle="modal"  data-bs-target="#addstudent">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19 2H5C4.44772 2 4 2.44772 4 3V21C4 21.5523 4.44772 22 5 22H19C19.5523 22 20 21.5523 20 21V3C20 2.44772 19.5523 2 19 2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path>
                                            <path d="M10.5 7H16.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M10.5 12H16.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M10.5 17H16.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 8C8.0523 8 8.5 7.5523 8.5 7C8.5 6.4477 8.0523 6 7.5 6C6.9477 6 6.5 6.4477 6.5 7C6.5 7.5523 6.9477 8 7.5 8Z" fill="currentColor"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 13C8.0523 13 8.5 12.5523 8.5 12C8.5 11.4477 8.0523 11 7.5 11C6.9477 11 6.5 11.4477 6.5 12C6.5 12.5523 6.9477 13 7.5 13Z" fill="currentColor"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 18C8.0523 18 8.5 17.5523 8.5 17C8.5 16.4477 8.0523 16 7.5 16C6.9477 16 6.5 16.4477 6.5 17C6.5 17.5523 6.9477 18 7.5 18Z" fill="currentColor"></path>
                                        </svg>
                                    </a>
                                    <a href="#" class="deleteBtn" data-id="{{ $q->id }}">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete">
                                            <path d="M4 5.5H20" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M9 2.5H15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M6 8.5H18V20C18 20.8285 17.3285 21.5 16.5 21.5H7.5C6.67155 21.5 6 20.8285 6 20V8.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"></path>
                                        </svg>
                                    </a>
                                    <form id="delete-form-{{ $q->id }}" 
                                        action="{{ url('admin/login-question/'.$q->id) }}" 
                                        method="POST" 
                                        style="display:none;">
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

<section class="themeModal rightSide rightsideSticky">

    <form id="questionForm" action="{{ url('admin/login-question/loginQuestionStore') }}" method="post">
        @csrf
        <div class="modal fade" id="addstudent" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-750px w-100 m-0 h-100">
                <div class="modal-content h-100">
                    <div class="modal-header">
                        <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="modalTitle">Add Question</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="themeForm">
                            <div class="row">
                                <input type="hidden" name="id" id="question_id">
                                <div class="col-xl-12 col-lg-12 col-md-12 mt-4">
                                    <div class="form">
                                        <div class="row">
                                            <div class="col-lg-12" style="display: none;">
                                                <div class="form-group mab-20">
                                                    <label for="fullName" class="form-label">Schools</label>
                                                    <select id="school" name="school" class="form-control">
                                                        <option value="">Select School</option>
                                                        @foreach ($schools as $school)
                                                            <option value="{{ $school->id }}" {{ session('selected_school') == $school->id ? 'selected' : '' }}>{{ $school->school_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-12" style="display: none;">
                                                <div class="form-group mab-20">
                                                    <label for="fullName" class="form-label">Month Name</label>
                                                    <select id="month_name" name="month_name" class="form-control">
                                                        <option value="">Select Month Name</option>
                                                        @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $num => $name)
                                                            <option value="{{ $num }}">{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group mab-20">
                                                    <label for="grade_level_id" class="form-label">Level</label>
                                                    <select id="grade_level_id" name="grade_level_id" class="form-control" required>
                                                        <option value="">Select Level</option>
                                                        @foreach($levels as $level)
                                                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group mab-20">
                                                    <label for="fullName" class="form-label">Month</label>
                                                    <select id="month_id" name="month_id" class="form-control">
                                                        <option>Select Month</option>
                                                        @foreach($months as $m)
                                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group mab-20">
                                                    <label for="question" class="form-label">Question</label>
                                                    <input type="text" class="form-control mb-3" id="question" name="question" placeholder="Question">
                                                </div>
                                            </div>
                                           <div class="col-lg-12">
                                                <div class="form-group mab-20">
                                                    <label for="type" class="form-label">Type</label>
                                                    <select class="form-select" id="type" name="type">
                                                        <option value="">Select Type</option>
                                                        <option value="mcq">MCQ</option>
                                                        <option value="true_false">True/False</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="form-group mab-20">
                                                    <label for="option" class="form-label">Option</label>
                                                    @for($i=0;$i<4;$i++)
                                                    <input type="hidden" name="option_ids[{{$i}}]" id="option_id{{$i}}">

                                                    <input type="text"
                                                        id="option{{$i}}"
                                                        name="options[{{$i}}]"
                                                        placeholder="Option {{$i+1}}"
                                                        class="form-control mb-3">

                                                    <input type="radio"
                                                        id="correct{{$i}}"
                                                        name="correct"
                                                        value="{{$i}}"
                                                        class="mb-3">

                                                    <label for="correct{{$i}}">Correct</label>
                                                    <br>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-center justify-content-center">
                        <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="themeBtn text-white">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// TYPE TOGGLE FUNCTION
// TYPE TOGGLE FUNCTION
function handleTypeChange(type) {
    if (type === 'true_false') {

        // Set True/False values and make readonly
        document.getElementById('option0').value = 'True';
        document.getElementById('option1').value = 'False';
        document.getElementById('option0').readOnly = true;
        document.getElementById('option1').readOnly = true;

        // Hide option 2 and option 3 completely
        document.getElementById('option2').style.display = 'none';
        document.getElementById('option3').style.display = 'none';
        document.getElementById('correct2').style.display = 'none';
        document.getElementById('correct3').style.display = 'none';
        document.querySelector('label[for="correct2"]').style.display = 'none';
        document.querySelector('label[for="correct3"]').style.display = 'none';

    } else {

        // MCQ — clear True/False values, make all 4 editable and visible
        document.getElementById('option0').value = '';
        document.getElementById('option1').value = '';
        document.getElementById('option0').readOnly = false;
        document.getElementById('option1').readOnly = false;

        for (let i = 0; i < 4; i++) {
            document.getElementById('option' + i).style.display = '';
            document.getElementById('correct' + i).style.display = '';
            document.querySelector('label[for="correct' + i + '"]').style.display = '';
        }
    }
}
 // ADD BUTTON CLICK
const modal = document.getElementById('addstudent');
// Listen for type change
document.getElementById('type').addEventListener('change', function () {
    handleTypeChange(this.value);
});

modal.addEventListener('hidden.bs.modal', function () {

    document.getElementById('modalTitle').innerText = 'Add Question';

    document.getElementById('questionForm').reset();
document.getElementById('question_id').value = '';

for(let i=0; i<4; i++){
    document.getElementById('option_id'+i).value = '';
    document.getElementById('option'+i).value = '';
    document.getElementById('option'+i).readOnly = false;
    document.getElementById('option'+i).style.display = '';
    document.getElementById('correct'+i).checked = false;
    document.getElementById('correct'+i).style.display = '';
    document.querySelector('label[for="correct'+i+'"]').style.display = '';
}

});
// EDIT BUTTON CLICK
document.querySelectorAll('.editBtn').forEach(button => {

    button.addEventListener('click', function () {

        document.getElementById('modalTitle').innerText = 'Edit Question';

        let id = this.dataset.id;
        let sid = this.dataset.sid;
        let name = this.dataset.name;
        let month = this.dataset.month;
        let level = this.dataset.level;
        let question = this.dataset.question;
        let type = this.dataset.type;

        let options = JSON.parse(this.dataset.options || '[]');
        let option_ids = JSON.parse(this.dataset.optionIds || '[]');
        let correctIndex = this.dataset.optionIndex;

        // set main fields
        document.getElementById('question_id').value = id;
        document.getElementById('school').value = sid;
        document.getElementById('month_name').value = name;
        document.getElementById('month_id').value = month;
        document.getElementById('grade_level_id').value = level || '';
        document.getElementById('question').value = question;
        document.getElementById('type').value = type;
        handleTypeChange(type);


        // reset all first
        for(let i=0; i<4; i++){
            document.getElementById('option'+i).value = '';
            document.getElementById('option_id'+i).value = '';
            document.getElementById('correct'+i).checked = false;
        }

        // fill options
        options.forEach((opt, i) => {
            if(document.getElementById('option'+i)){
                document.getElementById('option'+i).value = opt;
            }

            if(option_ids[i]){
                document.getElementById('option_id'+i).value = option_ids[i];
            }
        });

        // set correct radio
        if(correctIndex !== undefined){
            document.getElementById('correct'+correctIndex).checked = true;
        }

    });

});
//Delete   
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.deleteBtn').forEach(button => {

        button.addEventListener('click', function(e) {
            e.preventDefault();

            let id = this.getAttribute('data-id');
            let randomNumber = Math.floor(1000 + Math.random() * 9000);

            Swal.fire({
                title: 'Are you sure?',
                html: `
                    <p>This will delete the question!</p>
                    <p><strong>Please type this number to confirm:</strong></p>
                    <h3>${randomNumber}</h3>
                    <input type="text" id="confirmCode" class="swal2-input"
                        placeholder="Enter the number">
                `,
                text: "This will delete the question!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        });

    });

});
</script>
@endpush