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
                        <img src="{{ asset('asset/front/images/home.svg')}}" alt="">
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
                        <img src="{{ asset('asset/front/images/citymaill.svg')}}" alt="">
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
                        <img src="{{ asset('asset/front/images/backaccount.svg')}}" alt="">
                    </span>
                    <span class="sidebarLabel w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Bank Account</span>
                </a>
            </li>
            <li x-data="{ open: {{ Route::is(['education.city', 'education.city-mall', 'education.spending-tracker*','supermarket*', 'education.npos','city-mood','education.city-hall', 'education.main-hall', 'education.civic-chamber', 'education.well-being-room']) ? 'true' : 'false' }}  }" class="relative">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="City"
                    data-tooltip-placement="right"
                >
                    <span class="w-[30px]">
                        <img src="{{ asset('asset/front/images/cityhall.svg') }}" alt="">
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
                        <img src="{{ asset('asset/front/images/department.svg')}}" alt="">
                    </span>
                    <span class="sidebarLabel w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Educational Finance Department</span>
                </a>
            </li>
            <li>
                <a
                    href="#"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}"
                    data-tooltip="PISA"
                    data-tooltip-placement="right">
                    <span class="w-[30px]">
                        <img src="{{ asset('asset/front/images/pisa.svg')}}" alt="">
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
                        <img src="{{ asset('asset/front/images/calendar.svg')}}" alt="">
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
                        <img src="{{ asset('asset/front/images/settings.svg')}}" alt="">
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
</script>