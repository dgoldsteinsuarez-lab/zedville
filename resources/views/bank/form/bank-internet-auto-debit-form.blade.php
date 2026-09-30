@extends('layouts.profile')

@section('title', 'Internet - Payment and Auto Debit')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">Internet - Payment and Auto Debit</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10 userProfileDtls">
        <h3 class="text-lg font-bold mb-4">Customer Information</h3>
        <form action="{{ route('bank.store_auto_debit') }}" method="post">
            @csrf
            <input type="hidden" name="type" value="Internet">
            <div class="depostFormSec">
                <div class="flex flex-wrap lg:flex-nowrap gap-4">
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">Full Name</label>
                            <input class="form-control" placeholder="Full Name" type="text" name="fullname" value="{{ $user->nickname }}" />
                        </div>
                    </div>
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">Email Address</label>
                            <input class="form-control" placeholder="Email Address" type="email" name="email" value="{{ $user->email }}" />
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap lg:flex-nowrap gap-4">
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">Internet Account No</label>
                            <input class="form-control" placeholder="Internet Account No" type="text" name="accountno" />
                        </div>
                    </div>
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">Service Address</label>
                            <input class="form-control" placeholder="Service Address" type="text" name="serviceaddress" />
                        </div>
                    </div>
                </div>
                <h3 class="text-lg font-bold mb-4 mt-8">Bank Details (for Auto-Debit)</h3>
                <div class="flex flex-wrap lg:flex-nowrap gap-4">
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">Bank Name</label>
                            <input class="form-control" placeholder="Bank Name" type="text" name="bankname" value="{{ $bankAccount->bank_name  }}" />
                        </div>
                    </div>
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">Account Number</label>
                            <input class="form-control" placeholder="Account Number" type="number" name="backaccountnumber" value="{{ $bankAccount->primary_savings_account_number  }}" />
                        </div>
                    </div>
                </div>
                <h3 class="text-lg font-bold mb-4 mt-8">Payment Authorization</h3>
                <div class="flex gap-x-4 items-start form-group mb-2">
                    <input id="depositall" type="checkbox" class="rounded"  name="billschedule" value="1" />
                    <label for="depositall" class="text-xs leading-4 text-black">
                        Pay full bill amount automatically (variable each month)
                    </label>
                </div>
                <div class="flex gap-x-4 items-start form-group mb-2">
                    <input id="fixedpayment" type="checkbox" class="rounded" name="fixedpayment" value="1" />
                    <label for="fixedpayment" class="text-xs leading-4 text-black">
                        Fixed monthly payment
                    </label>
                </div>
                <div class="flex flex-wrap lg:flex-nowrap gap-4">
                    <div class="w-full lg:w-[40%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">Payment</label>
                            <input class="form-control" placeholder="₹" type="number" name="amount" />
                            <p class="subhelptxt text-xs text-gray-500 mt-1">(if bill exceeds this, pay manually)</p>
                        </div>
                    </div>
                </div>
                <div class="my-4 flex flex-wrap lg:flex-nowrap gap-4 justify-between">
                    <div class="w-full lg:w-[40%] xl:w-[35%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">Start Date</label>
                            <input class="form-control" placeholder="Start Date" type="date" name="startDate" />
                        </div>
                    </div>
                    <div class="w-full lg:w-[40%] xl:w-[35%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">End Date (if needed)</label>
                            <input class="form-control" placeholder="End Date" type="date"  name="endDate" />
                        </div>
                    </div>
                </div>
                <div class="mt-6 border-t border-[#D2DDDB] pt-6">
                    <h3 class="text-md font-bold mb-2">Terms & Consent</h3>
                    <div class="flex gap-x-4 items-start form-group mb-2">
                        <input id="tandagree" type="checkbox" class="rounded"  name="tandagree" value="1">
                        <label for="tandagree" class="text-xs leading-4 text-black">
                            I agree to the terms below:
                        </label>
                    </div>
                    <p class="mb-4">"I authorize <a href="#" target="_blank" class="text-themegreen font-semibold">ABC Communications</a> to debit my account for bill payments. I will receive payment alerts and can cancel anytime in writing. I confirm the account details are correct."</p>
                    
                    <div class="my-4 flex flex-wrap lg:flex-nowrap gap-4 justify-between">
                        <div class="w-full lg:w-[40%] xl:w-[35%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-md font-medium text-black">Signature</label>
                                <input class="form-control" placeholder="Signature" type="text" name="signature" />
                            </div>
                        </div>
                        <div class="w-full lg:w-[40%] xl:w-[35%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-md font-medium text-black">Date</label>
                                <input class="form-control" placeholder="Phone No." type="date" name="date" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button class="themeBtn mt-8" type="submit">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection