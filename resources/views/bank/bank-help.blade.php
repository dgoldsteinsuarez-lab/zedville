@extends('layouts.profile')

@section('title', __('bank.frequently_asked_questions'))

@section('content')
<div class="zb-page">
@include('bank.partials.nav')
<div class="grid grid-cols-1 gap-5">
    <div class="themeTabspills">
        <div class="w-full">
            <div class="py-6 lg:py-10 px-6 lg:px-8 w-full border border-[#D2DDDB] rounded-lg">
                <div class="tab-content">
                    <div class="helpSection">
                        <div class="mb-8 text-center">
                            <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-help text-purple-600 mr-3">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <path d="M12 17h.01"></path>
                                </svg>{{ __('bank.help_support') }}
                            </h2>
                            <p class="text-gray-600 mt-2 text-md lg:text-lg">{{ __('bank.find_answers_to_your_questions_and') }}</p>
                        </div>
                        {{-- the bank's official number: the one to call back after a suspicious call (App\Services\FraudService) --}}
                        <div class="mb-8 max-w-2xl mx-auto rounded-xl border border-[#9FD9C3] bg-[#F0FBF7] p-4">
                            <p class="font-semibold text-gray-900">📞 {{ __('fraud.bank_contact_title') }}: <span class="whitespace-nowrap">{{ \App\Services\FraudService::BANK_PHONE }}</span></p>
                            <p class="text-gray-700 mt-1">{{ __('fraud.bank_contact_text', ['phone' => \App\Services\FraudService::BANK_PHONE]) }}</p>
                        </div>
                        <div class="mb-8">
                            <div class="relative max-w-2xl mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>
                                <input placeholder="{{ __('bank.search_for_help_topics_faqs_or') }}" class="w-full pl-12 pr-4 py-4 border border-gray-300 rounded-lg focus:border-gray-400 text-lg" id="searchFaq" type="text" />
                            </div>
                        </div>
                        <div class="categorySec grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('bank.help_categories') }}</h3>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                    <a href="javascript:void(0);" class="w-full flex items-center space-x-3 p-3 rounded-lg transition-colors text-left border border-gray-200 hover:bg-gray-50 text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open">
                                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                        </svg>
                                        <span class="font-medium text-sm">{{ __('bank.all_topics') }}</span>
                                    </a>
                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg transition-colors text-left border border-gray-200 hover:bg-gray-50 text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span class="font-medium text-sm">{{ __('bank.account_management') }}</span>
                                    </a>
                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg transition-colors text-left border border-gray-200 hover:bg-gray-50 text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smartphone">
                                            <rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect>
                                            <path d="M12 18h.01"></path>
                                        </svg>
                                        <span class="font-medium text-sm">{{ __('bank.online_banking') }}</span>
                                    </a>
                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg transition-colors text-left border border-gray-200 hover:bg-gray-50 text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left-right">
                                            <path d="M8 3 4 7l4 4"></path>
                                            <path d="M4 7h16"></path>
                                            <path d="m16 21 4-4-4-4"></path>
                                            <path d="M20 17H4"></path>
                                        </svg>
                                        <span class="font-medium text-sm">{{ __('bank.transfers_payments') }}</span>
                                    </a>
                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg transition-colors text-left border border-gray-200 hover:bg-gray-50 text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card">
                                            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                            <line x1="2" x2="22" y1="10" y2="10"></line>
                                        </svg>
                                        <span class="font-medium text-sm">{{ __('bank.cards') }}</span>
                                    </a>
                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg transition-colors text-left border border-gray-200 hover:bg-gray-50 text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield">
                                            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                                        </svg>
                                        <span class="font-medium text-sm">{{ __('bank.security') }}</span>
                                    </a>
                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg transition-colors text-left border border-gray-200 hover:bg-gray-50 text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text">
                                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                            <path d="M10 9H8"></path>
                                            <path d="M16 13H8"></path>
                                            <path d="M16 17H8"></path>
                                        </svg>
                                        <span class="font-medium text-sm">{{ __('bank.statements') }}</span>
                                    </a>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('bank.quick_actions') }}</h3>
                                <div class="space-y-2">
                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50  border border-gray-200 transition-colors text-left">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock text-blue-600">
                                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ __('bank.reset_password') }}</p>
                                            <p class="text-xs text-gray-500">{{ __('bank.quickly_reset_your_online_banking_password') }}</p>
                                        </div>
                                    </a>
                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search text-blue-600">
                                                <circle cx="11" cy="11" r="8"></circle>
                                                <path d="m21 21-4.3-4.3"></path>
                                            </svg></div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ __('bank.find_atm_branch') }}</p>
                                            <p class="text-xs text-gray-500">{{ __('bank.locate_the_nearest_atm_or_branch') }}</p>
                                        </div>
                                    </a>
                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert text-blue-600">
                                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"></path>
                                                <path d="M12 9v4"></path>
                                                <path d="M12 17h.01"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ __('bank.report_fraud') }}</p>
                                            <p class="text-xs text-gray-500">{{ __('bank.report_suspicious_account_activity') }}</p>
                                        </div>
                                    </a>

                                    <a href="#" class="w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text text-blue-600">
                                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                                <path d="M10 9H8"></path>
                                                <path d="M16 13H8"></path>
                                                <path d="M16 17H8"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ __('bank.account_statements') }}</p>
                                            <p class="text-xs text-gray-500">{{ __('bank.access_your_account_statements') }}</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('bank.popular_help_topics') }}</h3>
                            <div class="populartopicLists space-y-2">
                                <a href="#" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <span class="font-medium text-gray-900">{{ __('bank.how_do_i_reset_my_online') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs text-gray-500">15,234 views</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-gray-400">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </div>
                                </a>
                                <a href="#" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <span class="font-medium text-gray-900">{{ __('bank.how_to_set_up_a_money') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs text-gray-500">12,856 views</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-gray-400">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </div>
                                </a>
                                <a href="#" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <span class="font-medium text-gray-900">{{ __('bank.understanding_account_fees') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs text-gray-500">11,492 views</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-gray-400">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </div>
                                </a>
                                <a href="#" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <span class="font-medium text-gray-900">{{ __('bank.how_to_download_bank_statements') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs text-gray-500">9,781 views</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-gray-400">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </div>
                                </a>
                                <a href="#" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <span class="font-medium text-gray-900">{{ __('bank.card_security_features') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs text-gray-500">8,634 views</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-gray-400">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('bank.frequently_asked_questions') }}</h3>
                            <div class="themeAccordian" id="faqaccordians">
                                <div class="accordion mb-4 rounded-lg border border-black/20 bg-themeyellow">
                                    <button class="accordion-toggle w-full pl-[30px] py-4 pr-10 text-left font-medium text-md xl:text-[16px] text-black">
                                        {{ __('bank.how_do_i_update_my_personal') }}
                                    </button>
                                    <div class="accordion-content px-6 text-black text-md xl:text-base leading-relaxed">
                                        {{ __('bank.you_can_update_your_personal_information') }}
                                    </div>
                                </div>
                                <div class="accordion mb-4 rounded-lg border border-black/20 bg-themeyellow">
                                    <button class="accordion-toggle w-full pl-[30px] py-4 pr-10 text-left font-medium text-md xl:text-[16px] text-black">
                                        {{ __('bank.why_am_i_locked_out_of') }}
                                    </button>
                                    <div class="accordion-content px-6 text-black text-md xl:text-base leading-relaxed">
                                        {{ __('bank.account_lockouts_typically_occur_after_multiple') }}
                                    </div>
                                </div>
                                <div class="accordion mb-4 rounded-lg border border-black/20 bg-themeyellow">
                                    <button class="accordion-toggle w-full pl-[30px] py-4 pr-10 text-left font-medium text-md xl:text-[16px] text-black">
                                        {{ __('bank.what_are_the_transfer_limits') }}
                                    </button>
                                    <div class="accordion-content px-6 text-black text-md xl:text-base leading-relaxed">
                                        {{ __('bank.transfer_limits_vary_based_on_your') }}
                                    </div>
                                </div>
                                <div class="accordion mb-4 rounded-lg border border-black/20 bg-themeyellow">
                                    <button class="accordion-toggle w-full pl-[30px] py-4 pr-10 text-left font-medium text-md xl:text-[16px] text-black">
                                        {{ __('bank.how_do_i_report_a_lost') }}
                                    </button>
                                    <div class="accordion-content px-6 text-black text-md xl:text-base leading-relaxed">
                                        {{ __('bank.immediately_lock_your_card_through_the') }}
                                    </div>
                                </div>
                                <div class="accordion mb-4 rounded-lg border border-black/20 bg-themeyellow">
                                    <button class="accordion-toggle w-full pl-[30px] py-4 pr-10 text-left font-medium text-md xl:text-[16px] text-black">
                                        {{ __('bank.how_do_i_set_up_two') }}
                                    </button>
                                    <div class="accordion-content px-6 text-black text-md xl:text-base leading-relaxed">
                                        {{ __('bank.go_to_security_settings_in_your') }}
                                    </div>
                                </div>
                                <div class="accordion mb-4 rounded-lg border border-black/20 bg-themeyellow">
                                    <button class="accordion-toggle w-full pl-[30px] py-4 pr-10 text-left font-medium text-md xl:text-[16px] text-black">
                                        {{ __('bank.how_far_back_can_i_access') }}
                                    </button>
                                    <div class="accordion-content px-6 text-black text-md xl:text-base leading-relaxed">
                                        {{ __('bank.digital_statements_are_available_for_up') }}
                                    </div>
                                </div>
                            </div>
                            <div id="faqnoresultsec" style="display: none;">
                                <div class="text-center py-8"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search mx-auto text-gray-400 mb-4">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </svg>
                                    <p class="text-gray-500">{{ __('bank.no_results_found_try_adjusting_your') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-xl p-6 border border-red-200 mb-8">
                            <div class="flex items-center space-x-3 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield text-red-600">
                                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('bank.security_tips') }}</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-green-600 mt-1">
                                            <path d="M20 6 9 17l-5-5"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ __('bank.use_strong_passwords') }}</p>
                                            <p class="text-xs text-gray-600">{{ __('bank.create_unique_passwords_with_letters_numbers') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-green-600 mt-1">
                                            <path d="M20 6 9 17l-5-5"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ __('bank.enable_two_factor_authentication') }}</p>
                                            <p class="text-xs text-gray-600">{{ __('bank.add_an_extra_layer_of_security') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-green-600 mt-1">
                                            <path d="M20 6 9 17l-5-5"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ __('bank.monitor_your_accounts') }}</p>
                                            <p class="text-xs text-gray-600">{{ __('bank.regularly_check_for_unauthorized_transactions') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-green-600 mt-1">
                                            <path d="M20 6 9 17l-5-5"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ __('bank.secure_your_devices') }}</p>
                                            <p class="text-xs text-gray-600">{{ __('bank.keep_your_devices_updated_and_use') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('bank.service_information') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="font-medium text-gray-900 mb-3">{{ __('bank.digital_banking') }}</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-600">{{ __('bank.online_banking_2') }}</span><span class="font-medium">{{ __('bank.n24_7_available') }}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">{{ __('bank.mobile_app') }}</span><span class="font-medium">{{ __('bank.n24_7_available') }}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">{{ __('bank.atm_network') }}</span><span class="font-medium">{{ __('bank.n24_7_access') }}</span></div>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900 mb-3">{{ __('bank.support_response_times') }}</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-600">{{ __('bank.secure_messages') }}</span><span class="font-medium">{{ __('bank.within_24_hours') }}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">{{ __('bank.account_issues') }}</span><span class="font-medium">{{ __('bank.same_business_day') }}</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">{{ __('bank.fraud_reports') }}</span><span class="font-medium">{{ __('bank.immediate_response') }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection