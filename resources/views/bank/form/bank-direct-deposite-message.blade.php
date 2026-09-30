@extends('layouts.profile')

@section('title', 'Direct Deposit Authorization form')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">Direct Deposit Authorization</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10 userProfileDtls">
        <p>Hello <strong>{{ $user->nickname }}</strong>,</p>
        <p>Please take <span style="color:#3c41fb">two quick steps</span> to authorize your salary deposits and automatic bill payments.</p>
        <p><strong>Set Up Direct Deposit for Your Salary</strong></p>
        <p>Authorize the city to deposit your monthly salary directly into your account—it's the fastest and most secure way to get paid.</p>
        <p>You'll authorize:</p>
        <ul>
            <li>Deposit 100% of your salary into your Universal Bank account</li>
        </ul>
        <br>
        <a href="{{ route('bank.direct_deposite') }}" class="themeBtn mt-6 inline-block">Set Up Direct Deposit</a>
    </div>
</div>
@endsection