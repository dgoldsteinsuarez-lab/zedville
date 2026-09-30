@extends('layouts.profile')

@section('title', 'Account Statements')

@section('content')

@push('styles')
<style>
    /* ── Statement date-range filter — theme-aligned, device friendly ── */
    .statementFilterForm label {
        color: #222;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .statementFilterForm input[type="date"] {
        width: 100%;
        min-height: 44px;
        padding: 10px 14px;
        font-size: 14px;
        font-family: 'Open Sans', sans-serif;
        color: #222;
        background-color: #fff;
        border: 1px solid #D2DDDB !important;
        border-radius: 8px !important;
        box-shadow: none !important;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
        -webkit-appearance: none;
        appearance: none;
    }

    .statementFilterForm input[type="date"]:hover {
        border-color: #9FD8CB !important;
    }

    .statementFilterForm input[type="date"]:focus {
        border-color: #00A47D !important;
        box-shadow: 0 0 0 3px rgba(0, 164, 125, 0.15) !important;
    }

    /* Make the native calendar icon a comfortable, on-brand tap target */
    .statementFilterForm input[type="date"]::-webkit-calendar-picker-indicator {
        cursor: pointer;
        padding: 4px;
        margin-left: 4px;
        border-radius: 6px;
        opacity: 0.65;
        transition: opacity 0.15s, background-color 0.15s;
    }
    .statementFilterForm input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
        background-color: rgba(0, 164, 125, 0.12);
    }

    /* iOS: stop the field from collapsing / misaligning when empty */
    .statementFilterForm input[type="date"]::-webkit-date-and-time-value {
        text-align: left;
    }

    /* Reset link → outline pill that pairs with the solid green Filter button */
    .statementFilterForm .statement-reset-btn {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 30px;
        border: 1px solid #00A47D;
        background: #fff;
        color: #016950;
        font-weight: 600;
        transition: 0.2s;
    }
    .statementFilterForm .statement-reset-btn:hover {
        background: #EEF9F5;
        color: #016950;
    }

    @media (max-width: 767px) {
        .statementFilterForm .filter-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <div class="leftheading">
        <h1 class="text-xl font-bold whitespace-nowrap ">Bank Statement for {{ $user->nickname }}</h1>
        <p class="text-gray-600 text-sm mt-1">Month: {{ $statement['month'] }} / Year: {{ $statement['year'] }}</p>
    </div>
    <!-- <a href="{{ route('bank.bank_statement_show') }}" class="themeBtn py-2 px-4 inline-block flex gap-2 items-center"><span class="font-bold leading-none">← </span>Back to Statements</a> -->
</div>
<div class="themeTabspills">
    <div class="w-full">
        <!-- Tabs Header -->
         @include('bank.partials.bankmenu')
    </div>
</div>
<div class="grid grid-cols-1 gap-5 mt-6">
    
    <div class="statementSeec">
        <div class="bg-white rounded-lg py-6 lg:py-10 px-6 lg:px-8 border border-[#D2DDDB] my-6">
            <!-- Date Range Filter -->
            <form method="GET" action="{{ route('bank.bank_statement_show') }}" class="statementFilterForm w-full bg-white border border-[#D2DDDB] rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="from_date" class="block text-sm font-medium text-gray-700">From Date</label>
                        <input type="date" name="from_date" id="from_date" 
                            value="{{ request('from_date') }}" 
                            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="to_date" class="block text-sm font-medium text-gray-700">To Date</label>
                        <input type="date" name="to_date" id="to_date" 
                            value="{{ request('to_date') }}" 
                            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="md:col-span-2 flex gap-2 filter-actions">
                        <button type="submit" class="themeBtn w-full py-2 px-4">Filter</button>
                        @if(request()->has('from_date') || request()->has('to_date'))
                            <a href="{{ route('bank.bank_statement_show') }}" class="statement-reset-btn w-full py-2 px-4 text-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
            <h3 class="font-semibold text-lg text-gray-900 mb-4">Statement Summary</h3>
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-600 font-medium">Total Income</p>
                    <p class="text-xl font-bold text-blue-700">+Ƶ{{ number_format($statement['summary']['totalIncome'], 2) }}</p></p>
                </div>
                <div class="text-center p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600 font-medium">Total Needs</p>
                    <p class="text-xl font-bold text-green-700">-Ƶ{{ number_format($statement['summary']['totalNeeds'], 2) }}</p>
                </div>
                <div class="text-center p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600 font-medium">Total Wants:</p>
                    <p class="text-xl font-bold text-red-700">-Ƶ{{ number_format($statement['summary']['totalWants'], 2) }}</p>
                </div>
                <div class="text-center p-4 bg-purple-50 border border-purple-200 rounded-lg">
                    <p class="text-sm text-purple-600 font-medium">Total Savings</p>
                    <p class="text-xl font-bold text-purple-700">+Ƶ{{ number_format($totalsaving, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6 border border-[#D2DDDB] my-6">
            <!-- <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-black mb-4">Transaction Details</h3>
                @php if (!$existingPenalty)  : @endphp
                <a href="{{ route('bank.banks_penalty') }}"><button class="bg-[#00A86B] text-white px-4 py-2 rounded-lg hover:bg-[#00945F] transition">Complete month and test</button></a>
                @php endif; @endphp
            </div> -->
            
            <div class="themeTable">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Transaction Date</th>
                            <th class="text-left">Transaction Description</th>
                             <th>Type</th>
                            <!-- <th>Category</th> -->
                            <th>Amount</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                     <tbody>
                    
                      @foreach($transactions as $txn1)
                       <tr>
                       <td>{{ \Carbon\Carbon::parse($txn1->transaction_date)->format('d/m/Y') }}</td>
                       <td>{{  $txn1->description }}</td>
                       <td class="text-right">
                                <div class="font-bold {{ strtolower($txn1->type) === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                {{ ucfirst($txn1->type) }}
                            </div>
                        </td>
                         <td><div class="text-right font-bold">{{ (strtolower($txn1->type) === 'credit' ? '+' : '-') }} {{ number_format($txn1->amount,2) }} ZEDS</div></td>
                        <td><div class="text-right font-bold">{{ number_format($txn1->balance ?? 0, 2) }} ZEDS</div></td>
                        </tr>
                      @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection