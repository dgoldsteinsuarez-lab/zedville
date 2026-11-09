@extends('layouts.profile')

@section('title', __('bank.electricity_bill_auto_pay_authorization'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">{{ __('bank.electricity_bill_auto_pay_authorization') }}</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10 userProfileDtls">
        <h3 class="text-lg font-bold mb-4">{{ __('bank.customer_information') }}</h3>
        <form action="{{ route('bank.store_auto_debit') }}" method="post">
            @csrf
            <input type="hidden" name="type" value="Electricity">
            <div class="depostFormSec">
                <div class="flex flex-wrap lg:flex-nowrap gap-4">
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">{{ __('bank.full_name') }}</label>
                            <input class="form-control" placeholder="{{ __('bank.full_name') }}" type="text" name="fullname" value="{{ $user->display_name }}" />
                        </div>
                    </div>
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">{{ __('bank.email_address') }}</label>
                            <input class="form-control" placeholder="{{ __('bank.email_address') }}" type="email" name="email" value="{{ $user->email }}" />
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap lg:flex-nowrap gap-4">
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">{{ __('bank.electricity_account_no_2') }}</label>
                            <input class="form-control" placeholder="{{ __('bank.electricity_account_no_2') }}" type="text" name="accountno" />
                        </div>
                    </div>
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">{{ __('bank.service_address') }}</label>
                            <input class="form-control" placeholder="{{ __('bank.service_address') }}" type="text" name="serviceaddress" />
                        </div>
                    </div>
                </div>
                <h3 class="text-lg font-bold mb-4 mt-8">{{ __('bank.bank_details_for_auto_debit') }}</h3>
                <div class="flex flex-wrap lg:flex-nowrap gap-4">
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">{{ __('bank.bank_name_2') }}</label>
                            <input class="form-control" placeholder="{{ __('bank.bank_name_2') }}" type="text" name="bankname" value="{{ $bankAccount->bank_name  }}" />
                        </div>
                    </div>
                    <div class="w-full lg:w-[50%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">{{ __('bank.account_number') }}</label>
                            <input class="form-control" placeholder="{{ __('bank.account_number') }}" type="number" name="bankaccountnumber" value="{{ $bankAccount->primary_savings_account_number  }}" />
                        </div>
                    </div>
                </div>
                <h3 class="text-lg font-bold mb-4 mt-8">{{ __('bank.payment_authorization') }}</h3>
                <div class="flex gap-x-4 items-start form-group mb-2">
                    <input id="depositall" type="checkbox" class="rounded" name="billschedule" value="1">
                    <label for="depositall" class="text-xs leading-4 text-black">
                        {{ __('bank.pay_full_bill_amount_automatically_variable') }}
                    </label>
                </div>
                <div class="flex gap-x-4 items-start form-group mb-2">
                    <input id="fixedpayment" type="checkbox" class="rounded" name="fixedpayment" value="1">
                    <label for="fixedpayment" class="text-xs leading-4 text-black">
                        {{ __('bank.fixed_monthly_payment') }}
                    </label>
                </div>
                <div class="flex flex-wrap lg:flex-nowrap gap-4">
                    <div class="w-full lg:w-[40%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">{{ __('bank.payment') }}</label>
                            <input class="form-control" placeholder="Ƶ" type="number" name="amount" />
                            <p class="subhelptxt text-xs text-gray-500 mt-1">{{ __('bank.if_bill_exceeds_this_pay_manually') }}</p>
                        </div>
                    </div>
                </div>
                <div class="my-4 flex flex-wrap lg:flex-nowrap gap-4 justify-between">
                    <div class="w-full lg:w-[40%] xl:w-[35%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">{{ __('bank.start_date') }}</label>
                            <input class="form-control" placeholder="{{ __('bank.start_date') }}" type="date" name="startDate" />
                        </div>
                    </div>
                    <div class="w-full lg:w-[40%] xl:w-[35%]">
                        <div class="mb-4 form-group">
                            <label class="block mb-2 text-md font-medium text-black">{{ __('bank.end_date_if_needed') }}</label>
                            <input class="form-control" placeholder="{{ __('bank.end_date') }}" type="date" name="endDate" />
                        </div>
                    </div>
                </div>
                <div class="mt-6 border-t border-[#D2DDDB] pt-6">
                    <h3 class="text-md font-bold mb-2">{{ __('bank.terms_consent') }}</h3>
                    <div class="flex gap-x-4 items-start form-group mb-2">
                        <input id="tandagree" type="checkbox" class="rounded" name="tandagree" value="1">
                        <label for="tandagree" class="text-xs leading-4 text-black">
                            {{ __('bank.i_agree_to_the_terms_below') }}
                        </label>
                    </div>
                    <p class="mb-4">{!! __('bank.i_authorize_company', ['company' => '<a href="#" target="_blank" class="text-themegreen font-semibold">ABC Electric Utility Co.</a>']) !!}</p>
                    
                    <div class="my-4 flex flex-wrap lg:flex-nowrap gap-4 justify-between">
                        <div class="w-full lg:w-[40%] xl:w-[35%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-md font-medium text-black">{{ __('bank.signature') }}</label>
                                <input class="form-control" placeholder="{{ __('bank.signature') }}" type="text" name="signature" />
                            </div>
                        </div>
                        <div class="w-full lg:w-[40%] xl:w-[35%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-md font-medium text-black">{{ __('bank.date') }}</label>
                                <input class="form-control" placeholder="{{ __('bank.phone_no') }}" type="date" name="date" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button class="themeBtn mt-8" type="submit">{{ __('messages.submit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection