<?php include 'head.php'; ?>

<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">City Mailbox</h1>
</div>
<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="themeTabspills">
        <div class="w-full">
            <!-- Tabs Header -->
            <div class="flex menus overborderleftright border-b border-[#D2DDDB]">
                <button class="tabitems tab-button " data-tab="tab1">
                    Primary <span class="numbers">10</span>
                </button>
                <button class="tabitems tab-button" data-tab="tab2">
                    Starred
                </button>
                <button class="tabitems tab-button" data-tab="tab3">
                    Deleted
                </button>
            </div>
            <!-- Tabs Content -->
            <div class="tailCard w-full mt-4" x-data="{ changeImageModal: false }">
                <div id="tab1" class="tab-content active">
                    <div class="flex flex-col">
                        <div class="flex-1 flex">
                            <main class="flex gap-8 flex-1 mailboxMain">
                                <div class="sticky top-0 flex-grow max-w-xs w-full flex flex-col relative z-50 flex-shrink-0 h-[calc(100vh-180px)]">
                                    <div class="overflow-y-auto mailboxLists">
                                        <div class="mailItems pointer unread">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer unread">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer unread">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Message -->
                                <div class="flex-1 w-0 flex flex-col">
                                    <div class="overflow-y-auto">
                                        <article class="tailCard bg-white py-6 lg:py-10 px-6 lg:px-8 max-w-[900px] w-full border border-[#D2DDDB] rounded-lg">
                                            <header class="mailDtlshead mb-12">
                                                <div class="dateTime text-xs leading-[18px] text-[#5C5C5C] ">Today, 16th July 2025, 11:26 AM</div>
                                                <h4 class="text-[24px] md:text-[28px] lg:text-[32px] font-semibold leading-tight">Welcome to Zedville</h4>
                                            </header>
                                            <div class="mailBodyTxt text-md xl:text-base leading-[18px] text-black">
                                                Dear [Customer Name],
                                                <br><br>We're thrilled to welcome you as a new customer! We appreciate you choosing us for your [banking/service] needs.<br><br>Your account number is: [Account Number][If applicable, include any initial login credentials or instructions]
                                                <br><br>We encourage you to explore our [website/portal] to learn more about the features and benefits of your new account. You'll find helpful resources and guides to get you started.
                                                <br><br>[If applicable, link to a quick start guide or tutorial]
                                                <br><br>Our team is here to support you. If you have any questions or need assistance, please don't hesitate to contact us.
                                                <br><br>Sincerely,
                                                <br>The [Your Company/Bank] Team
                                            </div>
                                            <div class="sendBy mt-16">
                                                <div class="flex w-fit items-center space-x-2 bg-[#EDF4F3] border border-[#D2DDDB] rounded-md py-1 px-3">
                                                    <span class="icon">
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M4 5C4 3.89543 4.89543 3 6 3H18C19.1046 3 20 3.89543 20 5V17.7639C20 19.2507 18.4354 20.2177 17.1056 19.5528L12.8944 17.4472C12.3314 17.1657 11.6686 17.1657 11.1056 17.4472L6.89443 19.5528C5.56462 20.2177 4 19.2507 4 17.7639V5Z" fill="#00A47D"/>
                                                            <rect x="6" y="5" width="12" height="2" rx="1" fill="black"/>
                                                        </svg>
                                                    </span>
                                                    <span class="stxt font-medium">Sent by :</span>
                                                    <span class="stxtname font-semibold">Administrator</span>
                                                </div>
                                            </div>
                                            <div class="actionsBtns flex flex-wrap gap-x-3 gap-y-2 mt-12">
                                                <button class="themeBtn">Starred</button>
                                                <button class="whiteBtn">Print</button>
                                                <button class="dangerBtn">Delete</button>
                                            </div>
                                        </article>
                                    </div>
                                </div>
                            </main>
                        </div>
                    </div>
                </div>
                <div id="tab2" class="tab-content">
                    <div class="flex flex-col">
                        <div class="flex-1 flex">
                            <main class="flex gap-8 flex-1 mailboxMain">
                                <div class="sticky top-0 flex-grow max-w-xs w-full flex flex-col relative z-50 flex-shrink-0 h-[calc(100vh-180px)]">
                                    <div class="overflow-y-auto mailboxLists">
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Message -->
                                <div class="flex-1 w-0 flex flex-col">
                                    <div class="overflow-y-auto">
                                        <article class="tailCard bg-white py-6 lg:py-10 px-6 lg:px-8 max-w-[900px] w-full border border-[#D2DDDB] rounded-lg">
                                            <header class="mailDtlshead mb-12">
                                                <div class="dateTime text-xs leading-[18px] text-[#5C5C5C] ">Today, 16th July 2025, 11:26 AM</div>
                                                <h4 class="text-[24px] md:text-[28px] lg:text-[32px] font-semibold leading-tight">Welcome to Zedville</h4>
                                            </header>
                                            <div class="mailBodyTxt text-md xl:text-base leading-[18px] text-black">
                                                Dear [Customer Name],
                                                <br><br>We're thrilled to welcome you as a new customer! We appreciate you choosing us for your [banking/service] needs.<br><br>Your account number is: [Account Number][If applicable, include any initial login credentials or instructions]
                                                <br><br>We encourage you to explore our [website/portal] to learn more about the features and benefits of your new account. You'll find helpful resources and guides to get you started.
                                                <br><br>[If applicable, link to a quick start guide or tutorial]
                                                <br><br>Our team is here to support you. If you have any questions or need assistance, please don't hesitate to contact us.
                                                <br><br>Sincerely,
                                                <br>The [Your Company/Bank] Team
                                            </div>
                                            <div class="sendBy mt-16">
                                                <div class="flex w-fit items-center space-x-2 bg-[#EDF4F3] border border-[#D2DDDB] rounded-md py-1 px-3">
                                                    <span class="icon">
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M4 5C4 3.89543 4.89543 3 6 3H18C19.1046 3 20 3.89543 20 5V17.7639C20 19.2507 18.4354 20.2177 17.1056 19.5528L12.8944 17.4472C12.3314 17.1657 11.6686 17.1657 11.1056 17.4472L6.89443 19.5528C5.56462 20.2177 4 19.2507 4 17.7639V5Z" fill="#00A47D"/>
                                                            <rect x="6" y="5" width="12" height="2" rx="1" fill="black"/>
                                                        </svg>
                                                    </span>
                                                    <span class="stxt font-medium">Sent by :</span>
                                                    <span class="stxtname font-semibold">Administrator</span>
                                                </div>
                                            </div>
                                            <div class="actionsBtns flex flex-wrap gap-x-3 gap-y-2 mt-12">
                                                <button class="themeBtn">Unstarred</button>
                                                <button class="whiteBtn">Print</button>
                                                <button class="dangerBtn">Delete</button>
                                            </div>
                                        </article>
                                    </div>
                                </div>
                            </main>
                        </div>
                    </div>
                </div>
                <div id="tab3" class="tab-content">
                    <div class="flex flex-col">
                        <div class="flex-1 flex">
                            <main class="flex gap-8 flex-1 mailboxMain">
                                <div class="sticky top-0 flex-grow max-w-xs w-full flex flex-col relative z-50 flex-shrink-0 h-[calc(100vh-180px)]">
                                    <div class="overflow-y-auto mailboxLists">
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mailItems pointer">
                                            <div class="flex items-center justify-between">
                                                <div class="leftpanel">
                                                    <h5 class="text-base font-semibold text-black singlelineTxt">Welcome to your new life!</h5>
                                                    <p class="text-xs leading-[18px] singlelineTxt">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est, possimus laboriosam...</p>
                                                </div>
                                                <div class="datepanel text-xs text-[#5C5C5C]">
                                                    <span class="time block">11:24</span>
                                                    <span class="date block">25th July</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Message -->
                                <div class="flex-1 w-0 flex flex-col">
                                    <div class="overflow-y-auto">
                                        <article class="tailCard bg-white py-6 lg:py-10 px-6 lg:px-8 max-w-[900px] w-full border border-[#D2DDDB] rounded-lg">
                                            <header class="mailDtlshead mb-12">
                                                <div class="dateTime text-xs leading-[18px] text-[#5C5C5C] ">Today, 16th July 2025, 11:26 AM</div>
                                                <h4 class="text-[24px] md:text-[28px] lg:text-[32px] font-semibold leading-tight">Welcome to Zedville</h4>
                                            </header>
                                            <div class="mailBodyTxt text-md xl:text-base leading-[18px] text-black">
                                                Dear [Customer Name],
                                                <br><br>We're thrilled to welcome you as a new customer! We appreciate you choosing us for your [banking/service] needs.<br><br>Your account number is: [Account Number][If applicable, include any initial login credentials or instructions]
                                                <br><br>We encourage you to explore our [website/portal] to learn more about the features and benefits of your new account. You'll find helpful resources and guides to get you started.
                                                <br><br>[If applicable, link to a quick start guide or tutorial]
                                                <br><br>Our team is here to support you. If you have any questions or need assistance, please don't hesitate to contact us.
                                                <br><br>Sincerely,
                                                <br>The [Your Company/Bank] Team
                                            </div>
                                            <div class="sendBy mt-16">
                                                <div class="flex w-fit items-center space-x-2 bg-[#EDF4F3] border border-[#D2DDDB] rounded-md py-1 px-3">
                                                    <span class="icon">
                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M4 5C4 3.89543 4.89543 3 6 3H18C19.1046 3 20 3.89543 20 5V17.7639C20 19.2507 18.4354 20.2177 17.1056 19.5528L12.8944 17.4472C12.3314 17.1657 11.6686 17.1657 11.1056 17.4472L6.89443 19.5528C5.56462 20.2177 4 19.2507 4 17.7639V5Z" fill="#00A47D"/>
                                                            <rect x="6" y="5" width="12" height="2" rx="1" fill="black"/>
                                                        </svg>
                                                    </span>
                                                    <span class="stxt font-medium">Sent by :</span>
                                                    <span class="stxtname font-semibold">Administrator</span>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                </div>
                            </main>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>