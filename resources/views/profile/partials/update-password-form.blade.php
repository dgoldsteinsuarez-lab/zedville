<form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')
    <div class="userProfileDtls themeform">
        <div class="fieldsItems border-b border-[#D2DDDB] pb-8">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">{{ __('profile.old_password') }} <span class="text-gray-800">*</span></h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative pswtoggle">
                        <input type="password" id="showpswd" name="current_password" class="form-control" placeholder="********" />
                        <span class="absolute togglePsw inset-y-0 right-3 flex items-center">
                            <svg class="showPsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="3.5" stroke="currentColor"></circle>
                                <path d="M20.188 10.9343C20.5762 11.4056 20.7703 11.6412 20.7703 12C20.7703 12.3588 20.5762 12.5944 20.188 13.0657C18.7679 14.7899 15.6357 18 12 18C8.36427 18 5.23206 14.7899 3.81197 13.0657C3.42381 12.5944 3.22973 12.3588 3.22973 12C3.22973 11.6412 3.42381 11.4056 3.81197 10.9343C5.23206 9.21014 8.36427 6 12 6C15.6357 6 18.7679 9.21014 20.188 10.9343Z" stroke="currentColor"></path>
                            </svg>
                            <svg class="hidePsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.39453 10.5156C9.1445 10.9535 9 11.4596 9 12C9 13.6569 10.3431 15 12 15C12.5402 15 13.0455 14.8544 13.4834 14.6045L14.2109 15.332C13.5774 15.7532 12.8178 16 12 16C9.79086 16 8 14.2091 8 12C8 11.1821 8.24569 10.4217 8.66699 9.78809L9.39453 10.5156ZM12 8C14.2091 8 16 9.79086 16 12C16 12.2736 15.9722 12.5407 15.9199 12.7988L14.9961 11.875C14.9322 10.3173 13.6819 9.06629 12.124 9.00293L11.2002 8.0791C11.4586 8.02668 11.7262 8 12 8Z" fill="currentColor"></path>
                                <path d="M7.21777 8.33887C5.92029 9.30039 4.86765 10.4393 4.19824 11.252C3.77388 11.7672 3.72949 11.8565 3.72949 12C3.72949 12.1435 3.77388 12.2328 4.19824 12.748C4.8934 13.592 6.00033 14.79 7.36719 15.7734C8.73818 16.7598 10.3282 17.5 12 17.5C13.1985 17.5 14.3541 17.1179 15.418 16.5391L16.1523 17.2734C14.9184 17.987 13.5099 18.4999 12 18.5C10.0362 18.5 8.24221 17.6346 6.7832 16.585C5.32033 15.5325 4.15073 14.2639 3.42578 13.3838C3.07382 12.9565 2.72949 12.5741 2.72949 12C2.72949 11.4259 3.07382 11.0435 3.42578 10.6162C4.10362 9.79325 5.16942 8.63002 6.50098 7.62207L7.21777 8.33887ZM12 5.5C13.9638 5.50007 15.7578 6.36537 17.2168 7.41504C18.6797 8.46756 19.8493 9.73608 20.5742 10.6162C20.9262 11.0435 21.2705 11.4259 21.2705 12C21.2705 12.5741 20.9262 12.9565 20.5742 13.3838C20.0937 13.9672 19.416 14.7192 18.5889 15.4678L17.8809 14.7598C18.6764 14.0438 19.3335 13.3165 19.8018 12.748C20.226 12.2329 20.2705 12.1435 20.2705 12C20.2705 11.8565 20.226 11.7671 19.8018 11.252C19.1066 10.408 17.9997 9.20998 16.6328 8.22656C15.2619 7.24022 13.6717 6.50007 12 6.5C11.3058 6.5 10.6259 6.62876 9.96973 6.84863L9.18945 6.06836C10.0693 5.71793 11.0134 5.5 12 5.5Z" fill="currentColor"></path>
                                <path d="M5 2L21 18" stroke="currentColor"></path>
                            </svg>
                        </span>
                    </div>
                    @if ($errors->updatePassword->has('current_password'))
                        <div class="text-red-500 text-sm mt-2">
                            {{ $errors->updatePassword->first('current_password') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="fieldsItems border-b border-[#D2DDDB] py-8">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">{{ __('profile.new_password') }} <span class="text-gray-800">*</span></h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative pswtoggle">
                        <input type="password" id="newshowpswd"  name="password" class="form-control" placeholder="********" />
                        <span class="absolute togglePsw inset-y-0 right-3 flex items-center">
                            <svg class="showPsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="3.5" stroke="currentColor"></circle>
                                <path d="M20.188 10.9343C20.5762 11.4056 20.7703 11.6412 20.7703 12C20.7703 12.3588 20.5762 12.5944 20.188 13.0657C18.7679 14.7899 15.6357 18 12 18C8.36427 18 5.23206 14.7899 3.81197 13.0657C3.42381 12.5944 3.22973 12.3588 3.22973 12C3.22973 11.6412 3.42381 11.4056 3.81197 10.9343C5.23206 9.21014 8.36427 6 12 6C15.6357 6 18.7679 9.21014 20.188 10.9343Z" stroke="currentColor"></path>
                            </svg>
                            <svg class="hidePsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.39453 10.5156C9.1445 10.9535 9 11.4596 9 12C9 13.6569 10.3431 15 12 15C12.5402 15 13.0455 14.8544 13.4834 14.6045L14.2109 15.332C13.5774 15.7532 12.8178 16 12 16C9.79086 16 8 14.2091 8 12C8 11.1821 8.24569 10.4217 8.66699 9.78809L9.39453 10.5156ZM12 8C14.2091 8 16 9.79086 16 12C16 12.2736 15.9722 12.5407 15.9199 12.7988L14.9961 11.875C14.9322 10.3173 13.6819 9.06629 12.124 9.00293L11.2002 8.0791C11.4586 8.02668 11.7262 8 12 8Z" fill="currentColor"></path>
                                <path d="M7.21777 8.33887C5.92029 9.30039 4.86765 10.4393 4.19824 11.252C3.77388 11.7672 3.72949 11.8565 3.72949 12C3.72949 12.1435 3.77388 12.2328 4.19824 12.748C4.8934 13.592 6.00033 14.79 7.36719 15.7734C8.73818 16.7598 10.3282 17.5 12 17.5C13.1985 17.5 14.3541 17.1179 15.418 16.5391L16.1523 17.2734C14.9184 17.987 13.5099 18.4999 12 18.5C10.0362 18.5 8.24221 17.6346 6.7832 16.585C5.32033 15.5325 4.15073 14.2639 3.42578 13.3838C3.07382 12.9565 2.72949 12.5741 2.72949 12C2.72949 11.4259 3.07382 11.0435 3.42578 10.6162C4.10362 9.79325 5.16942 8.63002 6.50098 7.62207L7.21777 8.33887ZM12 5.5C13.9638 5.50007 15.7578 6.36537 17.2168 7.41504C18.6797 8.46756 19.8493 9.73608 20.5742 10.6162C20.9262 11.0435 21.2705 11.4259 21.2705 12C21.2705 12.5741 20.9262 12.9565 20.5742 13.3838C20.0937 13.9672 19.416 14.7192 18.5889 15.4678L17.8809 14.7598C18.6764 14.0438 19.3335 13.3165 19.8018 12.748C20.226 12.2329 20.2705 12.1435 20.2705 12C20.2705 11.8565 20.226 11.7671 19.8018 11.252C19.1066 10.408 17.9997 9.20998 16.6328 8.22656C15.2619 7.24022 13.6717 6.50007 12 6.5C11.3058 6.5 10.6259 6.62876 9.96973 6.84863L9.18945 6.06836C10.0693 5.71793 11.0134 5.5 12 5.5Z" fill="currentColor"></path>
                                <path d="M5 2L21 18" stroke="currentColor"></path>
                            </svg>
                        </span>
                    </div>
                    @if ($errors->updatePassword->has('password'))
                        <div class="text-red-500 text-sm mt-2">
                            {{ $errors->updatePassword->first('password') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="fieldsItems border-b border-[#D2DDDB] py-8">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">{{ __('profile.re_confirm_password') }} <span class="text-gray-800">*</span></h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative pswtoggle">
                        <input type="password" id="renewshowpswd" name="password_confirmation" class="form-control" placeholder="********" />
                        <span class="absolute togglePsw inset-y-0 right-3 flex items-center">
                            <svg class="showPsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="3.5" stroke="currentColor"></circle>
                                <path d="M20.188 10.9343C20.5762 11.4056 20.7703 11.6412 20.7703 12C20.7703 12.3588 20.5762 12.5944 20.188 13.0657C18.7679 14.7899 15.6357 18 12 18C8.36427 18 5.23206 14.7899 3.81197 13.0657C3.42381 12.5944 3.22973 12.3588 3.22973 12C3.22973 11.6412 3.42381 11.4056 3.81197 10.9343C5.23206 9.21014 8.36427 6 12 6C15.6357 6 18.7679 9.21014 20.188 10.9343Z" stroke="currentColor"></path>
                            </svg>
                            <svg class="hidePsw" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.39453 10.5156C9.1445 10.9535 9 11.4596 9 12C9 13.6569 10.3431 15 12 15C12.5402 15 13.0455 14.8544 13.4834 14.6045L14.2109 15.332C13.5774 15.7532 12.8178 16 12 16C9.79086 16 8 14.2091 8 12C8 11.1821 8.24569 10.4217 8.66699 9.78809L9.39453 10.5156ZM12 8C14.2091 8 16 9.79086 16 12C16 12.2736 15.9722 12.5407 15.9199 12.7988L14.9961 11.875C14.9322 10.3173 13.6819 9.06629 12.124 9.00293L11.2002 8.0791C11.4586 8.02668 11.7262 8 12 8Z" fill="currentColor"></path>
                                <path d="M7.21777 8.33887C5.92029 9.30039 4.86765 10.4393 4.19824 11.252C3.77388 11.7672 3.72949 11.8565 3.72949 12C3.72949 12.1435 3.77388 12.2328 4.19824 12.748C4.8934 13.592 6.00033 14.79 7.36719 15.7734C8.73818 16.7598 10.3282 17.5 12 17.5C13.1985 17.5 14.3541 17.1179 15.418 16.5391L16.1523 17.2734C14.9184 17.987 13.5099 18.4999 12 18.5C10.0362 18.5 8.24221 17.6346 6.7832 16.585C5.32033 15.5325 4.15073 14.2639 3.42578 13.3838C3.07382 12.9565 2.72949 12.5741 2.72949 12C2.72949 11.4259 3.07382 11.0435 3.42578 10.6162C4.10362 9.79325 5.16942 8.63002 6.50098 7.62207L7.21777 8.33887ZM12 5.5C13.9638 5.50007 15.7578 6.36537 17.2168 7.41504C18.6797 8.46756 19.8493 9.73608 20.5742 10.6162C20.9262 11.0435 21.2705 11.4259 21.2705 12C21.2705 12.5741 20.9262 12.9565 20.5742 13.3838C20.0937 13.9672 19.416 14.7192 18.5889 15.4678L17.8809 14.7598C18.6764 14.0438 19.3335 13.3165 19.8018 12.748C20.226 12.2329 20.2705 12.1435 20.2705 12C20.2705 11.8565 20.226 11.7671 19.8018 11.252C19.1066 10.408 17.9997 9.20998 16.6328 8.22656C15.2619 7.24022 13.6717 6.50007 12 6.5C11.3058 6.5 10.6259 6.62876 9.96973 6.84863L9.18945 6.06836C10.0693 5.71793 11.0134 5.5 12 5.5Z" fill="currentColor"></path>
                                <path d="M5 2L21 18" stroke="currentColor"></path>
                            </svg>
                        </span>
                    </div>
                    @if ($errors->updatePassword->has('password_confirmation'))
                        <div class="text-red-500 text-sm mt-2">
                            {{ $errors->updatePassword->first('password_confirmation') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="buttons text-center flex items-center justify-center gap-4 mt-8">
        <button type="submit" class="themeBtn ">
            {{ __('profile.update_password') }}
        </button>
        <button type="reset" class="whiteBtn ">
            {{ __('messages.cancel') }}
        </button>
    </div>
    

</form>