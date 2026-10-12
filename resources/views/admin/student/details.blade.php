@extends('layouts.admin')

@section('title', 'Student Details')

@section('content')
<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Student Details</h1>
</div>
<!-- Body Widget -->
<div class="themeCard card card-custom mab-20">
    <div class="card-body pt-2 pb-2 px-0 pb-0">
        <ul class="nav nav-tabs customTabBg customTabs" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="home" aria-selected="true">Account Information</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab" aria-controls="bank" aria-selected="false">Bank</button>
            </li>
            <!-- <li class="nav-item" role="presentation">
                <button class="nav-link" id="billing-tab" data-bs-toggle="tab" data-bs-target="#billing" type="button" role="tab" aria-controls="contact" aria-selected="false" tabindex="-1">Change Password</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="integrate-tab" data-bs-toggle="tab" data-bs-target="#integrate" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Danger Zone</button>
            </li> -->
        </ul>
    </div>
</div>
<div class="tab-content" id="settingsTab">
    <div class="tab-pane show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <div class="themeCard card themeForm card-custom card-stretch gutter-b mab-15">
            <div class="card-body pall-30">
                <div class="settingHeadingPart pab-20">
                    <div class="userProfile">
                        <div class="d-flex justify-content-between">
                            <div class="userImgBadge">
                                <div class="userLogo">
                                    <!-- <a href="membership-privileges.php"> -->
                                        <!-- <span class="textLogo">F</span> -->
                                        <img src="{{ asset('asset/front/images/' . $student->avatarRelation->name) }}" class="img-fluid" alt="">
                                    <!-- </a> -->
                                </div>
                                <a href="#"><span class="userName fs-20 fw-bold theme-text-dark">{{ $student->name}}</span></a>
                            </div>
                            <div class="headBtnSec">
                                <a href="#" class="secondaryBtn theme-text-mute" data-bs-toggle="modal" data-bs-target="#editprofile">
                                    <span class="theme-text-dark fs-14 fw-500">Edit Profile</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="settingBody settingWidgetsSmall">
                    <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 0H0V24H24V0Z" fill="white" fill-opacity="0.01"></path>
                                    <path d="M12 10C13.933 10 15.5 8.433 15.5 6.5C15.5 4.567 13.933 3 12 3C10.067 3 8.5 4.567 8.5 6.5C8.5 8.433 10.067 10 12 10Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M3 20.4V21H21V20.4C21 18.1598 21 17.0397 20.5641 16.184C20.1806 15.4314 19.5686 14.8195 18.816 14.436C17.9603 14 16.8402 14 14.6 14H9.4C7.1598 14 6.0397 14 5.18405 14.436C4.43139 14.8195 3.81947 15.4314 3.43598 16.184C3 17.0397 3 18.1598 3 20.4Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">Student ID</span>
                                <span class="fs-14 theme-text-dark d-block lh-20">{{ $student->citizenId}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 0H0V24H24V0Z" fill="white" fill-opacity="0.01"></path>
                                    <path d="M12 10C13.933 10 15.5 8.433 15.5 6.5C15.5 4.567 13.933 3 12 3C10.067 3 8.5 4.567 8.5 6.5C8.5 8.433 10.067 10 12 10Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M3 20.4V21H21V20.4C21 18.1598 21 17.0397 20.5641 16.184C20.1806 15.4314 19.5686 14.8195 18.816 14.436C17.9603 14 16.8402 14 14.6 14H9.4C7.1598 14 6.0397 14 5.18405 14.436C4.43139 14.8195 3.81947 15.4314 3.43598 16.184C3 17.0397 3 18.1598 3 20.4Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">Student Age</span>
                                <span class="fs-14 theme-text-dark d-block lh-20">{{ $student->age}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 0H0V24H24V0Z" fill="white" fill-opacity="0.01"></path>
                                    <path d="M12 10C13.933 10 15.5 8.433 15.5 6.5C15.5 4.567 13.933 3 12 3C10.067 3 8.5 4.567 8.5 6.5C8.5 8.433 10.067 10 12 10Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M3 20.4V21H21V20.4C21 18.1598 21 17.0397 20.5641 16.184C20.1806 15.4314 19.5686 14.8195 18.816 14.436C17.9603 14 16.8402 14 14.6 14H9.4C7.1598 14 6.0397 14 5.18405 14.436C4.43139 14.8195 3.81947 15.4314 3.43598 16.184C3 17.0397 3 18.1598 3 20.4Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">Student Grade</span>
                                <span class="fs-14 theme-text-dark d-block lh-20">{{ optional($student->gradeRelation)->name ?? ' ' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 0H0V24H24V0Z" fill="white" fill-opacity="0.01"></path>
                                    <path d="M12 10C13.933 10 15.5 8.433 15.5 6.5C15.5 4.567 13.933 3 12 3C10.067 3 8.5 4.567 8.5 6.5C8.5 8.433 10.067 10 12 10Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M3 20.4V21H21V20.4C21 18.1598 21 17.0397 20.5641 16.184C20.1806 15.4314 19.5686 14.8195 18.816 14.436C17.9603 14 16.8402 14 14.6 14H9.4C7.1598 14 6.0397 14 5.18405 14.436C4.43139 14.8195 3.81947 15.4314 3.43598 16.184C3 17.0397 3 18.1598 3 20.4Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">Student Mascot</span>
                                <span class="fs-14 theme-text-dark d-block lh-20">{{ optional($student->mascotRelation)->name ?? ' ' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 14.5V18C2 18.5523 2.44772 19 3 19H16C16.5523 19 17 18.5523 17 18V2C17 1.44772 16.5523 1 16 1H3C2.44772 1 2 1.44772 2 2V5.5" stroke="#222222" stroke-width="1.4" stroke-linecap="round"></path>
                                    <path d="M1 10H12" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M8.62166 5.91643L12.7052 10L8.62166 14.0836" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">Last login</span>
                                <span class="fs-14 theme-text-dark d-block lh-20">{{ $student->loginTime}}</span>
                            </div>
                        </div>
                    </div>

                    <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 2.5H20" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M4 22.5H20" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M6 2.5V8.5L10.5 13.5" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M18 22.5V15.25L13.5 11" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M6 22.5V15.5L9.25 12.25" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M18 2.5V8.5L15 12" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M9 17H9.5" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M14.5732 16.8232L14.9268 17.1768" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M12 19.5H12.5" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">System Timezone</span>
                                <span class="fs-14 theme-text-dark lh-20 editSecField">(GMT 0.00) Edinburgh, London</span>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 0H0V24H24V0Z" fill="white" fill-opacity="0.01"></path>
                                    <path d="M12 10C13.933 10 15.5 8.433 15.5 6.5C15.5 4.567 13.933 3 12 3C10.067 3 8.5 4.567 8.5 6.5C8.5 8.433 10.067 10 12 10Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M3 20.4V21H21V20.4C21 18.1598 21 17.0397 20.5641 16.184C20.1806 15.4314 19.5686 14.8195 18.816 14.436C17.9603 14 16.8402 14 14.6 14H9.4C7.1598 14 6.0397 14 5.18405 14.436C4.43139 14.8195 3.81947 15.4314 3.43598 16.184C3 17.0397 3 18.1598 3 20.4Z" stroke="#222222" stroke-width="1.41" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">School</span>
                                <span class="fs-14 theme-text-dark d-block lh-20">Kolkata</span>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C17.5227 2 22 6.47733 22 12C22 17.5227 17.5227 22 12 22C10.3433 22 8.78068 21.5973 7.40534 20.884C7.01734 20.6833 6.00201 20.8873 4.36001 21.4973C4.25087 21.5379 4.13302 21.5492 4.01817 21.53C3.90333 21.5109 3.7955 21.462 3.70541 21.3882C3.61533 21.3145 3.54614 21.2184 3.50472 21.1096C3.46331 21.0008 3.45111 20.883 3.46935 20.768L3.48601 20.692L3.54135 20.492C3.95201 18.988 4.03801 18.0653 3.80001 17.7253C2.62527 16.0475 1.99669 14.0482 2.00001 12C2.00001 6.47733 6.47734 2 12 2ZM12 3.33333C7.21334 3.33333 3.33335 7.21333 3.33335 12C3.33335 13.8453 3.91001 15.5567 4.89334 16.962C5.16134 17.3447 5.16134 18.3733 4.89334 20.0473C6.56001 19.6007 7.60201 19.484 8.01801 19.6993C9.21001 20.3173 10.5647 20.6667 12 20.6667C16.7867 20.6667 20.6667 16.7867 20.6667 12C20.6667 7.21333 16.7867 3.33333 12 3.33333ZM9.85001 6.02667C10.5287 6.13733 11.056 6.59 11.4833 7.37533C12.0767 8.468 12.4753 9.71533 10.9147 10.6813L10.8453 10.722C11.0987 11.322 11.4227 11.8873 11.8127 12.4067C12.1867 12.9949 12.6414 13.5279 13.1633 13.99L13.292 13.91C13.4227 13.83 13.558 13.7587 13.6973 13.6953C14.9787 13.1253 15.9307 13.952 16.6147 14.7367C17.3027 15.522 17.4973 16.2787 17.196 16.9853C16.9727 17.514 16.5493 17.8433 15.9807 18.1753L15.5753 18.41C15.2793 18.5847 14.942 18.6733 14.5987 18.666C12.9373 18.666 10.5533 16.9853 8.74734 14.392C7.19934 12.1753 6.41134 9.712 6.74068 8.142C6.78387 7.8694 6.88358 7.60884 7.0334 7.37704C7.18323 7.14524 7.37986 6.94733 7.61068 6.796L7.94401 6.58733C8.61868 6.15933 9.17134 5.916 9.85001 6.02667ZM9.52334 7.15067C9.31001 7.15067 9.05534 7.24267 8.55268 7.562L8.20401 7.78067C8.11015 7.85228 8.03193 7.94233 7.97415 8.04528C7.91637 8.14823 7.88025 8.26191 7.86801 8.37933C7.65468 9.38933 8.13468 11.4947 9.69268 13.73C11.7247 16.65 14.228 17.8873 14.994 17.428C15.0727 17.3773 15.3933 17.1973 15.4273 17.178C15.93 16.884 16.0807 16.7127 16.156 16.5447C16.1973 16.4467 16.3227 16.1493 15.7667 15.5093C14.9187 14.5407 14.5267 14.604 14.168 14.7627C14.0725 14.8076 13.9793 14.8572 13.8887 14.9113L13.3927 15.1833C13.0533 15.3067 12.3753 15.2213 10.88 13.0747C9.97534 11.7767 9.57068 10.8587 9.67401 10.3613C9.71068 10.1693 9.82468 10.0013 9.98801 9.896L10.108 9.82267L10.3053 9.706C10.8647 9.36067 11.122 9.126 10.4747 7.92933C10.098 7.24267 9.76534 7.18533 9.65468 7.16667C9.61129 7.15849 9.56742 7.15315 9.52334 7.15067Z" fill="#222222"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">WhatsApp</span>
                                <span class="fs-14 theme-text-color d-none lh-20 nodata">
                                    <img src="images/icons/info-circle.svg" class="mar-5" alt=""> please fill in
                                </span>
                                <span class="fs-14 theme-text-dark lh-20 editSecField">15564847000</span>
                            </div>
                        </div>
                    </div> -->

                    <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M24 0H0V24H24V0Z" fill="white" fill-opacity="0.01"></path>
                                    <path d="M22 9V19.9091C22 20.5116 21.5523 21 21 21H3C2.44772 21 2 20.5116 2 19.9091V9L12 15.5L22 9Z" stroke="#222222" stroke-width="1.4" stroke-linejoin="round"></path>
                                    <path d="M2 8.89195L12 2L22 8.89195" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">Email</span>
                                <span class="fs-14 theme-text-dark d-block lh-20 editSecField">{{ $student->email}}</span>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17 2H7C6.17157 2 5.5 2.67157 5.5 3.5V20.5C5.5 21.3284 6.17157 22 7 22H17C17.8284 22 18.5 21.3284 18.5 20.5V3.5C18.5 2.67157 17.8284 2 17 2Z" stroke="#222222" stroke-width="1.4"></path>
                                    <path d="M11 5H13" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M10 19H14" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">Phone number</span>
                                <span class="fs-14 theme-text-dark lh-20 editSecField">1556 4847 000</span>
                            </div>
                        </div>
                    </div> -->

                    <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.92893 16.3787C3.11929 16.9216 2 17.6716 2 18.5C2 20.1569 6.47715 21.5 12 21.5C17.5229 21.5 22 20.1569 22 18.5C22 17.6716 20.8807 16.9216 19.0711 16.3787" stroke="#222222" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M12 17.5C12 17.5 18.5 13.252 18.5 8.3409C18.5 4.83892 15.5898 2 12 2C8.41015 2 5.5 4.83892 5.5 8.3409C5.5 13.252 12 17.5 12 17.5Z" stroke="#222222" stroke-width="1.4" stroke-linejoin="round"></path>
                                    <path d="M12 11C13.3807 11 14.5 9.8807 14.5 8.5C14.5 7.1193 13.3807 6 12 6C10.6193 6 9.5 7.1193 9.5 8.5C9.5 9.8807 10.6193 11 12 11Z" stroke="#222222" stroke-width="1.4" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">Address</span>
                                <span class="fs-14 theme-text-dark d-block lh-20">{{ $student->address}} </span>
                            </div>
                        </div>
                    </div>
                    <div class="settingItems">
                        <div class="settingInner">
                            <div class="itemsIcon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 21H21" stroke="#222222" stroke-width="1.5" stroke-linecap="round"/>
                                    
                                    <path d="M4 21V10L12 4L20 10V21" 
                                        stroke="#222222" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    
                                    <path d="M9 21V14H15V21" 
                                        stroke="#222222" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    
                                    <path d="M9 10H9.01M12 10H12.01M15 10H15.01" 
                                        stroke="#222222" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="itemsTxt">
                                <span class="fs-14 theme-text-mute d-block lh-20">Student School</span>
                                <span class="fs-14 theme-text-dark d-block lh-20">{{ optional($student->schoolRelation)->school_name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="bank" role="tabpanel" aria-labelledby="bank-tab">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="themeCard card themeForm card-custom card-stretch gutter-b mab-15">
            <div class="card-body pall-30">
                <h4 class="fs-16 fw-700 theme-text-dark mab-20">Current balance: {{ number_format($balance, 2) }} zeds</h4>

                @if (auth()->user()->isSuperAdmin())
                <form method="POST" action="{{ route('admin.ledger.reset') }}" class="row g-3">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <div class="col-md-3">
                        <label class="form-label">Set balance to</label>
                        <input type="number" step="0.01" min="0" name="new_balance" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reason (required)</label>
                        <input type="text" name="reason" class="form-control" required minlength="5" maxlength="500">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-warning" onclick="return confirm('Reset this balance? A new adjustment line will be added.')">Reset balance</button>
                    </div>
                </form>
                @endif
            </div>
        </div>

        @if (auth()->user()->isSuperAdmin())
        <div class="themeCard card themeForm card-custom card-stretch gutter-b mab-15">
            <div class="card-body pall-30">
                <h4 class="fs-16 fw-700 theme-text-dark mab-20">Transaction History</h4>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>#</th><th>Date</th><th>Description</th><th>Type</th><th>Amount</th><th>Balance</th><th>Reverse (reason required)</th></tr></thead>
                        <tbody>
                        @foreach ($entries as $e)
                            <tr>
                                <td>{{ $e->id }}</td>
                                <td>{{ $e->transaction_date?->format('Y-m-d H:i') }}</td>
                                <td>{{ $e->description }}</td>
                                <td>{{ $e->type }}</td>
                                <td>{{ number_format($e->amount, 2) }}</td>
                                <td>{{ number_format($e->balance, 2) }}</td>
                                <td>
                                    @if (!empty(($e->meta ?? [])['reversed_by']))
                                        <span class="text-muted">Reversed (line #{{ $e->meta['reversed_by'] }})</span>
                                    @elseif (in_array($e->category, ['Reversal', 'Admin Adjustment', 'Autopilot']))
                                        <span class="text-muted">&mdash;</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.ledger.reverse', $e->id) }}" class="d-flex gap-1" onsubmit="return confirm('Reverse transaction #{{ $e->id }}?')">
                                            @csrf
                                            <input type="text" name="reason" class="form-control form-control-sm" required minlength="5" maxlength="500" placeholder="Reason">
                                            <button class="btn btn-sm btn-outline-danger">Reverse</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="themeCard card themeForm card-custom card-stretch gutter-b mab-15">
            <div class="card-body pall-30">
                <h4 class="fs-16 fw-700 theme-text-dark mab-20">Salary Management</h4>
                <form method="POST" action="{{ route('admin.salary-bonuses.store') }}" class="mab-20">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <input type="text" name="type" class="form-control" list="salaryTypeOptions" placeholder="Bonus" value="{{ old('type') }}">
                            <datalist id="salaryTypeOptions">
                                <option value="Salary">
                                <option value="Allowance">
                                <option value="Bonus">
                                <option value="Payment">
                                <option value="Other">
                            </datalist>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Amount (zeds)</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Frequency</label>
                            <select name="frequency" class="form-control">
                                <option value="">One-time</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Start date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">End date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Reason (required)</label>
                            <input type="text" name="reason" class="form-control" required minlength="5" maxlength="1000">
                        </div>
                    </div>
                    <button type="submit" class="themeBtn text-white mt-3">Add Salary</button>
                </form>

                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead><tr><th>Type</th><th>Amount</th><th>Frequency</th><th>Dates</th><th>Reason</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @forelse ($salaryBonuses as $bonus)
                            <tr>
                                <td>{{ $bonus->type }}</td>
                                <td>{{ number_format($bonus->amount, 2) }} zeds</td>
                                <td>{{ $bonus->frequency ? ucfirst($bonus->frequency) : 'One-time' }}</td>
                                <td>
                                    {{ $bonus->start_date->format('d M Y') }}
                                    @if ($bonus->end_date) &rarr; {{ $bonus->end_date->format('d M Y') }} @endif
                                </td>
                                <td>{{ $bonus->reason }}</td>
                                <td>{{ ucfirst($bonus->status) }}</td>
                                <td class="d-flex gap-1">
                                    @if ($bonus->status === 'active')
                                        <form method="POST" action="{{ route('admin.salary-bonuses.pause', $bonus->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary">Pause</button>
                                        </form>
                                    @elseif ($bonus->status === 'paused')
                                        <form method="POST" action="{{ route('admin.salary-bonuses.resume', $bonus->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary">Resume</button>
                                        </form>
                                    @endif
                                    @if ($bonus->status !== 'cancelled')
                                        <form method="POST" action="{{ route('admin.salary-bonuses.cancel', $bonus->id) }}" onsubmit="return confirm('Remove this salary?')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted p-3">No salary or bonus entries yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="billing" role="tabpanel" aria-labelledby="billing-tab">
        <div class="themeCard card themeForm card-custom card-stretch gutter-b mab-15">
            <div class="card-body pall-30">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-6">
                        <form action="#" method="post">
                            <div class="themealert fs-14 lh-20 alert alert-warning-border mab-20 fade show" role="alert">
                                Once password has been changed, all other devices will be log out except yours
                            </div>
                            <div class="themeForm">
                                
                                <div class="form-group mab-20">
                                    <label for="newpassword" class="form-label">New password</label>
                                    <input class="form-control password" id="newpassword" type="password" placeholder="Please enter" name="newpsw" value="" required="">
                                </div>
                                <div class="form-group mab-20">
                                    <label for="" class="form-label">Confirm Password</label>
                                    <input class="form-control password" id="conpassword" type="password" placeholder="Please enter" name="conpassword" required="">
                                </div>
                                <div class="form-group mab-20 text-end">
                                    <button class="themeBtn text-white" type="submit">Reset</button>
                                </div>
                                <div class="divider"><span>Or</span></div>
                                <div class="form-group mab-20 d-flex gap-3 align-items-center">
                                    <h4 class="fs-16 fw-400 mb-0">Send password reset link via email.</h4>
                                    <button class="secondaryBtn">Send</button>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="integrate" role="tabpanel" aria-labelledby="billing-tab">
        <div class="row">
            <div class="col-md-6 col-lg-4">
                <div class="themeCard card themeForm card-custom card-stretch gutter-b mab-15">
                    <div class="card-body pall-30">
                        <h4 class="fs-16 fw-700 theme-text-dark mab-20">Deactivate Account</h4>
                        <div class="fs-14 mab-30 lh-21">You will not be able to receive messages, notifications for up to 24 hours.</div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="rules1">
                            <label class="form-check-label" for="rules1">&nbsp;</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="themeCard card themeForm card-custom card-stretch gutter-b mab-15">
                    <div class="card-body pall-30">
                        <h4 class="fs-16 fw-700 theme-text-dark mab-20">Delete Account</h4>
                        <div class="fs-14 mab-30 lh-21">Once you delete the account, there is no going back. Please be certain.</div>
                        <button class="themeBtn text-white" data-bs-toggle="modal" data-bs-target="#confirmModal">Delete my account</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="themeModal">
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-350px">
            <div class="modal-content">
                <div class="modal-body text-center pall-60">
                    <h4 class="fs-18 fw-700 mab-20">Are you sure?</h4>
                    <p>You won't be able to revert this!</p>
                    <div class="text-center mat-40">
                        
                        <button type="button" class="themeBtn text-white">Yes Delete it!</button>
                        <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="themeModal rightSide rightsideSticky">
    <form action="{{ url('admin/student/update-details/'.$student->id) }}" method="post">
        @csrf
        <div class="modal fade" id="editprofile" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-600px w-100 m-0 h-100">
                <div class="modal-content h-100">
                    <div class="modal-header">
                        <h4 class="modal-title fs-16 fw-700 theme-text-dark" id="exampleModalLabel">Edit Student</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="themeForm">
                            <div class="row">
                                <!-- <div class="col-xl-3 col-lg-12 col-md-4">
                                    <div class="profileDp">
                                        <div class="uploadImgCon">
                                            <img class="profile-pic" src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg">
                                            <i class="fa fa-camera upload-button"></i>
                                            <input class="file-upload" type="file" accept="image/*"/>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="col-xl-12 col-lg-12 col-md-12 mt-4">
                                    <div class="form">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group mab-20">
                                                    <label for="fullName" class="form-label">Full Name</label>
                                                    <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Full Name" value="{{ $student->name}}">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group mab-20">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="text" class="form-control mb-3" id="email" name="email" placeholder="Write your email here" value="{{ $student->email}}" readonly />
                                                </div>
                                            </div> 
                                            <div class="col-lg-6">
                                                <div class="form-group mab-20">
                                                    <label for="email" class="form-label">Age</label>
                                                    <input type="tel" class="form-control mb-3" id="" name="age" placeholder="Write your Age here" value="{{ $student->age}}" />
                                                </div>
                                            </div> 
                                            <div class="col-lg-12">
                                                <div class="form-group mab-20">
                                                    <label for="email" class="form-label">Citizen ID</label>
                                                    <input type="text" class="form-control mb-3" id="" name="citizenId" placeholder="Write your Citizen ID here" value="{{ $student->citizenId}}" readonly />
                                                </div>
                                            </div> 
                                            <div class="col-lg-6">
                                                <div class="form-group mab-20">
                                                    <label for="school" class="form-label">Schools</label>
                                                    <select class="form-select" id="school" name="school">
                                                        <option value="">Select School</option>
                                                        @foreach($schools as $school)
                                                        <option value="{{ $school->id }}" {{ (isset($student) && $student->sid == $school->id) ? 'selected' : '' }}>{{ $school->school_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group mab-20">
                                                    <label for="profession" class="form-label">Grade Level</label>
                                                    <select class="form-select" id="country" name="grade">
                                                        <option value="">Select Grade</option>
                                                        @foreach($grades as $grade)
                                                        <option value="{{ $grade->id }}" {{ $student->gradeRelation && $student->gradeRelation->id == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-4" style="display:none">
                                                <div class="form-group mab-20">
                                                    <label for="profession" class="form-label">Mascot</label>
                                                    <select class="form-select" id="country" name="mascot">
                                                        <option value="">Select Mascot</option>
                                                         @foreach($mascots as $mascot)
                                                        <option value="{{ $mascot->id }}" {{ $student->mascotRelation && $student->mascotRelation->id == $mascot->id ? 'selected' : '' }}>{{ $mascot->name }} </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            
                                            <div class="col-lg-12">
                                                <div class="form-group mab-20">
                                                    <label for="email" class="form-label">Address</label>
                                                    <input type="text" class="form-control mb-3" id="" name="address" placeholder="Write your address.." value="{{ $student->address}}">
                                                </div>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-center justify-content-center">
                        <button type="button" class="secondaryBtn theme-text-mute" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="themeBtn text-white">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection