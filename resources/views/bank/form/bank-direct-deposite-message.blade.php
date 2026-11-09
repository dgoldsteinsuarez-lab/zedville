@extends('layouts.profile')

@section('title', __('bank.direct_deposit_authorization_form'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">{{ __('bank.direct_deposit_authorization') }}</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10 userProfileDtls">
        <p>{!! __('bank.hello_strong_nickname_strong', ['nickname' => e($user->display_name)]) !!}</p>
        <p>{!! __('bank.please_take_two_quick_steps') !!}</p>
        <p><strong>{{ __('bank.set_up_direct_deposit_for_your') }}</strong></p>
        <p>{{ __('bank.authorize_the_city_to_deposit_your') }}</p>
        <p>{{ __('bank.you_ll_authorize') }}</p>
        <ul>
            <li>{{ __('bank.deposit_100_of_your_salary_into') }}</li>
        </ul>
        <br>
        <a href="{{ route('bank.direct_deposite') }}" class="themeBtn mt-6 inline-block">{{ __('bank.set_up_direct_deposit') }}</a>
    </div>
</div>
@endsection