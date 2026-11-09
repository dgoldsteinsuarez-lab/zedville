@extends('layouts.admin')

@section('title', 'School Month Settings')

@section('content')
<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">School Month Settings</h1>
</div>
<!-- Body Widget -->
<div class="themeCard card card-custom card-stretch gutter-b mab-15">
    <div id="msg" style="text-align:center; margin:20px 0;"></div>
    <div class="card-body pall-30">
        <p class="text-muted small">A Grade can start in a different month from its school: Grades &rsaquo; Manage Classes &rsaquo; Start month.</p>
        <div class="themeTable">
            <div class="table-responsive">
                <table class="table w-100">
                    <thead>
                        <tr>
                             <th>#</th>
                            <th>School Name</th>
                            <th>Start Month</th>
                        </tr>
                    </thead>
                    <tbody>
                         @foreach($schools as $key => $school)
                        <tr>
                             <td>{{ $key + 1 }}</td>
                             <td>{{ $school->school_name }}</td>
                           <td>
                                <select class="form-control month-dropdown" data-id="{{ $school->id }}">
                                    <option value="">Select Month</option>
                                    @for($i=1; $i<=12; $i++)
                                        <option value="{{ $i }}" 
                                            {{ $school->start_month == $i ? 'selected' : '' }}>
                                            {{ date('F', mktime(0,0,0,$i,1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </td>
                            
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
            </div>
           
        </div>
        <div style="text-align:center; margin:20px 0;">
            <button class="btn btn-primary px-4" onclick="saveAll()">Submit</button>
        </div>
    </div>
     
</div>

@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function saveAll() {

    let data = [];

    $('.month-dropdown').each(function() {

        let school_id = $(this).data('id');
        let start_month = $(this).val();

        if(start_month !== "") {
            data.push({
                school_id: school_id,
                start_month: start_month
            });
        }
    });

    $.ajax({
        url: "{{ url('admin/login-question/bulkSaveSMS') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            data: data
        },
        success: function(response) {

            if(response.status) {
                $('#msg').html('<div class="alert alert-success">'+response.message+'</div>');
            }

        },
        error: function() {
            $('#msg').html('<div class="alert alert-danger">Something went wrong</div>');
        }
    });
}
</script>
@endpush