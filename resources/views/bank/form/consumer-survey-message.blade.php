@extends('layouts.profile')

@section('title', __('bank.direct_deposit_authorization'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">{{ __('bank.direct_deposit_authorization') }}</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="w-full bg-white border border-color-[#D2DDDB] rounded p-10 userProfileDtls">
        <p>{!! __('bank.hello_strong_nickname_strong', ['nickname' => e($user->display_name)]) !!}</p>

        <p class="mt-3">
            {{ __('bank.you_have_successfully_completed_all_required') }}
        </p>

        <p class="mt-2">
            {{ __('bank.to_proceed_and_unlock_the_remaining') }}
        </p>

        <p class="mt-4 font-semibold">
            {{ __('bank.next_step_complete_the_survey') }}
        </p>

        <p>
            {{ __('bank.the_survey_only_takes_a_few') }}
        </p>

        <a href="{{ route('consumer-profile-survey') }}" class="themeBtn mt-6 inline-block">
            {{ __('bank.complete_survey') }}
        </a>
    </div>
</div>
@endsection
