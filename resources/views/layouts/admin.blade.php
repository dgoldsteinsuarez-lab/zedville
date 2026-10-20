<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="author" content="text/html" />

    <meta name="theme-color" content="#222">
    <meta name="msapplication-navbutton-color" content="#222">
    <meta name="apple-mobile-web-app-status-bar-style" content="#222">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zedville - Financial Skills for Your Future')</title>
    <link sizes='57x57' href={{ asset('asset/admin/images/favicon.png') }} rel='apple-touch-icon'>
    <link sizes='114x114' href={{ asset('asset/admin/images/favicon.png') }} rel='apple-touch-icon'>
    <link sizes='72x72'   href={{ asset('asset/admin/images/favicon.png') }}   rel='apple-touch-icon'>
    <link sizes='144x144' href={{ asset('asset/admin/images/favicon.png') }} rel='apple-touch-icon'>
    <link sizes='60x60'   href={{ asset('asset/admin/images/favicon.png') }}   rel='apple-touch-icon'>
    <link sizes='120x120' href={{ asset('asset/admin/images/favicon.png') }} rel='apple-touch-icon'>
    <link sizes='76x76'   href={{ asset('asset/admin/images/favicon.png') }}   rel='apple-touch-icon'>
    <link sizes='152x152' href={{ asset('asset/admin/images/favicon.png') }} rel='apple-touch-icon'>
    <link sizes='180x180' href={{ asset('asset/admin/images/favicon.png') }} rel='apple-touch-icon'>
    <link sizes='192x192' href={{ asset('asset/admin/images/favicon.png') }} rel='icon' type='image/png'>
    <link sizes='160x160' href={{ asset('asset/admin/images/favicon.png') }} rel='icon' type='image/png'>
    <link sizes='96x96'   href={{ asset('asset/admin/images/favicon.png') }}   rel='icon' type='image/png'>
    <link sizes='16x16'   href={{ asset('asset/admin/images/favicon.png') }}   rel='icon' type='image/png'>
    <link sizes='32x32'   href={{ asset('asset/admin/images/favicon.png') }}   rel='icon' type='image/png'>
    <link href="{{ asset('asset/admin/css/theme_plugin.css') }}?ver={{ rand(111, 999) }}" rel="stylesheet" />
    <link href="{{ asset('asset/admin/css/theme_style.css') }}?ver={{ rand(111, 999) }}" rel="stylesheet" />
    <style>
.toast-message {
    white-space: nowrap !important;
}
/* Submenu hover on minimized sidebar */
.aside-minimize .navbar-nav.sidenavigation > li {
    position: relative;
}
.aside-minimize .navbar-nav.sidenavigation > li > .collapseNavItems {
    display: none !important;
    position: absolute !important;
    left: 27px !important;
    top: 0 !important;
    min-width: 200px !important;
    background: #fff !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    border-radius: 8px !important;
    padding: 8px 0 !important;
    z-index: 1000 !important;
    margin-left: 8px !important;
    height: auto !important;
    overflow: visible !important;
}
.aside-minimize .navbar-nav.sidenavigation > li:hover > .collapseNavItems {
    display: block !important;
}
.aside-minimize .navbar-nav.sidenavigation > li > .collapseNavItems:hover {
    display: block !important;
}
.aside-minimize .sidebarnavTop {
    overflow: visible !important;
}
</style>
    @stack('styles')
</head>
<body class="themeAdmin aside-fixed header-fixed">
    <div class="pageLoader" id="pageLoad">
        <div class="whitePage"></div>
    </div>
    <section class="d-flex flex-column flex-root">
        <div class="d-flex flex-row flex-column-fluid page">
            <aside class="aside fixed-top d-flex flex-column" id="themeAside">
                <div class="logoHead flex-column-auto" id="scbrandLogo">
                    <a href="#" class="fullLogo">
                        <img alt="Logo" src="{{ asset('asset/admin/images/logo.svg') }}" class="img-fluid" />
                    </a>
                    <a href="#" class="smallLogo">
                        <img alt="Logo" src="{{ asset('asset/admin/images/shortlogo.svg') }}" class="img-fluid" />
                    </a>
                </div>
                <div class="aside-menu-wrapper flex-column-fluid" id="asideNavWrap">
                    <nav class="navbar navbar-expand-*" id="toggleNavsubnav" >
                        <div class="container-fluid p-0">
                            <div style="overflow-x: visible;" class="sidebarnavTop position-relative w-100">
                          
                           
                                <ul class="navbar-nav sidenavigation">
                                    @php $navSuper = auth()->user()->isSuperAdmin(); $navOrg = $navSuper || auth()->user()->isSchoolAdmin(); @endphp
                                    <li class="nav-item {{ Route::is('admin.dashboard') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="Dashboard">
                                        <a class="nav-link" href="{{ url('admin/dashboard') }}">
                                            <span class="navIcon">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><rect x="0" y="-960" width="960" height="960" rx="200" fill="#3B82F6"/><path fill="#fff" d="M540-600v-200h260v200H540ZM160-480v-320h260v320H160Zm380 320v-320h260v320H540Zm-380 0v-200h260v200H160Zm40-360h180v-240H200v240Zm380 320h180v-240H580v240Zm0-440h180v-120H580v120ZM200-200h180v-120H200v120Zm180-320Zm200-120Zm0 200ZM380-320Z"/></svg>
                                            </span>
                                            <span class="navTxt">Dashboard</span>
                                        </a>
                                    </li>
                                    @if(auth()->user()->isTutor())
                                    <li class="nav-item {{ request()->is('admin/my-students') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ url('admin/my-students') }}">
                                            <span class="navTxt">My Students</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ request()->is('admin/extensions*') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ url('admin/extensions') }}">
                                            <span class="navTxt">Extensions</span>
                                        </a>
                                    </li>
                                    @endif
                                    @php
                                        $usersMenuActive = request()->is(
                                            'admin/student*',
                                            'admin/role*',
                                            'admin/school-domain*',
                                            'admin/grade*',
                                            'admin/school-user*'
                                        );
                                    @endphp
                                    @if($navOrg)
                                    <li class="nav-item {{ $usersMenuActive ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="Users">
                                        <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#usernav"  aria-expanded="{{ $usersMenuActive ? 'true' : 'false' }}" aria-controls="sideNavbar">
                                            <span class="navIcon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect width="24" height="24" rx="5" fill="#8B5CF6"/>
                                                    <circle cx="11.9999" cy="7.25" r="3.4" stroke="#fff" stroke-width="1.2" stroke-linecap="round"/>
                                                    <path d="M13.9396 5.26612C14.1886 4.76306 14.5522 4.37269 14.9723 4.13101C15.3904 3.89044 15.8513 3.804 16.3004 3.87299C16.7506 3.94214 17.1867 4.16735 17.5489 4.53795C17.9121 4.90954 18.1821 5.40895 18.3129 5.97844C18.4437 6.54787 18.4266 7.15068 18.2646 7.70741C18.1026 8.26418 17.8064 8.73918 17.426 9.0797C17.0469 9.41911 16.6022 9.60971 16.1504 9.64425C15.6992 9.67876 15.2422 9.55763 14.8351 9.28345" stroke="#fff" stroke-width="1.2"/>
                                                    <path d="M10.0603 5.26612C9.81131 4.76306 9.44765 4.37269 9.02759 4.13101C8.60945 3.89044 8.14858 3.804 7.69945 3.87299C7.24923 3.94214 6.8132 4.16735 6.45099 4.53795C6.0878 4.90954 5.81773 5.40895 5.68694 5.97844C5.55616 6.54787 5.57327 7.15068 5.73526 7.70741C5.89725 8.26418 6.19347 8.73918 6.57385 9.0797C6.95299 9.41911 7.39768 9.60971 7.84948 9.64425C8.3007 9.67876 8.75769 9.55763 9.16474 9.28345" stroke="#fff" stroke-width="1.2"/>
                                                    <path d="M11.9999 13.25C17.3419 13.25 18.6072 17.3267 18.9069 19.2579C18.9916 19.8036 18.5522 20.25 17.9999 20.25H5.99994C5.44765 20.25 5.00827 19.8036 5.09296 19.2579C5.39267 17.3267 6.65798 13.25 11.9999 13.25Z" stroke="#fff" stroke-width="1.2" stroke-linecap="round"/>
                                                    <path d="M16.3017 11.6924L16.3017 11.0924H16.3017V11.6924ZM21.0263 16.752L21.6113 16.6186L21.6113 16.6186L21.0263 16.752ZM18.3652 17.8379L17.7888 18.0047L17.9142 18.4379H18.3652V17.8379ZM13.3359 13.0273L12.8928 12.6228L12.1483 13.4382L13.2375 13.6192L13.3359 13.0273ZM16.3017 11.6924L16.3017 12.2924C17.6277 12.2924 18.5442 12.9718 19.2041 13.9065C19.8755 14.8578 20.2474 16.0348 20.4413 16.8853L21.0263 16.752L21.6113 16.6186C21.4029 15.7046 20.9873 14.3519 20.1844 13.2145C19.3699 12.0606 18.1253 11.0924 16.3017 11.0924L16.3017 11.6924ZM21.0263 16.752L20.4413 16.8853C20.4627 16.9793 20.4385 17.0582 20.385 17.1206C20.3285 17.1865 20.2337 17.2379 20.1122 17.2379V17.8379V18.4379C21.0428 18.4379 21.8384 17.6148 21.6113 16.6186L21.0263 16.752ZM20.1122 17.8379V17.2379H18.3652V17.8379V18.4379H20.1122V17.8379ZM18.3652 17.8379L18.9415 17.6711C18.3632 15.6728 16.9289 13.0161 13.4342 12.4355L13.3359 13.0273L13.2375 13.6192C16.0723 14.0903 17.2691 16.2089 17.7888 18.0047L18.3652 17.8379ZM13.3359 13.0273L13.779 13.4319C14.3962 12.7558 15.204 12.2924 16.3017 12.2924V11.6924V11.0924C14.8103 11.0924 13.6964 11.7426 12.8928 12.6228L13.3359 13.0273Z" fill="#fff"/>
                                                    <path d="M7.69879 11.6924L7.69885 11.0924H7.69879V11.6924ZM10.6627 13.0273L10.7611 13.6192L11.8503 13.438L11.1057 12.6227L10.6627 13.0273ZM5.63434 17.8379V18.4379H6.08533L6.21069 18.0047L5.63434 17.8379ZM3.88727 17.8379L3.88726 18.4379H3.88727V17.8379ZM2.97321 16.752L2.38822 16.6186L2.38821 16.6186L2.97321 16.752ZM7.69879 11.6924L7.69874 12.2924C8.79559 12.2925 9.60221 12.756 10.2196 13.432L10.6627 13.0273L11.1057 12.6227C10.3025 11.7434 9.1899 11.0925 7.69885 11.0924L7.69879 11.6924ZM10.6627 13.0273L10.5642 12.4355C7.07051 13.0167 5.63619 15.673 5.05799 17.6711L5.63434 17.8379L6.21069 18.0047C6.73029 16.2091 7.92721 14.0906 10.7611 13.6192L10.6627 13.0273ZM5.63434 17.8379V17.2379H3.88727V17.8379V18.4379H5.63434V17.8379ZM3.88727 17.8379L3.88727 17.2379C3.76579 17.2379 3.67103 17.1865 3.61451 17.1206C3.56101 17.0582 3.53678 16.9793 3.5582 16.8853L2.97321 16.752L2.38821 16.6186C2.16111 17.6149 2.95675 18.4379 3.88726 18.4379L3.88727 17.8379ZM2.97321 16.752L3.55819 16.8853C3.75209 16.0349 4.12414 14.8579 4.79583 13.9066C5.45588 12.9718 6.37263 12.2924 7.69879 12.2924V11.6924V11.0924C5.8752 11.0924 4.6303 12.0605 3.81556 13.2144C3.01247 14.3518 2.5966 15.7046 2.38822 16.6186L2.97321 16.752Z" fill="#fff"/>
                                                </svg>
                                            </span>
                                            <span class="navTxt">Users</span>
                                            <span class="nav-arrow">
                                                <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1.07129 1.62756L6.00033 6.55661L10.9294 1.62756" stroke="currentColor" stroke-width="1.4" stroke-linecap="square"/>
                                                </svg>
                                            </span>
                                        </a>
                                        <div id="usernav" class="collapse {{ $usersMenuActive ? 'show' : '' }} collapseNavItems" data-bs-parent="#toggleNavsubnav">
                                            <ul class="subnav">
                                                @if($navSuper)
                                                <li class="nav-item {{ request()->is('admin/school-domain') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/school-domain') }}">
                                                        <span class="navTxt">School Domain</span>
                                                    </a>
                                                </li>
                                                @endif
                                                <li class="nav-item {{ request()->is('admin/grades') && !request()->is('admin/grade') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/grades') }}">
                                                        <span class="navTxt">Grades</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/grade') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/grade') }}">
                                                        <span class="navTxt">Classes</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/student') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/student') }}">
                                                        <span class="navTxt">Students</span>
                                                    </a>
                                                </li>
                                                <li class="subnav-label px-3 pt-2 pb-1 text-uppercase" style="font-size:.7rem; letter-spacing:.05em; color:#9CA3AF;">Staff &amp; Access</li>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="{{ url('admin/staff') }}">
                                                        <span class="navTxt">School User</span>
                                                    </a>
                                                </li>
                                                @if($navSuper)
                                                <li class="nav-item {{ request()->is('admin/role') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/role') }}">
                                                        <span class="navTxt">Role</span>
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </li>
                                    @endif
                                    {{-- Autopilot moved off the main sidebar onto the Dashboard card (Daniel,
                                         2026-10-20: "I dont want autopilot to be in such a relevant place...
                                         add it inside dashboard, as its empty") - still reachable by
                                         Tutor/School Admin/Super Admin, just no longer top-level nav. --}}
                                    @php
                                        $usersMenuActive1 = request()->is(
                                            'admin/email-templates*',
                                            'admin/email-campaigns*',
                                        );
                                    @endphp
                                    @if($navSuper)
                                    <li class="nav-item {{ $usersMenuActive1 ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="Mail Box">
                                        <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#mailbox" aria-expanded="{{ $usersMenuActive1 ? 'true' : 'false' }}" aria-controls="sideNavbar">
                                            <span class="navIcon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect width="24" height="24" rx="5" fill="#0EA5E9"/>
                                                    <rect x="4" y="6" width="16" height="12" rx="2" stroke="#fff" stroke-width="1.2"/>
                                                    <path d="M4 9L11.1056 12.5528C11.6686 12.8343 12.3314 12.8343 12.8944 12.5528L20 9" stroke="#fff" stroke-width="1.2"/>
                                                </svg>
                                            </span>
                                            <span class="navTxt">Mail Box</span>
                                            <span class="nav-arrow">
                                                <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1.07129 1.62756L6.00033 6.55661L10.9294 1.62756" stroke="currentColor" stroke-width="1.4" stroke-linecap="square"/>
                                                </svg>
                                            </span>
                                        </a>
                                        <div id="mailbox" class="collapse {{ $usersMenuActive1 ? 'show' : '' }} collapseNavItems" data-bs-parent="#toggleNavsubnav">
                                            <ul class="subnav">
                                                <li class="nav-item {{ request()->is('admin/email-templates*') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/email-templates') }}">
                                                        <span class="navTxt">Email Templates</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/email-campaigns*') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/email-campaigns') }}">
                                                        <span class="navTxt">Email Campaigns</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    @endif
                                    @php
                                        $navBank = $navOrg || auth()->user()->isTutor();
                                        $usersMenuActive2 = request()->is(
                                            'admin/accounts*',
                                            'admin/ledger*',
                                            'admin/salary-bonuses*',
                                        );
                                    @endphp
                                    @if($navBank)
                                    <li class="nav-item {{ $usersMenuActive2 ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="Bank">
                                        <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#citybank" aria-expanded="{{ $usersMenuActive2 ? 'true' : 'false' }}" aria-controls="sideNavbar">
                                            <span class="navIcon">


<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><rect x="0" y="-960" width="960" height="960" rx="200" fill="#059669"/><path fill="#fff" d="M260-280v-320h40v320h-40Zm200 0v-320h40v320h-40ZM141.54-160v-40h676.92v40H141.54ZM660-280v-320h40v320h-40ZM141.54-680v-33.85L480-875.38l338.46 161.53V-680H141.54Zm105.69-40h465.54-465.54Zm0 0h465.54L480-830 247.23-720Z"/></svg>

                                            </span>
                                            <span class="navTxt">Bank</span>
                                            <span class="nav-arrow">
                                                <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1.07129 1.62756L6.00033 6.55661L10.9294 1.62756" stroke="currentColor" stroke-width="1.4" stroke-linecap="square"/>
                                                </svg>
                                            </span>
                                        </a>
                                        <div id="citybank" class="collapse {{ $usersMenuActive2 ? 'show' : '' }} collapseNavItems" data-bs-parent="#toggleNavsubnav">
                                            <ul class="subnav">
                                                @if($navOrg)
                                                <li class="nav-item {{ request()->is('admin/accounts') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/accounts') }}">
                                                        <span class="navTxt">Accounts</span>
                                                    </a>
                                                </li>
                                                @endif
                                                @if($navSuper)
                                                <li class="nav-item {{ request()->is('admin/ledger*') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/ledger') }}">
                                                        <span class="navTxt">Reverse / Adjust</span>
                                                    </a>
                                                </li>
                                                @endif
                                                <li class="nav-item {{ request()->is('admin/salary-bonuses*') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/salary-bonuses') }}">
                                                        <span class="navTxt">Salary Bonuses</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    @endif
                                    @if($navSuper)
                                    <li class="nav-item {{ request()->is('admin/pisa*') ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="PISA">
                                        <a class="nav-link" href="{{ url('admin/pisa') }}">
                                            <span class="navIcon">
                                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><rect x="0" y="-960" width="960" height="960" rx="200" fill="#6366F1"/><path fill="#fff" d="M480-120 80-320v-240L0-600l480-240 480 240v320h-40v-300l-80 40v240L480-120Zm0-280 358-179-358-179-358 179 358 179Zm0 221 320-160v-180l-320 160-320-160v180l320 160Z"/></svg>
                                            </span>
                                            <span class="navTxt">PISA</span>
                                        </a>
                                    </li>
                                    @endif
                                    @php
                                        $usersMenuActive3 = request()->is(
                                            'admin/education/monthly-budget-activity*',
                                            'admin/education/poster*',
                                            'admin/education/high-budget-activity*',
                                            'admin/login-question',
                                            'admin/login-question/school-month-settings',
                                            'admin/finhero/activities',
                                            'admin/finhero/task-activities',
                                            'admin/finhero/point-defaults',
                                        );
                                    @endphp
                                    @if($navOrg)
                                     <li class="nav-item {{ $usersMenuActive3 ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="Variable - set per Grade">
                                        <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#education" aria-expanded="{{ $usersMenuActive3 ? 'true' : 'false' }}" aria-controls="sideNavbar">
                                            <span class="navIcon">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><rect x="0" y="-960" width="960" height="960" rx="200" fill="#F97316"/><path fill="#fff" d="M478.46-210.77q-48.77-33.38-104-51.31Q319.23-280 260-280q-31.23 0-61.35 5.23Q168.54-269.54 140-258q-21.77 8.69-40.88-4.85Q80-276.38 80-300.15v-385.08q0-14.85 8.19-26.77t21.5-16.92q35.23-15.54 73.31-23.31 38.08-7.77 77-7.77 58.77 0 113.88 18.08Q429-723.85 480-693.85v434.77q50.23-32 106.62-46.46Q643-320 700-320q32.92 0 59.73 3.69 26.81 3.69 57.19 12.62 8.46 2.31 15.77.38Q840-305.23 840-316v-418.46q5.77 1.15 11.04 3.19t10.04 5.42q9.46 5 14.19 14.23 4.73 9.24 4.73 20.24v389.69q0 23.77-20.27 36.92-20.27 13.16-44.35 5.23-27.76-10.77-56.73-15.61Q729.69-280 700-280q-60 0-116.77 16.77-56.77 16.77-104.77 52.46ZM560-360v-340l200-200v360L560-360Zm-120 81.92v-392.15q-42.23-23.23-87.35-36.5Q307.54-720 260-720q-37 0-68.54 6.62-31.54 6.61-56.84 16.76-6.16 2.31-10.39 6.54T120-679.69v365.46q0 10.77 7.31 12.69t15.77-1.15q24.23-8.39 52.57-12.85Q224-320 260-320q53.69 0 100.88 12.54 47.2 12.54 79.12 29.38Zm0 0v-392.15 392.15Z"/></svg>
                                            </span>
                                            <span class="navTxt">Education (per Grade)</span>
                                            <span class="nav-arrow">
                                                <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1.07129 1.62756L6.00033 6.55661L10.9294 1.62756" stroke="currentColor" stroke-width="1.4" stroke-linecap="square"/>
                                                </svg>
                                            </span>
                                        </a>
                                        <div id="education" class="collapse {{ $usersMenuActive3 ? 'show' : '' }} collapseNavItems" data-bs-parent="#toggleNavsubnav">
                                            <ul class="subnav">
                                                <li class="nav-item {{ request()->is('admin/education/monthly-budget-activity') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/education/monthly-budget-activity') }}">
                                                        <span class="navTxt">Monthly Budget Activity Position</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/education/emergency-fund-account') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/education/emergency-fund-account') }}">
                                                        <span class="navTxt">Emergency Fund Account Position</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/education/poster') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/education/poster') }}">
                                                        <span class="navTxt">Room Poster</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/education/high-budget-activity') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/education/high-budget-activity') }}">
                                                        <span class="navTxt">Spending Activity Position</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/login-question/school-month-settings') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/login-question/school-month-settings') }}">
                                                        <span class="navTxt">School Month Settings</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/login-question') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/login-question') }}">
                                                        <span class="navTxt">Login Question</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/finhero/activities') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/finhero/activities') }}">
                                                        <span class="navTxt">FinHero Task Activity</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/finhero/task-activities') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/finhero/task-activities') }}">
                                                        <span class="navTxt">FinHero Library Activity</span>
                                                    </a>
                                                </li>
                                                @if($navSuper)
                                                <li class="nav-item {{ request()->is('admin/finhero/point-defaults') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/finhero/point-defaults') }}">
                                                        <span class="navTxt">FinHero Point Defaults</span>
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </li>
                                    @php
                                        $usersMenuActive4 = request()->is(
                                            'admin/education/city-mall*',
                                            'admin/education/city-mall-store*',
                                            'admin/education/supermarket*',
                                            'admin/education/wants-iteams*',
                                            'admin/npos*',
                                            'admin/city-map*',
                                        );
                                    @endphp
                                    <li class="nav-item {{ $usersMenuActive4 ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="Fix for All Schools">
                                        <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#city" aria-expanded="{{ $usersMenuActive4 ? 'true' : 'false' }}" aria-controls="sideNavbar">
                                            <span class="navIcon">


<svg xmlns="http://www.w3.org/2000/svg" height="24px" width="24px" viewBox="0 0 24 24"><rect width="24" height="24" rx="5" fill="#14B8A6"/><path fill="#fff" d="M12 4L19 9H5L12 4Z"/><rect x="6" y="9" width="12" height="9" fill="none" stroke="#fff" stroke-width="1.5"/><rect x="10.5" y="13" width="3" height="5" fill="#fff"/></svg>
                                            </span>
                                            <span class="navTxt">Fix for All Schools</span>
                                            <span class="nav-arrow">
                                                <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1.07129 1.62756L6.00033 6.55661L10.9294 1.62756" stroke="currentColor" stroke-width="1.4" stroke-linecap="square"/>
                                                </svg>
                                            </span>
                                        </a>
                                        <div id="city" class="collapse {{ $usersMenuActive4 ? 'show' : '' }} collapseNavItems" data-bs-parent="#toggleNavsubnav">
                                            <ul class="subnav">
                                                <li class="nav-item {{ request()->is('admin/city-map*') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/city-map') }}">
                                                        <span class="navTxt">City Map</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/education/city-mall') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/education/city-mall') }}">
                                                        <span class="navTxt">City Mall</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/education/city-mall-store') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/education/city-mall-store') }}">
                                                        <span class="navTxt">Store</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/education/supermarket-store-names') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/education/supermarket-store-names') }}">
                                                        <span class="navTxt">Supermarket Stores</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/education/supermarket') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/education/supermarket') }}">
                                                        <span class="navTxt">Supermarket Products</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/education/wants-iteams') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/education/wants-iteams') }}">
                                                        <span class="navTxt">Wants Items</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/npos') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/npos') }}">
                                                        <span class="navTxt">NPOs</span>
                                                    </a>
                                                </li>
                                                
                                            </ul>
                                        </div>
                                    </li>
                                    @endif
                                    @php
                                        $usersMenuActive4 = request()->is(
                                            'admin/wellbeing*',
                                            'admin/referendum*',
                                            'admin/petition*',
                                        );
                                    @endphp
                                    <li class="nav-item {{ $usersMenuActive4 ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="City Hall">
                                        <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#cityHall" aria-expanded="{{ $usersMenuActive4 ? 'true' : 'false' }}" aria-controls="sideNavbar">
                                            <span class="navIcon">


                                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" width="24px" viewBox="0 0 24 24"><rect width="24" height="24" rx="5" fill="#C026D3"/><rect x="7" y="4" width="10" height="16" rx="2" fill="none" stroke="#fff" stroke-width="1.5"/><line x1="9.5" y1="8.5" x2="14.5" y2="8.5" stroke="#fff" stroke-width="1.3" stroke-linecap="round"/><line x1="9.5" y1="11.5" x2="14.5" y2="11.5" stroke="#fff" stroke-width="1.3" stroke-linecap="round"/><path d="M9.5 15.5l2 2 3-3.5" fill="none" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </span>
                                            <span class="navTxt">City Hall</span>
                                            <span class="nav-arrow">
                                                <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1.07129 1.62756L6.00033 6.55661L10.9294 1.62756" stroke="currentColor" stroke-width="1.4" stroke-linecap="square"/>
                                                </svg>
                                            </span>
                                        </a>
                                        <div id="cityHall" class="collapse {{ $usersMenuActive4 ? 'show' : '' }} collapseNavItems" data-bs-parent="#toggleNavsubnav">
                                            <ul class="subnav">
                                                @if($navSuper)
                                                <li class="nav-item {{ request()->is('admin/wellbeing') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/wellbeing') }}">
                                                        <span class="navTxt">Wellbeing Room</span>
                                                    </a>
                                                </li>
                                                @endif
                                                <li class="nav-item {{ request()->is('admin/referendum') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/referendum') }}">
                                                        <span class="navTxt">Civic Chamber Referendums</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item {{ request()->is('admin/petition') ? 'active' : '' }}">
                                                    <a class="nav-link" href="{{ url('admin/petition') }}">
                                                        <span class="navTxt">Civic Chamber Petitions</span>
                                                    </a>
                                                </li>
                                                
                                                
                                            </ul>
                                        </div>
                                    </li>
                                    @php
                                        $usersMenuActive = request()->is(
                                            'admin/calendar*',
                                        );
                                    @endphp
                                    <li class="nav-item {{ $usersMenuActive ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="Calendar">
                                        <a class="nav-link" href="{{ url('admin/calendar') }}">
                                            <span class="navIcon">
                                              
<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><rect x="0" y="-960" width="960" height="960" rx="200" fill="#EF4444"/><path fill="#fff" d="M224.62-120q-27.62 0-46.12-18.5Q160-157 160-184.62v-510.76q0-27.62 18.5-46.12Q197-760 224.62-760h70.76v-89.23h43.08V-760h286.16v-89.23h40V-760h70.76q27.62 0 46.12 18.5Q800-723 800-695.38v510.76q0 27.62-18.5 46.12Q763-120 735.38-120H224.62Zm0-40h510.76q9.24 0 16.93-7.69 7.69-7.69 7.69-16.93v-350.76H200v350.76q0 9.24 7.69 16.93 7.69 7.69 16.93 7.69ZM200-575.39h560v-119.99q0-9.24-7.69-16.93-7.69-7.69-16.93-7.69H224.62q-9.24 0-16.93 7.69-7.69 7.69-7.69 16.93v119.99Zm0 0V-720-575.39Zm280 181.54q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.57 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.19 9.19 21.57 0 12.39-9.19 21.58-9.2 9.19-21.58 9.19Zm-160 0q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.57 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.19 9.19 21.57 0 12.39-9.19 21.58-9.2 9.19-21.58 9.19Zm320 0q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.57 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.19 9.19 21.57 0 12.39-9.19 21.58-9.2 9.19-21.58 9.19ZM480-240q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.58 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.2 9.19 21.58 0 12.39-9.19 21.58Q492.38-240 480-240Zm-160 0q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.58 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.2 9.19 21.58 0 12.39-9.19 21.58Q332.38-240 320-240Zm320 0q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.58 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.2 9.19 21.58 0 12.39-9.19 21.58Q652.38-240 640-240Z"/></svg>

                                            </span>
                                            <span class="navTxt">Calendar</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="navbottomSticky w-100">
                                <ul class="navbar-nav sidenavigation">
                                     @php
                                        $usersMenuActive = request()->is(
                                            'admin/profile*',
                                        );
                                    @endphp
                                    <li class="nav-item {{ $usersMenuActive ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="Settings">
                                        <a class="nav-link" href="{{ url('admin/profile') }}">
                                            <span class="navIcon">
                                               
<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><rect x="0" y="-960" width="960" height="960" rx="200" fill="#64748B"/><path fill="#fff" d="m405.38-120-14.46-115.69q-19.15-5.77-41.42-18.16-22.27-12.38-37.88-26.53L204.92-235l-74.61-130 92.23-69.54q-1.77-10.84-2.92-22.34-1.16-11.5-1.16-22.35 0-10.08 1.16-21.19 1.15-11.12 2.92-25.04L130.31-595l74.61-128.46 105.93 44.61q17.92-14.92 38.77-26.92 20.84-12 40.53-18.54L405.38-840h149.24l14.46 116.46q23 8.08 40.65 18.54 17.65 10.46 36.35 26.15l109-44.61L829.69-595l-95.31 71.85q3.31 12.38 3.7 22.73.38 10.34.38 20.42 0 9.31-.77 19.65-.77 10.35-3.54 25.04L827.92-365l-74.61 130-107.23-46.15q-18.7 15.69-37.62 26.92-18.92 11.23-39.38 17.77L554.62-120H405.38ZM440-160h78.23L533-268.31q30.23-8 54.42-21.96 24.2-13.96 49.27-38.27L736.46-286l39.77-68-87.54-65.77q5-17.08 6.62-31.42 1.61-14.35 1.61-28.81 0-15.23-1.61-28.81-1.62-13.57-6.62-29.88L777.77-606 738-674l-102.08 42.77q-18.15-19.92-47.73-37.35-29.57-17.42-55.96-23.11L520-800h-79.77l-12.46 107.54q-30.23 6.46-55.58 20.81-25.34 14.34-50.42 39.42L222-674l-39.77 68L269-541.23q-5 13.46-7 29.23t-2 32.77q0 15.23 2 30.23t6.23 29.23l-86 65.77L222-286l99-42q23.54 23.77 48.88 38.12 25.35 14.34 57.12 22.34L440-160Zm38.92-220q41.85 0 70.93-29.08 29.07-29.07 29.07-70.92t-29.07-70.92Q520.77-580 478.92-580q-42.07 0-71.04 29.08-28.96 29.07-28.96 70.92t28.96 70.92Q436.85-380 478.92-380ZM480-480Z"/></svg>
                                            </span>
                                            <span class="navTxt">Settings</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>
                </div>
            </aside>
            <section class="d-flex flex-column flex-row-fluid themeWrapper">
                    @include('layouts.admin-header-top')
                <section class="bodyContent pall-30 d-flex flex-column flex-column-fluid">
                    <div class="d-flex flex-column-fluid">
                        <div class="container-fluid p-0">

{{-- Daniel, 2026-10-20: "when I select the school in Admin, it should show
     clearly at the top the school that I am in." The header badge
     (components/school-badge.blade.php) was too easy to miss - this is the
     same information, impossible to miss, first thing on every admin page.
     Super Admin only - everyone else is pinned to one school by IsAdmin
     middleware and already knows which one. --}}
@if (auth()->check() && auth()->user()->isSuperAdmin())
    @php $currentSchool = \App\Models\SchoolDomain::find(session('selected_school')); @endphp
    @if ($currentSchool)
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 p-3" style="background:#F1FCF8; border:1px solid #CFE9DE; border-radius:10px;">
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background:#1B6B3A; color:#fff; font-size:.8rem; padding:.45em .8em;">MANAGING</span>
                <span class="fw-600" style="color:#1B6B3A; font-size:1rem;">{{ $currentSchool->school_name }}</span>
            </div>
            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editmodel" class="btn btn-sm" style="background:#fff; border:1px solid #1B6B3A; color:#1B6B3A;">
                Switch School
            </a>
        </div>
    @else
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 p-3" style="background:#FEF3C7; border:1px solid #FCD34D; border-radius:10px;">
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background:#D97706; color:#fff; font-size:.8rem; padding:.45em .8em;">NO SCHOOL SELECTED</span>
                <span class="text-muted" style="font-size:.9rem;">Pick a school to manage its Grades, Classes, Students and settings.</span>
            </div>
            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editmodel" class="btn btn-sm" style="background:#D97706; border:1px solid #D97706; color:#fff;">
                Select a school
            </a>
        </div>
    @endif
@endif

@include('partials.impersonation-banner')

@if (auth()->check() && auth()->user()->isTutor())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 p-3" style="background:#EEF2FF; border:1px solid #C7D2FE; border-radius:10px;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge" style="background:#4F46E5; color:#fff; font-size:.8rem; padding:.45em .8em;">TUTOR MODE</span>
            <span class="text-muted" style="font-size:.9rem;">You're managing your students and classes.</span>
        </div>
        <a href="{{ url('/dashboard') }}" class="btn btn-sm" style="background:#fff; border:1px solid #4F46E5; color:#4F46E5;">
            Switch to Student View &rarr;
        </a>
    </div>
@endif

     @yield('content')

   
                </div>
            </div>
            </section>
        </section>
    </div>
</section>



<script src="{{ asset('asset/admin/js/themePlugin.js') }}?ver={{ rand(111, 999) }}" type="text/javascript"></script>
<!-- <script src="{{ asset('asset/admin/js/ckeditor.js') }}?ver={{ rand(111, 999) }}"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script> -->
<script src="{{ asset('asset/admin/js/themeScript.js') }}?ver={{ rand(111, 999) }}" type="text/javascript"></script>
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    //ALl Message timeour
    setTimeout(function() {
        const msg = document.getElementById('success-message');
        if (msg) {
            msg.style.transition = 'opacity 0.5s ease';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500); // remove after fade out
        }
    }, 3000);
    //Delete data url
    document.querySelectorAll('.deleteBtn').forEach(button => {
    button.addEventListener('click', function() {
        const url = this.getAttribute('data-url');
        const form = document.getElementById('modalDeleteForm');
        form.setAttribute('action', url);
    });
});
@if(Session::has('success'))
        toastr.success("{{ Session::get('success') }}", "Success", {
            positionClass: "toast-top-right"
        });
    @endif

    @if(Session::has('error'))
        toastr.error("{{ Session::get('error') }}", "Error", {
            positionClass: "toast-top-right"
        });
    @endif

    @if ($errors->any())
        toastr.error("{{ $errors->first() }}", "Error", {
            positionClass: "toast-top-right"
        });
    @endif

    @if(Session::has('warning'))
        toastr.warning("{{ Session::get('warning') }}", "Warning", {
            positionClass: "toast-top-right"
        });
    @endif

    @if(Session::has('info'))
        toastr.info("{{ Session::get('info') }}", "Info", {
            positionClass: "toast-top-right"
        });
    @endif
</script>
@stack('scripts')
</body>
</html>