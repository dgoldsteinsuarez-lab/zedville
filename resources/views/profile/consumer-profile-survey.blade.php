@extends('layouts.profile')

@section('title', 'Dashboard')

@section('content')
<div class="zv-settings-title flex flex-col items-start justify-between pb-3 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">{{ __('profile.account_settings') }}</h1>
</div>
<div class="grid grid-cols-1 gap-5 mt-2">
    <div class="themeTabspills">
        <div class="w-full">
            <!-- Tabs Header -->
            <div class="flex menus overborderleftright border-b border-[#D2DDDB]">
                <button class="tabitems tab-button" data-tab="tab1">
                    {{ __('profile.profile') }}
                </button>
                <button class="tabitems tab-button" data-tab="tab2">
                    {{ __('profile.password') }}
                </button>
                <button class="tabitems tab-button active" data-tab="tab3">
                    {{ __('citizen.profile.step') }}
                </button>
                <button class="tabitems tab-button" data-tab="tab4">
                    {{ __('profile.closet') }}
                </button>
                <button class="tabitems tab-button" data-tab="tab5">
                    {{ __('profile.badges') }}
                </button>
                <button class="tabitems tab-button" data-tab="tab6">
                    {{ __('profile.my_mood') }}
                </button>
                <button class="tabitems tab-button" data-tab="tab7">
                    {{ __('layout.notifications') }}
                </button>
            </div>

            <!-- Tabs Content -->
            <div class="zv-settings tailCard py-6 lg:py-10 px-6 lg:px-8 max-w-[900px] w-full mt-4 border border-[#D2DDDB] rounded-lg" x-data="{ changeImageModal: false, moodMeater: false }">
                <div id="tab1" class="tab-content">
                    @include('profile.partials.update-profile-information-form')
                </div>
                <div id="tab2" class="tab-content">
                    @include('profile.partials.update-password-form')
                </div>
                <div id="tab3" class="tab-content active">
                    @include('profile.partials.surveys')
                </div>
                <div id="tab4" class="tab-content">
                    <p>{{ __('profile.closet_not_available') }}</p>
                </div>
                <div id="tab5" class="tab-content">
                    <p>{{ __('profile.badges_not_available') }}</p>
                </div>
                <div id="tab6" class="tab-content" data-tab="tab6">
                    <p>{{ __('profile.my_mood_not_available') }}</p>
                </div>
                <div id="tab7" class="tab-content" data-tab="tab7">
                    @include('profile.partials.notification-settings')
                </div>

            </div>
        </div>
    </div>
</div>
<script>
window.addEventListener("DOMContentLoaded", function () {
    // Respect a #tabN link (e.g. the notification bell's Settings shortcut
    // opens straight to #tab7) — otherwise default to Consumer Profile.
    const hashTab = window.location.hash.replace('#', '');
    const target = document.querySelector('[data-tab="' + hashTab + '"]') || document.querySelector('[data-tab="tab3"]');
    target.click();
});
</script>
@endsection