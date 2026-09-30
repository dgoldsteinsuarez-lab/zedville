@extends('layouts.profile')

@section('title', 'Direct Deposit Authorization')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">Direct Deposit Authorization</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10 userProfileDtls">
        <p>Hello <strong>{{ $user->nickname }}</strong>,</p>

        <p class="mt-3">
            You have successfully completed all required setup steps.
        </p>

        <p class="mt-2">
            To proceed and unlock the remaining features, please complete the short survey.
            This will help us finalize your profile and enable additional services.
        </p>

        <p class="mt-4 font-semibold">
            Next Step: Complete the Survey
        </p>

        <p>
            The survey only takes a few minutes and is required to continue with other activities.
        </p>

        <a href="{{ route('consumer-profile-survey') }}" class="themeBtn mt-6 inline-block">
            Complete Survey
        </a>
    </div>
</div>
@endsection
