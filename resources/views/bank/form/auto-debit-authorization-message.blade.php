@extends('layouts.profile')

@section('title', __('bank.direct_deposit_authorization_form'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">{{ __('bank.direct_deposit_authorization') }}</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10 userProfileDtls">
        <p>{{ __('bank.set_up_automatic_payments_for_your') }}</p>
        <p>{{ __('bank.you_ll_authorize') }}</p>
        <ul>
            <li>{{ __('bank.automatic_payment_of_the_full_bill') }}</li>
        </ul>
        <br>
        <a href="{{ route('bank.auto_debit_authorization_form') }}" class="themeBtn mt-6 inline-block">{{ __('bank.set_up_auto_debit') }}</a>
    </div>
</div>
@endsection