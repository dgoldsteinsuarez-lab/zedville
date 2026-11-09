@extends('layouts.profile')
@section('no_page_back', '1'){{-- has its own back link at the top --}}

@section('title', 'NPOs Donate Now')

@section('content')
@php
    // Icon and thank-you message follow the NPO's category (lang/<language>/npos.php).
    $thanksKey = [
        'Animals' => 'animals', 'Elderly' => 'elderly', 'Children' => 'children', 'Food' => 'food',
        'Education' => 'education', 'Community' => 'community', 'Environment' => 'environment',
    ][$npo->category] ?? 'default';
    $icon = [
        'animals' => '🐾', 'elderly' => '👴', 'children' => '🧒', 'food' => '🍎',
        'education' => '📚', 'community' => '🏠', 'environment' => '🌱',
    ][$thanksKey] ?? '💝';
@endphp
@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
        .success-checkmark {
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0;
        }
        @keyframes confettiFall {
            0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(400px) rotate(720deg); opacity: 0; }
        }
        .float-gentle {
            animation: floatGentle 3s ease-in-out infinite;
        }
        @keyframes floatGentle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* ── Align donate screen with the app design system ── */

        /* Card shell → app card (1px #D2DDDB, 8px radius, soft shadow) */
        #checkoutForm,
        #thankYouScreen {
            border-radius: 12px !important;
            border: 1px solid #D2DDDB !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
            font-family: 'Open Sans', sans-serif;
        }

        /* Inputs → 8px radius, green focus ring */
        #checkoutForm input:not(.pin-input) {
            border-radius: 8px !important;
        }
        #checkoutForm input:focus {
            border-color: #00A47D !important;
            box-shadow: 0 0 0 3px rgba(0, 164, 125, 0.15) !important;
        }
        #checkoutForm .pin-input {
            border-radius: 8px !important;
        }

        /* Confirm button → .themeBtn identity (solid green, dark edge, pill) */
        #confirmBtn,
        #newDonationBtn {
            background: #00A47D !important;
            border: 1px solid #016950 !important;
            box-shadow: 0 3px 0 #016950 !important;
            border-radius: 30px !important;
            transform: none !important;
            transition: 0.2s !important;
        }
        #confirmBtn:hover:not(:disabled),
        #newDonationBtn:hover {
            background: #016950 !important;
            transform: translateY(3px) !important;
            box-shadow: 0 1px 0 #016950 !important;
        }
        #confirmBtn:disabled {
            opacity: 0.6;
        }

        /* Top-right Back button → same black-outline pill used everywhere */
        .donate-back-btn {
            background-color: #fff !important;
            border: 1px solid #000 !important;
            color: #000 !important;
            box-shadow: 0 3px 0 #000 !important;
            border-radius: 30px !important;
            transition: 0.2s !important;
        }
        .donate-back-btn:hover {
            background-color: #fff !important;
            color: #000 !important;
            transform: translateY(3px);
            box-shadow: 0 1px 0 #000 !important;
        }
    </style>
@endpush
<!-- <div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">Donate Now</h1>
</div> -->
<div class="flex items-center justify-between">
    
    <!-- Left Side - Page Title -->
    <h1 class="text-xl font-bold whitespace-nowrap">
        {{ __('npos.donate_now') }}
    </h1>

    <!-- Right Side - Back Button -->
    <button onclick="history.back()"
        class="donate-back-btn flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition">
        {{ __('npos.back') }}
        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7" />
        </svg>
    </button>

</div>
<div class="grid grid-cols-1 gap-5" x-data="{viewPin: false  }">
<div class="row">
    <div class="col-12">
        <div class="w-full p-4 sm:p-6 lg:p-8">
                <div class="w-full">

                    <!-- Checkout Card -->
                    <div id="checkoutForm" class="bg-white rounded-3xl shadow-xl shadow-warm-200/40 overflow-hidden border border-warm-100">
                        
                        <!-- Organization Header -->
                        <div class="bg-gradient-to-r from-sage-100 via-cream-100 to-warm-100 p-6">
                            <div class="flex items-center gap-4">
                                <div id="orgIcon" class="float-gentle w-16 h-16 bg-white rounded-2xl shadow-md flex items-center justify-center text-3xl">
                                    {{ $icon }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-sage-500 font-semibold uppercase tracking-wider mb-1">{{ __('npos.donating_to') }}</p>
                                    <h2 id="orgName" class="text-xl font-bold text-gray-800">{{ ct('activities', $name) }}</h2>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ __('npos.account') }} <span id="orgAccount" class="font-mono text-gray-600">{{ $accNO }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <div class="p-6 space-y-5">
                            
                            <!-- Donation Amount -->
                            <div class="bg-gradient-to-r from-warm-50 to-blush-50 rounded-2xl p-4 border border-warm-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">💝</span>
                                        <span class="text-gray-600 font-medium">{{ __('npos.your_donation') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="number" id="donationAmount" placeholder="0.00" min="1" step="0.01" class="w-28 text-right text-2xl font-bold text-warm-600 bg-transparent border-b-2 border-warm-300 focus:border-warm-500 outline-none placeholder-warm-300">
                                        <span class="text-lg font-semibold text-warm-500">Zed</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Information Section -->
                            <div class="space-y-4">
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    {{ __('npos.card_information') }}
                                </h3>

                                <!-- Cardholder Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('npos.cardholder_name') }}</label>
                                    <input type="text" placeholder="{{ __('npos.name_on_card') }}" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-warm-400 focus:ring-4 focus:ring-warm-100 outline-none transition-all text-gray-700 placeholder-gray-400" value="{{ $bankAccount ? $bankAccount->student_name : '' }}">
                                </div>

                                <!-- Card Number -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('shop.card_number') }}</label>
                                    <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-warm-400 focus:ring-4 focus:ring-warm-100 outline-none transition-all text-gray-700 placeholder-gray-400 font-mono tracking-wide" value="{{ $bankAccount ? trim(chunk_split($bankAccount->card_number, 4, ' ')) : '' }}">
                                </div>

                                <!-- Expiry & CVV Row -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('npos.expiry_date') }}</label>
                                        <input type="text" id="expiryDate" placeholder="{{ __('shop.expiry_placeholder') }}" maxlength="5" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-warm-400 focus:ring-4 focus:ring-warm-100 outline-none transition-all text-gray-700 placeholder-gray-400 font-mono" value="{{ $bankAccount ? \Carbon\Carbon::parse($bankAccount->card_valid)->format('m/y') : '' }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('shop.cvv') }}</label>
                                        <input type="password" placeholder="•••" maxlength="4" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-warm-400 focus:ring-4 focus:ring-warm-100 outline-none transition-all text-gray-700 placeholder-gray-400 font-mono tracking-widest" value="{{ $bankAccount ? $bankAccount->card_cvv : '' }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-dashed border-gray-200"></div>

                            <!-- PIN Entry Section -->
                            <div class="space-y-3">
                                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    {{ __('npos.enter_pin') }}
                                </label>
                                <div class="flex justify-center gap-3">
                                    <input type="password" maxlength="1" class="pin-input w-14 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:border-warm-400 focus:ring-4 focus:ring-warm-100 outline-none transition-all bg-cream-50" data-index="0">
                                    <input type="password" maxlength="1" class="pin-input w-14 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:border-warm-400 focus:ring-4 focus:ring-warm-100 outline-none transition-all bg-cream-50" data-index="1">
                                    <input type="password" maxlength="1" class="pin-input w-14 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:border-warm-400 focus:ring-4 focus:ring-warm-100 outline-none transition-all bg-cream-50" data-index="2">
                                    <input type="password" maxlength="1" class="pin-input w-14 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:border-warm-400 focus:ring-4 focus:ring-warm-100 outline-none transition-all bg-cream-50" data-index="3">
                                </div>
                                <p id="pinError" class="text-center text-sm text-red-500 hidden">{{ __('npos.pin_invalid') }}</p>
                                
                                <!-- Forgot PIN Link -->
                                <div class="text-center pt-1">
                                    <p class="text-sm text-gray-500">
                                        {{ __('shop.forgot_pin') }} <!--href="#my-accounts" id="forgotPinLink" -->
                                        <a href="javascript:void(0)"  @click="viewPin = true" class="text-warm-500 hover:text-warm-600 font-semibold inline-flex items-center gap-1 hover:underline transition-colors">
                                            {{ __('shop.view_pin') }}
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    </p>
                                </div>
                            </div>

                            <!-- Confirm Button -->
                            <button id="confirmBtn" class="w-full bg-gradient-to-r from-warm-400 to-warm-500 hover:from-warm-500 hover:to-warm-600 text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-warm-300/40 transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-3 text-lg">
                                <span class="text-xl">💝</span>
                                {{ __('npos.confirm_donation') }}
                            </button>

                            <!-- Security Note -->
                            <p class="text-center text-xs text-gray-400 flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-sage-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                {{ __('npos.secured') }}
                            </p>
                        </div>
                    </div>

                    <!-- Thank You Screen (Hidden by default) -->
                    <div id="thankYouScreen" class="bg-white rounded-3xl shadow-xl shadow-warm-200/40 overflow-hidden border border-warm-100 hidden relative">
                        <div id="confettiContainer" class="absolute inset-0 overflow-hidden pointer-events-none"></div>
                        
                        <div class="p-8 text-center relative z-10">
                            <!-- Success Checkmark -->
                            <div class="success-checkmark w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-sage-300 to-sage-500 rounded-full flex items-center justify-center shadow-lg shadow-sage-300/40">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">{{ __('npos.thank_you') }}</h2>
                            <p class="text-lg text-warm-500 font-bold mb-4">{!! __('npos.donated', ['amount' => '<span id="donatedAmount">0.00</span>']) !!}</p>
                            
                            <p id="thankYouMessage" class="text-gray-600 mb-6 leading-relaxed">
                                {{ __('npos.thanks.' . $thanksKey) }}
                            </p>
                            
                            <div class="bg-cream-100 rounded-xl p-4 mb-6">
                                <p class="text-sm text-gray-500 mb-1">{{ __('npos.transaction_reference') }}</p>
                                <!-- <p id="transactionRef" class="font-mono text-gray-700 font-bold">TXN-2024-XXXXX</p> -->
                                <p id="transactionRef" class="font-mono text-gray-700 font-bold"></p>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button id="newDonationBtn" class="flex-1 bg-gradient-to-r from-warm-400 to-warm-500 hover:from-warm-500 hover:to-warm-600 text-white font-semibold py-3 px-4 rounded-xl transition-all shadow-md">
                                    {{ __('npos.make_another') }}
                                </button>
                                <button class="flex-1 bg-sage-100 hover:bg-sage-200 text-sage-700 font-semibold py-3 px-4 rounded-xl transition-colors">
                                    {{ __('npos.download_receipt') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
    </div>
</div>
<!-- For Model -->
  <!-- View Pin Modal -->
    <div
        x-show="viewPin"
        x-transition.opacity
        class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
        @keydown.escape.window="viewPin = false" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="viewPin = false"></div>
        <!-- Modal Box -->
        <div class="modalDilog max-w-[450px]">
            <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                <div class="flex justify-between items-center mb-4">
                    <button @click="viewPin = false" class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                            <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
                <div class="bodyContent">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye text-green-600">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('shop.your_pin') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('shop.debit_card_pin') }}</p>
                        </div>
                    </div>
                    <div class="bg-green-50 p-3 rounded border border-green-200">
                        <div class="text-xl text-center text-green-800 font-bold mb-3">{{$bankAccount->card_pin}}</div>
                        <div class="text-md text-center text-green-800 font-semibold mb-2">{{ __('shop.pin_security_title') }}</div>
                        <ul class="text-left space-y-1">
                            <li>• {{ __('shop.pin_security.0') }}</li>
                            <li>• {{ __('shop.pin_security.1') }}</li>
                            <li>• {{ __('shop.pin_security.2') }}</li>
                            <li>• {{ __('shop.pin_security.3') }}</li>
                            <li>• {{ __('shop.pin_security.4') }}</li>
                        </ul>
                    </div>
                    <div class="text-center mt-8">
                        <button @click="viewPin = false" class="themeBtn w-full">{{ __('shop.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
@push('scripts')
    <script src="https://cdn.tailwindcss.com"></script>

<script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                    },
                    colors: {
                        warm: {
                            50: '#EEF9F5',
                            100: '#D2DDDB',
                            200: '#B7E3D6',
                            300: '#9FD8CB',
                            400: '#00A47D',
                            500: '#00926F',
                            600: '#016950',
                            700: '#014A38',
                        },
                        sage: {
                            50: '#EEF9F5',
                            100: '#E7FBF3',
                            200: '#B7E3D6',
                            300: '#9FD8CB',
                            400: '#00A47D',
                            500: '#016950',
                        },
                        blush: {
                            50: '#EEF9F5',
                            100: '#E7FBF3',
                            200: '#B7E3D6',
                            300: '#9FD8CB',
                            400: '#00A47D',
                        },
                        cream: {
                            50: '#F7FCF7',
                            100: '#EEF9F5',
                            200: '#B7E3D6',
                        }
                    }
                }
            }
        }
        // Texts in the student's language (lang/<language>/npos.php).
        const NPO_T = {{ \Illuminate\Support\Js::from(__('npos.js')) }};

        // DOM Elements
        const pinInputs = document.querySelectorAll('.pin-input');
        const confirmBtn = document.getElementById('confirmBtn');
        const checkoutForm = document.getElementById('checkoutForm');
        const thankYouScreen = document.getElementById('thankYouScreen');
        const pinError = document.getElementById('pinError');
        
        const newDonationBtn = document.getElementById('newDonationBtn');
        const cardNumberInput = document.getElementById('cardNumber');
        const expiryDateInput = document.getElementById('expiryDate');

        // Card Number Formatting
        cardNumberInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value.substring(0, 19);
        });

        // Expiry Date Formatting
        expiryDateInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        // PIN Input Handling
        pinInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                
                if (!/^\d*$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                if (value.length === 1 && index < pinInputs.length - 1) {
                    pinInputs[index + 1].focus();
                }

                pinError.classList.add('hidden');
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    pinInputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 4);
                pastedData.split('').forEach((char, i) => {
                    if (pinInputs[i]) {
                        pinInputs[i].value = char;
                    }
                });
                if (pastedData.length === 4) {
                    pinInputs[3].focus();
                }
            });
        });

        // Confirm Donation
        confirmBtn.addEventListener('click', () => {
            const pin = Array.from(pinInputs).map(input => input.value).join('');
            const amount = document.getElementById('donationAmount').value;
            // Amount validation
            if (amount === '' || parseFloat(amount) <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: NPO_T.amount_required_title,
                    text: NPO_T.amount_required_text,
                    confirmButtonColor: '#16a34a'
                });
                document.getElementById('donationAmount').focus();
                return;
            }
            if (pin.length !== 4) {
                pinError.classList.remove('hidden');
                pinInputs[0].focus();
                return;
            }

            confirmBtn.disabled = true;
            confirmBtn.innerHTML = `
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${NPO_T.processing}
            `;
            //New Ajax code here to process the donation and handle response
            //const amount = document.getElementById('donationAmount').value;
            const npo_name = document.getElementById('orgName').innerText;
            const account_no = document.getElementById('orgAccount').innerText;
            fetch("{{ route('education.store.storeDonation') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    npo_name: npo_name,
                    account_no: account_no,
                    amount: amount,
                    pin: pin
                })
            })
            .then(res => res.json())
            .then(data => {

                if(data.status){

                    document.getElementById('transactionRef').textContent = data.txn;

                    // ✅ Show thank you screen AFTER database success
                    setTimeout(() => {
                        showThankYouScreen();
                    }, 1000);

                }else{

                    pinError.innerText = data.message || NPO_T.incorrect_pin;
                    pinError.classList.remove('hidden');

                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = `
                        <span class="text-xl">💝</span>
                        ${NPO_T.confirm_donation}
                    `;
                }

            });
            /*setTimeout(() => {
                showThankYouScreen();
            }, 1500);*/
        });

        // Show Thank You Screen
        function showThankYouScreen() {
            //const txnRef = 'TXN-2024-' + Math.random().toString(36).substr(2, 5).toUpperCase();
            //document.getElementById('transactionRef').textContent = txnRef;
            
            
            // Display the donated amount
            const amount = document.getElementById('donationAmount').value || '0.00';
            document.getElementById('donatedAmount').textContent = parseFloat(amount)
                .toLocaleString(document.documentElement.lang, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            checkoutForm.classList.add('hidden');
            thankYouScreen.classList.remove('hidden');

            createConfetti();
        }

        // Create Confetti Animation
        function createConfetti() {
            const container = document.getElementById('confettiContainer');
            const colors = ['#1a9e5a', '#86efb8', '#e6f7ef', '#157a46', '#0f6e3d', '#bbf7d4'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                confetti.style.width = (Math.random() * 10 + 5) + 'px';
                confetti.style.height = (Math.random() * 10 + 5) + 'px';
                confetti.style.animation = `confettiFall ${Math.random() * 2 + 2}s ease-out ${Math.random() * 0.5}s forwards`;
                container.appendChild(confetti);
            }

            setTimeout(() => {
                container.innerHTML = '';
            }, 4000);
        }

        // New Donation Button
        newDonationBtn.addEventListener('click', () => {
            pinInputs.forEach(input => input.value = '');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = `
                <span class="text-xl">💝</span>
                        ${NPO_T.confirm_donation}
                    `;

            thankYouScreen.classList.add('hidden');
            checkoutForm.classList.remove('hidden');
        });
    </script>
@endpush