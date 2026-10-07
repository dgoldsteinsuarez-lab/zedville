@extends('layouts.profile')

@section('title', 'My Account')

@section('content')
    <div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
        <h1 class="text-xl font-bold whitespace-nowrap ">My Account</h1>

    </div>

    <div class="grid grid-cols-1 gap-5 mt-6"
        x-data="{ confirmationModal: false, viewPin: false, changePin: false, viewAccountDtls: false, viewmoneymarketmodal: false, openEmergencyModal: false, emerFundmodal: false, openAccountModal: false, showaccountnumber: false, showcardnumber: false, showibannumber: false, showbicswiftnumber: false, addtowalletModal: false  }">

        <div class="themeTabspills">
            <div class="w-full">
                <!-- Tabs Header -->
                @include('bank.partials.bankmenu')

                <div class="py-6 lg:py-10 px-6 lg:px-8 max-w-[1000px] w-full mt-4 border border-[#D2DDDB] rounded-lg">
                    <div class="tab-content">
                        <div class="myAccountSec">
                            <h3 class="text-lg font-bold whitespace-nowrap ">Account Summary</h3>
                            <div class="flex flex-wrap gap-4 mt-6">
                                <div class="w-full">
                                    <div
                                        class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-green-50 border-green-200 hover:bg-green-100">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 bg-green-300 rounded-xl flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-piggy-bank text-green-600">
                                                    <path
                                                        d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2h0V5z">
                                                    </path>
                                                    <path d="M2 9v1c0 1.1.9 2 2 2h1"></path>
                                                    <path d="M16 11h0"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-lg text-gray-900">Primary Savings Account</h4>
                                                <p class="text-md text-gray-600">
                                                    {{$bankAccount->primary_savings_account_number}}</p>
                                                <p class="text-md text-gray-600">Interest: 0%</p>
                                                <div class="flex items-center justify-start space-x-1">
                                                    <div class="w-1 h-1 rounded-full bg-green-500"></div><span
                                                        class="text-xs font-medium text-gray-700">Highly flexible</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="px-1.5 py-0.5 rounded text-xs font-bold bg-green-400 text-white">Active</span>
                                            <div class="font-bold text-xl text-green-600">
                                                {{ number_format($lastBalance, 2) }} ZEDS</div>
                                            <a href="javascript:void(0);" @click="viewAccountDtls = true"
                                                class="text-md text-green-800 mt-1">View →</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full">
                                    <div
                                        class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-orange-50 border-orange-200 hover:bg-orange-100">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-12 h-12 bg-yellow-300 rounded-xl flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-shield text-yellow-600">
                                                    <path
                                                        d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-lg text-gray-900">Emergency Fund Account</h4>
                                                @if($bankAccount->is_open_emergency_account != 0)
                                                    <p class="text-md text-gray-600">
                                                        {{$bankAccount->emergency_fund_account_number}}</p>
                                                    <p class="text-md text-gray-600">Interest: 2%</p>
                                                    <div class="flex items-center justify-start space-x-1">
                                                        <div class="w-1 h-1 rounded-full bg-yellow-500"></div><span
                                                            class="text-xs font-medium text-gray-700">Flexible, no fees</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            @php
                                                $emergencyAmount = $bankAccount->emergency_fund_account_amount ?? 0;
                                            @endphp
                                            @if($bankAccount->is_open_emergency_account == 0)
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-xs font-bold bg-gray-200 text-gray-900">Open</span>
                                            @else
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-xs font-bold bg-gray-200 text-gray-900">View</span>
                                            @endif
                                            <div class="font-bold text-xl text-yellow-500">
                                                {{ number_format($bankAccount->emergency_fund_account_amount, 2) }} ZEDS
                                            </div>

                                            @if($bankAccount->is_open_emergency_account == 0)
                                                <a href="javascript:void(0);" @click="openEmergencyModal = true"
                                                    class="text-md text-yellow-600 mt-1">Open →</a>
                                            @else
                                                <a href="javascript:void(0);" @click="emerFundmodal = true"
                                                    class="text-md text-yellow-600 mt-1">View →</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full">
                                    <div
                                        class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-purple-50 border-purple-200 hover:bg-purple-100">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-12 h-12 bg-purple-300 rounded-xl flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-trending-up text-purple-600">
                                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                                    <polyline points="16 7 22 7 22 13"></polyline>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-lg text-gray-900">Money Market Account</h4>
                                                @if($bankAccount->is_open_money_market_account != 0)
                                                    <p class="text-md text-gray-600">
                                                        {{$bankAccount->money_market_account_number}}</p>
                                                    <p class="text-md text-gray-600">Interest: 4%</p>
                                                    <div class="flex items-center justify-start space-x-1">
                                                        <div class="w-1 h-1 rounded-full bg-purple-500"></div><span
                                                            class="text-xs font-medium text-gray-700">Restiricted Access</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            @php
                                                $moneymarketsAmount = $bankAccount->money_market_account_amount ?? 0;
                                            @endphp
                                            @if($bankAccount->is_open_money_market_account == 0)
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-xs font-bold bg-gray-200 text-gray-900">Open</span>
                                            @else
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-xs font-bold bg-gray-200 text-gray-900">View</span>
                                            @endif
                                            <div class="font-bold text-xl text-purple-600">
                                                {{ number_format($bankAccount->money_market_account_amount, 2) }} ZEDS</div>
                                            @if($bankAccount->is_open_money_market_account == 0)
                                                <a href="javascript:void(0);" @click="openAccountModal = true"
                                                    class="text-md text-purple-800 mt-1">Open →</a>
                                            @else
                                                <a href="javascript:void(0);" @click="viewmoneymarketmodal = true"
                                                    class="text-md text-purple-800 mt-1">View →</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accSummeryCardDtls mt-6">
                                <div class="flex flex-wrap lg:flex-nowrap gap-6 items-start">
                                    <div class="w-full xl:w-[50%]">
                                        <div class="bg-white rounded-lg p-4 border border-[#D2DDDB] flex-shrink-0">
                                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-credit-card text-themegreen mr-2">
                                                    <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                                    <line x1="2" x2="22" y1="10" y2="10"></line>
                                                </svg>{{ $bankAccount->card_name }}
                                            </h3>
                                            <div
                                                class="w-full h-40 bg-gradient-to-br from-gray-900 to-gray-700 rounded-lg p-3 text-white relative overflow-hidden mx-auto mb-4">
                                                <div
                                                    class="absolute top-0 right-0 w-16 h-16 bg-white opacity-10 rounded-full -translate-y-6 translate-x-6">
                                                </div>
                                                <div class="flex justify-between items-start mb-4">
                                                    <div class="text-xs font-medium">{{ $bankAccount->bank_name }}</div>
                                                    <div
                                                        class="bg-yellow-400 text-black px-1.5 py-0.5 rounded text-xs font-bold">{{ collect(explode(' ', $bankAccount->bank_name))->map(function ($word) {
        return strtoupper(substr($word, 0, 1));
    })->implode('') }}</div>
                                                </div>
                                                <div class="space-y-2">
                                                    <div class="text-sm font-mono tracking-wider">
                                                        {{ trim(chunk_split($bankAccount->card_number, 4, ' ')) }}</div>
                                                    <div class="flex justify-between items-end">
                                                        <div class="flex space-x-3">
                                                            <div>
                                                                <div class="text-xs opacity-75">VALID THRU</div>
                                                                <div class="font-mono text-xs">
                                                                    {{ \Carbon\Carbon::parse($bankAccount->card_valid)->format('m/y') }}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="text-xs opacity-75">CVV</div>
                                                                <div class="font-mono text-xs">{{ $bankAccount->card_cvv }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="bg-white text-gray-900 px-1.5 py-0.5 rounded text-xs font-semibold">
                                                            DEBIT</div>
                                                    </div>
                                                    <div class="mt-1">
                                                        <div class="text-xs opacity-75">CARDHOLDER</div>
                                                        <div class="font-medium text-xs">{{ $bankUserName }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <div
                                                    class="inline-flex items-center space-x-2 bg-green-100 text-green-800 px-3 py-2 rounded-full">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div><span
                                                        class="text-sm font-medium">Card Active</span>
                                                </div>
                                            </div>
                                            <div class="mt-4 space-y-3">
                                                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="lucide lucide-shield text-blue-600 mr-2">
                                                        <path
                                                            d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                                        </path>
                                                    </svg>Security & Controls
                                                </h3>

                                                <div class="buttonsLists flex gap-4">
                                                    <button @click="viewPin = true"
                                                        class="themeBtn w-full flex items-center justify-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-eye">
                                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        <span>See PIN</span>
                                                    </button>
                                                    <button @click="changePin = true"
                                                        class="secondaryBtn w-full flex items-center justify-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-shield">
                                                            <path
                                                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                                            </path>
                                                        </svg>
                                                        <span>Change PIN</span>
                                                    </button>
                                                </div>
                                                <div class="buttonsLists flex gap-4">
                                                    <button @click="confirmationModal = true"
                                                        class="dangerBtn w-full flex items-center justify-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-snowflake">
                                                            <line x1="2" x2="22" y1="12" y2="12"></line>
                                                            <line x1="12" x2="12" y1="2" y2="22"></line>
                                                            <path d="m20 16-4-4 4-4"></path>
                                                            <path d="m4 8 4 4-4 4"></path>
                                                            <path d="m16 4-4 4-4-4"></path>
                                                            <path d="m8 20 4-4 4 4"></path>
                                                        </svg>
                                                        <span>Freeze Card</span>
                                                    </button>
                                                    <button @click="addtowalletModal = true"
                                                        class="secondaryBtn w-full flex items-center justify-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-smartphone">
                                                            <rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect>
                                                            <path d="M12 18h.01"></path>
                                                        </svg>
                                                        <span>Add to Wallet</span>
                                                    </button>
                                                </div>
                                                <!-- <div class="singlebutton">
                                                    <button @click="confirmationModal = true" class="dangerBtn w-full flex items-center justify-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-snowflake">
                                                            <line x1="2" x2="22" y1="12" y2="12"></line>
                                                            <line x1="12" x2="12" y1="2" y2="22"></line>
                                                            <path d="m20 16-4-4 4-4"></path>
                                                            <path d="m4 8 4 4-4 4"></path>
                                                            <path d="m16 4-4 4-4-4"></path>
                                                            <path d="m8 20 4-4 4 4"></path>
                                                        </svg>
                                                        <span>Freeze Card</span>
                                                    </button>
                                                </div> -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-full xl:w-[50%]">
                                        <div class="flex-1 bg-white rounded-lg p-6 border border-[#d2dddb]">
                                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="lucide lucide-copy text-themegreen mr-2">
                                                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2">
                                                    </path>
                                                </svg>Card Information
                                            </h3>
                                            <div class="grid grid-cols-1 gap-4 mb-6">
                                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                                    <div>
                                                        <div @click="showaccountnumber = true"
                                                            class="text-sm font-semibold text-gray-700 mb-1 flex items-center">
                                                            Account Number
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="lucide lucide-info ml-2 text-gray-400 cursor-pointer hover:text-gray-600">
                                                                <circle cx="12" cy="12" r="10"></circle>
                                                                <path d="M12 16v-4"></path>
                                                                <path d="M12 8h.01"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="font-mono text-sm text-gray-900" id="AccountNumber"
                                                            data-copy="{{ $bankAccount->primary_savings_account_number }}">
                                                            {{ $bankAccount->primary_savings_account_number }}
                                                        </div>
                                                        <!-- <div class="font-mono text-sm text-gray-900" id="AccountNumber" data-copy="{{ $bankAccount->card_number }}">
                                                            @php
                                                            $number = $bankAccount->card_number;
                                                            $last4 = substr($number, -4); // "7192"
                                                        @endphp
                                                        •••• •••• •••• {{ $last4 }}
                                                        </div> -->
                                                    </div>
                                                    <button onclick="copyField('AccountNumber')"
                                                        class="text-themegreen hover:text-black p-2"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-copy">
                                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                                            <path
                                                                d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                                    <div>
                                                        <div @click="showcardnumber = true"
                                                            class="text-sm font-semibold text-gray-700 mb-1 flex items-center">
                                                            Card Number
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="lucide lucide-info ml-2 text-gray-400 cursor-pointer hover:text-gray-600">
                                                                <circle cx="12" cy="12" r="10"></circle>
                                                                <path d="M12 16v-4"></path>
                                                                <path d="M12 8h.01"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="font-mono text-sm text-gray-900" id="cardNumber"
                                                            data-copy="{{ $bankAccount->card_number }}">
                                                            {{ trim(chunk_split($bankAccount->card_number, 4, ' ')) }}</div>
                                                    </div>
                                                    <button onclick="copyField('cardNumber')"
                                                        class="text-themegreen hover:text-black p-2"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-copy">
                                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                                            <path
                                                                d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                                    <div>
                                                        <div @click="showibannumber = true"
                                                            class="text-sm font-semibold text-gray-700 mb-1 flex items-center">
                                                            IBAN
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="lucide lucide-info ml-2 text-gray-400 cursor-pointer hover:text-gray-600">
                                                                <circle cx="12" cy="12" r="10"></circle>
                                                                <path d="M12 16v-4"></path>
                                                                <path d="M12 8h.01"></path>
                                                            </svg>
                                                        </div>
                                                        <div id="iban" data-copy="{{ $bankAccount->card_iban }}"
                                                            class="font-mono text-sm text-gray-900">
                                                            {{ trim(chunk_split($bankAccount->card_iban, 4, ' ')) }}</div>
                                                    </div>
                                                    <button onclick="copyField('iban')"
                                                        class="text-themegreen hover:text-black p-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-copy">
                                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                                            <path
                                                                d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                                    <div>
                                                        <div @click="showbicswiftnumber = true"
                                                            class="text-sm font-semibold text-gray-700 mb-1 flex items-center">
                                                            BIC/SWIFT Code
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="lucide lucide-info ml-2 text-gray-400 cursor-pointer hover:text-gray-600">
                                                                <circle cx="12" cy="12" r="10"></circle>
                                                                <path d="M12 16v-4"></path>
                                                                <path d="M12 8h.01"></path>
                                                            </svg>
                                                        </div>
                                                        <div id="bicswift" data-copy="{{ $bankAccount->card_swift }}"
                                                            class="font-mono text-sm text-gray-900">
                                                            {{ $bankAccount->card_swift }}</div>
                                                    </div>
                                                    <button onclick="copyField('bicswift')"
                                                        class="text-themegreen hover:text-black p-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-copy">
                                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                                            <path
                                                                d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-700">Account Holder</div>
                                                        <div id="AccountHolder" data-copy="{{ $bankUserName }}"
                                                            class="font-mono text-lg font-semibold text-gray-900">
                                                            {{ $bankUserName }}</div>
                                                    </div>
                                                    <button onclick="copyField('AccountHolder')"
                                                        class="text-themegreen hover:text-black p-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-copy">
                                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                                            <path
                                                                d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="pt-4 border-t border-[#d2dddb]">
                                                <h4 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="lucide lucide-settings text-themegreen mr-2">
                                                        <path
                                                            d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z">
                                                        </path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>Account Actions
                                                </h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <button class="themeBtn flex items-center justify-center gap-2"
                                                        onclick="window.location.href='{{ route('bank.transfer') }}'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-arrow-left-right">
                                                            <path d="M8 3 4 7l4 4"></path>
                                                            <path d="M4 7h16"></path>
                                                            <path d="m16 21 4-4-4-4"></path>
                                                            <path d="M20 17H4"></path>
                                                        </svg>
                                                        <span>Transfer Funds</span>
                                                    </button>
                                                    <button class="whiteBtn flex items-center justify-center gap-2"
                                                        onclick="window.location.href='{{ route('bank.bank_statement_show') }}'">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="lucide lucide-file-text">
                                                            <path
                                                                d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z">
                                                            </path>
                                                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                                            <path d="M10 9H8"></path>
                                                            <path d="M16 13H8"></path>
                                                            <path d="M16 17H8"></path>
                                                        </svg>
                                                        <span>View Statement</span>
                                                    </button>
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

        <!-- Modals -->
        <!-- View Pin Modal -->
        <div x-show="viewPin" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="viewPin = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="viewPin = false"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-[450px]">
                <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                    <div class="flex justify-between items-center mb-4">
                        <button @click="viewPin = false"
                            class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="bodyContent">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-eye text-blue-600">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Your PIN</h3>
                                <p class="text-sm text-gray-600">Debit Card PIN</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 p-3 rounded border border-blue-200">
                            <div class="text-xl text-center text-blue-800 font-bold mb-3">{{$bankAccount->card_pin}}</div>
                            <div class="text-md text-center text-blue-800 font-semibold mb-2">📌 Important PIN Security
                                Information:</div>
                            <ul class="text-left space-y-1">
                                <li>• Never share your PIN with anyone</li>
                                <li>• Use your PIN for ATM withdrawals and card purchases</li>
                                <li>• Cover the keypad when entering your PIN</li>
                                <li>• Change your PIN regularly for security</li>
                                <li>• Contact us immediately if compromised</li>
                            </ul>
                        </div>
                        <div class="text-center mt-8">
                            <button @click="viewPin = false" class="themeBtn w-full">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Pin -->
        <div x-show="changePin" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="changePin = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="changePin = false"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-[450px]">
                <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                    <div class="flex justify-between items-center mb-4">
                        <button @click="changePin = false"
                            class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="bodyContent userProfileDtls">
                        <form action="{{ route('bank.updatePin') }}" method="post">
                            @csrf

                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-eye text-blue-600">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Change PIN</h3>
                                    <p class="text-sm text-gray-600">Security Verification Required</p>
                                </div>
                            </div>
                            <div class="bg-orange-50 p-3 rounded border border-orange-200 mb-4">
                                <div class="text-sm font-semibold text-orange-800 mb-4">Security Question:</div>
                                <div class="form-group">
                                    <label class="block mb-2 text-base font-medium text-black">What is 10 - 11?</label>
                                    <input class="form-control" placeholder="Enter answer" type="number" value=""
                                        name="security_answer">
                                </div>
                            </div>
                            <input type="hidden" name="bank_account_id" value="{{ $bankAccount->id }}">
                            <div class="form-group mb-4">
                                <label class="block mb-2 text-base font-medium text-black">New PIN (4 digits)</label>
                                <input class="form-control" placeholder="Enter New PIN" type="passowrd" value=""
                                    name="new_pin" minlength="4" maxlength="4" required>
                            </div>
                            <div class="form-group mb-4">
                                <label class="block mb-2 text-base font-medium text-black">Confirm New PIN</label>
                                <input class="form-control" placeholder="Confirm New PIN" type="passowrd" value=""
                                    name="confirm_pin" minlength="4" maxlength="4" required>
                            </div>
                            <div class="text-center grid grid-cols-1 md:grid-cols-2 gap-3 mt-8">
                                <button type="button" @click="changePin = false" class="whiteBtn w-full">Cancel</button>
                                <button type="submit" class="themeBtn w-full">Change PIN</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Freeze -->
        <div x-show="confirmationModal" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="confirmationModal = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="confirmationModal = false"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-[450px]">
                <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                    <div class="flex justify-between items-center mb-4">
                        <button @click="confirmationModal = false"
                            class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="bodyContent">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-shield text-red-600">
                                    <path
                                        d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Card Freeze Service</h3>
                                <p class="text-sm text-gray-600">Temporary Service Unavailability</p>
                            </div>
                        </div>
                        <div class="bg-red-50 p-4 rounded-xl border border-red-200 mb-4">
                            <div class="text-sm text-red-800 font-bold mb-1">Service Temporarily Unavailable</div>
                            <div class="text-sm text-red-800">We apologize, but our card freeze functionality is currently
                                undergoing scheduled maintenance and is not available at this time.</div>
                        </div>
                        <div class="bg-blue-50 p-3 rounded border border-blue-200">
                            <div class="text-sm text-blue-800 font-bold mb-1">About Card Freeze:</div>
                            <div class="text-sm text-blue-800">Card freeze allows you to temporarily suspend all
                                transactions on your debit card while keeping your account active. This is useful if you've
                                misplaced your card or suspect unauthorized use. You can unfreeze your card anytime through
                                our mobile app or by contacting customer service.</div>
                        </div>
                        <div class="text-center mt-8">
                            <button @click="confirmationModal = false" class="themeBtn w-full">I Understand</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Account -->
        <div x-show="viewAccountDtls" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="viewAccountDtls = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="viewAccountDtls = false"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-full fullscreen">
                <div class="modalContent bg-white z-100 border border-color-[#D2DDDB] h-full">
                    <div class="sticky z-10 top-0 bg-white border-b border-gray-200 py-6 px-6 shadow-sm">
                        <div class="container mx-auto">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-x-4">
                                    <button @click="viewAccountDtls = false"
                                        class="text-gray-400 hover:text-gray-600 p-3 rounded-full transition-colors"
                                        title="Go back">
                                        <span class="text-xl font-bold">←</span>
                                    </button>
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-piggy-bank text-green-600">
                                            <path
                                                d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2h0V5z">
                                            </path>
                                            <path d="M2 9v1c0 1.1.9 2 2 2h1"></path>
                                            <path d="M16 11h0"></path>
                                        </svg>
                                    </div>
                                    <div class="flex items-center">
                                        <div>
                                            <h4 class="text-2xl font-bold text-gray-900">Primary Savings Account</h4>
                                            <p class="text-sm text-gray-600">UB-2024-SAV-0991</p>
                                        </div>
                                    </div>
                                </div>
                                <button @click="viewAccountDtls = false"
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
                    </div>

                    <div class="bodyContent p-6">
                        <div class="container mx-auto max-w-[1040px]">
                            <div class="bg-themegreen/10 rounded-xl p-6 mb-8">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-themegreen text-sm mb-1">Available Balance</div>
                                        <div class="flex items-center space-x-3">
                                            <span
                                                class="text-3xl font-bold text-themegreen">{{ number_format($lastBalance, 2) }}
                                                ZEDS</span>
                                        </div>
                                        <div class="text-dark-800 text-sm mt-1">Interest Rate: 0% per annum</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-dark-800 opacity-90 text-sm">Account Type</div>
                                        <div class="text-xl text-dark-800 font-semibold">Primary Savings</div>
                                    </div>
                                </div>
                            </div>
                            <div class="accountActivity mb-8">
                                <h2 class="text-xl font-bold text-gray-900 mb-6">📋 Recent Activity</h2>
                                <div class="border border-gray-100 rounded-lg">
                                    @foreach($transactions as $txn)
                                        <div
                                            class="flex flex-wrap gap-y-4 items-center justify-between p-3 border-b border-gray-100 last:border-b-0">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                    <span class="text-lg">
                                                        @php
                                                            // Choose icon based on transaction type or description
                                                            if (stripos($txn->description, 'electricity') !== false)
                                                                echo '⚡';
                                                            elseif (stripos($txn->description, 'Internet') !== false)
                                                                echo '🌐';
                                                            elseif (stripos($txn->description, 'School') !== false)
                                                                echo '🎓';
                                                            elseif (stripos($txn->description, 'Water') !== false)
                                                                echo '💧';
                                                            elseif (stripos($txn->description, 'atm') !== false)
                                                                echo '🏧';
                                                            elseif (stripos($txn->description, 'online') !== false)
                                                                echo '🛒';
                                                            elseif (stripos($txn->description, 'salary') !== false)
                                                                echo '💰';
                                                            elseif (stripos($txn->description, 'coffee') !== false)
                                                                echo '☕';
                                                            elseif (stripos($txn->description, 'gas') !== false)
                                                                echo '⛽';
                                                            else
                                                                echo '💵';
                                                        @endphp
                                                    </span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 text-sm">{{ $txn->description }}</p>
                                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                                        {{ \Carbon\Carbon::parse($txn->transaction_date)->diffForHumans() }}
                                                        {{ \Carbon\Carbon::parse($txn->transaction_date)->format('g:i A') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p
                                                    class="text-lg font-bold  {{ $txn->type == 'debit' ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ $txn->type == 'debit' ? '-' . number_format($txn->amount, 2) : '+' . number_format($txn->amount, 2) }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="accFeaturesLists">
                                <h2 class="text-xl font-bold text-gray-900 mb-6">📋 Recent Activity</h2>
                                <div class="p-6 bg-blue-50 rounded-xl border border-blue-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Primary Functions</h4>
                                            <ul class="text-sm text-gray-700 space-y-2">
                                                <li>• Salary deposit and income</li>
                                                <li>• Everyday spending and purchases</li>
                                                <li>• Bill payments and direct debits</li>
                                                <li>• Source for transfers to other accounts</li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Access &amp; Flexibility
                                            </h4>
                                            <ul class="text-sm text-gray-700 space-y-2">
                                                <li>• Highly flexible withdrawals</li>
                                                <li>• Immediate access to funds</li>
                                                <li>• No withdrawal restrictions</li>
                                                <li>• Primary operational account</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                    <h4 class="text-2xl font-bold text-gray-900">Money Market Account Agreement</h4>
                                    <p class="text-sm text-gray-600">Universal Bank</p>
                                </div>
                                <button @click="openAccountModal = false"
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
                                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                                    <h4 class="font-semibold text-blue-900 mb-3 text-lg">Account Information</h4>
                                    <div class="space-y-3 text-sm">
                                        <p><strong>Account Type:</strong> Money Market Account</p>
                                        <p><strong>Interest Rate:</strong> 4% per annum</p>
                                        <p><strong>Purpose:</strong> Long-term savings (1+ years) for maximum growth</p>
                                        <p><strong>Withdrawal Flexibility:</strong> Restricted withdrawals (affects interest
                                            rate)</p>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-6 rounded-lg">
                                    <h4 class="font-semibold text-gray-900 mb-4 text-lg">Terms &amp; Conditions</h4>
                                    <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">
                                        <div class="font-semibold text-md mb-1">Bank Name: Universal Bank</div>
                                        <div class="font-semibold text-md mb-1">By checking the box, you agree to:</div>
                                        <ul>
                                            <li>• Purpose: Save long-term (1+ years) to earn 4% interest per year</li>
                                            <li>• Withdrawals:</li>
                                            <li>- Best for funds not needed for at least 1 year</li>
                                            <li>- If you withdraw any money in a month, that month's interest drops to 3.6%
                                            </li>
                                            <li>- Returns to 4% next month if no more withdrawals</li>
                                            <li>• Interest: Calculated monthly (based on end-of-month balance), added on the
                                                1st of each month</li>
                                            <li>• Bank's Rules: Can set limits or fix errors. May close account if rules are
                                                broken, fraud suspected, or account inactive</li>
                                            <li>• Privacy: Your data is kept secure for banking</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                                    <h4 class="font-semibold text-purple-900 mb-3 text-lg">Interest Calculation Details</h4>
                                    <div class="text-sm text-gray-700 space-y-3">
                                        <div>
                                            <p class="mb-1"><strong>Normal Rate (No Withdrawals):</strong></p>
                                            <p>• 4% per annum (0.33% per month)</p>
                                            <p>• Example: 790 ZEDS = 2.68 ZEDS monthly interest</p>
                                        </div>
                                        <div>
                                            <p class="mb-1"><strong>Penalty Rate (With Withdrawals):</strong></p>
                                            <p>• 3.6% per annum (0.30% per month) for entire month</p>
                                            <p>• Example: 740 ZEDS = 2.26 ZEDS monthly interest</p>
                                        </div>
                                        <div class="bg-orange-100 p-3 rounded border border-orange-200">
                                            <p><strong>Important:</strong> Any withdrawal during a month reduces that
                                                month's interest rate to 3.6%. Rate returns to 4% the following month if no
                                                withdrawals are made.</p>
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
                                    <label for="agreement-checkbox" class="text-sm text-gray-700 font-medium"><strong>I have
                                            read and agree to the terms above.</strong></label>
                                </div>
                            </div>
                            <div class="w-full mt-4 text-center flex flex-wrap gap-4 justify-center">
                                <button type="button" @click="openAccountModal = false" class="whiteBtn">Cancel</button>
                                @if($bankAccount->is_open_money_market_account == 0)
                                    <button type="submit" class="themeBtn">Accept & Open Account</button>
                                @else
                                    <button type="button" @click="openAccountModal = false; viewmoneymarketmodal = true"
                                        class="themeBtn">Accept & Open Account</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- View Money Market Account Modal -->
        <div x-show="viewmoneymarketmodal" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="viewmoneymarketmodal = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="viewmoneymarketmodal = false"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-full fullscreen">
                <div class="modalContent bg-white z-100 border border-color-[#D2DDDB] h-full">
                    <div class="sticky z-10 top-0 bg-white border-b border-gray-200 py-6 px-6 shadow-sm">
                        <div class="container mx-auto">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-x-4">
                                    <button @click="viewmoneymarketmodal = false"
                                        class="text-gray-400 hover:text-gray-600 p-3 rounded-full transition-colors"
                                        title="Go back">
                                        <span class="text-xl font-bold">←</span>
                                    </button>
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-piggy-bank text-green-600">
                                            <path
                                                d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2h0V5z">
                                            </path>
                                            <path d="M2 9v1c0 1.1.9 2 2 2h1"></path>
                                            <path d="M16 11h0"></path>
                                        </svg>
                                    </div>
                                    <div class="flex items-center">
                                        <div>
                                            <h4 class="text-2xl font-bold text-gray-900">Money Market Account</h4>
                                            <p class="text-sm text-gray-600">UB-2024-MMA-2856</p>
                                        </div>
                                    </div>
                                </div>
                                <button @click="viewmoneymarketmodal = false"
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
                    </div>

                    <div class="bodyContent p-6">
                        <div class="container mx-auto max-w-[1040px]">
                            <div class="bg-themeyellow rounded-xl p-6 mb-8">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-themegreen text-sm mb-1">Available Balance</div>
                                        <div class="flex items-center space-x-3">
                                            <span
                                                class="text-3xl font-bold text-themegreen">{{ number_format($bankAccount->money_market_account_amount, 2) }}
                                                ZEDS</span>
                                        </div>
                                        <div class="text-dark-800 text-sm mt-1">Interest Rate: 4% per annum</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-dark-800 opacity-90 text-sm">Account Type</div>
                                        <div class="text-xl text-dark-800 font-semibold">Money Market</div>
                                    </div>
                                </div>
                            </div>
                            <div class="intEarningSection mb-8">
                                <h2 class="text-xl font-bold text-gray-900 mb-6">💰 Total Earnings</h2>
                                <div class="space-y-4">
                                    <div class="w-full">
                                        <div
                                            class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-purple-50 border-purple-200 hover:bg-purple-100">
                                            <div class="flex items-center space-x-3">
                                                <div>
                                                    <!-- <h4 class="font-semibold text-md text-gray-900">This Month (Estimated)</h4> -->
                                                    <p class="text-2xl font-bold text-themegreen">
                                                        {{ number_format($moneymarketintrest['estimated_interest'], 2) }}
                                                        ZEDS</p>
                                                    <div class="flex items-center justify-start space-x-1">
                                                        <span class="text-xs font-medium text-gray-700">Based on current
                                                            balance</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div class="w-full">
                                        <div class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-blue-50 border-blue-200 hover:bg-blue-100">
                                            <div class="flex items-center space-x-3">
                                                <div>
                                                    <h4 class="font-semibold text-md text-gray-700">Annual Projection</h4>
                                                    <p class="text-2xl font-bold text-blue-500">{{ number_format($moneymarketintrest['annual_projection'], 2) }} ZEDS</p>
                                                    <div class="flex items-center justify-start space-x-1">
                                                        <span class="text-xs font-medium text-gray-700">At {{ $moneymarketintrest['annual_rate'] }}% rate</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                    <div class="w-full">
                                        <div
                                            class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-yellow-50 border-yellow-200 hover:bg-yellow-100">
                                            <div class="flex items-center space-x-3">
                                                <div>
                                                    <h4 class="font-semibold text-md text-gray-700">Next Payment</h4>
                                                    <p class="text-2xl font-bold text-yellow-500">
                                                        {{ $moneymarketintrest['next_payment_date'] }}</p>
                                                    <div class="flex items-center justify-start space-x-1">
                                                        <span class="text-xs font-medium text-gray-700">Interest credit
                                                            date</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 p-4 bg-orange-100 rounded-xl border border-orange-200">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="text-orange-600">⚠️</span>
                                        <span class="font-bold text-orange-800">Important Withdrawal Warning</span>
                                    </div>
                                    <p class="text-sm text-orange-800">Any withdrawal during a calendar month will reduce
                                        the interest rate to <strong>3.6%</strong> for that entire month. Rate returns to 4%
                                        the following month if no withdrawals are made.</p>
                                </div>
                            </div>
                            <div class="accountActivity mb-8">
                                <h2 class="text-xl font-bold text-gray-900 mb-6">📋 Recent Activity</h2>
                                <div class="border border-gray-100 rounded-lg">
                                    @foreach($moneymarkettransactions as $moneytxn)
                                        <div
                                            class="flex flex-wrap gap-y-4 items-center justify-between p-3 border-b border-gray-100 last:border-b-0">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                    <span class="text-lg">📈</span></div>
                                                <div>
                                                    <p class="font-medium text-gray-900 text-sm">{{ $moneytxn->description }}
                                                    </p>
                                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                                        {{ \Carbon\Carbon::parse($moneytxn->transaction_date)->diffForHumans() }}
                                                        {{ \Carbon\Carbon::parse($moneytxn->transaction_date)->format('g:i A') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p
                                                    class="text-lg font-bold {{ $moneytxn->type == 'credit' ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ $moneytxn->type == 'credit' ? '-' . number_format($moneytxn->amount, 2) : '+' . number_format($moneytxn->amount, 2) }}
                                                    ZEDS</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="accFeaturesLists">
                                <h2 class="text-xl font-bold text-gray-900 mb-6">ℹ️ Account Features & Purpose</h2>
                                <div class="p-6 bg-blue-50 rounded-xl border border-blue-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 mb-3">High-Yield Savings</h4>
                                            <ul class="text-sm text-gray-700 space-y-2">
                                                <li>• 4% base annual interest rate</li>
                                                <li>• Long-term savings (1+ years)</li>
                                                <li>• Maximum earning potential</li>
                                                <li>• Monthly compound interest</li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Withdrawal Rules</h4>
                                            <ul class="text-sm text-gray-700 space-y-2">
                                                <li>• Interest drops to 3.6% if withdrawal made</li>
                                                <li>• Penalty applies to entire month</li>
                                                <li>• Returns to 4% following month</li>
                                                <li>• Best for untouched savings</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                                    <h4 class="text-2xl font-bold text-gray-900">Opening Your Emergency Fund Account</h4>
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
                                        <p><strong>Dear Valued Citizen,</strong></p>
                                        <p>Emergency Fund Accounts help you prepare for unexpected expenses.</p>
                                        <p><strong>Important:</strong></p>
                                        <ul class="list-disc ml-6">
                                            <li>The mandatory 20% auto-debit will help build your emergency fund.</li>
                                            <li>This account should be used only for emergencies.</li>
                                            <li>Your goal should be to save 6 months of your salary.</li>
                                            <li>Opening this account is mandatory as part of our new financial
                                                responsibility program.</li>
                                        </ul>

                                    </div>
                                </div>

                                <!-- Account Information -->
                                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                                    <h4 class="font-semibold text-blue-900 mb-3 text-lg">Account Information</h4>
                                    <div class="space-y-2 text-sm">
                                        <p><strong>Account Type:</strong> Emergency Fund Account</p>
                                        <p><strong>Interest Rate:</strong> 2% per annum</p>
                                        <p><strong>Purpose:</strong> Emergency expenses and short-term savings</p>
                                        <p><strong>Withdrawal Flexibility:</strong> Flexible withdrawals, no penalties for
                                            students</p>
                                    </div>
                                </div>

                                <!-- Financial Education -->
                                <div class="bg-gray-50 p-6 rounded-lg">
                                    <h4 class="font-semibold text-gray-900 mb-4 text-lg">Financial Education Information
                                    </h4>
                                    <div class="text-sm text-gray-700 leading-relaxed">
                                        <ul class="list-disc ml-6">
                                            <li>20% of your salary will be automatically transferred to this account each
                                                month.</li>
                                            <li>Use this account only for real emergencies.</li>
                                            <li>Goal: Save 6 months' worth of salary.</li>
                                            <li>Opening this account is mandatory.</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Instructions -->
                                <div class="bg-gray-50 p-6 rounded-lg">
                                    <h4 class="font-semibold text-gray-900 mb-4 text-lg">Instructions</h4>
                                    <div class="text-sm text-gray-700 leading-relaxed">
                                        <ul class="list-disc ml-6">
                                            <li>Fill out the Auto-Debit Authorization Form below.</li>
                                            <li>Click Submit to complete your application.</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Auto-Debit Authorization Form -->
                                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                    <h4 class="font-semibold text-gray-900 mb-4 text-lg">Emergency Fund Account – Auto-Debit
                                        Authorization Form</h4>
                                    <div class="text-sm text-gray-700 leading-relaxed">
                                        <p class="text-sm leading-relaxed">
                                            I,
                                            <span class="inline-block border-b border-gray-600 px-2">
                                                {{$bankUserName}}
                                            </span>, authorize the bank to automatically debit
                                            20% of my monthly salary
                                            <span
                                                class="inline-block border-b border-gray-600 px-10">{{ $emmsavingsAmount }}</span>
                                            zed
                                            from my primary account

                                            <span
                                                class="inline-block border-b border-gray-600 px-16">{{$bankAccount->emergency_fund_account_number}}</span>
                                            and transfer it to my Emergency Fund Account
                                            every first Friday of the month.
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
                                    <label for="emeragree-checkbox" class="text-sm text-gray-700 font-medium"><strong>I have
                                            read and agree to the terms above.</strong></label>
                                </div>
                            </div>
                            <div class="w-full mt-4 text-center flex flex-wrap gap-4 justify-center">
                                <button type="button" @click="openEmergencyModal = false" class="whiteBtn">Cancel</button>
                                <!-- <button type="button" @click="openEmergencyModal = false; emerFundmodal = true" class="themeBtn">Accept & Open Account</button> -->
                                <button type="submit" class="themeBtn">Accept & Open Account</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Emergency Fund View -->
        <div x-show="emerFundmodal" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="emerFundmodal = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="emerFundmodal = false"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-full fullscreen">
                <div class="modalContent bg-white z-100 border border-color-[#D2DDDB] h-full">
                    <div class="sticky z-10 top-0 bg-white border-b border-gray-200 py-6 px-6 shadow-sm">
                        <div class="container mx-auto">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-x-4">
                                    <button @click="emerFundmodal = false"
                                        class="text-gray-400 hover:text-gray-600 p-3 rounded-full transition-colors"
                                        title="Go back">
                                        <span class="text-xl font-bold">←</span>
                                    </button>
                                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="lucide lucide-shield text-orange-600">
                                            <path
                                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="flex items-center">
                                        <div>
                                            <h4 class="text-2xl font-bold text-gray-900">Emergency Fund Account</h4>
                                            <p class="text-sm text-gray-600">UB-2024-EMG-1425</p>
                                        </div>
                                    </div>
                                </div>
                                <button @click="emerFundmodal = false"
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
                    </div>

                    <div class="bodyContent p-6">
                        <div class="container mx-auto max-w-[1040px]">
                            <div class="bg-themeyellow rounded-xl p-6 mb-8">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-themegreen text-sm mb-1">Available Balance</div>
                                        <div class="flex items-center space-x-3">
                                            <span
                                                class="text-3xl font-bold text-themegreen">{{ number_format($bankAccount->emergency_fund_account_amount, 2) }}
                                                ZEDS</span>
                                        </div>
                                        <div class="text-dark-800 text-sm mt-1">Interest Rate: 2% per annum</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-dark-800 opacity-90 text-sm">Account Type</div>
                                        <div class="text-xl text-dark-800 font-semibold">Emergency Fund</div>
                                    </div>
                                </div>
                            </div>
                            <div class="fundProgressSec mt-8 userProfileDtls">
                                <h2 class="text-xl font-bold text-gray-900 mb-6">🎯 Emergency Fund Progress Tracker</h2>
                                <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 mb-6">
                                    <!-- <p class="text-sm text-gray-600 mb-4">Financial experts recommend saving 3-6 months' worth of essential living expenses for your emergency reserve.</p> -->
                                    <p class="text-sm text-gray-600 mb-4">An <b>emergency</b> fund is your safety net for
                                        unexpected costs like medical bills, and it should cover 3-6 months of essential
                                        living expenses like rent, groceries, transportation, and utilities.</p>
                                    <!-- <div class="mb-4 form-group">
                                        <label class="block mb-2 text-base font-medium text-black">Monthly Essential Expenses (ZEDS)</label>
                                        <input type="number" placeholder="Enter your monthly expenses" class="form-control" value="1000" />
                                    </div> -->

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div class="p-4 bg-white rounded-lg border border-gray-200">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="font-semibold text-gray-900">3-Month Emergency Reserve</h4>
                                                <span class="text-sm text-orange-600 font-medium">Basic Safety Net</span>
                                            </div>
                                            <div class="mb-3">
                                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                                    <span>Progress</span>
                                                    <span>{{ number_format($lastBalance, 2) }} / 3,000 ZEDS</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-orange-500 h-2 rounded-full transition-all duration-300"
                                                        style="width: 16.6667%;"></div>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">16.7% Complete</div>
                                            </div>
                                            <!-- <p class="text-xs text-gray-600">Covers basic living expenses for short-term emergencies like medical bills or car repairs.</p> -->
                                            <p class="text-xs text-gray-600">Covers 3 months of essential living expenses
                                            </p>
                                        </div>
                                        <div class="p-4 bg-white rounded-lg border border-gray-200">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="font-semibold text-gray-900">6-Month Emergency Reserve</h4>
                                                <span class="text-sm text-green-600 font-medium">Optimal Security</span>
                                            </div>
                                            <div class="mb-3">
                                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                                    <span>Progress</span>
                                                    <span>{{ number_format($lastBalance, 2) }} / 6,000 ZEDS</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-green-500 h-2 rounded-full transition-all duration-300"
                                                        style="width: 8.33333%;"></div>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">8.3% Complete</div>
                                            </div>
                                            <!-- <p class="text-xs text-gray-600">Provides comprehensive protection against major life disruptions like job loss or income reduction.</p> -->
                                            <p class="text-xs text-gray-600">Covers 6 months of essential living expenses
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                        <h5 class="font-medium text-blue-900 mb-2">💡 Savings Recommendations</h5>
                                        <div class="text-sm text-blue-800 space-y-1">
                                            <p>• <strong>Need 2,500 more ZEDS</strong> to reach your 3-month emergency
                                                reserve goal</p>
                                            <p>• <strong>Need 5,500 more ZEDS</strong> to reach your optimal 6-month
                                                emergency reserve</p>
                                            <p>• Consider saving <strong>250-500 ZEDS monthly</strong> to reach your 3-month
                                                goal in 5-10 months</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="intEarningSection mb-8">
                                <h2 class="text-xl font-bold text-gray-900 mb-6">💰 Your Interest Earnings</h2>
                                <div class="space-y-4">
                                    <div class="w-full">
                                        <div
                                            class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-purple-50 border-purple-200 hover:bg-purple-100">
                                            <div class="flex items-center space-x-3">
                                                <div>
                                                    <h4 class="font-semibold text-md text-gray-900">This Month (Estimated)
                                                    </h4>
                                                    <p class="text-2xl font-bold text-themegreen">
                                                        {{ number_format($emergencyFundInterest['estimated_interest'], 2) }}
                                                        ZEDS</p>
                                                    <div class="flex items-center justify-start space-x-1">
                                                        <span class="text-xs font-medium text-gray-700">Based on current
                                                            balance</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-full">
                                        <div
                                            class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-blue-50 border-blue-200 hover:bg-blue-100">
                                            <div class="flex items-center space-x-3">
                                                <div>
                                                    <h4 class="font-semibold text-md text-gray-700">Annual Projection</h4>
                                                    <p class="text-2xl font-bold text-blue-500">
                                                        {{ number_format($emergencyFundInterest['annual_projection'], 2) }}
                                                        ZEDS</p>
                                                    <div class="flex items-center justify-start space-x-1">
                                                        <span class="text-xs font-medium text-gray-700">At
                                                            {{ $emergencyFundInterest['annual_rate'] }}% rate</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-full">
                                        <div
                                            class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-yellow-50 border-yellow-200 hover:bg-yellow-100">
                                            <div class="flex items-center space-x-3">
                                                <div>
                                                    <h4 class="font-semibold text-md text-gray-700">Next Payment</h4>
                                                    <p class="text-2xl font-bold text-yellow-500">
                                                        {{ $emergencyFundInterest['next_payment_date'] }}</p>
                                                    <div class="flex items-center justify-start space-x-1">
                                                        <span class="text-xs font-medium text-gray-700">Interest credit
                                                            date</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accountActivity mb-8">
                                <h2 class="text-xl font-bold text-gray-900 mb-6">📋 Recent Activity</h2>
                                <div class="border border-gray-100 rounded-lg">
                                    @foreach($emmengercyfundtransactions as $emmtxn)
                                        <div
                                            class="flex flex-wrap gap-y-4 items-center justify-between p-3 border-b border-gray-100 last:border-b-0">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                    <span class="text-lg">🔄</span></div>
                                                <div>
                                                    <p class="font-medium text-gray-900 text-sm">{{ $emmtxn->description }}</p>
                                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                                        {{ \Carbon\Carbon::parse($emmtxn->transaction_date)->diffForHumans() }}
                                                        {{ \Carbon\Carbon::parse($emmtxn->transaction_date)->format('g:i A') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p
                                                    class="text-lg font-bold {{ $emmtxn->type == 'credit' ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ $emmtxn->type == 'credit' ? '-' . number_format($emmtxn->amount, 2) : '+' . number_format($emmtxn->amount, 2) }}
                                                    ZEDS</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="accFeaturesLists">
                                <h2 class="text-xl font-bold text-gray-900 mb-6">ℹ️ Account Features & Purpose</h2>
                                <div class="p-6 bg-blue-50 rounded-xl border border-blue-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Purpose & Benefits</h4>
                                            <ul class="text-sm text-gray-700 space-y-2">
                                                <li>• Emergency expenses coverage</li>
                                                <li>• Short-term savings growth</li>
                                                <li>• 2% annual interest rate</li>
                                                <li>• No fees for students</li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Interest Calculation</h4>
                                            <ul class="text-sm text-gray-700 space-y-2">
                                                <li>• Computed on ending monthly balance</li>
                                                <li>• Credited on 1st of each month</li>
                                                <li>• Flexible withdrawals allowed</li>
                                                <li>• Compound monthly growth</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Account Number -->
        <div x-show="showaccountnumber" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="showaccountnumber = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-[450px]" @click.stop>
                <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                    <!-- <div class="flex justify-between items-center mb-4">
                        <button @click="showaccountnumber = false" class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div> -->
                    <div class="bodyContent">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <circle cx="12" cy="12" r="10" fill="#DDEBFF" />
                                    <circle cx="12" cy="12" r="9" stroke="#1A73E8" stroke-width="1.5" />
                                    <path d="M12 16v-4" stroke="#1A73E8" stroke-width="1.5" stroke-linecap="round" />
                                    <circle cx="12" cy="8" r="1" fill="#1A73E8" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Account Number</h3>
                                <p class="text-sm text-gray-600">Information & Usage</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 p-3 rounded border border-blue-200">
                            <p>Your Account Number identifies your specific bank account. Use it to receive money transfers
                                and set up payments. Each account has a unique number.</p>
                        </div>
                        <div class="text-center mt-8">
                            <button @click="showaccountnumber = false" class="themeBtn w-full">Got It</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Account Number -->
        <!-- Card Number -->
        <div x-show="showcardnumber" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="showcardnumber = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-[450px]" @click.stop>
                <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                    <!-- <div class="flex justify-between items-center mb-4">
                        <button @click="showcardnumber = false" class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div> -->
                    <div class="bodyContent">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <circle cx="12" cy="12" r="10" fill="#DDEBFF" />
                                    <circle cx="12" cy="12" r="9" stroke="#1A73E8" stroke-width="1.5" />
                                    <path d="M12 16v-4" stroke="#1A73E8" stroke-width="1.5" stroke-linecap="round" />
                                    <circle cx="12" cy="8" r="1" fill="#1A73E8" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Card Number</h3>
                                <p class="text-sm text-gray-600">Information & Usage</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 p-3 rounded border border-blue-200">
                            <p>Your 16-digit card number is the unique identifier for your debit card. Use this number for
                                online purchases, over-the-phone transactions, and card verification. Never share this
                                number with unauthorized parties.</p>
                        </div>
                        <div class="text-center mt-8">
                            <button @click="showcardnumber = false" class="themeBtn w-full">Got It</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Number -->
        <!-- IBN Number -->
        <div x-show="showibannumber" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="showibannumber = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-[450px]" @click.stop>
                <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                    <!-- <div class="flex justify-between items-center mb-4">
                        <button @click="showibannumber = false" class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div> -->
                    <div class="bodyContent">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <circle cx="12" cy="12" r="10" fill="#DDEBFF" />
                                    <circle cx="12" cy="12" r="9" stroke="#1A73E8" stroke-width="1.5" />
                                    <path d="M12 16v-4" stroke="#1A73E8" stroke-width="1.5" stroke-linecap="round" />
                                    <circle cx="12" cy="8" r="1" fill="#1A73E8" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">IBAN (International Bank Account Number)</h3>
                                <p class="text-sm text-gray-600">Information & Usage</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 p-3 rounded border border-blue-200">
                            <p>Your IBAN is used for international money transfers and payments. It identifies your bank
                                account across national borders. Share this with employers for salary deposits or when
                                receiving international transfers.</p>
                        </div>
                        <div class="text-center mt-8">
                            <button @click="showibannumber = false" class="themeBtn w-full">Got It</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- IBN Number -->
        <!-- BIC/SWIFT Number -->
        <div x-show="showbicswiftnumber" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="showbicswiftnumber = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50"></div>
            <!-- <div class="fixed inset-0 bg-black bg-opacity-50" @click="showbicswiftnumber = false"></div> -->
            <!-- Modal Box -->
            <div class="modalDilog max-w-[450px]" @click.stop>
                <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                    <!-- <div class="flex justify-between items-center mb-4">
                        <button @click="showbicswiftnumber = false" class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div> -->
                    <div class="bodyContent">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <circle cx="12" cy="12" r="10" fill="#DDEBFF" />
                                    <circle cx="12" cy="12" r="9" stroke="#1A73E8" stroke-width="1.5" />
                                    <path d="M12 16v-4" stroke="#1A73E8" stroke-width="1.5" stroke-linecap="round" />
                                    <circle cx="12" cy="8" r="1" fill="#1A73E8" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">BIC/SWIFT Code</h3>
                                <p class="text-sm text-gray-600">Information & Usage</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 p-3 rounded border border-blue-200">
                            <!-- <p>The Bank Identifier Code (BIC) or SWIFT code identifies Universal Bank in international transactions. This code is required alongside your IBAN for international wire transfers and cross-border payments.</p> -->
                            <p>WL' represents 'World' since this is a global learning platform. Real BIC codes use official
                                country codes like US, GB, or DE.</p>
                            <br>
                            <p>The Bank Identifier Code (BIC) or SWIFT code identifies Universal Bank in international
                                transactions. This code is required alongside your IBAN for international wire transfers and
                                cross-border payments.<span style="color:red"> WL' represents 'World' since this is a global
                                    learning platform. Real BIC codes use official country codes like US, GB, or DE.</span>
                            </p>
                        </div>
                        <div class="text-center mt-8">
                            <button @click="showbicswiftnumber = false" class="themeBtn w-full">Got It</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- BIC/SWIFT Number -->
        <!-- Add to Wallet -->
        <div x-show="addtowalletModal" x-transition.opacity
            class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
            @keydown.escape.window="addtowalletModal = false" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="addtowalletModal = false"></div>
            <!-- Modal Box -->
            <div class="modalDilog max-w-[450px]">
                <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                    <div class="flex justify-between items-center mb-4">
                        <button @click="addtowalletModal = false"
                            class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="bodyContent">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-smartphone">
                                    <rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect>
                                    <path d="M12 18h.01"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Add to Wallet</h3>
                                <p class="text-sm text-gray-600">Temporary Service Unavailability</p>
                            </div>
                        </div>
                        <div class="bg-red-50 p-4 rounded-xl border border-red-200 mb-4">
                            <div class="text-sm text-red-800 font-bold mb-1">Service Temporarily Unavailable</div>
                            <div class="text-sm text-red-800">This feature is not available yet. </div>
                        </div>
                        <div class="text-center mt-8">
                            <button @click="addtowalletModal = false" class="themeBtn w-full">I Understand</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add to Wallet -->
    </div>
@endsection
<script>
    function copyField(elementId) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const value = element.getAttribute('data-copy');
        if (!value) return;

        navigator.clipboard.writeText(value).then(() => {
            // Optional: feedback
            alert('Copied: ' + value);
        }).catch(err => {
            console.error('Copy failed: ', err);
        });
    }
</script>