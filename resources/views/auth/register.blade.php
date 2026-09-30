
@extends('layouts.front')
@section('title', 'Register')

@section('content')
<section class="registerSection xl:my-20 lg:my-16 md:my-12 sm:my-10 my-8">
    <div class="container mx-auto">
        <div class="max-w-[600px] mx-auto p-[20px] lg:p-[20px] xl:p-[60px] accountFormSection" style="max-width: 600px;">
            <div class="contentHead text-center mb-10">
                <h1 class="heading text-3xl font-bold mb-3">Register an Account</h1>
                <p class="text-base leading-26">After registering an account your money management will be a fun as a virtual citizen. Practice like an adult.</p>
            </div>
            <div class="themeForm iconForm">
                @if ($errors->any())
                    <div class="mb-4">
                        <div class="text-red-600 font-semibold mb-2">Whoops! Something went wrong:</div>
                        <ul class="list-disc list-inside text-sm text-red-500">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="relative form-group mb-5">
                        <label for="" class="form-label mb-2">Name <span class="text-gray-800">*</span></label>
                        <div class="relative haveIcon">
                            <span class="absolute inset-y-3 left-3 flex items-center">
                                <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.99934 2.16675C5.39698 2.16675 1.66602 5.89771 1.66602 10.5001C1.66602 15.1025 5.39698 18.8334 9.99934 18.8334C14.6017 18.8334 18.3327 15.1025 18.3327 10.5001C18.3327 5.89771 14.6017 2.16675 9.99934 2.16675Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M3.55859 15.7881C3.55859 15.7881 5.41612 13.4167 9.99945 13.4167C14.5828 13.4167 16.4403 15.7881 16.4403 15.7881" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 10.5003C11.3807 10.5003 12.5 9.38095 12.5 8.00024C12.5 6.61953 11.3807 5.50024 10 5.50024C8.61929 5.50024 7.5 6.61953 7.5 8.00024C7.5 9.38095 8.61929 10.5003 10 10.5003Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="text" name="name" value="{{old('name')}}" required autofocus minlength="3" maxlength="8" autocomplete="name" class="form-control" placeholder="Enter your full name" >
                        </div>
                    </div>
                    <div class="relative form-group mb-5">
                        <label for="" class="form-label mb-2">Nickname <span class="text-gray-800">*</span></label>
                        <p class="text-xs leading-4 text-black mb-2">This is what other students and your teacher see — never your family name. Your first name is perfect.</p>
                        <div class="relative haveIcon">
                            <span class="absolute inset-y-3 left-3 flex items-center">
                                <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.99934 2.16675C5.39698 2.16675 1.66602 5.89771 1.66602 10.5001C1.66602 15.1025 5.39698 18.8334 9.99934 18.8334C14.6017 18.8334 18.3327 15.1025 18.3327 10.5001C18.3327 5.89771 14.6017 2.16675 9.99934 2.16675Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M3.55859 15.7881C3.55859 15.7881 5.41612 13.4167 9.99945 13.4167C14.5828 13.4167 16.4403 15.7881 16.4403 15.7881" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 10.5003C11.3807 10.5003 12.5 9.38095 12.5 8.00024C12.5 6.61953 11.3807 5.50024 10 5.50024C8.61929 5.50024 7.5 6.61953 7.5 8.00024C7.5 9.38095 8.61929 10.5003 10 10.5003Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="text" name="nickname" value="{{old('nickname')}}" required maxlength="50" pattern="\S+" title="One word only, no spaces — e.g. your first name" class="form-control" placeholder="e.g. your first name">
                        </div>
                    </div>
                    <div class="relative form-group mb-5">
                        <label for="" class="form-label mb-2">Email <span class="text-gray-800">*</span></label>
                        <div class="relative haveIcon">
                            <span class="absolute inset-y-3 left-3 flex items-center">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 15.7143V8.28571C3 7.02335 4.07452 6 5.4 6H18.6C19.9255 6 21 7.02335 21 8.28571V15.7143C21 16.9767 19.9255 18 18.6 18H5.4C4.07452 18 3 16.9767 3 15.7143Z" stroke="currentColor"/>
                                    <path d="M7 9L12 12L17 9" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="email" name="email" value="{{old('email')}}" required autocomplete="username" class="form-control" placeholder="Enter your email address">
                        </div>
                    </div>
                    <div class="relative form-group mb-5">
                        <label for="classCode" class="form-label mb-2">
                            Class Code <span class="text-gray-800">*</span>
                        </label>

                        <div class="relative haveIcon">
                            <span class="absolute inset-y-3 left-3 flex items-center">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2L3 7L12 12L21 7L12 2Z"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                    <path d="M3 17L12 22L21 17"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                    <path d="M3 12L12 17L21 12"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                            </span>

                            <input
                                type="text"
                                name="classCode"
                                value="{{ old('classCode') }}"
                                required
                                maxlength="6"
                                class="form-control"
                                placeholder="Enter 6 digit class code">
                        </div>
                    </div>
                    <!-- <div class="relative form-group mb-5">
                        <label for="" class="form-label mb-2">Re-enter Email <span class="text-gray-800">*</span></label>
                        <div class="relative haveIcon">
                            <span class="absolute inset-y-3 left-3 flex items-center">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 15.7143V8.28571C3 7.02335 4.07452 6 5.4 6H18.6C19.9255 6 21 7.02335 21 8.28571V15.7143C21 16.9767 19.9255 18 18.6 18H5.4C4.07452 18 3 16.9767 3 15.7143Z" stroke="currentColor"/>
                                    <path d="M7 9L12 12L17 9" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="email" name="reEnterEmail" required class="form-control" placeholder="Re-enter your email address">
                        </div>
                    </div> -->
                    <div class="relative form-group mb-5" style="display: none;">
                        <label for="" class="form-label mb-2">Select Role <span class="text-gray-800">*</span></label>
                        <div class="relative haveIcon">
                            <span class="absolute inset-y-0 left-3 flex items-center">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="9.00085" cy="5.4967" r="2.86579" stroke="currentColor" stroke-width="0.9" stroke-linecap="round"/>
                                    <path d="M11.1289 3.59012C11.3186 3.26152 11.6015 2.99657 11.9418 2.82875C12.2821 2.66093 12.6645 2.5978 13.0407 2.64732C13.4169 2.69685 13.7699 2.85681 14.0552 3.10698C14.3405 3.35716 14.5451 3.68631 14.6433 4.05281C14.7416 4.4193 14.7289 4.80669 14.6069 5.16599C14.4849 5.52528 14.2592 5.84033 13.9582 6.07132C13.6571 6.3023 13.2944 6.43883 12.9158 6.46364C12.5372 6.48846 12.1597 6.40044 11.8311 6.21073" stroke="currentColor" stroke-width="0.9"/>
                                    <path d="M6.87257 3.59012C6.68286 3.26152 6.39996 2.99657 6.05966 2.82875C5.71937 2.66093 5.33695 2.5978 4.96076 2.64732C4.58458 2.69685 4.23153 2.85681 3.94627 3.10698C3.661 3.35716 3.45632 3.68631 3.35812 4.05281C3.25991 4.4193 3.27259 4.80669 3.39456 5.16599C3.51652 5.52528 3.74229 5.84033 4.04331 6.07132C4.34433 6.3023 4.70708 6.43883 5.0857 6.46364C5.46431 6.48846 5.84179 6.40044 6.17038 6.21073" stroke="currentColor" stroke-width="0.9"/>
                                    <path d="M9.00081 9.75977C12.8256 9.75977 13.8428 12.8226 14.1133 14.4517C14.2038 14.9966 13.7636 15.444 13.2113 15.444H4.79028C4.238 15.444 3.79782 14.9966 3.88829 14.4517C4.15881 12.8226 5.176 9.75977 9.00081 9.75977Z" stroke="currentColor" stroke-width="0.9" stroke-linecap="round"/>
                                    <path d="M12.4246 8.85376L12.4246 8.40376L12.4245 8.40376L12.4246 8.85376ZM16.1326 12.6614L16.5688 12.5509L16.5688 12.5509L16.1326 12.6614ZM15.2332 13.7454L15.2332 14.1954L15.2333 14.1954L15.2332 13.7454ZM14.0671 13.7454L13.6349 13.8705L13.7289 14.1954H14.0671V13.7454ZM10.0662 9.91626L9.73396 9.61271L9.1753 10.2241L9.99225 10.3601L10.0662 9.91626ZM12.4246 8.85376V9.30376C13.4398 9.30376 14.154 9.79866 14.6759 10.4887C15.2073 11.1913 15.5203 12.0768 15.6963 12.7718L16.1326 12.6614L16.5688 12.5509C16.3799 11.805 16.0297 10.7867 15.3937 9.94577C14.7481 9.09219 13.7908 8.40376 12.4246 8.40376V8.85376ZM16.1326 12.6614L15.6963 12.7718C15.7596 13.0217 15.5732 13.2953 15.233 13.2954L15.2332 13.7454L15.2333 14.1954C16.0761 14.1951 16.7959 13.448 16.5688 12.5509L16.1326 12.6614ZM15.2332 13.7454V13.2954H14.0671V13.7454V14.1954H15.2332V13.7454ZM14.0671 13.7454L14.4994 13.6203C14.0405 12.0348 12.9051 9.93278 10.1401 9.47237L10.0662 9.91626L9.99225 10.3601C12.2624 10.7381 13.2199 12.4366 13.6349 13.8705L14.0671 13.7454ZM10.0662 9.91626L10.3984 10.2198C10.8942 9.67712 11.5429 9.3038 12.4246 9.30376L12.4246 8.85376L12.4245 8.40376C11.2468 8.40381 10.3688 8.91792 9.73396 9.61271L10.0662 9.91626Z" fill="currentColor"/>
                                    <path d="M5.57666 8.85376L5.57666 8.40376L5.57666 8.40376L5.57666 8.85376ZM7.93604 9.91626L8.00991 10.3602L8.82682 10.2242L8.26828 9.61276L7.93604 9.91626ZM3.93408 13.7454V14.1954H4.27232L4.36634 13.8705L3.93408 13.7454ZM2.76807 13.7454L2.76788 14.1954H2.76807V13.7454ZM1.86865 12.6614L1.43242 12.5509L1.43241 12.5509L1.86865 12.6614ZM5.57666 8.85376V9.30376C6.45884 9.30376 7.10801 9.67702 7.60379 10.2198L7.93604 9.91626L8.26828 9.61276C7.63322 8.91754 6.75452 8.40376 5.57666 8.40376V8.85376ZM7.93604 9.91626L7.86216 9.47236C5.09672 9.9326 3.96071 12.0346 3.50182 13.6203L3.93408 13.7454L4.36634 13.8705C4.7813 12.4366 5.73931 10.738 8.00991 10.3602L7.93604 9.91626ZM3.93408 13.7454V13.2954H2.76807V13.7454V14.1954H3.93408V13.7454ZM2.76807 13.7454L2.76826 13.2954C2.42807 13.2952 2.24167 13.0215 2.30489 12.7718L1.86865 12.6614L1.43241 12.5509C1.20531 13.448 1.92525 14.195 2.76788 14.1954L2.76807 13.7454ZM1.86865 12.6614L2.30488 12.7718C2.48088 12.0768 2.7939 11.1913 3.32534 10.4887C3.84719 9.79867 4.5614 9.30377 5.57666 9.30376L5.57666 8.85376L5.57666 8.40376C4.21045 8.40377 3.25309 9.0922 2.60752 9.94578C1.97154 10.7867 1.6213 11.805 1.43242 12.5509L1.86865 12.6614Z" fill="#222222"/>
                                </svg>
                            </span>
                            <select name="role" class="form-control" required id="">
                                <option value="4">Student</option>
                            </select>
                        </div>
                    </div>
                    <div class="relative form-group mb-5">
                        <label for="" class="form-label mb-2">Password <span class="text-gray-800">*</span></label>
                        <div class="relative haveIcon pswtoggle">
                            <span class="absolute inset-y-0 left-3 flex items-center">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16.8 16.4L15 17.75C13.2222 19.0833 10.7778 19.0833 9 17.75L7.2 16.4C5.18555 14.8892 4 12.5181 4 10V6C4 4.89543 4.89543 4 6 4H18C19.1046 4 20 4.89543 20 6V10C20 12.5181 18.8144 14.8892 16.8 16.4Z" stroke="currentColor" stroke-linecap="round"/>
                                    <circle cx="12" cy="10" r="1" fill="currentColor"/>
                                    <circle cx="9" cy="10" r="1" fill="currentColor"/>
                                    <circle cx="15" cy="10" r="1" fill="currentColor"/>
                                </svg>
                            </span>
                            <input type="password" name="password" required autocomplete="new-password"  class="form-control pswfield" placeholder="Password" />
                            <span class="absolute togglePsw inset-y-0 right-3 flex items-center">
                                <svg class="showPsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="3.5" stroke="currentColor"/>
                                    <path d="M20.188 10.9343C20.5762 11.4056 20.7703 11.6412 20.7703 12C20.7703 12.3588 20.5762 12.5944 20.188 13.0657C18.7679 14.7899 15.6357 18 12 18C8.36427 18 5.23206 14.7899 3.81197 13.0657C3.42381 12.5944 3.22973 12.3588 3.22973 12C3.22973 11.6412 3.42381 11.4056 3.81197 10.9343C5.23206 9.21014 8.36427 6 12 6C15.6357 6 18.7679 9.21014 20.188 10.9343Z" stroke="currentColor"/>
                                </svg>
                                <svg class="hidePsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.39453 10.5156C9.1445 10.9535 9 11.4596 9 12C9 13.6569 10.3431 15 12 15C12.5402 15 13.0455 14.8544 13.4834 14.6045L14.2109 15.332C13.5774 15.7532 12.8178 16 12 16C9.79086 16 8 14.2091 8 12C8 11.1821 8.24569 10.4217 8.66699 9.78809L9.39453 10.5156ZM12 8C14.2091 8 16 9.79086 16 12C16 12.2736 15.9722 12.5407 15.9199 12.7988L14.9961 11.875C14.9322 10.3173 13.6819 9.06629 12.124 9.00293L11.2002 8.0791C11.4586 8.02668 11.7262 8 12 8Z" fill="currentColor"/>
                                    <path d="M7.21777 8.33887C5.92029 9.30039 4.86765 10.4393 4.19824 11.252C3.77388 11.7672 3.72949 11.8565 3.72949 12C3.72949 12.1435 3.77388 12.2328 4.19824 12.748C4.8934 13.592 6.00033 14.79 7.36719 15.7734C8.73818 16.7598 10.3282 17.5 12 17.5C13.1985 17.5 14.3541 17.1179 15.418 16.5391L16.1523 17.2734C14.9184 17.987 13.5099 18.4999 12 18.5C10.0362 18.5 8.24221 17.6346 6.7832 16.585C5.32033 15.5325 4.15073 14.2639 3.42578 13.3838C3.07382 12.9565 2.72949 12.5741 2.72949 12C2.72949 11.4259 3.07382 11.0435 3.42578 10.6162C4.10362 9.79325 5.16942 8.63002 6.50098 7.62207L7.21777 8.33887ZM12 5.5C13.9638 5.50007 15.7578 6.36537 17.2168 7.41504C18.6797 8.46756 19.8493 9.73608 20.5742 10.6162C20.9262 11.0435 21.2705 11.4259 21.2705 12C21.2705 12.5741 20.9262 12.9565 20.5742 13.3838C20.0937 13.9672 19.416 14.7192 18.5889 15.4678L17.8809 14.7598C18.6764 14.0438 19.3335 13.3165 19.8018 12.748C20.226 12.2329 20.2705 12.1435 20.2705 12C20.2705 11.8565 20.226 11.7671 19.8018 11.252C19.1066 10.408 17.9997 9.20998 16.6328 8.22656C15.2619 7.24022 13.6717 6.50007 12 6.5C11.3058 6.5 10.6259 6.62876 9.96973 6.84863L9.18945 6.06836C10.0693 5.71793 11.0134 5.5 12 5.5Z" fill="currentColor"/>
                                    <path d="M5 2L21 18" stroke="currentColor"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="relative form-group mb-5">
                        <label for="" class="form-label mb-2">Re-confirm password <span class="text-gray-800">*</span></label>
                        <div class="relative haveIcon pswtoggle">
                            <span class="absolute inset-y-0 left-3 flex items-center">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16.8 16.4L15 17.75C13.2222 19.0833 10.7778 19.0833 9 17.75L7.2 16.4C5.18555 14.8892 4 12.5181 4 10V6C4 4.89543 4.89543 4 6 4H18C19.1046 4 20 4.89543 20 6V10C20 12.5181 18.8144 14.8892 16.8 16.4Z" stroke="currentColor" stroke-linecap="round"/>
                                    <circle cx="12" cy="10" r="1" fill="currentColor"/>
                                    <circle cx="9" cy="10" r="1" fill="currentColor"/>
                                    <circle cx="15" cy="10" r="1" fill="currentColor"/>
                                </svg>
                            </span>
                            <input type="password" name="password_confirmation" required autocomplete="new-password" class="form-control pswfield" placeholder="Re-confirm password">
                            <span class="absolute togglePsw inset-y-0 right-3 flex items-center">
                                <svg class="showPsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="3.5" stroke="currentColor"/>
                                    <path d="M20.188 10.9343C20.5762 11.4056 20.7703 11.6412 20.7703 12C20.7703 12.3588 20.5762 12.5944 20.188 13.0657C18.7679 14.7899 15.6357 18 12 18C8.36427 18 5.23206 14.7899 3.81197 13.0657C3.42381 12.5944 3.22973 12.3588 3.22973 12C3.22973 11.6412 3.42381 11.4056 3.81197 10.9343C5.23206 9.21014 8.36427 6 12 6C15.6357 6 18.7679 9.21014 20.188 10.9343Z" stroke="currentColor"/>
                                </svg>
                                <svg class="hidePsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.39453 10.5156C9.1445 10.9535 9 11.4596 9 12C9 13.6569 10.3431 15 12 15C12.5402 15 13.0455 14.8544 13.4834 14.6045L14.2109 15.332C13.5774 15.7532 12.8178 16 12 16C9.79086 16 8 14.2091 8 12C8 11.1821 8.24569 10.4217 8.66699 9.78809L9.39453 10.5156ZM12 8C14.2091 8 16 9.79086 16 12C16 12.2736 15.9722 12.5407 15.9199 12.7988L14.9961 11.875C14.9322 10.3173 13.6819 9.06629 12.124 9.00293L11.2002 8.0791C11.4586 8.02668 11.7262 8 12 8Z" fill="currentColor"/>
                                    <path d="M7.21777 8.33887C5.92029 9.30039 4.86765 10.4393 4.19824 11.252C3.77388 11.7672 3.72949 11.8565 3.72949 12C3.72949 12.1435 3.77388 12.2328 4.19824 12.748C4.8934 13.592 6.00033 14.79 7.36719 15.7734C8.73818 16.7598 10.3282 17.5 12 17.5C13.1985 17.5 14.3541 17.1179 15.418 16.5391L16.1523 17.2734C14.9184 17.987 13.5099 18.4999 12 18.5C10.0362 18.5 8.24221 17.6346 6.7832 16.585C5.32033 15.5325 4.15073 14.2639 3.42578 13.3838C3.07382 12.9565 2.72949 12.5741 2.72949 12C2.72949 11.4259 3.07382 11.0435 3.42578 10.6162C4.10362 9.79325 5.16942 8.63002 6.50098 7.62207L7.21777 8.33887ZM12 5.5C13.9638 5.50007 15.7578 6.36537 17.2168 7.41504C18.6797 8.46756 19.8493 9.73608 20.5742 10.6162C20.9262 11.0435 21.2705 11.4259 21.2705 12C21.2705 12.5741 20.9262 12.9565 20.5742 13.3838C20.0937 13.9672 19.416 14.7192 18.5889 15.4678L17.8809 14.7598C18.6764 14.0438 19.3335 13.3165 19.8018 12.748C20.226 12.2329 20.2705 12.1435 20.2705 12C20.2705 11.8565 20.226 11.7671 19.8018 11.252C19.1066 10.408 17.9997 9.20998 16.6328 8.22656C15.2619 7.24022 13.6717 6.50007 12 6.5C11.3058 6.5 10.6259 6.62876 9.96973 6.84863L9.18945 6.06836C10.0693 5.71793 11.0134 5.5 12 5.5Z" fill="currentColor"/>
                                    <path d="M5 2L21 18" stroke="currentColor"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-x-4 items-start form-group mb-4 mt-10">
                        <input id="peivacypolicy" type="checkbox" class="rounded">
                        <label for="peivacypolicy" class="text-xs leading-4 text-black">
                            I agree to let FinEdu application to provide digital finance practice tools and resources to process my personal data as explained in the <a href="#" class="theme-text-color">Privacy Policy</a>
                        </label>
                    </div>
                    <div class="flex gap-x-4 items-start form-group mb-4">
                        <input id="terms" type="checkbox" class="rounded">
                        <label for="terms" class="text-xs leading-4 text-black">
                            I understand and agree to the <a href="#" class="theme-text-color">Terms of Service</a>
                        </label>
                    </div>
                    <x-timezone-field />
                    <div class="mt-10 text-center">
                        <button type="submit" class="themeBtn flex items-center justify-center mx-auto gap-2">
                            Get in Touch
                            <span class="icon">
                                <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.08008 10.3H15.0801M15.0801 10.3L9.80008 5.79999M15.0801 10.3L9.80008 14.8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </button>
                    </div>
                    <div class="mt-10 text-center">
                        <div class="text-base leading-6 text-black">Already have an account here? <a href="{{ route('login') }}" class="theme-text-color font-semibold hover:text-blue-900">Login</a></div>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
    
</section>
@endsection