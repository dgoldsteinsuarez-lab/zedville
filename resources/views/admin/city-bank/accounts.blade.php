@extends('layouts.admin')

@section('title', 'City Bank Account')

@section('content')
    <div class="adminHeading">
        <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">City Bank Account</h1>
    </div>
    <!-- Body Widget -->
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
                                    <form method="GET" action="{{ url('admin/accounts') }}" class="mb-0">
                                    <div class="leftpanel d-flex gap-2">
                                        
                                            <div class="input-group w-300px custom-input">
                                                <span class="input-group-text bg-transparent border-0 theme-text-mute pe-1">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                                <input type="text" class="form-control fs-14" name="search" value="{{ request('search') }}" placeholder="Search Role Name" />
                                            </div>
                                            <button type="submit" class="themeBtn text-white">Search</button>
                                        
                                    </div>
                                    </form>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="themeTable">
                    <div class="table-responsive">
                        <table class="table w-100" id="accountsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Primary Savings Account</th>
                                    <th>Card Number</th>
                                    <th>Current Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $i=0;
                                @endphp
                                @forelse ($accounts as $account)
                                @php
                                $i++;
                                @endphp
                                    <tr>
                                        <td valign="middle"><a class="theme-text-dark">{{$i}}</a></td>
                                        <td valign="middle">{{ $account->student_name }}</td>
                                        <td valign="middle">{{ $account->student_email }}</td>
                                        <td valign="middle">{{ $account->primary_savings_account_number }}</td>
                                        <td valign="middle">{{ $account->card_number }}</td>

                                        <td valign="middle">₹ {{ number_format($account->current_balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No accounts found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!--<div class="mt-3">
                        {{ $accounts->links() }}
                    </div>
                     <div style="padding:15px; display:flex; justify-content:flex-end; gap:10px; align-items:center;">
                        <button class="btn btn-secondary" >Prev</button>
                        <span id="pageInfo">Page 1 of 1</span>
                        <button class="btn btn-secondary" >Next</button>
                    </div> -->
                    <div style="padding:15px; display:flex; justify-content:flex-end; gap:10px; align-items:center;">
    
                        {{-- Prev Button --}}
                        @if ($accounts->onFirstPage())
                            <button class="btn btn-secondary" disabled>Prev</button>
                        @else
                            <a href="{{ $accounts->previousPageUrl() }}" class="btn btn-secondary">Prev</a>
                        @endif

                        {{-- Page Info --}}
                        <span id="pageInfo">
                            Page {{ $accounts->currentPage() }} of {{ $accounts->lastPage() }}
                        </span>
                        {{-- Page Numbers --}}
                        <!-- <span id="pageInfo" style="display:flex; gap:6px;">
                            @for ($i = 1; $i <= $accounts->lastPage(); $i++)
                                @if ($i == $accounts->currentPage())
                                    <span class="btn btn-primary disabled">{{ $i }}</span>
                                @else
                                    <a href="{{ $accounts->url($i) }}" class="btn btn-outline-secondary">{{ $i }}</a>
                                @endif
                            @endfor
                        </span> -->


                        {{-- Next Button --}}
                        @if ($accounts->hasMorePages())
                            <a href="{{ $accounts->nextPageUrl() }}" class="btn btn-secondary">Next</a>
                        @else
                            <button class="btn btn-secondary" disabled>Next</button>
                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>  
@endsection
@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    // $('#accountsTable').DataTable({
    //     pageLength: 20,
    //     lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
    //     ordering: false,
    //     searching: true,
    //     responsive: true,
    //     dom: 'lfrtip' // shows length menu correctly
    //     //lengthChange: false,
    // });
});

</script>
@endpush







