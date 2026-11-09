@extends('layouts.profile')

@section('title', __('bank.bank_account'))

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">{{ __('messages.nav_bank_account') }}</h1>
</div>
<div class="grid grid-cols-1 gap-5 mt-6"  x-data="{ confirmationModal: false }">
    <!-- Account Opening From -->
     <div class="w-full max-w-[650px]">
        <div class="bg-white rounded-lg border border-[#D2DDDB]">
            <h2 class="text-xl font-bold py-4 border-b border-[#D2DDDB] text-center px-6">{{ __('bank.universal_bank_student_account_application') }}</h2>
            <div class="cardBody p-6 userProfileDtls ">
                <form action="{{ route('bank.store') }}" method="post">
                     @csrf
                    <h3 class="text-lg font-bold mb-6">{{ __('layout.personal_information') }}</h3>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">{{ __('bank.full_name') }}</label>
                                <input class="form-control" name="fullName" placeholder="{{ __('bank.enter_full_name') }}" type="text" value="{{ $user->display_name }}" />
                            </div>
                        </div>
                        <!-- <div class="w-full lg:w-[50%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">Last Name</label>
                                <input class="form-control" placeholder="Enter last name" type="text" value="" />
                            </div>
                        </div> -->
                    </div>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4" >
                        <div class="w-full lg:w-[50%]" style="display: none;">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">{{ __('bank.date_of_birth') }}</label>
                                <input class="form-control" name="dob" placeholder="{{ __('bank.enter_first_name') }}" type="date" />
                            </div>
                        </div>
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">{{ __('bank.student_id') }}</label>
                                <input class="form-control" name="studentId" placeholder="{{ __('bank.student_id') }}" type="text" value="{{ $user->citizenId }}" disabled />
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold my-6">{{ __('bank.contact_information') }}</h3>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">{{ __('bank.email_address') }}</label>
                                <input class="form-control" name="email" placeholder="john@zedville.com" type="email" value="{{ $user->email }}" />
                            </div>
                        </div>
                        <div class="w-full lg:w-[50%]" style="display: none;">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">{{ __('bank.mobile_number') }}</label>
                                <input class="form-control" name="phone" placeholder="+00 0000 000 000" type="text" />
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">{{ __('bank.home_address') }}</label>
                                <textarea name="homeAddress" class="form-control logheight" id="" placeholder="{{ __('bank.home_address_2') }}">{{ $user->address }}</textarea>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold my-6" style="display: none;">{{ __('bank.account_preferance') }}</h3>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4" style="display: none;">
                        <div class="w-full">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">{{ __('bank.prefered_username') }}</label>
                                <input class="form-control" name="AccPreName" placeholder="" type="text" />
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-x-4 items-start form-group mb-4">
                        <input id="rcvaccemail" name="rcvaccemail" type="checkbox" class="rounded" value="1">
                        <label for="rcvaccemail" class="text-xs leading-4 text-black">
                            {{ __('bank.i_agree_receive_account_statements_via') }}
                        </label>
                    </div>
                    <div class="flex gap-x-4 items-start form-group">
                        <input id="tandc" type="checkbox" name="tandc" class="rounded" value="1" @click="confirmationModal = true">
                        <label for="tandc" class="text-xs leading-4 text-black" @click="confirmationModal = true" style="cursor: pointer;">
                            {{ __('bank.i_agree_to_terms_conditions') }}
                        </label>
                    </div>
                    <x-timezone-field />
                    <div class="mt-10 text-center">
                        <button type="submit" class="themeBtn flex items-center justify-center mx-auto gap-2">
                            {{ __('bank.submit_application') }}
                            <span class="icon">
                                <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.08008 10.3H15.0801M15.0801 10.3L9.80008 5.79999M15.0801 10.3L9.80008 14.8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
    <!-- Account Opening From -->
     <div
        x-show="confirmationModal"
        x-transition.opacity
        class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
        @keydown.escape.window="confirmationModal = false" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="confirmationModal = false"></div>
        <!-- Modal Box -->
        <div class="modalDilog max-w-[900px]">
            <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                <!-- <div class="flex justify-between items-center mb-4">
                    <button @click="confirmationModal = false" class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                            <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div> -->
                <div class="bodyContent">
                    <div class="flex items-center space-x-3 mb-4">
                        <!-- <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield text-red-600">
                                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                            </svg>
                        </div> -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('bank.student_bank_account_terms_conditions') }}</h3>
                            <!-- <p class="text-sm text-gray-600">Temporary Service Unavailability</p> -->
                        </div>
                    </div>
                    <!-- <div class="bg-red-50 p-4 rounded-xl border border-red-200 mb-4">
                        <div class="text-sm text-red-800 font-bold mb-1">Service Temporarily Unavailable</div>
                        <div class="text-sm text-red-800">We apologize, but our card freeze functionality is currently undergoing scheduled maintenance and is not available at this time.</div>
                    </div> -->
                    <div class="bg-blue-50 p-4 rounded border border-blue-200 mt-4">
                        <div class="text-sm text-blue-800 font-bold mb-3">{{ __('bank.welcome_to_zedville') }}</div>

                        <div class="text-sm text-blue-800 space-y-4">
                            <p><strong>{{ __('bank.by_creating_an_account_you_agree') }}</strong></p>

                            <ul class="list-decimal list-inside space-y-2">
                                <li>
                                    {!! __('bank.strong_educational_use_only_strong') !!}
                                    <ul class="list-disc list-inside ml-5">
                                        <li>{{ __('bank.this_is_a_simulated_bank_for') }}</li>
                                        <li>{{ __('bank.no_real_money_is_involved') }}</li>
                                        <li>{{ __('bank.all_transactions_are_practice_activities') }}</li>
                                    </ul>
                                </li>

                                <li>
                                    {!! __('bank.strong_your_account_strong') !!}
                                    <ul class="list-disc list-inside ml-5">
                                        <li>{{ __('bank.do_not_share_your_real_name') }}</li>
                                        <li>{{ __('bank.keep_your_login_information_private') }}</li>
                                    </ul>
                                </li>

                                <li>
                                    {!! __('bank.strong_appropriate_behavior_strong') !!}
                                    <ul class="list-disc list-inside ml-5">
                                        <li>{{ __('bank.be_respectful_to_classmates_and_teachers') }}</li>
                                        <li>{{ __('bank.use_the_platform_only_for_class') }}</li>
                                        <li>{{ __('bank.follow_your_teacher_s_instructions') }}</li>
                                        <li>{{ __('bank.do_not_post_share_or_upload') }}</li>
                                    </ul>
                                </li>

                                <li>
                                    {!! __('bank.strong_tutor_access_strong') !!}
                                    <ul class="list-disc list-inside ml-5">
                                        <li>{{ __('bank.your_tutor_can_view_your_account') }}</li>
                                        <li>{{ __('bank.this_helps_them_guide_your_learning') }}</li>
                                        <li>{{ __('bank.all_activities_are_monitored_for_educational') }}</li>
                                    </ul>
                                </li>

                                <li>
                                    {!! __('bank.strong_privacy_strong') !!}
                                    <ul class="list-disc list-inside ml-5">
                                        <li>{{ __('bank.we_protect_your_information') }}</li>
                                        <li>{{ __('bank.only_your_teacher_and_school_have') }}</li>
                                        <li>{{ __('bank.your_pseudonym_keeps_your_identity_private') }}</li>
                                        <li>{{ __('bank.we_collect_basic_information_like_your') }}</li>
                                        <li>{{ __('bank.your_data_will_not_be_shared') }}</li>
                                    </ul>
                                </li>

                                <li>
                                    {!! __('bank.strong_account_rules_strong') !!}
                                    <ul class="list-disc list-inside ml-5">
                                        <li>{{ __('bank.complete_activities_honestly') }}</li>
                                        <li>{{ __('bank.don_t_share_passwords') }}</li>
                                        <li>{{ __('bank.report_any_problems_to_your_teacher') }}</li>
                                    </ul>
                                </li>

                                <li>
                                    {!! __('bank.strong_changes_to_terms_strong') !!}
                                    <ul class="list-disc list-inside ml-5">
                                        <li>{{ __('bank.we_may_update_these_terms_and') }}</li>
                                        <li>{{ __('bank.we_ll_let_you_know_if') }}</li>
                                    </ul>
                                </li>
                            </ul>
                            <p><strong>{{ __('bank.by_clicking_i_agree_you_confirm') }}</strong></p>
                        </div>
                    </div>

                    <div class="text-center mt-8">
                        <button id="agreeButton"  @click="confirmationModal = false" class="themeBtn w-full">{{ __('bank.i_agree') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
  const agreeButton = document.getElementById('agreeButton');
  const tandcCheckbox = document.getElementById('tandc');
  const tandcLabel = document.querySelector('label[for="tandc"]');

  agreeButton.addEventListener('click', function() {
    // Disable the checkbox
    tandcCheckbox.disabled = true;

    // Make the label unclickable
    tandcLabel.style.pointerEvents = 'none';
    tandcLabel.style.opacity = '0.6'; // optional: visual feedback
  });
});
</script>
</div>
@endsection