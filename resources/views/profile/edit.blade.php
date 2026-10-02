@extends('layouts.profile')

@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">Account Settings</h1>
</div>
<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="themeTabspills">
        <div class="w-full">
            <!-- Tabs Header -->
            <div class="flex menus overborderleftright border-b border-[#D2DDDB]">
                <button class="tabitems tab-button" data-tab="tab1">
                    Profile
                </button>
                <button class="tabitems tab-button" data-tab="tab2">
                    Password
                </button>
                <button class="tabitems tab-button" data-tab="tab3">
                    Consumer Profile
                </button>
                <button class="tabitems tab-button" data-tab="tab4">
                    Closet
                </button>
                <button class="tabitems tab-button" data-tab="tab5">
                    Badges
                </button>
                <button class="tabitems tab-button" data-tab="tab6">
                    My Mood
                </button>
                <button class="tabitems tab-button" data-tab="tab7">
                    Notifications
                </button>
                @if(auth()->user()->isStudent())
                <button class="tabitems tab-button" data-tab="tab8">
                    Autopilot
                </button>
                @endif
            </div>

            <!-- Tabs Content -->
            <div class="tailCard py-6 lg:py-10 px-6 lg:px-8 max-w-[900px] w-full mt-4 border border-[#D2DDDB] rounded-lg" x-data="{ changeImageModal: false, moodMeater: false }">
                <div id="tab1" class="tab-content active">
                    @include('profile.partials.update-profile-information-form')
                </div>
                <div id="tab2" class="tab-content">
                    @include('profile.partials.update-password-form')
                </div>
                <div id="tab3" class="tab-content">
                    @if($ConsumersurveyExists)
                    @include('profile.partials.surveys-results')
                    @else
                    @include('profile.partials.surveys')
                    @endif
                </div>
                <div id="tab4" class="tab-content">
                    @include('profile.partials.closet')
                </div>
                <div id="tab5" class="tab-content">
                    @include('profile.partials.student-badges')
                </div>
                <div id="tab6" class="tab-content">
                    @include('profile.partials.my-mood')
                </div>
                <div id="tab7" class="tab-content">
                    @include('profile.partials.notification-settings')
                </div>
                @if(auth()->user()->isStudent())
                <div id="tab8" class="tab-content">
                    @include('profile.partials.autopilot')
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
<script>
    // themeScript.js auto-opens the first tab on load — if the URL has a
    // #tabN (e.g. the notification bell's Settings shortcut → #tab7),
    // re-open that one right after. Runs on window "load" so it fires
    // after themeScript's own DOMContentLoaded handler, not before it.
    window.addEventListener("load", function () {
        const hashTab = window.location.hash.replace('#', '');
        if (!hashTab) return;
        const target = document.querySelector('[data-tab="' + hashTab + '"]');
        target?.click();
    });
</script>
@endsection