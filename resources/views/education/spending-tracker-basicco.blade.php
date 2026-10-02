@extends('layouts.profile')

@section('title', 'City Hall - Educational Finance Department')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('asset/front/css/efd.css') }}?ver={{ rand(111, 999) }}">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

            :root {
                --primary-teal: #00A47D;
                --primary-dark: #016950;
                --accent-yellow: #FFF5D4;
                --text-dark: #222222;
                --text-muted: #5C5C5C;
                --bg-body: #F7FCF7;
                --bg-sidebar: #EEF9F5;
                --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
                --border-color: #D2DDDB;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Open Sans', sans-serif;
            }

            header h1,
            .brand,
            .section-title {
                font-family: 'Manrope', sans-serif;
            }

            body {
                background-color: var(--bg-body);
                color: var(--text-dark);
                height: 100vh;
                overflow: hidden;
                display: flex;
            }

            /* --- SIDEBAR --- */
            .sidebar {
                width: 280px;
                background-color: var(--bg-sidebar);
                height: 100%;
                display: flex;
                flex-direction: column;
                border-right: 1px solid var(--border-color);
                padding: 24px;
                flex-shrink: 0;
                z-index: 100;
            }

            .brand {
                font-size: 24px;
                font-weight: 700;
                margin-bottom: 30px;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .brand span.zed {
                color: #000;
            }

            .brand span.ville {
                color: var(--primary-teal);
            }

            .user-widget {
                background: #EEF9F5;
                padding: 12px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 30px;
                border: 1px solid #B7E3D6;
            }

            .user-avatar {
                width: 40px;
                height: 40px;
                background: #00A47D;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 20px;
            }

            .user-info h4 {
                font-size: 14px;
                font-weight: 600;
            }

            .user-info p {
                font-size: 12px;
                color: var(--text-muted);
            }

            .nav-menu {
                list-style: none;
                flex-grow: 1;
            }

            .nav-item {
                margin-bottom: 8px;
            }

            .nav-link {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px 16px;
                border-radius: 8px;
                text-decoration: none;
                color: var(--text-muted);
                font-weight: 500;
                font-size: 14px;
                transition: all 0.2s;
            }

            .nav-link:hover,
            .nav-link.active {
                background-color: #E7FBF3;
                color: var(--primary-dark);
                font-weight: 600;
            }

            .balance-card {
                background: var(--accent-yellow);
                padding: 20px;
                border-radius: 12px;
                margin-top: auto;
                border: 1px solid #FFE48D;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }

            .balance-label {
                font-size: 12px;
                color: #8a6d1d;
                font-weight: 600;
                margin-bottom: 4px;
            }

            .balance-amount {
                font-size: 24px;
                font-weight: 800;
                color: #222222;
            }

            /* --- MAIN CONTENT --- */
            .main-content {
                flex-grow: 1;
                overflow-y: auto;
                padding: 30px 40px;
                position: relative;
            }

            header h1 {
                font-size: 28px;
                font-weight: 700;
                color: #222222;
            }

            .app-grid {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 30px;
                align-items: start;
            }

            /* --- STORE SECTION --- */
            .section-card {
                background: white;
                border-radius: 12px;
                padding: 25px;
                box-shadow: var(--card-shadow);
                border: 1px solid var(--border-color);
            }

            .section-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid var(--border-color);
            }

            .section-title {
                font-size: 18px;
                font-weight: 700;
                color: #222222;
            }

            /* View Toggles */
            .view-controls {
                display: flex;
                gap: 5px;
                background: #EEF9F5;
                padding: 4px;
                border-radius: 30px;
                border: 1px solid #D2DDDB;
            }

            .view-btn {
                border: none;
                background: transparent;
                padding: 6px 14px;
                cursor: pointer;
                border-radius: 30px;
                color: var(--text-muted);
                font-size: 14px;
                font-weight: 600;
            }

            .view-btn.active {
                background: #00A47D;
                color: #fff;
                box-shadow: none;
                font-weight: 600;
            }

            /* Items Container - Handles Layout */
            .items-container {
                display: grid;
                gap: 15px;
            }

            /* GRID VIEW Styles */
            .items-container.grid-view {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }

            .items-container.grid-view .item-card {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }

            .items-container.grid-view .item-emoji {
                font-size: 48px;
                margin-bottom: 10px;
            }

            .items-container.grid-view .item-name {
                margin-bottom: 5px;
            }

            /* LIST VIEW Styles (Compact) */
            .items-container.list-view {
                grid-template-columns: 1fr;
            }

            .items-container.list-view .item-card {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 10px 20px;
                /* Reduced padding for compact height */
                height: 65px;
                /* Fixed compact height */
            }

            .items-container.list-view .item-left {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .items-container.list-view .item-emoji {
                font-size: 24px;
                margin-bottom: 0;
            }

            .items-container.list-view .item-name {
                margin-bottom: 0;
                font-size: 15px;
            }

            .items-container.list-view .item-price {
                margin-right: 15px;
            }

            .item-card {
                border: 1px solid #D2DDDB;
                border-radius: 12px;
                background: #fff;
                transition: all 0.2s ease;
                cursor: pointer;
                display: flex;
            }

            .item-card:hover {
                border-color: var(--primary-teal);
                background: #EEF9F5;
                box-shadow: 0 2px 8px rgba(0, 164, 125, 0.12);
            }

            .item-name {
                font-weight: 600;
                color: #222222;
            }

            .item-price {
                display: inline-block;
                padding: 4px 12px;
                background: #EEF9F5;
                border: 1px solid #B7E3D6;
                border-radius: 20px;
                font-weight: 700;
                font-size: 13px;
                color: #016950;
            }

            .item-controls {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .btn-mini {
                width: 28px;
                height: 28px;
                border-radius: 6px;
                border: none;
                cursor: pointer;
                font-weight: bold;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: 0.2s;
            }

            .btn-minus {
                background: #fee2e2;
                color: #ef4444;
            }

            .btn-plus {
                background: #d1fae5;
                color: #10b981;
            }

            .qty-badge {
                font-weight: 700;
                font-size: 14px;
                min-width: 15px;
                text-align: center;
            }

            /* --- CART SECTION --- */
            .tracker-section {
                position: sticky;
                top: 20px;
            }

            .cart-list {
                min-height: 150px;
                margin-bottom: 20px;
            }

            .cart-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 12px;
                border: 1px solid #D2DDDB;
                border-radius: 10px;
                margin-bottom: 8px;
                background: #F7FCF7;
            }

            .cart-item-info {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .cart-item-emoji {
                font-size: 18px;
            }

            .cart-item-text {
                display: flex;
                flex-direction: column;
            }

            .cart-item-name {
                font-weight: 600;
                font-size: 13px;
            }

            .cart-item-sub {
                font-size: 11px;
                color: var(--text-muted);
            }

            .cart-item-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .cart-item-price {
                font-weight: 700;
                font-size: 14px;
                color: var(--primary-teal);
            }

            .btn-remove-cart {
                background: none;
                border: none;
                color: #fab1a0;
                font-size: 18px;
                cursor: pointer;
                padding: 2px;
                line-height: 1;
            }

            .btn-remove-cart:hover {
                color: #d63031;
            }

            .empty-cart {
                text-align: center;
                color: #b2bec3;
                font-style: italic;
                padding: 30px 0;
                border: 2px dashed #e2e8f0;
                border-radius: 12px;
            }

            .budget-row.total {
                border-top: 2px dashed #e2e8f0;
                padding-top: 15px;
                margin-top: 15px;
                font-weight: 700;
                font-size: 18px;
                color: #2d3436;
                display: flex;
                justify-content: space-between;
            }

            /* --- CHECKOUT FORM --- */
            .checkout-box {
                margin-top: 20px;
                background: #EEF9F5;
                padding: 20px;
                border-radius: 12px;
                border: 1px solid #B7E3D6;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .form-group label {
                display: block;
                font-size: 11px;
                font-weight: 700;
                margin-bottom: 4px;
                color: #546e7a;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .form-control {
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #D2DDDB;
                border-radius: 8px;
                font-size: 13px;
                transition: 0.15s;
                background: #fff;
            }

            .form-control:focus {
                outline: none;
                border-color: var(--primary-teal);
                background: white;
                box-shadow: 0 0 0 3px rgba(0, 164, 125, 0.15);
            }

            .form-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .btn-pay {
                width: 100%;
                padding: 14px;
                background: var(--primary-teal);
                color: white;
                border: 1px solid var(--primary-dark);
                border-radius: 30px;
                font-weight: 700;
                font-size: 15px;
                cursor: pointer;
                box-shadow: 0 3px 0 var(--primary-dark);
                transition: 0.2s;
                margin-top: 10px;
            }

            .btn-pay:hover {
                background: var(--primary-dark);
                transform: translateY(3px);
                box-shadow: 0 1px 0 var(--primary-dark);
            }

            .btn-pay:disabled {
                background: #9FD8CB;
                border-color: #9FD8CB;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }

            /* OVERLAY */
            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(5px);
                z-index: 1000;
                display: none;
                justify-content: center;
                align-items: center;
            }

            .overlay.show {
                display: flex;
                animation: fadeIn 0.3s;
            }

            .success-modal {
                background: white;
                padding: 40px;
                border-radius: 20px;
                text-align: center;
                max-width: 450px;
                width: 90%;
                border-top: 8px solid var(--primary-teal);
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            @media (max-width: 900px) {
                .app-grid {
                    grid-template-columns: 1fr;
                }

                .sidebar {
                    display: none;
                }

                /* Simplified for mobile demo */
            }
        </style>
    @endpush
    <div class="flex items-start justify-between pb-6">
        <!-- Left text -->
        @php
            $titles = [
                'accessories' => 'Accessories',
                'bespirit-sport-shop' => 'BeSpirit - Sport Shop',
                'beats-music-store' => 'Beats - Music Store',
                'comfort-zone' => 'Comfort Zone',
                'beu-beLuxury' => 'BeU - BeLuxury',
                'tech-hub' => 'Tech Hub',
                'daily-essentials' => 'Daily Essentials',
                'stationery-store' => 'Stationery Store',
                'the-basics-co' => 'The Basics Co.',
                'basicco' => 'The Basics Co.',
                'stationary' => 'The Stanary.',
                'citymall' => 'The City Mall.',
            ];
        @endphp

        <div>
            <!-- <h1 class="text-xl font-bold whitespace-nowrap">The Basics Co.</h1> -->
            <h1 class="text-xl font-bold whitespace-nowrap">
                {{ $titles[$type] ?? 'Store' }}
            </h1>
            <p class="text-gray-500 mb-5">Secure Student Shopping Portal</p>
        </div>

        <!-- Right Back button -->
        <button onclick="history.back()" class="zvBackBtn px-4 py-2 text-sm font-semibold rounded">
            ← Back
        </button>
    </div>
    <div class="grid grid-cols-1 gap-5 " x-data="{viewPin: false  }">
        <div class="themeTabspills">
            <div class="w-full">
                <!-- Tabs Header -->
                <!-- Tabs Content -->
                <div class="tailCard   w-full  rounded-lg" x-data="{ changeImageModal: false, moodMeater: false }">
                    <div class="overlay" id="overlay">
                        <div class="success-modal">
                            <h1 style="font-size: 50px; margin-bottom: 10px;">✔</h1>
                            <h2 style="color: var(--primary-dark); margin-bottom: 10px;">Payment Successful!</h2>
                            <p id="deliveryMessage" style="color: #636e72; margin-bottom: 25px; line-height: 1.6;">
                                Transaction complete.</p>
                            <button class="btn-pay" onclick="closeSuccessMessage()"
                                style="background: var(--text-dark);">Close</button>
                        </div>
                    </div>
                    <div class="sidebar" style="display: none;">
                        <div class="brand"><span class="zed">Zed</span><span class="ville">Ville</span></div>
                        <div class="user-widget">
                            <div class="user-avatar">DG</div>
                            <div class="user-info">
                                <h4>Daniel G.</h4>
                                <p>Grade 8 Student</p>
                            </div>
                        </div>
                        <ul class="nav-menu">
                            <li class="nav-item"><a href="#" class="nav-link"><span class="nav-icon">🏠</span> Home</a></li>
                            <li class="nav-item"><a href="#" class="nav-link active"><span class="nav-icon">🛍️</span> The
                                    Basics Co.</a></li>
                            <li class="nav-item"><a href="#" class="nav-link"><span class="nav-icon">📊</span> Bank
                                    Account</a></li>
                        </ul>
                        <div class="balance-card">
                            <div class="balance-label">ACCOUNT BALANCE</div>
                            <div class="balance-amount"><span id="sidebarBalance">{{ $lastBalance }}</span> ZED</div>
                        </div>
                    </div>
                    @if (!$hasBankAccount)
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>⚠ Bank Account Required</strong><br>
                            Please open your bank account to start the Consumer Profile Survey.
                            <br>
                            <br>
                            <a href="{{ route('bank.index') }}" class="btn btn-sm btn-primary mt-2">
                                Open Bank Account
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <br>
                        <br>
                    @endif
                    <div>
                        <div class="app-grid">
                            <div class="store-section section-card">
                                <div class="section-header">
                                    <span class="section-title">Products</span>

                                    <div class="view-controls">
                                        <button class="view-btn active" id="btnList" onclick="switchView('list')">☰
                                            List</button>
                                        <button class="view-btn" id="btnGrid" onclick="switchView('grid')">⸬ Grid</button>
                                    </div>
                                </div>

                                <div class="items-container list-view" id="itemsContainer">
                                </div>
                            </div>

                            <div class="tracker-section section-card">
                                <div class="section-header">
                                    <span class="section-title">Your Cart</span>
                                    <span id="itemCounter"
                                        style="background: #e2e8f0; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 700; display:none;">0/10</span>
                                </div>

                                <div class="cart-list" id="selectedItemsList">
                                    <div class="empty-cart">Cart is empty</div>
                                </div>

                                <div class="budget-row total">
                                    <span>Total</span>
                                    <span id="checkoutTotal">0 Z</span>
                                </div>

                                <div class="checkout-box">
                                    <h4 style="margin-bottom: 15px; color: var(--primary-dark); font-weight:700;">Secure
                                        Checkout</h4>

                                    <div class="form-group">
                                        <label>Shipping Method</label>
                                        <select id="shippingType" class="form-control" onchange="updateShippingAddress()">
                                            <option value="self">My Own Address (Default)</option>
                                            <option value="discretionary">Current Discretionary “Month”</option>
                                            <option value="classmate">Gift to Classmate</option>
                                            <option value="npo">Donate to NPO</option>

                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label id="addressLabel">Delivery Address</label>
                                        <div id="addressDisplay"
                                            style="font-size: 13px; color: #555; padding: 10px; background: #fff; border-radius: 8px; border: 1px dashed #ccc;">
                                            123 Main Dorm, Room 456
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Student Name</label>
                                        <input type="text" class="form-control" placeholder="Daniel Goldstein"
                                            value="{{$user->nickname}}">
                                    </div>

                                    <div class="form-group">
                                        <label>Card Number</label>
                                        <input type="text" class="form-control" placeholder="xxxx xxxx xxxx xxxx"
                                            value="{{ $bankAccount ? trim(chunk_split($bankAccount->card_number, 4, ' ')) : 'No bank account opened' }}">
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Expiry</label>
                                            <input type="text" class="form-control" placeholder="MM/YY"
                                                value="{{ $bankAccount ? \Carbon\Carbon::parse($bankAccount->card_valid)->format('m/y') : 'No bank account opened' }}">
                                        </div>
                                        <div class="form-group">
                                            <label>CVV</label>
                                            <input type="text" class="form-control" placeholder="123"
                                                value="{{ $bankAccount ? $bankAccount->card_cvv : 'No bank account opened' }}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Enter 4-Digit PIN</label>
                                        <input type="password" id="pinInput" class="form-control" maxlength="4"
                                            placeholder="• • • •">
                                    </div>
                                    <div class="text-center pt-1">
                                        <p class="text-sm text-gray-500">
                                            Forgot your PIN? <!--href="#my-accounts" id="forgotPinLink" -->
                                            <a href="javascript:void(0)" @click="viewPin = true"
                                                class="text-warm-500 hover:text-warm-600 font-semibold inline-flex items-center gap-1 hover:underline transition-colors">
                                                Click here to view your PIN
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                    </path>
                                                </svg>
                                            </a>
                                        </p>
                                    </div>
                                    @if ($hasBankAccount)
                                        <button id="payButton" class="btn-pay" onclick="processPayment()" disabled>PAY
                                            NOW</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- For Model -->
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
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-eye text-green-600">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Your PIN</h3>
                                <p class="text-sm text-gray-600">Debit Card PIN</p>
                            </div>
                        </div>
                        <div class="bg-green-50 p-3 rounded border border-green-200">
                            <div class="text-xl text-center text-green-800 font-bold mb-3">{{$bankAccount->card_pin}}</div>
                            <div class="text-md text-center text-green-800 font-semibold mb-2">📌 Important PIN Security
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
    </div>
    <select id="classmateSelect" class="form-control" style="display:none;">
        <option value="">Select Classmate</option>
        @foreach($classmates as $classmate)
            <option value="{{ $classmate->id }}">
                {{ $classmate->nickname }}
            </option>
        @endforeach
    </select>
    @php
        $storeItems = [];

        foreach ($products as $p) {
            $storeItems[] = [
                'id' => $p->id,
                'name' => $p->product_name,
                'price' => $p->price,
                'image' => url('public/uploads/products/' . $p->image),
                'quantity' => 0,
                'type' => $p->type,           // extra data
                'category' => $p->category,   // extra data
            ];
        }
    @endphp

@endsection
@push('scripts')
    <script>
        const STORE_TYPE = 'citymall';
        const INITIAL_BALANCE = {{ $lastBalance }};
        //const INITIAL_BALANCE = 4250;
        /*let storeItems = [
            { id: 1, name: "T-Shirt", price: 3, emoji: "👕", quantity: 0 },
            { id: 2, name: "Trousers", price: 10, emoji: "👖", quantity: 0 },
            { id: 3, name: "Shoes", price: 15, emoji: "👟", quantity: 0 },
            { id: 4, name: "Sweater", price: 8, emoji: "🧶", quantity: 0 },
            { id: 5, name: "Jacket", price: 15, emoji: "🧥", quantity: 0 },
            { id: 6, name: "Socks", price: 5, emoji: "🧦", quantity: 0 },
            { id: 7, name: "Boxers", price: 7, emoji: "🩳", quantity: 0 }
        ];*/
        let storeItems = @json($storeItems);
        //console.log(storeItems);
        let cart = [];
        let totalSpent = 0;
        const MAX_ITEMS = 1000;
        let currentView = 'list'; // Default View
        let cartLoadedFromServer = false;

        //Save Cart
        function saveCartToServer() {
            fetch('/cart/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ cart, store_type: STORE_TYPE })
            });
        }

        //Save Cart
        // --- VIEW TOGGLE LOGIC ---
        function switchView(view) {
            currentView = view;
            const container = document.getElementById('itemsContainer');

            // Toggle container classes
            if (view === 'grid') {
                container.classList.remove('list-view');
                container.classList.add('grid-view');
                document.getElementById('btnGrid').classList.add('active');
                document.getElementById('btnList').classList.remove('active');
            } else {
                container.classList.remove('grid-view');
                container.classList.add('list-view');
                document.getElementById('btnList').classList.add('active');
                document.getElementById('btnGrid').classList.remove('active');
            }
            renderItems(); // Re-render items to match layout
        }

        function renderItems() {
            const container = document.getElementById('itemsContainer');
            container.innerHTML = '';

            storeItems.forEach(item => {
                const itemCard = document.createElement('div');
                itemCard.className = 'item-card';

                // Different HTML structure based on view to optimize density
                if (currentView === 'list') {
                    // Feature 1: Compact List View <span class="item-emoji">${item.emoji}</span>
                    itemCard.innerHTML = `
                            <div class="item-left">
                                <img src="${item.image}"
                                    alt="${item.name}"
                                    style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                                <span class="item-name">${item.name}</span>
                            </div>
                            <div class="item-right" style="display:flex; align-items:center;">
                                <span class="item-price">${item.price} Z</span>
                                <div class="item-controls">
                                    <button class="btn-mini btn-minus" onclick="removeltem(${item.id})">-</button>
                                    <span class="qty-badge" id="qty-${item.id}">${item.quantity}</span>
                                    <button class="btn-mini btn-plus" onclick="addItem(${item.id})">+</button>
                                </div>
                            </div>
                        `;
                } else {
                    // Standard Grid View <span class="item-emoji">${item.emoji}</span>
                    itemCard.innerHTML = `
                            <img src="${item.image}"
                            alt="${item.name}"
                            style="width:100%;height:auto;object-fit:cover;border-radius:6px;">
                            <div class="item-name">${item.name}</div>
                            <div class="item-price">${item.price} Z</div>
                            <div class="item-controls" style="justify-content:center; margin-top:10px;">
                                <button class="btn-mini btn-minus" onclick="removeltem(${item.id})">-</button>
                                <span class="qty-badge" id="qty-${item.id}">${item.quantity}</span>
                                <button class="btn-mini btn-plus" onclick="addItem(${item.id})">+</button>
                            </div>
                        `;
                }
                container.appendChild(itemCard);
            });
        }

        function addItem(itemId) {
            const item = storeItems.find(i => i.id === itemId);
            const totalItems = cart.reduce((sum, i) => sum + i.quantity, 0);

            if (totalItems >= MAX_ITEMS) {
                alert(`Maximum ${MAX_ITEMS} items allowed!`);
                return;
            }

            item.quantity++;
            const cartItem = cart.find(i => i.id === itemId);
            if (cartItem) {
                cartItem.quantity++;
            } else {
                cart.push({ ...item });
            }
            updateDisplay();
        }

        function removeltem(itemId) {
            const item = storeItems.find(i => i.id === itemId);
            if (item.quantity > 0) {
                item.quantity--;
                const cartItem = cart.find(i => i.id === itemId);
                if (cartItem) {
                    cartItem.quantity--;
                    if (cartItem.quantity === 0) {
                        cart = cart.filter(i => i.id !== itemId);
                    }
                }
            }
            updateDisplay();
        }

        // Feature 2: Remove Item completely from Cart via 'X' icon
        function removeCartItemCompletely(itemId) {
            const item = storeItems.find(i => i.id === itemId);
            item.quantity = 0; // Reset store qty
            cart = cart.filter(i => i.id !== itemId); // Remove from cart array
            updateDisplay();
        }

        function updateDisplay() {
            // Update quantity badges in store
            storeItems.forEach(item => {
                const el = document.getElementById(`qty-${item.id}`);
                if (el) el.textContent = item.quantity;
            });

            totalSpent = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

            document.getElementById('sidebarBalance').textContent = INITIAL_BALANCE - totalSpent;
            document.getElementById('checkoutTotal').textContent = `${totalSpent} Z`;
            document.getElementById('itemCounter').textContent = `${totalItems}/${MAX_ITEMS}`;
            document.getElementById('payButton').disabled = cart.length === 0;

            const cartList = document.getElementById('selectedItemsList');
            if (cart.length === 0) {
                cartList.innerHTML = `<div class="empty-cart">Cart is empty</div>`;
            } else {
                cartList.innerHTML = cart.map(item => `
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <img src="${item.image}"
                                    alt="${item.name}"
                                    style="width:40px;height:40px;object-fit:cover;border-radius:6px;">

                                <div class="cart-item-text">
                                    <span class="cart-item-name">${item.name}</span>
                                    <span class="cart-item-sub">Qty: ${item.quantity}</span>
                                </div>
                            </div>
                            <div class="cart-item-actions">
                                <div class="cart-item-price">${item.price * item.quantity} Z</div>
                                <button class="btn-remove-cart" onclick="removeCartItemCompletely(${item.id})" title="Remove Item">×</button>
                            </div>
                        </div>
                    `).join('');
            }
            if (cartLoadedFromServer) {
                saveCartToServer();
            }

        }
        const npos = @json($npos);
        function updateShippingAddress() {
            const type = document.getElementById('shippingType').value;
            const display = document.getElementById('addressDisplay');

            if (type === 'self') {
                display.innerHTML = '123 Main Dorm, Room 456<br>Campus City, 12345';
            } else if (type === 'classmate') {
                //display.innerHTML = '<select class="form-control"><option>Alex Johnson</option><option>John Smith</option><option>Casey Williams</option><option>Riley Brown</option><option>Taylor Davis</option></select>';
                display.innerHTML = '';
                const select = document.getElementById('classmateSelect');
                select.style.display = 'block';
                display.appendChild(select);
            } else if (type === 'npo') {
                //display.innerHTML = '<select class="form-control"><option>Animal Shelter</option><option>Senior Care Home</option><option>Food Bank</option></select>';
                let options = '<option value="">Select NPO</option>';

                npos.forEach(npo => {
                    options += `<option value="${npo.id}">${npo.name}</option>`;
                });

                display.innerHTML = `
                    <select class="form-control" id="npoSelect">
                        ${options}
                    </select>
                `;
            } else {
                display.innerHTML = '<strong>Community Garden Project</strong><br><small>Discretionary Activity Month</small>';
            }
        }
        /*function processPayment() {
            const pin = document.getElementById('pinInput').value;
            if (pin.length !== 4) {
                alert("Please enter a 4-digit PIN");
                return;
            }

            document.getElementById('deliveryMessage').innerHTML = `
                Payment of <strong>${totalSpent} Z</strong> successful.<br>
                Remaining Balance: <strong>${INITIAL_BALANCE - totalSpent} Z</strong>
            `;
            document.getElementById('overlay').classList.add('show');

            setTimeout(() => {
                closeSuccessMessage();
                resetCart();
            }, 6000);
        }*/
        function processPayment() {
            const pin = document.getElementById('pinInput').value;

            if (pin.length !== 4) {
                alert("Please enter a 4-digit PIN");
                return;
            }

            fetch('/order/place', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    pin: pin,
                    cart: cart,
                    total: totalSpent
                })
            })
                .then(res => res.json())
                .then(res => {
                    if (!res.status) {
                        alert(res.message);
                        return;
                    }

                    document.getElementById('deliveryMessage').innerHTML = `
                        Payment of <strong>${totalSpent} Z</strong> successful.<br>
                        Remaining Balance: <strong>${res.balance} Z</strong>
                    `;

                    document.getElementById('overlay').classList.add('show');

                    resetCart(); // your existing function
                })
                .catch(() => {
                    alert('Payment error');
                });
        }

        function closeSuccessMessage() {
            document.getElementById('overlay').classList.remove('show');
        }

        function resetCart() {
            cart = [];
            storeItems.forEach(i => i.quantity = 0);
            document.getElementById('pinInput').value = '';
            updateDisplay();
            renderItems(); // Re-render store to clear qty badges
            fetch('/cart/clear?store_type=citymall', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

        }

        // Initialize
        renderItems();
        updateDisplay();

        document.addEventListener('DOMContentLoaded', () => {
            fetch('/cart/get?store_type=citymall')
                .then(res => res.json())
                .then(data => {
                    if (!data.cart || data.cart.length === 0) {
                        cartLoadedFromServer = true;
                        return;
                    }

                    cart = data.cart;

                    storeItems.forEach(item => {
                        const saved = cart.find(c => c.id === item.id);
                        item.quantity = saved ? saved.quantity : 0;
                    });

                    cartLoadedFromServer = true;
                    updateDisplay();
                    renderItems();
                });
        });


    </script>
@endpush