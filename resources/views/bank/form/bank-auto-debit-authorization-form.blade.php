@extends('layouts.profile')

@section('title', 'Consolidated Auto-Debit Authorization Form')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">Consolidated Auto-Debit Authorization Form</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10">
        <h3 class="text-lg font-bold mb-4">Universal Auto-Debit Authorization Form</h3>
        <form action="{{ route('bank.store_auto_debit_authorization_form') }}" method="post">
            @csrf

            <!-- Part 1 -->
            <h4 class="text-lg font-bold mb-3 mt-6">Part 1: Customer & Service Information</h4>
            <!-- <p class="mb-3">☑ Click all the services you want to pay automatically</p> -->
             <p class="mb-3 flex items-center gap-3">
                <input type="checkbox" checked disabled class="scale-150 accent-green-600">
                <span>Click all the services you want to pay automatically</span>
            </p>


            <div class="mb-4 space-y-3">
                <div>
                    <input type="checkbox" id="all_services" class="rounded accent-green-600 cursor-pointer scale-150">
                    <label for="all_services" class="ml-2 font-semibold">Select All Services</label>
                </div>

                <div>
                    <input type="checkbox" id="school_fees" class="rounded accent-green-600 cursor-pointer service-checkbox scale-150" name="services[school]" value="{{ $billers['City School']->account_number ?? '' }}" checked disabled>
                    <label for="school_fees" class="ml-2">School Fees (Required)</label>
                    <div class="mt-1 ml-6">
                        <label>School Account No.: 
                            <span class="font-semibold underline">{{ $billers[3]->account_number ?? '__________' }}</span>
                        </label>
                    </div>
                    <!-- <input type="hidden" name="services_meta[school][name]" value="City School">
                    <input type="hidden" name="services_meta[school][account]" value="{{ $billers[3]->account_number ?? '' }}">
                    <input type="hidden" name="services_meta[school][amount]" value="{{ $billers[3]->amount ?? '' }}"> -->
                    <!-- REAL submitted values -->
                    <input type="hidden"
                        name="services[school]"
                        value="{{ $billers[3]->account_number ?? '' }}">

                    <input type="hidden"
                        name="services_meta[school][name]"
                        value="City School">

                    <input type="hidden"
                        name="services_meta[school][account]"
                        value="{{ $billers[3]->account_number ?? '' }}">

                    <input type="hidden"
                        name="services_meta[school][amount]"
                        value="{{ $billers[3]->amount ?? '' }}">
                </div>

                <div>
                    <input type="checkbox" id="internet_service" class="rounded accent-green-600 cursor-pointer service-checkbox scale-150" name="services[internet]" value="{{ $billers['Internet Service Provider']->account_number ?? '' }}">
                    <label for="internet_service" class="ml-2">Internet Service (ABC Communications)</label>
                    <div class="mt-1 ml-6">
                        <label>Internet Account No.: 
                            <span class="font-semibold underline">{{  $billers[12]->account_number ?? '__________' }}</span>
                        </label>
                    </div>
                    <input type="hidden" name="services_meta[internet][name]" value="Internet">
                    <input type="hidden" name="services_meta[internet][account]" value="{{ $billers[12]->account_number ?? '' }}">
                    <input type="hidden" name="services_meta[internet][amount]" value="{{ $billers[12]->amount ?? '' }}">
                </div>

                <div>
                    <input type="checkbox" id="electricity_service" class="rounded accent-green-600 cursor-pointer service-checkbox scale-150"  name="services[electricity]" value="{{ $billers['Utility - Electricity']->account_number ?? '' }}">
                    <label for="electricity_service" class="ml-2">Electricity Service (ABC Electric Utility Co.)</label>
                    <div class="mt-1 ml-6">
                        <label>Electricity Account No.: 
                            <span class="font-semibold underline">{{ $billers[10]->account_number ?? '__________' }}</span>
                        </label>
                    </div>
                    <input type="hidden" name="services_meta[electricity][name]" value="Utility - Electricity">
                    <input type="hidden" name="services_meta[electricity][account]" value="{{ $billers[10]->account_number ?? '' }}">
                    <input type="hidden" name="services_meta[electricity][amount]" value="{{ $billers[10]->amount ?? '' }}">
                </div>

                <div>
                    <input type="checkbox" id="water_service" class="rounded accent-green-600 cursor-pointer service-checkbox scale-150" name="services[water]" value="{{ $billers['Utility - Water']->account_number ?? '' }}">
                    <label for="water_service" class="ml-2">Water Service (ABC Water Utility Co.)</label>
                    <div class="mt-1 ml-6">
                        <label>Water Account No.: 
                            <span class="font-semibold underline">{{ $billers[11]->account_number ?? '__________' }}</span>
                        </label>
                    </div>
                    <input type="hidden" name="services_meta[water][name]" value="Utility - Water">
                    <input type="hidden" name="services_meta[water][account]" value="{{ $billers[11]->account_number ?? '' }}">
                    <input type="hidden" name="services_meta[water][amount]" value="{{ $billers[11]->amount ?? '' }}">
                </div>
            </div>

            <div class="mt-8">
                <p class="font-bold mb-2">Your Information:</p>
                <p>Full Name: <span class="font-semibold">{{ $user->nickname }}</span></p>
                <p>Email Address: <span class="font-semibold">{{ $user->email }}</span></p>
                <p>Home Address: <span class="font-semibold">{{ $user->student_address ?? 'N/A' }}</span></p>
            </div>

            <!-- Part 2 -->
            <h4 class="text-lg font-bold mb-3 mt-8">Part 2: Bank Details (for Auto-Debit)</h4>
            <p>Bank Name: <span class="font-semibold">{{ $bankAccount->bank_name ?? '__________' }}</span></p>
            <p>Account Number: <span class="font-semibold">{{ $bankAccount->primary_savings_account_number ?? '__________' }}</span></p>
            <p>SWIFT/BIC Code: <span class="font-semibold">{{ $bankAccount->card_swift ?? '__________' }}</span></p>
            <input type="hidden" name="fullname" value="{{ $user->nickname }}">
            <input type="hidden" name="email" value="{{ $user->email }}">
            <input type="hidden" name="serviceaddress" value="{{ $user->student_address }}">
            <input type="hidden" name="bankname" value="{{ $bankAccount->bank_name }}">
            <input type="hidden" name="bankaccountnumber" value="{{ $bankAccount->primary_savings_account_number }}">


            <!-- Part 3 -->
            <h4 class="text-lg font-bold mb-3 mt-8">Part 3: Payment Authorization</h4>
            <div class="flex gap-x-3 items-start mb-3">
                <input type="checkbox" id="full_payment" checked class="rounded accent-green-600 cursor-pointer scale-150">
                <label for="full_payment" class="text-md">Pay the full bill amount each month (We'll deduct the exact amount due)</label>
            </div>

            <!-- Part 4 -->
            <h4 class="text-lg font-bold mb-3 mt-8">Part 4: Terms & Consent</h4>
            <div class="flex gap-x-3 items-start mb-4">
                <input type="checkbox" id="terms" class="rounded accent-green-600 cursor-pointer scale-150" required>
                <label for="terms" class="text-md leading-6">
                    I agree to the terms and authorize the debits as detailed below:
                </label>
            </div>

            <blockquote class="border-l-4 border-green-600 pl-4 italic text-gray-700 mb-6">
                “I hereby authorize ABC Group Companies (including ABC School Co., ABC Communications, 
                ABC Electric Utility Co., and ABC Water Utility Co.) to initiate debit entries to my bank 
                account for the bill payments of the services I have selected above. I understand I will 
                receive payment alerts prior to each debit and that this authorization can be canceled at 
                any time by notifying the respective company in writing. I confirm that the bank account 
                details provided are correct.”
            </blockquote>

            <!-- <p class="text-gray-600 italic mb-6">
                After you click “Confirm & Activate Auto-Pay,” you will be returned to your mailbox and see 
                a notification that says: <strong>“Congratulations, you are all set!”</strong>
            </p> -->

            <x-timezone-field />

            <div class="text-center">
                <button type="submit" class="themeBtn mt-8">Confirm & Activate Auto-Pay</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle all service checkboxes (except the required school fees)
    document.addEventListener('DOMContentLoaded', () => {
        const allServices = document.getElementById('all_services');
        const checkboxes = document.querySelectorAll('.service-checkbox:not(#school_fees)');

        allServices.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = allServices.checked);
        });
    });
</script>
@endsection
