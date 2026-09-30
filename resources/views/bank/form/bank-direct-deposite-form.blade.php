@extends('layouts.profile')

@section('title', 'Direct Deposit Authorization form')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">Direct Deposit Authorization</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10 userProfileDtls">
        <h3 class="text-lg font-bold mb-4">Student Information</h3>
        <form action="{{ route('bank.authorize_direct_deposit') }}" method="post">
            @csrf
            <div class="depostFormSec">
                <div class="w-full lg:w-[50%]">
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-md font-medium text-black">Full Name: {{ $user->nickname }}</label>
                                <label class="block mb-2 text-md font-medium text-black">Email Address: {{ $user->email }}</label>
                                <label class="block mb-2 text-md font-medium text-black">Student ID: {{ $user->citizenId }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-[50%]">
                    <h3 class="text-lg font-bold mb-4 mt-8">Bank Account Details</h3>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-md font-medium text-black">Bank Name: {{ $bankAccount->bank_name }}</label>
                                <label class="block mb-2 text-md font-medium text-black">Account Holder’s Name: {{ $bankAccount->student_name }}</label>
                                <label class="block mb-2 text-md font-medium text-black">Account Number: {{ $bankAccount->primary_savings_account_number }}</label>
                                <label class="block mb-2 text-md font-medium text-black">Confirm Account Number: {{ $bankAccount->primary_savings_account_number }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-x-4 items-start form-group mb-4">
                    <p><strong>Authorization</strong></p>
                </div>
                <div class="flex gap-x-4 items-start form-group mb-4">
                    <p>Deposit Preference:</p>
                </div>
                <div class="flex gap-x-4 items-start form-group mb-4">
                    <input id="depositall" type="checkbox" class="rounded" name="bankdeposite" checked value="1">
                    <label for="depositall" class="text-xs leading-4 text-black">
                        Deposit 100% of my net salary into the account listed above.
                    </label>
                </div>
                <div class="my-6">
                    <p>By clicking the button below, I authorize the following:</p>
                    
                </div>
                <div class="my-6">
                    <p class="mb-4">"I hereby authorize Zedville City Hall to initiate electronic deposits of my salary into the designated bank account. I understand that I may cancel this authorization at any time in writing through the Payroll Department. I confirm that the bank information provided is accurate and that Zedville City Hall is not liable for delays or errors resulting from incorrect information."</p>
                </div>
                 <x-timezone-field />
                <div class="text-center">
                    <button class="themeBtn mt-8" type="submit">Authorize Direct Deposit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection