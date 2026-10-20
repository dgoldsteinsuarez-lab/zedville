<style>
    /* ── Updated sidebar palette & profile block ── */
    .userAdmin aside {
        background: #F1FCF8 !important;
        border-right-width: 1px !important;
        border-right-color: #D2DDDB !important;
    }

    /* menu item states */
    .userAdmin aside .sidenav li.active > a,
    .userAdmin aside .sidenav li.active > a.active {
        background-color: #CFF2E4 !important;
    }
    .userAdmin aside .sidenav a:hover,
    .userAdmin aside .sidenav button:hover {
        background-color: #E3F7EF !important;
    }

    /* profile (name + image) block: full-width top & bottom outline, no side gap */
    .userAdmin aside .userDtls {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .userAdmin aside .userDtls > div {
        background: transparent !important;
        border: 0 !important;
        border-top: 1px solid #D2DDDB !important;
        border-bottom: 1px solid #D2DDDB !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 25px 22px !important;
    }

    /* badge icon under the avatar: -35% */
    .userAdmin aside .sidebarUserSection .userImg span img {
        transform: scale(0.65);
        transform-origin: center;
    }
</style>
<aside
    x-cloak
    x-transition:enter="transition transform duration-300"
    x-transition:enter-start="-translate-x-full opacity-30  ease-in"
    x-transition:enter-end="translate-x-0 opacity-100 ease-out"
    x-transition:leave="transition transform duration-300"
    x-transition:leave-start="translate-x-0 opacity-100 ease-out"
    x-transition:leave-end="-translate-x-full opacity-0 ease-in"
    class="fixed inset-y-0 z-10 flex flex-col flex-shrink-0 w-72 max-h-screen overflow-hidden transition-all transform border-r-[2px] border-color-[#E8E8EA] bg-[#EEF9F5] lg:z-auto lg:static"
    :class="{'-translate-x-full lg:translate-x-0 lg:w-20': !isSidebarOpen}">
    <!-- sidebar header -->
    <div class="flex items-center justify-between flex-shrink-0" :class="{'lg:justify-center': !isSidebarOpen}">
        <div class="py-4 px-[30px] w-full" :class="{'px-[12px]': !isSidebarOpen}">
            <div class="sidebarLogo" :class="{'collaspeSidebar': !isSidebarOpen}">
                <img src="{{ asset('asset/front/images/logo.svg')}}" class="normalLogo" alt="">
                <img src="{{ asset('asset/front/images/logo-small.svg')}}" class="smalllogo" alt="">
            </div>
        </div>

    </div>
    <!-- Sidebar links -->
    <div class="userDtls px-[22px] mt-[8px] xl:mt-[16px] 2xl:mt-[30px]" :class="{'px-[12px]': !isSidebarOpen}">
        <div class="flex items-center justify-between rounded-md bg-[#56F4CF]/10 p-1 xl:p-2 2xl:p-3 border-2 border-[#56F4CF]" :class="{'p-1': !isSidebarOpen}">
            @php
                use App\Models\Avatar;
                use App\Models\Mailbox;
                $user = auth()->user();
                $unreadMailCount = Mailbox::where('student_id', $user->id)
                ->where('type', 'primary')
                ->where('read', 0)
                ->count();
                $avatar = Avatar::where('status', 1)->get();
                $selectedAvatar = $avatar->firstWhere('id', $user->avatar);
                $hour = now()->hour; // Laravel's current hour (24-hour format)
                if ($hour >= 5 && $hour < 12) {
                    $greeting = 'Good morning';
                } elseif ($hour >= 12 && $hour < 17) {
                    $greeting = 'Good afternoon';
                } elseif ($hour >= 17 && $hour < 21) {
                    $greeting = 'Good evening';
                } else {
                    $greeting = 'Good night';
                }
            @endphp
            <a href="{{ route('profile.edit') }}" class="sidebarUserSection flex items-center relative gap-x-2 w-full justify-between">
                <div class="userImg relative">
                    <img src="{{ asset('asset/front/images/' . ($selectedAvatar->name ?? 'default-avatar.png'))}}" class="w-12 h-12 rounded-full object-cover" :class="{'w-8 h-8': !isSidebarOpen}" alt="User Avatar">
                    <span class="absolute bottom-[4px] 2xl:bottom-[-10px] xl:bottom-[-4px] right-[-6px] 2xl:right-[-10px] xl:right-[-6px]" :class="{'bottom-0 right-0 w-4': !isSidebarOpen}">
                        <img src="{{ asset('asset/front/images/label1.svg')}}" :class="[
                            {'w-4': !isSidebarOpen},
                            'w-4 xl:w-5 2xl:w-auto'
                            ]" alt="User Label">
                    </span>
                </div>
                <div class="userName mr-auto" :class="{'hidden': !isSidebarOpen}">
                    <p class="text-sm text-[#5C5C5C] singlelineTxt"><span id="zvGreeting">{{ $greeting }}</span>,</p>
                    <h4 class="text-md xl:text-lg 2xl:text-xl font-semibold text-black singlelineTxt">{{ $user->nickname }}</h4>
                </div>
                
            </a>
        </div>
    </div>

    <!-- Dropdown menu -->
    <div id="dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700">
        <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefaultButton">
            <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Dashboard</a>
            </li>
            <li>
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Settings</a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Earnings</a>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" onclick="event.preventDefault(); this.closest('form').submit();">Sign out</a>
                </form>
            </li>
        </ul>
    </div>

    <nav class="sidenav flex-1 px-[22px] mt-[30px] overflow-hidden hover:overflow-y-auto" :class="{'px-[12px]': !isSidebarOpen}">
        <ul class="overflow-hidden">
            <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="Home"
                    data-tooltip-placement="right">
                    <span class="w-[30px]">
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#3B82F6"/><path fill="#fff" d="M12 4L19 10V20H5V10L12 4Z"/><rect x="10" y="14" width="4" height="6" fill="#3B82F6"/></svg>
                    </span>
                    <span class="sidebarLabel w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Home</span>
                </a>
            </li>
            <li class="{{ Route::is('profile.mailbox') ? 'active' : '' }}">
                <a
                    href="{{ route('profile.mailbox') }}"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="City Mailbox"
                    data-tooltip-placement="right">
                    <span class="w-[30px]">
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#F97316"/><rect x="5" y="7" width="14" height="10" rx="2" fill="none" stroke="#fff" stroke-width="1.4"/><path d="M5 9l7 4.5L19 9" fill="none" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <!-- <span class="w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">City Mailbox</span> -->
                     <div class="sidebarLabel flex items-center justify-between w-[calc(100%-30px)]"
                        :class="{ 'lg:hidden': !isSidebarOpen }">

                        <span>City Mailbox</span>

                        @if($unreadMailCount > 0)
                            <span id="sidebarUnreadBadge"
                                class="bg-red-500 text-white text-xs font-bold rounded-full min-w-[22px] h-[22px] px-2 flex items-center justify-center">
                                {{ $unreadMailCount }}
                            </span>
                        @endif

                    </div>
                </a>
            </li>
            <li class="{{ Route::is(['bank.index', 'bank.my_account', 'bank.transfer', 'bank.pay_bills', 'bank.bank_statements', 'bank.help', 'bank.manage_payee', 'bank.recurring_payment', 'bank.payment_history', 'bank.bank_statement_show', 'bank.viewStatement']) ? 'active' : '' }}">
                <a
                    href="{{ route('bank.index') }}"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="Bank Account"
                    data-tooltip-placement="right">
                    <span class="w-[30px]">
                        <svg width="24" height="24" viewBox="0 -960 960 960" xmlns="http://www.w3.org/2000/svg"><circle cx="480" cy="-480" r="480" fill="#059669"/><path fill="#fff" d="M260-280v-320h40v320h-40Zm200 0v-320h40v320h-40ZM141.54-160v-40h676.92v40H141.54ZM660-280v-320h40v320h-40ZM141.54-680v-33.85L480-875.38l338.46 161.53V-680H141.54Zm105.69-40h465.54-465.54Zm0 0h465.54L480-830 247.23-720Z"/></svg>
                    </span>
                    <span class="sidebarLabel w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Bank Account</span>
                </a>
            </li>
            <li x-data="{ open: {{ Route::is(['education.city', 'education.city-mall', 'education.spending-tracker*','supermarket*', 'education.npos','city-mood','education.city-hall', 'education.main-hall', 'education.civic-chamber', 'education.well-being-room']) ? 'true' : 'false' }}  }" class="relative">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open; if (!isSidebarOpen) { isSidebarOpen = true; window.localStorage.setItem('isSidebarOpen', true); }"
                    class="w-full flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="City"
                    data-tooltip-placement="right"
                >
                    <span class="w-[30px]">
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#C026D3"/><rect x="4" y="12" width="4" height="8" fill="#fff"/><rect x="10" y="7" width="5" height="13" fill="#fff"/><rect x="17" y="10" width="4" height="10" fill="#fff"/></svg>
                    </span>

                    <span
                        class="sidebarLabel w-[calc(100%-30px)] text-left"
                        :class="{ 'lg:hidden': !isSidebarOpen }"
                    >
                        City
                    </span>

                    <!-- Arrow icon -->
                    <svg
                        class="w-4 h-4 ml-auto transition-transform duration-300"
                        :class="{ 'rotate-180': open, 'lg:hidden': !isSidebarOpen }"
                        fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <ul
                    x-show="open"
                    x-collapse
                    class="mt-1 pl-10 space-y-1 overflow-hidden"
                    :class="{ 'hidden': !isSidebarOpen }"
                >
                    <li class="{{ Route::is('education.city') ? 'active' : '' }}">
                        <a href="{{ url('education/city') }}"
                        class="block px-2 py-2 text-sm rounded-md hover:bg-[#E9F0E9]">
                            City Map
                        </a>
                    </li>
                    <li class="{{ Route::is(['education.city-hall', 'education.main-hall', 'education.civic-chamber', 'education.well-being-room']) ? 'active' : '' }}">
                        <a href="{{ url('education/city-hall') }}"
                        class="block px-2 py-2 text-sm rounded-md hover:bg-[#E9F0E9]">
                            City Hall
                        </a>
                    </li>

                    <li class="{{ Route::is(['education.city-mall', 'education.spending-tracker*']) ? 'active' : '' }}">
                        <a href="{{ url('education/city-mall') }}"
                        class="block px-2 py-2 text-sm rounded-md hover:bg-[#E9F0E9]">
                            City Mall
                        </a>
                    </li>
                    <li class="{{ Route::is('supermarket*') ? 'active' : '' }}">
                        <a href="{{ url('supermarket') }}"
                        class="block px-2 py-2 text-sm rounded-md hover:bg-[#E9F0E9]">
                            Supermarket
                        </a>
                    </li>
                    <li class="{{ Route::is('education.npos') ? 'active' : '' }}">
                        <a href="{{ url('education/npos') }}"
                        class="block px-2 py-2 text-sm rounded-md hover:bg-[#E9F0E9]">
                            NPOs
                        </a>
                    </li>
                    <li class="{{ Route::is('city-mood') ? 'active' : '' }}">
                        <a href="{{ url('city-mood') }}"
                        class="block px-2 py-2 text-sm rounded-md hover:bg-[#E9F0E9]">
                            City Mood
                        </a>
                    </li>
                </ul>

            </li>

            <li class="{{ Route::is(['education.educational_finance_department', 'education.index']) ? 'active' : '' }}">
                <a
                    href="{{ route('education.educational_finance_department') }}"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="Educational Finance Department"
                    data-tooltip-placement="right">
                    <span class="w-[30px]">
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#F59E0B"/><rect x="4" y="7" width="16" height="12" rx="2" fill="none" stroke="#fff" stroke-width="1.4"/><path d="M4 10.5h16" stroke="#fff" stroke-width="1.4"/><circle cx="16" cy="14" r="1.3" fill="#fff"/></svg>
                    </span>
                    <span class="sidebarLabel w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Educational Finance Department</span>
                </a>
            </li>
            <li>
                <a
                    href="{{ route('academy.index') }}"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="Academy"
                    data-tooltip-placement="right">
                    <span class="w-[30px]">
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#6366F1"/><rect x="7" y="3.5" width="10" height="3" rx="1" fill="#fff"/><rect x="5" y="5.5" width="14" height="15" rx="2" fill="none" stroke="#fff" stroke-width="1.4"/><path d="M8.5 13l2.5 2.5 5-5" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="sidebarLabel w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Academy</span>
                </a>
            </li>
            <li>
                <a
                    href="{{ route('academy.pisa') }}"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="PISA"
                    data-tooltip-placement="right">
                    <span class="w-[30px]">
                        <svg width="24" height="24" viewBox="0 -960 960 960" xmlns="http://www.w3.org/2000/svg"><circle cx="480" cy="-480" r="480" fill="#65A30D"/><path fill="#fff" d="M480-120 80-320v-240L0-600l480-240 480 240v320h-40v-300l-80 40v240L480-120Zm0-280 358-179-358-179-358 179 358 179Zm0 221 320-160v-180l-320 160-320-160v180l320 160Z"/></svg>
                    </span>
                    <span class="sidebarLabel w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">PISA</span>
                </a>
            </li>
            <li {{ Route::is('education.calendar') ? 'active' : '' }}>
                <a
                    href="{{ route('education.calendar') }}"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="Calendar"
                    data-tooltip-placement="right">
                    <span class="w-[30px]">
                        <svg width="24" height="24" viewBox="0 -960 960 960" xmlns="http://www.w3.org/2000/svg"><circle cx="480" cy="-480" r="480" fill="#EF4444"/><path fill="#fff" d="M224.62-120q-27.62 0-46.12-18.5Q160-157 160-184.62v-510.76q0-27.62 18.5-46.12Q197-760 224.62-760h70.76v-89.23h43.08V-760h286.16v-89.23h40V-760h70.76q27.62 0 46.12 18.5Q800-723 800-695.38v510.76q0 27.62-18.5 46.12Q763-120 735.38-120H224.62Zm0-40h510.76q9.24 0 16.93-7.69 7.69-7.69 7.69-16.93v-350.76H200v350.76q0 9.24 7.69 16.93 7.69 7.69 16.93 7.69ZM200-575.39h560v-119.99q0-9.24-7.69-16.93-7.69-7.69-16.93-7.69H224.62q-9.24 0-16.93 7.69-7.69 7.69-7.69 16.93v119.99Zm0 0V-720-575.39Zm280 181.54q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.57 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.19 9.19 21.57 0 12.39-9.19 21.58-9.2 9.19-21.58 9.19Zm-160 0q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.57 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.19 9.19 21.57 0 12.39-9.19 21.58-9.2 9.19-21.58 9.19Zm320 0q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.57 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.19 9.19 21.57 0 12.39-9.19 21.58-9.2 9.19-21.58 9.19ZM480-240q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.58 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.2 9.19 21.58 0 12.39-9.19 21.58Q492.38-240 480-240Zm-160 0q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.58 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.2 9.19 21.58 0 12.39-9.19 21.58Q332.38-240 320-240Zm320 0q-12.38 0-21.58-9.19-9.19-9.19-9.19-21.58 0-12.38 9.19-21.58 9.2-9.19 21.58-9.19 12.38 0 21.58 9.19 9.19 9.2 9.19 21.58 0 12.39-9.19 21.58Q652.38-240 640-240Z"/></svg>
                    </span>
                    <span class="sidebarLabel w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Calendar</span>
                </a>
            </li>
            <li class="{{ Route::is('profile.edit') ? 'active' : '' }}">
                <a
                    href="{{ route('profile.edit') }}"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="Settings"
                    data-tooltip-placement="right">
                    <span class="w-[30px]">
                        <svg width="24" height="24" viewBox="0 -960 960 960" xmlns="http://www.w3.org/2000/svg"><circle cx="480" cy="-480" r="480" fill="#64748B"/><path fill="#fff" d="m405.38-120-14.46-115.69q-19.15-5.77-41.42-18.16-22.27-12.38-37.88-26.53L204.92-235l-74.61-130 92.23-69.54q-1.77-10.84-2.92-22.34-1.16-11.5-1.16-22.35 0-10.08 1.16-21.19 1.15-11.12 2.92-25.04L130.31-595l74.61-128.46 105.93 44.61q17.92-14.92 38.77-26.92 20.84-12 40.53-18.54L405.38-840h149.24l14.46 116.46q23 8.08 40.65 18.54 17.65 10.46 36.35 26.15l109-44.61L829.69-595l-95.31 71.85q3.31 12.38 3.7 22.73.38 10.34.38 20.42 0 9.31-.77 19.65-.77 10.35-3.54 25.04L827.92-365l-74.61 130-107.23-46.15q-18.7 15.69-37.62 26.92-18.92 11.23-39.38 17.77L554.62-120H405.38ZM440-160h78.23L533-268.31q30.23-8 54.42-21.96 24.2-13.96 49.27-38.27L736.46-286l39.77-68-87.54-65.77q5-17.08 6.62-31.42 1.61-14.35 1.61-28.81 0-15.23-1.61-28.81-1.62-13.57-6.62-29.88L777.77-606 738-674l-102.08 42.77q-18.15-19.92-47.73-37.35-29.57-17.42-55.96-23.11L520-800h-79.77l-12.46 107.54q-30.23 6.46-55.58 20.81-25.34 14.34-50.42 39.42L222-674l-39.77 68L269-541.23q-5 13.46-7 29.23t-2 32.77q0 15.23 2 30.23t6.23 29.23l-86 65.77L222-286l99-42q23.54 23.77 48.88 38.12 25.35 14.34 57.12 22.34L440-160Zm38.92-220q41.85 0 70.93-29.08 29.07-29.07 29.07-70.92t-29.07-70.92Q520.77-580 478.92-580q-42.07 0-71.04 29.08-28.96 29.07-28.96 70.92t28.96 70.92Q436.85-380 478.92-380ZM480-480Z"/></svg>
                    </span>
                    <span class="sidebarLabel w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Settings</span>
                </a>
            </li>
            <!-- Sidebar Links... -->
        </ul>
    </nav>
    @php
    use App\Models\BankAccount;
    use App\Models\Transaction;
    $bankAccounts = BankAccount::where('student_id', auth()->id())->get();
    $totalBalance = $bankAccounts->sum('primary_savings_account_amount') +
                    $bankAccounts->sum('emergency_fund_account_amount') +
                    $bankAccounts->sum('money_market_account_amount');
    $latestTxn = Transaction::where('user_id', $user->id)
    ->latest('id') // or latest('created_at') if you track timestamps
    ->first();
    $lastBalance = $latestTxn ? $latestTxn->balance : 0;

    @endphp
</aside>

{{-- Sidebar icon tooltip. Every nav item above already carries a
     data-tooltip/data-tooltip-placement attribute, but nothing ever
     implemented it - with the sidebar collapsed to icon-only, there was
     no way to tell icons apart (Daniel, 2026-10-20: "I don't recognize
     well which one is for a school or bank or city"). A plain CSS
     ::after tooltip won't work here - <aside>, its <nav> and its <ul>
     are all overflow:hidden (for the slide-in/out animation and the
     scrollable link list), which would clip it. This div is deliberately
     placed as a sibling of </aside>, not a descendant, and positioned via
     JS with position:fixed, so none of that clipping applies. --}}
<div id="sidebarTooltip" class="fixed hidden z-[9999] px-2.5 py-1.5 rounded-md text-xs font-medium text-white whitespace-nowrap pointer-events-none" style="background:#1F2937;"></div>
<script>
    /* Greeting follows the viewer's own local time (device time zone),
       not the server clock. SSR value above is a fallback until this runs. */
    (function () {
        var el = document.getElementById('zvGreeting');
        if (!el) return;
        var h = new Date().getHours();
        var g = h >= 5 && h < 12  ? 'Good morning'
              : h >= 12 && h < 17 ? 'Good afternoon'
              : h >= 17 && h < 21 ? 'Good evening'
              : 'Good night';
        el.textContent = g;
    })();

    (function () {
        var tip = document.getElementById('sidebarTooltip');
        if (!tip) return;

        document.addEventListener('mouseover', function (e) {
            var el = e.target.closest('[data-tooltip]');
            if (!el) return;

            // Only needed when the sidebar is collapsed to icons - when
            // it's expanded, the text label is already shown right next
            // to the icon. Same localStorage key themeScript.js's
            // Alpine setup() reads/writes, so this always matches the
            // sidebar's actual current state.
            if (window.localStorage.getItem('isSidebarOpen') === 'true') return;

            var rect = el.getBoundingClientRect();
            tip.textContent = el.getAttribute('data-tooltip');
            tip.style.top = (rect.top + rect.height / 2) + 'px';
            tip.style.left = (rect.right + 10) + 'px';
            tip.style.transform = 'translateY(-50%)';
            tip.classList.remove('hidden');
        });

        document.addEventListener('mouseout', function (e) {
            if (e.target.closest('[data-tooltip]')) {
                tip.classList.add('hidden');
            }
        });
    })();
</script>