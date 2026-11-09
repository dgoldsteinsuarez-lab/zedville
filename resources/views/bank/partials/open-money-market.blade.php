{{-- Opening the Money Market account (moved from the old bank home, 2026-10-29). Needs an Alpine x-data with openAccountModal. --}}
    <!-- Open Account Form -->

    <div x-show="openAccountModal" x-transition.opacity
        class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
        @keydown.escape.window="openAccountModal = false" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="openAccountModal = false"></div>
        <!-- Modal Box -->
        <div class="modalDilog max-w-[700px]">
            <div class="modalContent bg-white z-100 rounded-lg border border-color-[#D2DDDB]">
                <form action="{{ route('bank.moneymarketamount') }}" method="post">
                    @csrf
                    <div
                        class="sticky top-0 bg-white z-10 border-b border-gray-200 p-6 flex items-center justify-between shadow-sm">
                        <div class="flex justify-between align-center w-full">
                            <div class="items-center">
                                <h4 class="text-2xl font-bold text-gray-900">{{ __('bank.money_market_account_agreement') }}</h4>
                                <p class="text-sm text-gray-600">Universal Bank</p>
                            </div>
                            <button type="button" @click="openAccountModal = false"
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
                        @php
                            // the contract with the student's rate: 4 % before the Intermediate, 5 % in it, 6 % from its
                            // third month (ZedDirecto); a month with money taken out earns nothing (Daniel, 2026-11-08)
                            $mmR = \App\Services\SavingsLedger::rate('money_market', auth()->user());
                            $mmRate = \App\Services\PriceRise::pct($mmR * 100, false);
                            $mmMonthly = \App\Services\PriceRise::pct(round($mmR * 100 / 12, 2), false);
                        @endphp
                        <div class="space-y-6">
                            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                                <h4 class="font-semibold text-blue-900 mb-3 text-lg">{{ __('efd.account_information') }}</h4>
                                <div class="space-y-3 text-sm">
                                    <p><strong>{{ __('bank.account_type') }}</strong> {{ __('bank.money_market_account') }}</p>
                                    <p><strong>{{ __('bank.interest_rate') }}</strong> {{ __('bank_ui.rate_year', ['rate' => $mmRate]) }}</p>
                                    <p><strong>{{ __('bank.purpose') }}</strong> {{ __('bank.long_term_savings_1_years_for') }}</p>
                                    <p><strong>{{ __('bank.withdrawal_flexibility') }}</strong> {{ __('bank_ui.mm_flexibility') }}</p>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="font-semibold text-gray-900 mb-4 text-lg">{{ __('bank.terms_conditions') }}</h4>
                                <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">
                                    <div class="font-semibold text-md mb-1">{{ __('bank.bank_name_universal_bank') }}</div>
                                    <div class="font-semibold text-md mb-1">{{ __('bank.by_checking_the_box_you_agree') }}</div>
                                    <ul>
                                        <li>{{ __('bank_ui.mm_purpose', ['rate' => $mmRate]) }}</li>
                                        <li>{{ __('bank.withdrawals') }}</li>
                                        <li>{{ __('bank.best_for_funds_not_needed_for') }}</li>
                                        <li>{{ __('bank_ui.mm_withdraw') }}</li>
                                        <li>{{ __('bank_ui.mm_fee') }}</li>
                                        <li>{{ __('bank_ui.mm_back', ['rate' => $mmRate]) }}</li>
                                        <li>{{ __('bank.interest_calculated_monthly_based_on_end') }}</li>
                                        <li>{{ __('bank.bank_s_rules_can_set_limits') }}</li>
                                        <li>{{ __('bank.privacy_your_data_is_kept_secure') }}</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                                <h4 class="font-semibold text-purple-900 mb-3 text-lg">{{ __('bank.interest_calculation_details') }}</h4>
                                <div class="text-sm text-gray-700 space-y-3">
                                    <div>
                                        <p class="mb-1"><strong>{{ __('bank.normal_rate_no_withdrawals') }}</strong></p>
                                        <p>{{ __('bank_ui.mm_normal', ['rate' => $mmRate, 'monthly' => $mmMonthly]) }}</p>
                                        <p>{{ __('bank_ui.mm_example', ['amount' => zeds(790), 'interest' => zeds(round(790 * $mmR / 12, 2))]) }}</p>
                                    </div>
                                    <div>
                                        <p class="mb-1"><strong>{{ __('bank.penalty_rate_with_withdrawals') }}</strong></p>
                                        <p>{{ __('bank_ui.mm_penalty') }}</p>
                                        <p>{{ __('bank_ui.mm_penalty_example', ['amount' => zeds(790), 'out' => zeds(50), 'fee' => zeds(50 * \App\Services\SavingsLedger::WITHDRAWAL_FEE)]) }}</p>
                                    </div>
                                    <div class="bg-orange-100 p-3 rounded border border-orange-200">
                                        <p><strong>{{ __('bank.important') }}</strong> {{ __('bank_ui.mm_important', ['rate' => $mmRate]) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="sticky bottom-0 z-10 bg-white border-t border-gray-200 p-6 flex flex-wrap items-center justify-between shadow-sm">
                        <div class="w-full">
                            <div
                                class="flex items-center space-x-3 p-4 bg-blue-50 rounded-xl border border-blue-200 w-full">
                                <input id="agreement-checkbox"
                                    class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                    required type="checkbox">
                                <label for="agreement-checkbox" class="text-sm text-gray-700 font-medium"><strong>{{ __('efd.agree_terms') }}</strong></label>
                            </div>
                        </div>
                        <div class="w-full mt-4 text-center flex flex-wrap gap-4 justify-center">
                            <button type="button" @click="openAccountModal = false" class="whiteBtn">{{ __('messages.cancel') }}</button>
                            @if($bankAccount->is_open_money_market_account == 0)
                                <button type="submit" class="themeBtn">{{ __('efd.accept_open') }}</button>
                            @else
                                <button type="button" @click="openAccountModal = false; viewmoneymarketmodal = true"
                                    class="themeBtn">{{ __('efd.accept_open') }}</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
