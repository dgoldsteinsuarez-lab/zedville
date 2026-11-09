{{-- Opening the Emergency Fund (moved from the old bank home, 2026-10-29). Needs an Alpine x-data with openEmergencyModal. --}}
    <!-- Open Emergency Fund Account Modal -->
    <div x-show="openEmergencyModal" x-transition.opacity
        class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
        @keydown.escape.window="openEmergencyModal = false" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="openEmergencyModal = false"></div>
        <!-- Modal Box -->
        <div class="modalDilog max-w-[700px]">
            <div class="modalContent bg-white z-100 rounded-lg border border-color-[#D2DDDB]">
                <form action="{{ route('bank.emengercyfundaccount') }}" method="post">
                    @csrf
                    <div
                        class="sticky top-0 bg-white z-10 border-b border-gray-200 p-6 flex items-center justify-between shadow-sm">
                        <div class="flex justify-between align-center w-full">
                            <div class="items-center">
                                <!-- <h4 class="text-2xl font-bold text-gray-900">Emergency Fund Account Agreement</h4> -->
                                <h4 class="text-2xl font-bold text-gray-900">{{ __('efd.ef_opening_title') }}</h4>
                                <p class="text-sm text-gray-600">Universal Bank</p>
                            </div>
                            <button type="button" @click="openEmergencyModal = false"
                                class="text-gray-500 hover:opacity-80 focus:outline-none transition">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                    <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bodyContent p-6">
                        <div class="space-y-6">
                            <!-- Intro Section -->
                            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                                <!-- <h4 class="font-semibold text-blue-900 mb-3 text-lg">Opening Your Emergency Fund Account</h4> -->
                                <div class="space-y-2 text-sm text-gray-800 leading-relaxed">
                                    <p><strong>{{ __('efd.dear_citizen') }}</strong></p>
                                    <p>{{ __('efd.ef_intro') }}</p>
                                    <p><strong>{{ __('bank.important') }}</strong></p>
                                    <ul class="list-disc ml-6">
                                        <li>{{ __('efd.ef_important.0') }}</li>
                                        <li>{{ __('efd.ef_important.1') }}</li>
                                        <li>{{ __('efd.ef_important.2') }}</li>
                                        <li>{{ __('efd.ef_important.3') }}</li>
                                    </ul>
                                    <p><strong>{{ __('bank.please_follow_these_simple_steps') }}</strong></p>
                                    <p><strong>{{ __('bank.instructions') }}</strong></p>
                                    <ul class="list-disc ml-6">
                                        <li>{{ __('bank.go_to_universal_bank') }}</li>
                                        <li>{{ __('bank.select_emergency_fund_account') }}</li>
                                        <li>{{ __('bank.fill_out_the_auto_debit_authorization') }}</li>
                                        <li>{{ __('bank.submit_your_application') }}</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                                <h4 class="font-semibold text-blue-900 mb-3 text-lg">{{ __('efd.account_information') }}</h4>
                                <div class="space-y-2 text-sm">
                                    <p><strong>{{ __('bank.account_type') }}</strong> {{ __('efd.account_type_value') }}</p>
                                    <p><strong>{{ __('bank.interest_rate') }}</strong> {{ __('efd.interest_rate_value') }}</p>
                                    <p><strong>{{ __('bank.purpose') }}</strong> {{ __('efd.purpose_value') }}</p>
                                    <p><strong>{{ __('bank.withdrawal_flexibility') }}</strong> {{ __('efd.withdrawal_value') }}</p>
                                </div>
                            </div>

                            <!-- Financial Education -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="font-semibold text-gray-900 mb-4 text-lg">{{ __('efd.financial_education_information') }}
                                </h4>
                                <div class="text-sm text-gray-700 leading-relaxed">
                                    <ul class="list-disc ml-6">
                                        <li>{{ __('efd.ef_education.0') }}</li>
                                        <li>{{ __('efd.ef_education.1') }}</li>
                                        <li>{{ __('bank.goal_save_6_months_worth_of') }}</li>
                                        <li>{{ __('efd.ef_education.3') }}</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="font-semibold text-gray-900 mb-4 text-lg">{{ __('efd.instructions') }}</h4>
                                <div class="text-sm text-gray-700 leading-relaxed">
                                    <ul class="list-disc ml-6">
                                        <li>{{ __('efd.ef_instructions.0') }}</li>
                                        <li>{{ __('bank.click_submit_to_complete_your_application') }}</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Auto-Debit Authorization Form -->
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                <h4 class="font-semibold text-gray-900 mb-4 text-lg">{{ __('efd.ef_auth_title') }}</h4>
                                <div class="text-sm text-gray-700 leading-relaxed">
                                    <p class="text-sm leading-relaxed">
                                        {!! __('efd.ef_auth_text', [
                                            'name' => '<span class="inline-block border-b border-gray-600 px-2">' . e($bankUserName) . '</span>',
                                            'amount' => '<span class="inline-block border-b border-gray-600 px-10">' . e($emmsavingsAmount) . '</span>',
                                            'account' => '<span class="inline-block border-b border-gray-600 px-16">' . e($bankAccount->primary_savings_account_number) . '</span>',
                                        ]) !!}
                                    </p>

                                    <!-- <p>
                                        I, _{{$bankUserName}}________________________ (full name, automatic), authorize the bank to automatically debit 
                                        20% of my monthly salary (_________________________ zed, automatic) from my primary account 
                                        (Account #: ______automatic___________________) and transfer it to my Emergency Fund Account 
                                        every first Friday of the month.
                                    </p> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="sticky bottom-0 z-10 bg-white border-t border-gray-200 p-6 flex flex-wrap items-center justify-between shadow-sm">
                        <div class="w-full">
                            <div
                                class="flex items-center space-x-3 p-4 bg-blue-50 rounded-xl border border-blue-200 w-full">
                                <input id="emeragree-checkbox"
                                    class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                    required type="checkbox">
                                <label for="emeragree-checkbox" class="text-sm text-gray-700 font-medium"><strong>{{ __('efd.agree_terms') }}</strong></label>
                            </div>
                        </div>
                        <div class="w-full mt-4 text-center flex flex-wrap gap-4 justify-center">
                            <button type="button" @click="openEmergencyModal = false" class="whiteBtn">{{ __('messages.cancel') }}</button>
                            <!-- <button type="button" @click="openEmergencyModal = false; emerFundmodal = true" class="themeBtn">Accept & Open Account</button> -->
                            <button type="submit" class="themeBtn">{{ __('efd.accept_open') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
