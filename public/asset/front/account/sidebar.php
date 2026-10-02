<!-- Sidebar -->
<!-- <button @click="toggleSidbarMenu()" class="p-0 absolute left-[70px] top-[10px] rounded-full w-8 h-8 hover:bg-gray-200 bg-gray-200" :class="{ 'hidden': !isSidebarOpen }">
    <svg class="w-6 h-6 text-gray-600" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.073 12.5H5.5C5.35767 12.5 5.23875 12.4522 5.14325 12.3567C5.04775 12.2612 5 12.1423 5 12C5 11.8577 5.04775 11.7387 5.14325 11.6432C5.23875 11.5477 5.35767 11.5 5.5 11.5H17.073L13.3385 7.7655C13.241 7.668 13.19 7.55325 13.1855 7.42125C13.181 7.28908 13.232 7.16792 13.3385 7.05775C13.4487 6.94742 13.5676 6.89133 13.6953 6.8895C13.8228 6.8875 13.9417 6.94167 14.052 7.052L18.4345 11.4345C18.5218 11.5218 18.5831 11.611 18.6182 11.702C18.6536 11.793 18.6712 11.8923 18.6712 12C18.6712 12.1077 18.6536 12.207 18.6182 12.298C18.5831 12.389 18.5218 12.4782 18.4345 12.5655L14.052 16.948C13.9545 17.0455 13.8388 17.0965 13.7048 17.101C13.5708 17.1055 13.4487 17.0526 13.3385 16.9422C13.232 16.8321 13.1778 16.7142 13.176 16.5885C13.174 16.4628 13.2282 16.3448 13.3385 16.2345L17.073 12.5Z" fill="currentColor"/>
    </svg>
</button> -->
<aside
    x-cloak
    x-transition:enter="transition transform duration-300"
    x-transition:enter-start="-translate-x-full opacity-30  ease-in"
    x-transition:enter-end="translate-x-0 opacity-100 ease-out"
    x-transition:leave="transition transform duration-300"
    x-transition:leave-start="translate-x-0 opacity-100 ease-out"
    x-transition:leave-end="-translate-x-full opacity-0 ease-in"
    class="fixed inset-y-0 z-10 flex flex-col flex-shrink-0 w-72 max-h-screen overflow-hidden transition-all transform border-r-[2px] border-color-[#E8E8EA] lg:z-auto lg:static"
    :class="{'-translate-x-full lg:translate-x-0 lg:w-20': !isSidebarOpen}">
    <!-- sidebar header -->
    <div class="flex items-center justify-between flex-shrink-0" :class="{'lg:justify-center': !isSidebarOpen}">
        <div class="py-4 px-[30px] w-full" :class="{'px-[12px]': !isSidebarOpen}">
            <div class="sidebarLogo" :class="{'collaspeSidebar': !isSidebarOpen}">
                <img src="../images/logo.svg" class="normalLogo" alt="">
                <img src="../images/logo-small.svg" class="smalllogo" alt="">
            </div>
        </div>

    </div>
    <!-- Sidebar links -->
    <div class="userDtls px-[22px] mt-[8px] xl:mt-[16px] 2xl:mt-[30px]" :class="{'px-[12px]': !isSidebarOpen}">
        <div class="flex items-center justify-between rounded-md bg-[#56F4CF]/10 p-1 xl:p-2 2xl:p-3 border-2 border-[#56F4CF]" :class="{'p-1': !isSidebarOpen}">
            <div class="flex items-center relative gap-x-2 w-full justify-between">
                <div class="userImg relative">
                    <img src="../images/users.png" alt="">
                    <span class="absolute bottom:[-4px] 2xl:bottom-[-10px] xl:bottom-[-4px] right-[-6px] 2xl:right-[-10px] xl:right-[-6px]" :class="{'bottom-0 right-0': !isSidebarOpen}">
                        <img src="../images/label1.svg" class="w-4 2xl:w-auto xl:w-5" :class="{'w-4': !isSidebarOpen}" alt="">
                    </span>
                </div>
                <div class="userName ml-3" :class="{'hidden': !isSidebarOpen}">
                    <p class="text-sm text-[#5C5C5C]">Good morning,</p>
                    <h4 class="text-md xl:text-lg 2xl:text-xl font-semibold text-black">Daniel G.</h4>
                </div>
                <button data-dropdown-toggle="accDropdown" class="dropBtn" :class="{'hidden': !isSidebarOpen}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="1" transform="rotate(-90 12 12)" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <circle cx="12" cy="18" r="1" transform="rotate(-90 12 18)" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <circle cx="12" cy="6" r="1" transform="rotate(-90 12 6)" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </button>
                <div id="accDropdown" class="z-10 hidden absolute top-full right-0 bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 dark:divide-gray-600">
                    <div class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                        <div class="font-semibold">Daniel G.</div>
                        <div class="font-medium truncate">daniel@gmail.com</div>
                    </div>
                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownInformationButton">
                        <li>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">City Mailbox</a>
                        </li>
                        <li>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Bank Account</a>
                        </li>
                        <li>
                            <a href="account-setting.php" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Settings</a>
                        </li>
                    </ul>
                    <div class="py-2">
                        <a href="../login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Sign out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Dropdown menu -->
    <div id="dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700">
        <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefaultButton">
            <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Dashboard</a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Settings</a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Earnings</a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Sign out</a>
            </li>
        </ul>
    </div>

    <nav class="sidenav flex-1 px-[22px] mt-[30px] overflow-hidden hover:overflow-y-auto" :class="{'px-[12px]': !isSidebarOpen}">
        <ul class="overflow-hidden">
            <li class="active">
                <a
                    href="account.php"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}">
                    <span class="w-[30px]">
                        <img src="../images/home.svg" alt="">
                    </span>
                    <span class="w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Home</span>
                </a>
            </li>
            <li>
                <a
                    href="mailbox.php"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}">
                    <span class="w-[30px]">
                        <img src="../images/citymaill.svg" alt="">
                    </span>
                    <span class="w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">City Mailbox</span>
                </a>
            </li>
            <li>
                <a
                    href="#"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}">
                    <span class="w-[30px]">
                        <img src="../images/backaccount.svg" alt="">
                    </span>
                    <span class="w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Bank Account</span>
                </a>
            </li>
            <li>
                <a
                    href="#"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}">
                    <span class="w-[30px]">
                        <img src="../images/cityhall.svg" alt="">
                    </span>
                    <span class="w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">City Hall</span>
                </a>
            </li>
            <li>
                <a
                    href="#"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}">
                    <span class="w-[30px]">
                        <img src="../images/department.svg" alt="">
                    </span>
                    <span class="w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Educational Finance Department</span>
                </a>
            </li>
            <li>
                <a
                    href="#"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}">
                    <span class="w-[30px]">
                        <img src="../images/pisa.svg" alt="">
                    </span>
                    <span class="w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">PISA</span>
                </a>
            </li>
            <li>
                <a
                    href="account-setting.php"
                    class="flex items-center px-2 py-3 space-x-2 text-base font-semibold rounded-md hover:bg-[#E9F0E9] hover:text-black active"
                    :class="{'justify-center': !isSidebarOpen}">
                    <span class="w-[30px]">
                        <img src="../images/settings.svg" alt="">
                    </span>
                    <span class="w-[calc(100%-30px)]" :class="{ 'lg:hidden': !isSidebarOpen }">Settings</span>
                </a>
            </li>
            <!-- Sidebar Links... -->
        </ul>
    </nav>
    <!-- Sidebar footer -->
    <div class="flex-shrink-0 py-2 max-h-auto">
        <div class="currencyDtls px-[22px] mb-[8px] xl:mb-[10px] 2xl:mb-[20px]" :class="{'px-[12px]': !isSidebarOpen}">
            <div class="flex items-center justify-between rounded-md bg-themeyellow p-1 xl:p-2 2xl:p-3 border-2 border-[#FFE48D]" :class="{'p-1': !isSidebarOpen}" data-tooltip="Account Balance : 4250 ZED" data-tooltip-placement="right">
                <div class="flex items-center gap-x-2" :class="{'flex-wrap gap-y-2': !isSidebarOpen}">
                    <div class="userImg relative">
                        <img src="../images/currency-coin.svg" alt="">
                    </div>
                    <div class="userName" :class="{'hidden': !isSidebarOpen}">
                        <p class="text-sm text-[#5C5C5C]">Account Balance</p>
                        <h4 class="text-md xl:text-lg 2xl:text-xl font-semibold text-black">4250 ZED</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="userlavelDtls px-[22px] pb-2" :class="{'hidden': !isSidebarOpen}">
            <span class="text-md text-[#5C5C5C] mb-4 block">Current Level</span>
            <div class="userlavelprogress w-full flex gap-2 justify-between items-start">
                <div class="lavelItems current">
                    <img src="../images/lavel1.svg" alt="">
                </div>
                <div class="lavelItems current">
                    <img src="../images/lavel2.svg" alt="">
                </div>
                <div class="lavelItems">
                    <img src="../images/lavel3.svg" alt="">
                </div>
                <div class="lavelItems">
                    <img src="../images/lavel4.svg" alt="">
                </div>
            </div>
        </div> -->
    </div>
</aside>