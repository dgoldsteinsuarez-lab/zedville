<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="author" content="text/html" />

    <meta name="theme-color" content="#00A47D">
    <meta name="msapplication-navbutton-color" content="#00A47D">
    <meta name="apple-mobile-web-app-status-bar-style" content="#00A47D">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Zedville - Financial Skills for Your Future</title>
    <link sizes='57x57' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='114x114' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='72x72' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='144x144' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='60x60' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='120x120' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='76x76' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='152x152' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='180x180' href='{{ asset('asset/front/images/favicon.png') }}' rel='apple-touch-icon'>
    <link sizes='192x192' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <link sizes='160x160' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <link sizes='96x96' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <link sizes='16x16' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <link sizes='32x32' href='{{ asset('asset/front/images/favicon.png') }}' rel='icon' type='image/png'>
    <!-- Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.4/dist/tailwind.min.css" rel="stylesheet">

    <!-- Tailwind Typography plugin via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/@tailwindcss/typography@0.4.1/dist/typography.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <link href="{{ asset('asset/front/css/theme_plugin.css') }}?ver={{ rand(111, 999) }}" rel="stylesheet" />
    <link href="{{ asset('asset/front/css/theme_style.css') }}?ver={{ rand(111, 999) }}" rel="stylesheet" />
    <link href="{{ asset('asset/front/css/surveys.css') }}?ver={{ rand(111, 999) }}" rel="stylesheet" />
    <link href="{{ asset('asset/front/css/citizen-activation.css') }}?ver={{ rand(111, 999) }}" rel="stylesheet" />
     {{-- REQUIRED FOR PUSH --}}
    @stack('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            content: [
                "./index.html",
                "./src/**/*.{js,ts,jsx,tsx}",
            ],
            theme: {
                extend: {
                    colors: {
                        themegreen: '#00A47D',
                        themeyellow: '#FFF5D4',
                    },
                },
            },
        };
    </script>
    <style>[x-cloak] { display: none !important; }

    /* ── Avatar dropdown — aligned to the app design system ── */
    .avatarDropdown,
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 {
        border-radius: 8px !important;
        border: 1px solid #D2DDDB !important;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12) !important;
    }
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 > .bg-\[\#56F4CF\]\/10 {
        background-color: #EEF9F5 !important;
        border-bottom: 1px solid #D2DDDB !important;
    }
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 h3 {
        font-family: 'Manrope', sans-serif;
        font-weight: 700;
        color: #222;
    }
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 .bg-\[\#56F4CF\]\/10 p,
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 .bg-\[\#56F4CF\]\/10 .mt-3 {
        color: #5C5C5C !important;
    }
    /* section labels */
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 h4 {
        color: #5C5C5C !important;
        letter-spacing: 0.06em;
        font-family: 'Manrope', sans-serif;
    }
    /* rows */
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 a.hover\:bg-gray-50:hover {
        background-color: #EEF9F5 !important;
    }
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 a .bg-gray-100 {
        background-color: #EEF9F5 !important;
        border: 1px solid #B7E3D6 !important;
    }
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 a .bg-gray-100 svg {
        color: #016950 !important;
    }
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 a p.text-gray-900 {
        color: #222 !important;
    }
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 a p.text-gray-500 {
        color: #5C5C5C !important;
    }
    /* footer + sign out */
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 .border-t.border-gray-200 {
        border-top-color: #D2DDDB !important;
    }
    .relative > .absolute.right-0.mt-3.bg-white.rounded-xl.shadow-2xl.w-80 button.bg-red-50 {
        border-radius: 30px !important;
        border: 1px solid #EFC7CB !important;
        font-weight: 600 !important;
    }
    </style>
</head>

<body>
<div class="userAdmin">
    <div class="flex h-screen overflow-y-hidden" x-data="setup()">
        {{-- Content Pre Loader (re-enabled). Show/hide handled in themeScript.js --}}
        <div id="pageLoader" class="fixed inset-0 z-50 flex items-center justify-center text-white bg-white bg-opacity-90 opacity-100 transition-opacity duration-300 ease-in-out" style="backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px)">
            <div class="themeLoader">
                <span class="ant-spin-dot">
                    <i></i>
                    <i></i>
                    <i></i>
                    <i></i>
                </span>
            </div>
        </div>
       @include('layouts.profile-sidebar')
    
      <div class="flex flex-col flex-1 h-full">
        <header class="flex-shrink-0 hidden">
            <div class="flex items-center justify-between px-2">
                <!-- Navbar left -->
                <div class="flex items-center space-x-3 relative">
                    <button @click="toggleSidbarMenu()" class="p-0 absolute left-[-22px] top-[20px] w-7 h-7 text-[#667085] flex items-center justify-center transition duration-300 ease-in-out">
                        <svg class="w-[18px] h-[18px]" :class="{ 'transform transition-transform -scale-x-100': isSidebarOpen }" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M9 3V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <header class="flex-shrink-0 border-b bg-white">
            <div class="flex items-center justify-between py-2 px-4 lg:px-8" x-data="{ changeImageModal: false, notificationDrawer: false }">
                <!-- Navbar left -->
                <div class="flex items-center space-x-3">
                    <!-- Toggle sidebar button -->
                    <button @click="toggleSidbarMenu()" class="w-7 h-7 text-[#667085] flex items-center justify-center transition duration-300 ease-in-out">
                        <svg class="w-[18px] h-[18px]" :class="{ 'transform transition-transform -scale-x-100': isSidebarOpen }" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M9 3V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="sidebarLogo lg:hidden">
                        <img src="{{ asset('asset/front/images/logo.svg')}}" class="w-[100px]" alt="">
                    </div>
                </div>
                @php
                use App\Models\Avatar;
                 $user = Auth::user();
                 $avatar = Avatar::where('status', 1)->get();
                $selectedAvatar = $avatar->firstWhere('id', $user->avatar);
                use App\Models\BankAccount;
                $bankAccounts = BankAccount::where('student_id', auth()->id())->get();
                $studentClass = $user->gradeRelation;
                $languageLocked = $studentClass && $studentClass->language_mode === 'locked';
                $currentLocale = app()->getLocale();
                @endphp
                <!-- Navbar right -->
                <div class="relative flex items-center space-x-3">
                    <!-- Language switcher (bugs.md, 2026-10-21) -->
                    @if ($languageLocked)
                        <span class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs text-gray-500" title="{{ __('messages.language_locked') }}">
                            &#128274; {{ strtoupper($currentLocale) }}
                        </span>
                    @else
                        <div class="flex items-center border rounded-lg overflow-hidden text-xs font-semibold">
                            @foreach (['en' => 'EN', 'es' => 'ES'] as $code => $label)
                                <form action="{{ route('language.switch') }}" method="post" class="m-0">
                                    @csrf
                                    <input type="hidden" name="language" value="{{ $code }}">
                                    <button type="submit"
                                        class="px-2.5 py-1.5 {{ $currentLocale === $code ? 'bg-themegreen text-white' : 'text-gray-500 hover:bg-themegreen/10' }}"
                                        @if ($currentLocale === $code) disabled @endif>
                                        {{ $label }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                    <!-- Notification -->
                    <div class="relative">
                        <button @click="notificationDrawer = true" class="relative px-3 py-2.5 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-themegreen/10 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                            </svg>
                            @if(($notificationUnreadCount ?? 0) > 0)
                                <span id="bellUnreadBadge" class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[8px] rounded-full flex items-center justify-center font-medium">{{ $notificationUnreadCount > 9 ? '9+' : $notificationUnreadCount }}</span>
                            @endif
                        </button>
                    </div>
                    <!-- avatar button -->
                    <div class="relative" x-data="{ isOpen: false }">
                        <button @click="isOpen = !isOpen" class="flex items-center space-x-2 focus:outline-none hover:bg-themegreen/10 transition-colors px-3 py-1 rounded-md">
                            <div class="bg-gray-200 rounded-full focus:outline-none focus:ring">
                                <img src="{{ asset('asset/front/images/' . ($selectedAvatar->name ?? 'default-avatar.png')) }}" class="w-8 h-8 rounded-full object-cover" alt="">
                            </div>
                            <span class="text-md font-semibold">{{ $user->nickname }}</span>
                        </button>

                        <!-- Dropdown card -->
                       <div
    x-cloak
    @click.away="isOpen = false"
    @keydown.escape="isOpen = false"
    x-show.transition.opacity="isOpen"
    class="absolute right-0 mt-3 transform bg-white rounded-xl shadow-2xl w-80 min-w-max z-10 overflow-hidden"
>
                            <div class="bg-[#56F4CF]/10 p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                        <img src="{{ asset('asset/front/images/' . ($selectedAvatar->name ?? 'default-avatar.png')) }}" class="w-12 h-12 rounded-full object-cover" alt="User">
                                    </div>
                                    <div>
                                        <h3 class="font-semibold">{{ $user->nickname }}</h3>
                                        <p class="text-sm">{{ $user->email }}</p>
                                        <p class="text-xs">{{ $bankAccounts->isNotEmpty() ? '****' . substr($bankAccounts->first()->primary_savings_account_amount, -4) : '' }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-xs">Last login: {{ Auth::user()->loginTime ? \Carbon\Carbon::parse(Auth::user()->loginTime)->calendar() : 'Never' }}</div>
                            </div>
                            <div class="max-h-96 overflow-y-auto px-4 py-2">
                                <div>
                                    <div class="py-2">
                                        <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Account</h4>
                                    </div>
                                    <div class="py-2">
                                        <a href="{{ route('profile.edit') }}" class="w-full flex items-center space-x-3 px-4 py-3 hover:bg-gray-50 transition-colors text-left rounded-md">
                                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user text-gray-600">
                                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900 text-sm">Personal Information</p>
                                                <p class="text-xs text-gray-500">Update your personal details</p>
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-gray-400">
                                                <path d="m9 18 6-6-6-6"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <div class="py-2">
                                        <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Preferences</h4>
                                    </div>
                                    <div class="py-2">
                                        <a href="#" class="w-full flex items-center space-x-3 px-4 py-3 hover:bg-gray-50 transition-colors text-left rounded-md">
                                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings text-gray-600">
                                                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900 text-sm">App Settings</p>
                                                <p class="text-xs text-gray-500">Language, theme, and display</p>
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-gray-400">
                                                <path d="m9 18 6-6-6-6"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <div class="py-2">
                                        <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Support</h4>
                                    </div>
                                    <div class="py-2">
                                        <a href="#" class="w-full flex items-center space-x-3 px-4 py-3 hover:bg-gray-50 transition-colors text-left rounded-md">
                                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download text-gray-600">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <polyline points="7 10 12 15 17 10"></polyline>
                                                    <line x1="12" x2="12" y1="15" y2="3"></line>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900 text-sm">Download App</p>
                                                <p class="text-xs text-gray-500">Not available now</p>
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right text-gray-400">
                                                <path d="m9 18 6-6-6-6"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 p-4">
                                <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                    <button class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out" onclick="event.preventDefault(); this.closest('form').submit();">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                            <polyline points="16 17 21 12 16 7"></polyline>
                                            <line x1="21" x2="9" y1="12" y2="12"></line>
                                        </svg>
                                        <span>Sign Out</span>
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- Notification drawer -->
                <div
                    x-show="notificationDrawer"
                    class="fixed w-full h-full z-10 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
                    @keydown.escape.window="notificationDrawer = false" style="display: none;">
                    <!-- Backdrop: fades in/out -->
                    <div
                        x-show="notificationDrawer"
                        x-transition:enter="transition-opacity ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-black bg-opacity-50" @click="notificationDrawer = false"></div>

                    <!-- Modal Box: slides in from the right -->
                    <div
                        x-show="notificationDrawer"
                        x-transition:enter="transition transform ease-out duration-300"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition transform ease-in duration-200"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full"
                        class="modalDilog fullHeight rightside max-w-[600px]">
                        <div class="modalContent h-full bg-white rounded-lg z-100">
                            <div class="flex justify-between items-center w-full">
                                <div class="p-4 border-b border-[#D2DDDB] flex items-center justify-between w-full">
                                    <div class="flex items-center space-x-3">
                                        <h2 class="text-lg font-semibold text-gray-900">Notifications</h2>
                                        @if(($notificationUnreadCount ?? 0) > 0)
                                            <span id="notificationUnreadPill" class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-medium">{{ $notificationUnreadCount }} new</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button id="notificationMarkAllRead" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Mark all read</button>
                                        <button @click="notificationDrawer = false" class="text-gray-500 hover:opacity-80 focus:outline-none transition">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                                <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="bodyContent scrollHeight overflow-y-auto">
                                <div class="themeTabspills">
                                    <div class="w-full">
                                        <!-- <div class="flex px-4 mt-4 menus sinlgelineTabs overborderleftright border-b border-[#D2DDDB]">
                                            <button class="tabitems notitab-button active" data-tab="notitab1">
                                                All Notifications <span class="numbers">8</span>
                                            </button>
                                            <button class="tabitems notitab-button" data-tab="notitab2">
                                                Transactions <span class="numbers">2</span>
                                            </button>
                                            <button class="tabitems notitab-button" data-tab="notitab3">
                                                Security <span class="numbers">2</span>
                                            </button>
                                            <button class="tabitems notitab-button" data-tab="notitab4"> Transfers <span class="numbers">2</span>
                                            </button>
                                            <button class="tabitems notitab-button" data-tab="notitab5">
                                                Account <span class="numbers">2</span>
                                            </button>
                                        </div> -->
                                        <div class="tailCard notiListsSec mt-4">
                                            <div id="notitab1" class="notitab-content active">
                                                <div class="notificationLists divide-y divide-gray-100">
                                                    @forelse($notificationFeed ?? [] as $n)
                                                        @php
                                                            $colorMap = [
                                                                'blue'   => ['border' => 'border-l-blue-500',   'iconBg' => 'bg-blue-100',   'iconText' => 'text-blue-600'],
                                                                'green'  => ['border' => 'border-l-green-500',  'iconBg' => 'bg-green-100',  'iconText' => 'text-green-600'],
                                                                'purple' => ['border' => 'border-l-purple-500', 'iconBg' => 'bg-purple-100', 'iconText' => 'text-purple-600'],
                                                                'orange' => ['border' => 'border-l-orange-500', 'iconBg' => 'bg-orange-100', 'iconText' => 'text-orange-600'],
                                                                'amber'  => ['border' => 'border-l-amber-500',  'iconBg' => 'bg-amber-100',  'iconText' => 'text-amber-600'],
                                                                'red'    => ['border' => 'border-l-red-500',    'iconBg' => 'bg-red-100',    'iconText' => 'text-red-600'],
                                                            ];
                                                            $c = $colorMap[$n->color] ?? $colorMap['blue'];
                                                            $isUnread = is_null($n->read_at);
                                                        @endphp
                                                        <div id="notification-{{ $n->id }}"
                                                             class="p-4 border-l-4 transition-colors {{ $c['border'] }} notificatItem {{ $isUnread ? 'noread bg-red-50 hover:bg-red-100' : 'bg-white hover:bg-gray-50' }}">
                                                            <div class="flex items-start space-x-3">
                                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $c['iconText'] }} {{ $c['iconBg'] }}">
                                                                    @include('partials.notification-icon', ['icon' => $n->icon])
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="flex items-start justify-between">
                                                                        <div class="flex-1">
                                                                            <h3 class="text-sm font-medium {{ $isUnread ? 'text-gray-900' : 'text-gray-700' }}">{{ $n->title }}</h3>
                                                                            @if($n->body)
                                                                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $n->body }}</p>
                                                                            @endif
                                                                            <p class="text-xs text-gray-500 mt-2">{{ $n->created_at->diffForHumans() }}</p>
                                                                        </div>
                                                                        <div class="flex items-center space-x-1 ml-2">
                                                                            @if($isUnread)
                                                                                <button type="button" class="notificationMarkRead p-1 text-gray-400 hover:text-blue-600 rounded" data-id="{{ $n->id }}" title="Mark as read">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check">
                                                                                        <path d="M20 6 9 17l-5-5"></path>
                                                                                    </svg>
                                                                                </button>
                                                                            @endif
                                                                            <button type="button" class="notificationDelete p-1 text-gray-400 hover:text-red-600 rounded" data-id="{{ $n->id }}" title="Delete notification">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2">
                                                                                    <path d="M3 6h18"></path>
                                                                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                                                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                                                                    <line x1="10" x2="10" y1="11" y2="17"></line>
                                                                                    <line x1="14" x2="14" y1="11" y2="17"></line>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="p-8 text-center text-sm text-gray-400">
                                                            No notifications yet. Real activity from your account will show up here.
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 border-t border-[#D2DDDB]">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                                        <div class="flex items-center space-x-1">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div><span>Read</span>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <div class="w-2 h-2 bg-red-500 rounded-full"></div><span>Unread</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('profile.edit') }}#tab7" class="flex items-center space-x-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings">
                                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <span>Settings</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="bg-white flex-1 max-h-full py-10 px-8 overflow-hidden overflow-y-auto">

            <!-- Toast Message  -->
             <!-- <div class="fixed top-5 right-5 z-50 transition transform duration-300 ease-out translate-y-0 opacity-100" > -->
                <!-- <div class="flex items-center w-full justify-between max-w-xs p-2 mb-2 text-green-700 bg-green-100 rounded-lg shadow" role="alert">
                    <svg class="w-5 h-5 mr-2 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium mr-auto">This theme_style</span>
                    <button type="button" class="ml-2" onclick="this.parentElement.remove();">
                        &times;
                    </button>
                </div> -->
                <!-- Success -->
                 @if(session('success'))
               <div class="fixed inset-0 alert-box flex items-center justify-center z-50 transition transform duration-300 ease-out translate-y-0 opacity-100 backdrop-blur-sm">
                    <div class="flex items-center w-full justify-between max-w-md p-4 mb-2  bg-green-100 border  border-green-500 rounded-lg shadow-md" role="alert">
                        <div class="flex items-start w-full">
                            <!-- ✅ Left icon -->
                            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-green-100 rounded-full mt-0.5 mr-3">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z"
                                    clip-rule="evenodd" />
                                </svg>
                            </div>
                            <!-- 🧾 Middle content -->
                            <div class="flex-1">
                                <h3 class="text-gray-900 font-semibold text-lg">Successful</h3>
                                <p class="text-gray-600 text-lg mt-0.5">
                                 {!! session('success') !!}
                                </p>
                            </div>
                            <!-- ❌ Right close button -->
                            <button
                                onclick="this.closest('.alert-box').remove();"
                                style="font-size:24px"
                                type="button"
                                class="ml-3 text-gray-700 hover:text-gray-600  leading-none">
                            ×
                            </button>
                        </div>
                    </div>
                    </div>
                 @endif
                <!-- Warning -->
                <div class="flex items-center w-full justify-between max-w-xs p-2 mb-2 text-yellow-700 bg-yellow-100 rounded-lg shadow" role="alert" style="display:none">
                    <svg class="w-5 h-5 mr-2 text-red-700" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.366-.446.972-.446 1.338 0l6.857 8.333c.395.48.032 1.2-.669 1.2H3.217c-.701 0-1.064-.72-.669-1.2l6.857-8.333zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-.993.883L9 6v4a1 1 0 001.993.117L11 10V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium mr-auto">Info toast message!</span>
                    <button type="button" class="ml-2" onclick="this.parentElement.remove();">
                        &times;
                    </button>
                </div>
                <!-- Danger Error -->
                  @if(session('error'))
                <!-- <div class="flex items-center w-full justify-between max-w-xs p-2 mb-2 text-red-700 bg-red-100 rounded-lg shadow" role="alert">
                    <svg class="w-5 h-5 mr-2 text-red-700" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.366-.446.972-.446 1.338 0l6.857 8.333c.395.48.032 1.2-.669 1.2H3.217c-.701 0-1.064-.72-.669-1.2l6.857-8.333zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-.993.883L9 6v4a1 1 0 001.993.117L11 10V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium mr-auto">{{ session('error') }}</span>
                    <button type="button" class="ml-2" onclick="this.parentElement.remove();">
                        &times;
                    </button>
                </div> -->
                 <div class="fixed inset-0 alert-box flex items-center justify-center z-50 transition transform duration-300 ease-out translate-y-0 opacity-100 backdrop-blur-sm">
                    <div class="flex items-center w-full justify-between max-w-md p-4 mb-2  bg-red-100 border  border-red-500 rounded-lg shadow-md" role="alert">
                        <div class="flex items-start w-full">
                            <!-- ✅ Left icon -->
                            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-red-100 rounded-full mt-0.5 mr-3">
                                <svg class="w-6 h-6 mr-2 text-red-700" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.366-.446.972-.446 1.338 0l6.857 8.333c.395.48.032 1.2-.669 1.2H3.217c-.701 0-1.064-.72-.669-1.2l6.857-8.333zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-.993.883L9 6v4a1 1 0 001.993.117L11 10V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                            </div>
                            <!-- 🧾 Middle content -->
                            <div class="flex-1">
                                <h3 class="text-red-900 font-semibold text-lg">Error</h3>
                                <p class="text-gray-600 text-lg mt-0.5">
                                {!! session('error') !!}
                                </p>
                            </div>
                            <!-- ❌ Right close button -->
                            <button
                                onclick="this.closest('.alert-box').remove();"
                                style="font-size:24px"
                                type="button"
                                class="ml-3 text-gray-700 hover:text-gray-600  leading-none">
                            ×
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                @if(session('status') === 'password-updated')
                    <!-- <div class="flex items-center w-full justify-between max-w-xs p-2 mb-2 text-green-700 bg-green-100 rounded-lg shadow" role="alert">
                        <svg class="w-5 h-5 mr-2 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium mr-auto"> Profile updated successfully.</span>
                        <button type="button" class="ml-2" onclick="this.parentElement.remove();">
                            &times;
                        </button>
                    </div> -->
                    <div class="fixed inset-0 alert-box flex items-center justify-center z-50 transition transform duration-300 ease-out translate-y-0 opacity-100 backdrop-blur-sm">
                        <div class="flex items-center w-full justify-between max-w-md p-4 mb-2  bg-green-100 border  border-green-500 rounded-lg shadow-md" role="alert">
                            <div class="flex items-start w-full">
                                <!-- ✅ Left icon -->
                                <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-green-100 rounded-full mt-0.5 mr-3">
                                    <svg class="w-6 h-6 mr-2 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" clip-rule="evenodd" />
                                                </svg>
                                </div>
                                <!-- 🧾 Middle content -->
                                <div class="flex-1">
                                    <h3 class="text-red-900 font-semibold text-lg">Successfull</h3>
                                    <p class="text-gray-600 text-lg mt-0.5">
                                    Profile updated successfully.
                                    </p>
                                </div>
                                <!-- ❌ Right close button -->
                                <button
                                    onclick="this.closest('.alert-box').remove();"
                                    style="font-size:24px"
                                    type="button"
                                    class="ml-3 text-gray-700 hover:text-gray-600  leading-none">
                                ×
                                </button>
                            </div>
                        </div>
                        </div>
                @endif
                 @if(session('error') === 'password-updated')
                <!-- <div class="flex items-center w-full justify-between max-w-xs p-2 mb-2 text-red-700 bg-red-100 rounded-lg shadow" role="alert">
                    <svg class="w-5 h-5 mr-2 text-red-700" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.366-.446.972-.446 1.338 0l6.857 8.333c.395.48.032 1.2-.669 1.2H3.217c-.701 0-1.064-.72-.669-1.2l6.857-8.333zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-.993.883L9 6v4a1 1 0 001.993.117L11 10V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium mr-auto">Password not updated.</span>
                    <button type="button" class="ml-2" onclick="this.parentElement.remove();">
                        &times;
                    </button>
                </div> -->
                <div class="fixed inset-0 alert-box flex items-center justify-center z-50 transition transform duration-300 ease-out translate-y-0 opacity-100 backdrop-blur-sm">
                    <div class="flex items-center w-full justify-between max-w-md p-4 mb-2  bg-red-100 border  border-red-500 rounded-lg shadow-md" role="alert">
                        <div class="flex items-start w-full">
                            <!-- ✅ Left icon -->
                            <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-red-100 rounded-full mt-0.5 mr-3">
                                <svg class="w-6 h-6 mr-2 text-red-700" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.366-.446.972-.446 1.338 0l6.857 8.333c.395.48.032 1.2-.669 1.2H3.217c-.701 0-1.064-.72-.669-1.2l6.857-8.333zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-.993.883L9 6v4a1 1 0 001.993.117L11 10V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                            </div>
                            <!-- 🧾 Middle content -->
                            <div class="flex-1">
                                <h3 class="text-red-900 font-semibold text-lg">Error</h3>
                                <p class="text-gray-600 text-lg mt-0.5">
                                Password not updated.
                                </p>
                            </div>
                            <!-- ❌ Right close button -->
                            <button
                                onclick="this.closest('.alert-box').remove();"
                                style="font-size:24px"
                                type="button"
                                class="ml-3 text-gray-700 hover:text-gray-600  leading-none">
                            ×
                            </button>
                        </div>
                    </div>
                    </div>
                @endif
            <!-- </div> -->
            <!-- Toast Message End  -->

            @if (auth()->check() && session()->has('impersonator_id'))
                <div class="flex justify-between items-center flex-wrap gap-2 mb-6 p-3 rounded-lg" style="background:#FEF3C7; border:1px solid #FCD34D;">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-white px-3 py-1 rounded-full" style="background:#D97706;">VIEWING AS</span>
                        <span class="text-sm text-gray-600">{{ auth()->user()->name }} ({{ auth()->user()->role_name }}) - read-only, nothing you do here is saved</span>
                    </div>
                    <form action="{{ url('/impersonate/stop') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="text-sm font-medium px-3 py-1.5 rounded-md bg-white border" style="border-color:#D97706; color:#D97706;">
                            Return to my account
                        </button>
                    </form>
                </div>
            @endif

            @if (auth()->check() && auth()->user()->isTutor())
                <div class="flex justify-between items-center flex-wrap gap-2 mb-6 p-3 rounded-lg" style="background:#E8F5EE; border:1px solid #A7E3C3;">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-white px-3 py-1 rounded-full" style="background:#1B6B3A;">STUDENT MODE</span>
                        <span class="text-sm text-gray-600">You're viewing this as a Tutor — this is your own citizen account.</span>
                    </div>
                    <a href="{{ url('admin/my-students') }}" class="text-sm font-medium px-3 py-1.5 rounded-md bg-white border" style="border-color:#1B6B3A; color:#1B6B3A;">
                        &larr; Switch to Tutor Mode
                    </a>
                </div>
            @endif

            @yield('content')

        </main> <!-- Main End -->
        <!-- Footer -->
        <!-- <footer class="flex items-center justify-between flex-shrink-0 p-4">
          
        </footer> -->
        <!-- Mood Meater -->
         @php
            $hasMoodToday = \App\Models\MoodLog::where('user_id', auth()->id())
                ->whereDate('created_at', now()->toDateString())
                ->exists();
            $hasQuizToday = \App\Models\LoginQuizAttempt::where('user_id', auth()->id())
                ->whereDate('created_at', now()->toDateString())
                ->exists();
        @endphp
        @if (!$hasMoodToday)
        <div x-data="moodPopup()">
            <div
                x-show="moodMeater"
                x-transition.opacity
                class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
                @keydown.escape.window="moodMeater = false" style="display: none;">
                <!-- Backdrop -->
                <!-- <div class="fixed inset-0 bg-black bg-opacity-50" @click="moodMeater = false"></div> -->
                 <div class="fixed inset-0 bg-black bg-opacity-50"></div>

                <!-- Modal Box -->
                <div class="modalDilog max-w-[620px]">
                    <div class="modalContent bg-white py-8 px-14 rounded-lg z-100 border border-color-[#D2DDDB]">
                       
                        <div class="bodyContent">
                            <form @submit.prevent="submitMoodForm" data-save-url="{{ route('profile.saveMode') }}">
                                @csrf
                                <div class="heading text-center">
                                    <h4 class="text-xl xl:text-[28px] 2xl:text-[32px] font-semibold text-black mb-2">Mood Meter</h4>
                                    <h5 class="text-md xl:text-base font-semibold text-black mb-4">How are you feeling today?</h5>
                                    <p class="text-xs max-w-[450px] w-full mx-auto">Your mood selection will help us understand and this practice will help you build your emotional vocabulary and enhance your emotional wellness.</p>
                                </div>
                                <div class="moodemoji grid grid-cols-2 grid-rows-2 gap-12 mt-8 mb-10">
                                    <div class="emojigroup egroup1 grid grid-cols-2 grid-rows-2 gap-2">
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj1" value="furious">
                                            <label for="mdemj1">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji1.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#D10500]">Furious</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj4" value="angry">
                                            <label for="mdemj4">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji4.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#D10500]">Angry</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj2" value="nervous">
                                            <label for="mdemj2">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji2.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#D10500]">Nervous</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj3" value="worried">
                                            <label for="mdemj3">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji3.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#D10500]">Worried</div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                    </div>
                                    <div class="emojigroup egroup2 grid grid-cols-2 grid-rows-2 gap-2">
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj5" value="excited">
                                            <label for="mdemj5">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji5.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#FFB100]">Excited</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj7" value="ecstatic">
                                            <label for="mdemj7">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji7.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#FFB100]">Ecstatic</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj6" value="happy">
                                            <label for="mdemj6">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji6.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#FFB100]">Happy</div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj8" value="joyful">
                                            <label for="mdemj8">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji8.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#FFB100]">Joyful</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="emojigroup egroup3 grid grid-cols-2 grid-rows-2 gap-2">
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj9" value="lonely">
                                            <label for="mdemj9">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji9.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#023181]">Lonely</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj10" value="sad">
                                            <label for="mdemj10">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji10.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#023181]">Sad</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj11" value="hopeless">
                                            <label for="mdemj11">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji11.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#023181]">Hopeless</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj12" value="disappointed">
                                            <label for="mdemj12">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji12.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#023181]">Disappointed</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="emojigroup egroup4 grid grid-cols-2 grid-rows-2 gap-2">
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj15" value="calm">
                                            <label for="mdemj15">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji15.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#279348]">Calm</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj16" value="serene">
                                            <label for="mdemj16">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji16.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#279348]">Serene</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj13" value="at_ease">
                                            <label for="mdemj13">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji13.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#279348]">At Ease</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj14" value="content">
                                            <label for="mdemj14">
                                                <div class="mdinner">
                                                    <img src="{{ asset('asset/front/images/emoji/mood-emoji14.svg')}}" alt="">
                                                    <div class="heading text-xs font-semobold text-[#279348]">Content</div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="text-center">
                                    <!-- <button @click="moodMeater = false" class="themeBtn">
                                        Save
                                    </button> -->
                                    <button type="submit" class="themeBtn">
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
        @endif
       @if(!$hasQuizToday)
        <div x-data="quizPopup()" x-init="init()">
            <div
                x-show="open"
                x-transition.opacity
                class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
                style="display:none;">
                <div class="fixed inset-0 bg-black bg-opacity-50"></div>
                <div class="modalDilog max-w-[560px]">
                    <div class="modalContent bg-white py-8 px-10 rounded-lg z-100 border border-[#D2DDDB]">

                        <!-- Header -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-themegreen/10 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00A47D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                                        <path d="M12 17h.01"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">Daily Quiz</h4>
                                    <p class="text-xs text-gray-500">Test your financial knowledge</p>
                                </div>
                            </div>
                            
                        </div>

                        <!-- Loading -->
                        <div x-show="loading" class="text-center py-10 text-gray-400">
                            <svg class="animate-spin mx-auto mb-3 w-8 h-8 text-themegreen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            <p class="text-sm">Loading question...</p>
                        </div>

                        <!-- Question -->
                        <div x-show="!loading && !submitted">
                            <p class="text-sm font-semibold text-gray-800 mb-5 leading-relaxed" x-text="question"></p>
                            <div class="space-y-3">
                                <template x-for="option in options" :key="option.id">
                                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-themegreen hover:bg-themegreen/5 transition-all"
                                        :class="selectedOptionId === option.id ? 'border-themegreen bg-themegreen/10' : ''"
                                        x-show="option.text && option.text.trim() !== ''">
                                        <input type="radio" name="quiz_answer" :value="option.id"
                                            @change="selectedOptionId = option.id"
                                            class="accent-themegreen w-4 h-4 flex-shrink-0">
                                        <span class="text-sm text-gray-700" x-text="option.text"></span>
                                    </label>
                                </template>
                            </div>
                            <button @click="submitAnswer()"
                                :disabled="!selectedOptionId || submitting"
                                class="mt-6 w-full py-3 rounded-lg bg-themegreen text-white font-semibold text-sm hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!submitting">Submit Answer</span>
                                <span x-show="submitting">Submitting...</span>
                            </button>
                        </div>

                        <!-- Result -->
                        <div x-show="submitted" class="text-center py-4">
                            <!-- Correct -->
                            <div x-show="isCorrect">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#00A47D" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-green-700 mb-2">Correct! 🎉</h3>
                                <p class="text-sm text-gray-500">Great job! You got it right.</p>
                            </div> 
                            <!-- Wrong -->
                             <div x-show="!isCorrect">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 6 6 18M6 6l12 12"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-red-600 mb-2">Not quite! 💡</h3>
                                <p class="text-sm text-gray-500 mb-1">The correct answer was:</p>
                                <p class="text-sm font-bold text-themegreen" x-text="correctText"></p>
                            </div> 
                            <button @click="open = false"
                                class="mt-6 w-full py-3 rounded-lg bg-themegreen text-white font-semibold text-sm hover:bg-green-700 transition">
                                Done
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @endif
      </div>

    </div>
</div>
    
    
    <script src="{{ asset('asset/front/js/themePlugin.js') }}?ver={{ rand(111, 999) }}" type="text/javascript"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="{{ asset('asset/front/js/themeScript.js') }}?ver={{ rand(111, 999) }}" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('asset/front/js/surveys.js') }}?ver={{ rand(111, 999) }}" type="text/javascript"></script>
     @stack('scripts')
     @if (!$hasMoodToday)
    <script>
        window.__moodInitDone = false;
    window.moodPopup = function moodPopup() {
    return {
        moodMeater: true,
        isSubmitting: false,  // 🔐 Guard flag


        submitMoodForm() {
            if (this.isSubmitting) return;  // Prevent re-entry
            this.isSubmitting = true;       // Lock
            console.log("submitMoodForm triggered");

            const selected = document.querySelector('input[name="modeemoji"]:checked');
            if (!selected) {
                alert("Please select a mood.");
                this.isSubmitting = false;
                return;
            }

            const moodValue = selected.value;
            const form = document.querySelector('form[data-save-url]');
            const csrfInput = form.querySelector('input[name="_token"]');
            const csrfToken = csrfInput ? csrfInput.value : null;
            const saveUrl = form.getAttribute('data-save-url');

            if (!csrfToken || !saveUrl) {
                alert('CSRF token or save URL not found!');
                this.isSubmitting = false;
                return;
            }

            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ mood: moodValue })
            })
            .then(async res => {
                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    console.log('Saved:', data);
                    this.setMidnightCookie();
                    this.moodMeater = false;
                    window.__moodDone = true; // ← add this line
                } catch (e) {
                    console.error('Invalid JSON:', text);
                    alert("Server error, please try again.");
                }
            })
            .catch(err => {
                console.error('Failed:', err);
                alert("Failed to save mood.");
            })
            .finally(() => {
                this.isSubmitting = false;  // Unlock after done
            });
        },

        setMidnightCookie() {
            const midnight = new Date();
            midnight.setHours(24, 0, 0, 0);
            const expires = "expires=" + midnight.toUTCString();
            document.cookie = "moodSubmitted=true; " + expires + "; path=/";
        }
    };
}


    
    </script>
@endif
@if(!$hasQuizToday)
<script>
window.quizPopup = function () {
    return {
        open: false,
        loading: true,
        submitted: false,
        submitting: false,
        quizId: null,
        question: '',
        options: [],
        selectedOptionId: null,
        isCorrect: false,
        correctText: '',

        init() {
            // 🔥 GLOBAL LOCK (prevents multiple init across DOM)
            if (window.__quizInitialized) return;
            window.__quizInitialized = true;

            @if(!$hasMoodToday)
                window.__moodDone = false;

                const checkMood = setInterval(() => {
                    if (window.__moodDone === true) {
                        clearInterval(checkMood);
                        setTimeout(() => this.loadQuestion(), 700);
                    }
                }, 300);

            @else
                setTimeout(() => this.loadQuestion(), 800);
            @endif
        },

        loadQuestion() {
            // 🔥 GLOBAL API LOCK (prevents multiple fetch)
            if (window.__quizLoaded) return;
            window.__quizLoaded = true;

            fetch('{{ route("login.quiz.today") }}')
                .then(r => r.json())
                .then(data => {
                    this.loading = false;

                    if (!data.show) return;

                    this.quizId   = data.id;
                    this.question = data.question;

                    this.options = (data.options || []).filter(o => o.text && o.text.trim() !== '');

                    this.open = true;
                })
                .catch(() => {
                    this.loading = false;
                });
        },

        submitAnswer() {
            if (!this.selectedOptionId || this.submitting) return;

            this.submitting = true;

            fetch('{{ route("login.quiz.submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    question_id: this.quizId,
                    option_id: this.selectedOptionId
                })
            })
            .then(r => r.json())
            .then(data => {
                this.isCorrect   = data.is_correct;
                this.correctText = data.correct_text;
                this.submitted   = true;
                this.submitting  = false;
            })
            .catch(() => {
                this.submitting = false;
            });
        }
    };
};
</script>
@endif
 <!-- resources/views/layouts/app.blade.php -->
    <!-- other scripts -->
<script>
        document.addEventListener("DOMContentLoaded", function() {
    // Select all Bootstrap or custom alerts
    const alerts = document.querySelectorAll('.alert-box');

    alerts.forEach(alert => {
        setTimeout(() => {
            // Add fade-out animation if using Bootstrap
            alert.classList.add('fade');
            alert.classList.remove('show');
            
            // Remove from DOM after transition
            setTimeout(() => alert.remove(), 500);
        }, 5000); // 5 seconds
    });
});
    document.addEventListener("DOMContentLoaded", function() {
        try {
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
            document.querySelectorAll('.timezone-field').forEach(el => {
                if (!el.value) { // don't overwrite if already set
                    el.value = tz;
                }
            });
        } catch (e) {
            // fallback to UTC silently
            document.querySelectorAll('.timezone-field').forEach(el => {
                if (!el.value) el.value = 'UTC';
            });
        }
    });

    // ── Notification bell: mark read / mark all read / delete ──
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        function post(url, method) {
            return fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        }

        function decrementUnreadBadge() {
            const bellBadge = document.getElementById('bellUnreadBadge');
            const pill = document.getElementById('notificationUnreadPill');
            [bellBadge, pill].forEach(el => {
                if (!el) return;
                const n = parseInt(el.textContent) || 0;
                const next = Math.max(0, n - 1);
                if (next === 0) { el.remove(); } else { el.textContent = el === pill ? (next + ' new') : next; }
            });
        }

        function markItemRead(item) {
            if (!item) return;
            item.classList.remove('noread', 'bg-red-50', 'hover:bg-red-100');
            item.classList.add('bg-white', 'hover:bg-gray-50');
            item.querySelector('h3')?.classList.replace('text-gray-900', 'text-gray-700');
        }

        document.body.addEventListener('click', function (e) {
            const readBtn = e.target.closest('.notificationMarkRead');
            if (readBtn) {
                const id = readBtn.dataset.id;
                post('/notifications/' + id + '/read', 'POST').then(() => {
                    markItemRead(document.getElementById('notification-' + id));
                    readBtn.remove();
                    decrementUnreadBadge();
                });
                return;
            }

            const delBtn = e.target.closest('.notificationDelete');
            if (delBtn) {
                const id = delBtn.dataset.id;
                const item = document.getElementById('notification-' + id);
                const wasUnread = item?.classList.contains('noread');
                post('/notifications/' + id, 'DELETE').then(() => {
                    item?.remove();
                    if (wasUnread) decrementUnreadBadge();
                });
                return;
            }

            if (e.target.closest('#notificationMarkAllRead')) {
                post('/notifications/read-all', 'POST').then(() => {
                    document.querySelectorAll('.notificatItem.noread').forEach(item => {
                        markItemRead(item);
                        item.querySelector('.notificationMarkRead')?.remove();
                    });
                    document.getElementById('notificationUnreadPill')?.remove();
                    document.getElementById('bellUnreadBadge')?.remove();
                });
            }
        });
    });
</script>
</body>
</html>