@extends('layouts.profile')

@section('title', __('bank.direct_deposit_authorization_form'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">{{ __('bank.direct_deposit_authorization') }}</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10 userProfileDtls">
        <h3 class="text-lg font-bold mb-4">{{ __('bank.student_information') }}</h3>
        <form action="{{ route('bank.authorize_direct_deposit') }}" method="post">
            @csrf
            <div class="depostFormSec">
                <div class="w-full lg:w-[50%]">
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-md font-medium text-black">{{ __('bank.full_name_nickname', ['nickname' => $user->display_name]) }}</label>
                                <label class="block mb-2 text-md font-medium text-black">{{ __('bank.email_address_email', ['email' => $user->email]) }}</label>
                                <label class="block mb-2 text-md font-medium text-black">{{ __('bank.student_id_citizen_id', ['citizen_id' => $user->citizenId]) }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-[50%]">
                    <h3 class="text-lg font-bold mb-4 mt-8">{{ __('bank.bank_account_details') }}</h3>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-md font-medium text-black">{{ __('bank.bank_name_bank_name', ['bank_name' => $bankAccount->bank_name]) }}</label>
                                <label class="block mb-2 text-md font-medium text-black">{{ __('bank.account_holder_s_name_student_name', ['student_name' => $bankAccount->student_name]) }}</label>
                                <label class="block mb-2 text-md font-medium text-black">{{ __('bank.account_number_primary_savings_account_number', ['primary_savings_account_number' => $bankAccount->primary_savings_account_number]) }}</label>
                                <label class="block mb-2 text-md font-medium text-black">{{ __('bank.confirm_account_number_primary_savings_account', ['primary_savings_account_number' => $bankAccount->primary_savings_account_number]) }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-x-4 items-start form-group mb-4">
                    <p><strong>{{ __('bank.authorization') }}</strong></p>
                </div>
                <div class="flex gap-x-4 items-start form-group mb-4">
                    <p>{{ __('bank.deposit_preference') }}</p>
                </div>
                <div class="flex gap-x-4 items-start form-group mb-4">
                    <input id="depositall" type="checkbox" class="rounded" name="bankdeposite" checked value="1">
                    <label for="depositall" class="text-xs leading-4 text-black">
                        {{ __('bank.deposit_100_of_my_net_salary') }}
                    </label>
                </div>
                <div class="my-6">
                    <p>{{ __('bank.by_clicking_the_button_below_i') }}</p>
                    
                </div>
                <div class="my-6">
                    <p class="mb-4">{{ __('bank.i_hereby_authorize_zedville_city_hall') }}</p>
                </div>
                 <x-timezone-field />
                <div class="text-center">
                    <button class="themeBtn mt-8" type="submit">{{ __('bank.authorize_direct_deposit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection