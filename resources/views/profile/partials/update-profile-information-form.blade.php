<form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')
    <div class="userProfileDtls themeform">
        <div class="fieldsItems border-b border-[#D2DDDB] pb-8">
            <div class="uderImgUploadSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-bold text-black mb-1">Profile Picture</h4>
                    <h6 class="text-xs text-[#5C5C5C]">Change or set your profile picture</h6>
                </div>
                <div class="rightImgUpload flex flex-wrap md:flex-nowrap items-center gap-x-8 gap-y-6">
                    <div class="uploadImg">
                        <div class="userImg relative">
                            <img src="{{ asset('asset/front/images/' . ($selectedAvatar->name ?? 'default-avatar.png')) }}" alt="User Image" class="w-24 h-24 rounded-full object-cover" id="previewImage">
                            <button type="button" @click="changeImageModal = true" class="changeImg w-6 h-6 absolute top-0 right-0 rounded-full bg-[#F9F9F9] border border-color-[#E9EBF0] text-black hover:text-themegreen focus:outline-none transition flex items-center justify-center">
                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.66797 3.95605C9.10473 3.70046 9.64527 3.70046 10.082 3.95605C10.2487 4.05358 10.3992 4.20784 10.5957 4.4043C10.7922 4.60075 10.9464 4.75132 11.0439 4.91797C11.2995 5.35473 11.2995 5.89527 11.0439 6.33203C10.9493 6.49368 10.8004 6.63924 10.6123 6.82715L10.6113 6.83008L6.2998 11.1416C6.12025 11.3212 5.98009 11.4655 5.80371 11.5654C5.62725 11.6653 5.43103 11.7108 5.18457 11.7725L4.03027 12.0605L4.0166 12.0645L4.00195 12.0674C3.84377 12.107 3.68525 12.1473 3.55371 12.1602C3.40937 12.1743 3.18632 12.1687 3.00879 11.9912C2.83126 11.8137 2.82573 11.5906 2.83984 11.4463C2.85348 11.3069 2.89759 11.1372 2.93945 10.9697L3.22754 9.81543C3.28915 9.56897 3.33466 9.37275 3.43457 9.19629C3.53446 9.01991 3.67885 8.87975 3.8584 8.7002L8.1543 4.4043C8.35075 4.20784 8.50132 4.05358 8.66797 3.95605Z" stroke="currentColor" stroke-width="0.8" />
                                    <path d="M7.8125 4.6875L9.6875 3.4375L11.5625 5.3125L10.3125 7.1875L7.8125 4.6875Z" fill="currentColor" />
                                </svg>
                            </button>
                            <!-- <span class="batchImg absolute bottom-0 right-[-8px]">
                                <img src="{{ asset('asset/front/images/lavel1.svg')}}" class="w-8" alt="">
                            </span> -->
                        </div>
                    </div>
                    <div class="uploadFields hidden">
                        <div class="btnsSec flex flex-wrap items-center gap-4">
                            <input type="file" id="fileInput" class="hidden" accept=".png, .jpg, .jpeg, .gif" onchange="previewImage(event)">
                            <button type="button" class="themeBtn flex align-center gap-1 py-1 px-4" onclick="document.getElementById('fileInput').click();">
                                <span class="icon">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.99967 4.16667L9.57541 3.74241L9.99967 3.31814L10.4239 3.74241L9.99967 4.16667ZM10.5997 11.6667C10.5997 11.998 10.331 12.2667 9.99967 12.2667C9.6683 12.2667 9.39967 11.998 9.39967 11.6667L10.5997 11.6667ZM5.83301 8.33334L5.40874 7.90907L9.57541 3.74241L9.99967 4.16667L10.4239 4.59094L6.25727 8.7576L5.83301 8.33334ZM9.99967 4.16667L10.4239 3.74241L14.5906 7.90907L14.1663 8.33334L13.7421 8.7576L9.57541 4.59094L9.99967 4.16667ZM9.99967 4.16667L10.5997 4.16667L10.5997 11.6667L9.99967 11.6667L9.39967 11.6667L9.39967 4.16667L9.99967 4.16667Z" fill="currentColor" />
                                        <path d="M4.16699 13.3333L4.16699 14.1667C4.16699 15.0871 4.91318 15.8333 5.83366 15.8333L14.167 15.8333C15.0875 15.8333 15.8337 15.0871 15.8337 14.1667V13.3333" stroke="currentColor" stroke-width="1.2" />
                                    </svg>
                                </span>
                                Upload Image
                            </button>
                            <button type="button" class="whiteBtn flex align-center gap-1 py-1 px-4" onclick="document.getElementById('fileInput').value = ''; previewImage(event);">
                                <span class="icon">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.33301 12.5L8.33301 10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
                                        <path d="M11.667 12.5L11.667 10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
                                        <path d="M2.5 5.83333H17.5V5.83333C17.0353 5.83333 16.803 5.83333 16.6098 5.87176C15.8164 6.02957 15.1962 6.64977 15.0384 7.44315C15 7.63635 15 7.86867 15 8.33333V12.6667C15 14.5523 15 15.4951 14.4142 16.0809C13.8284 16.6667 12.8856 16.6667 11 16.6667H9C7.11438 16.6667 6.17157 16.6667 5.58579 16.0809C5 15.4951 5 14.5523 5 12.6667V8.33333C5 7.86867 5 7.63635 4.96157 7.44315C4.80376 6.64977 4.18356 6.02957 3.39018 5.87176C3.19698 5.83333 2.96466 5.83333 2.5 5.83333V5.83333Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
                                        <path d="M8.39045 2.80883C8.48541 2.72023 8.69465 2.64194 8.98572 2.5861C9.2768 2.53027 9.63343 2.5 10.0003 2.5C10.3672 2.5 10.7239 2.53027 11.0149 2.5861C11.306 2.64194 11.5152 2.72023 11.6102 2.80883" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
                                    </svg>
                                </span>
                                Remove
                            </button>
                        </div>
                        <h6 class="text-xs text-[#5C5C5C] mt-3">We support PNGs, JPEGs, GIFs under 10 MB</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="fieldsItems border-b border-[#D2DDDB] py-8">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">User Name <br/>(write two names, but not your family name)</h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative">
                        <input type="text" name="name" id="editableName" class="form-control" placeholder="User Name" value="{{ old('name', $user->name) }}" minlength="3" maxlength="8" readonly>
                        <button type="button" class="bg-transparent text-black hover:text-themegreen focus:outline-none transition absolute inset-y-3 right-3 flex items-center" onclick="
                                const input = document.getElementById('editableName');
                                input.removeAttribute('readonly');
                                this.setAttribute('disabled', true);
                                input.focus();
                            ">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 5.91406C15.3604 5.91406 15.6531 6.066 15.916 6.2666C16.1673 6.45837 16.4444 6.73733 16.7676 7.06055L16.9395 7.23242C17.2627 7.55564 17.5416 7.83268 17.7334 8.08398C17.934 8.3469 18.0859 8.63961 18.0859 9C18.0859 9.36038 17.934 9.6531 17.7334 9.91602C17.5416 10.1673 17.2627 10.4444 16.9395 10.7676L9.74512 17.9619C9.56928 18.1377 9.4185 18.2942 9.22754 18.4023C9.03664 18.5104 8.82512 18.5589 8.58398 18.6191L5.92969 19.2832C5.7655 19.3242 5.587 19.3702 5.43848 19.3848C5.28375 19.3999 5.02289 19.3959 4.81348 19.1865C4.60407 18.9771 4.6001 18.7163 4.61523 18.5615C4.62976 18.413 4.67575 18.2345 4.7168 18.0703L5.38086 15.416C5.44114 15.1749 5.48962 14.9634 5.59766 14.7725C5.70578 14.5815 5.86225 14.4307 6.03809 14.2549L13.2324 7.06055C13.5556 6.73733 13.8327 6.45837 14.084 6.2666C14.3469 6.066 14.6396 5.91406 15 5.91406Z" stroke="currentColor" />
                                <path d="M12.5 7.5L15.5 5.5L18.5 8.5L16.5 11.5L12.5 7.5Z" fill="currentColor" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="fieldsItems border-b border-[#D2DDDB] py-8">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">Nickname <br/>(what others see — never your family name)</h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative">
                        <input type="text" name="nickname" id="editableNickname" class="form-control" placeholder="Nickname" value="{{ old('nickname', $user->nickname) }}" minlength="3" maxlength="15" pattern="\S+" readonly>
                        <button type="button" class="bg-transparent text-black hover:text-themegreen focus:outline-none transition absolute inset-y-3 right-3 flex items-center" onclick="
                                const input = document.getElementById('editableNickname');
                                input.removeAttribute('readonly');
                                this.setAttribute('disabled', true);
                                input.focus();
                            ">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 5.91406C15.3604 5.91406 15.6531 6.066 15.916 6.2666C16.1673 6.45837 16.4444 6.73733 16.7676 7.06055L16.9395 7.23242C17.2627 7.55564 17.5416 7.83268 17.7334 8.08398C17.934 8.3469 18.0859 8.63961 18.0859 9C18.0859 9.36038 17.934 9.6531 17.7334 9.91602C17.5416 10.1673 17.2627 10.4444 16.9395 10.7676L9.74512 17.9619C9.56928 18.1377 9.4185 18.2942 9.22754 18.4023C9.03664 18.5104 8.82512 18.5589 8.58398 18.6191L5.92969 19.2832C5.7655 19.3242 5.587 19.3702 5.43848 19.3848C5.28375 19.3999 5.02289 19.3959 4.81348 19.1865C4.60407 18.9771 4.6001 18.7163 4.61523 18.5615C4.62976 18.413 4.67575 18.2345 4.7168 18.0703L5.38086 15.416C5.44114 15.1749 5.48962 14.9634 5.59766 14.7725C5.70578 14.5815 5.86225 14.4307 6.03809 14.2549L13.2324 7.06055C13.5556 6.73733 13.8327 6.45837 14.084 6.2666C14.3469 6.066 14.6396 5.91406 15 5.91406Z" stroke="currentColor" />
                                <path d="M12.5 7.5L15.5 5.5L18.5 8.5L16.5 11.5L12.5 7.5Z" fill="currentColor" />
                            </svg>
                        </button>
                        @error('nickname')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="fieldsItems border-b border-[#D2DDDB] py-8">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">Email Address</h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative">
                        <input type="email" name="email" id="noneditaleEmail" class="form-control" placeholder="Enter your email" value="{{ old('email', $user->email) }}" disabled>
                    </div>
                </div>
            </div>
        </div>

        <div class="fieldsItems border-b border-[#D2DDDB] py-8" style="display:none">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">Age</h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative">
                        <input type="text" id="editableAge" name="age" class="form-control" placeholder="Enter your Age" value="{{ old('age', $user->age) }}" readonly>
                        <button type="button" class="bg-transparent text-black hover:text-themegreen focus:outline-none transition absolute inset-y-3 right-3 flex items-center" onclick="
                                const ageinput = document.getElementById('editableAge');
                                ageinput.removeAttribute('readonly');
                                this.setAttribute('disabled', true);
                                ageinput.focus();
                            ">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 5.91406C15.3604 5.91406 15.6531 6.066 15.916 6.2666C16.1673 6.45837 16.4444 6.73733 16.7676 7.06055L16.9395 7.23242C17.2627 7.55564 17.5416 7.83268 17.7334 8.08398C17.934 8.3469 18.0859 8.63961 18.0859 9C18.0859 9.36038 17.934 9.6531 17.7334 9.91602C17.5416 10.1673 17.2627 10.4444 16.9395 10.7676L9.74512 17.9619C9.56928 18.1377 9.4185 18.2942 9.22754 18.4023C9.03664 18.5104 8.82512 18.5589 8.58398 18.6191L5.92969 19.2832C5.7655 19.3242 5.587 19.3702 5.43848 19.3848C5.28375 19.3999 5.02289 19.3959 4.81348 19.1865C4.60407 18.9771 4.6001 18.7163 4.61523 18.5615C4.62976 18.413 4.67575 18.2345 4.7168 18.0703L5.38086 15.416C5.44114 15.1749 5.48962 14.9634 5.59766 14.7725C5.70578 14.5815 5.86225 14.4307 6.03809 14.2549L13.2324 7.06055C13.5556 6.73733 13.8327 6.45837 14.084 6.2666C14.3469 6.066 14.6396 5.91406 15 5.91406Z" stroke="currentColor" />
                                <path d="M12.5 7.5L15.5 5.5L18.5 8.5L16.5 11.5L12.5 7.5Z" fill="currentColor" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="fieldsItems border-b border-[#D2DDDB] py-8" style="display: none;">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">Grade Level</h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative">
                        <select name="grade" class="form-select">
                            <option value="">Select Grade Level</option>
                            @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" {{ $user->grade == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="fieldsItems border-b border-[#D2DDDB] py-8">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">Citizen ID</h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative">
                        <input type="text" id="citizenID" class="form-control" placeholder="Enter Citizen ID" value="{{ old('citizenId', $user->citizenId) }}" disabled>
                    </div>
                </div>
            </div>
        </div>
        @if ($myAddress)
        <div class="fieldsItems border-b border-[#D2DDDB] py-8">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">My City Address</h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative">
                        <input type="text" class="form-control" value="{{ $myAddress->house_number }} {{ $myAddress->street->name }}" disabled>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="fieldsItems border-b border-[#D2DDDB] py-8">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">Address</h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative">
                        <input type="text" id="editAddress" name="address" class="form-control" placeholder="Enter your address" value="{{ old('address', $user->address) }}" readonly>
                        <button type="button" class="bg-transparent text-black hover:text-themegreen focus:outline-none transition absolute inset-y-3 right-3 flex items-center" onclick="
                                const addInput = document.getElementById('editAddress');
                                addInput.removeAttribute('readonly');
                                this.setAttribute('disabled', true);
                                addInput.focus();
                            ">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 5.91406C15.3604 5.91406 15.6531 6.066 15.916 6.2666C16.1673 6.45837 16.4444 6.73733 16.7676 7.06055L16.9395 7.23242C17.2627 7.55564 17.5416 7.83268 17.7334 8.08398C17.934 8.3469 18.0859 8.63961 18.0859 9C18.0859 9.36038 17.934 9.6531 17.7334 9.91602C17.5416 10.1673 17.2627 10.4444 16.9395 10.7676L9.74512 17.9619C9.56928 18.1377 9.4185 18.2942 9.22754 18.4023C9.03664 18.5104 8.82512 18.5589 8.58398 18.6191L5.92969 19.2832C5.7655 19.3242 5.587 19.3702 5.43848 19.3848C5.28375 19.3999 5.02289 19.3959 4.81348 19.1865C4.60407 18.9771 4.6001 18.7163 4.61523 18.5615C4.62976 18.413 4.67575 18.2345 4.7168 18.0703L5.38086 15.416C5.44114 15.1749 5.48962 14.9634 5.59766 14.7725C5.70578 14.5815 5.86225 14.4307 6.03809 14.2549L13.2324 7.06055C13.5556 6.73733 13.8327 6.45837 14.084 6.2666C14.3469 6.066 14.6396 5.91406 15 5.91406Z" stroke="currentColor" />
                                <path d="M12.5 7.5L15.5 5.5L18.5 8.5L16.5 11.5L12.5 7.5Z" fill="currentColor" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="fieldsItems border-b border-[#D2DDDB] py-8"  style="display: none;">
            <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                    <h4 class="text-base font-medium text-black">Select Mascot</h4>
                </div>
                <div class="rightFields w-full">
                    <div class="relative">
                        <select name="mascot" class="form-select">
                            <option value="">Select Mascot</option>
                            @foreach($mascots as $mascot)
                            <option value="{{ $mascot->id }}" {{ $user->mascot == $mascot->id ? 'selected' : '' }}>{{ $mascot->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="buttons text-center flex items-center justify-center gap-4 mt-8">
        <button type="submit" class="themeBtn ">
            Save Changes
        </button>
        <button type="reset" class="whiteBtn ">
            Cancel
        </button>
    </div>
</form>

 <!-- Modals -->

<div
    x-show="changeImageModal"
    x-transition.opacity
    class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
    @keydown.escape.window="changeImageModal = false" style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="changeImageModal = false"></div>

    <!-- Modal Box -->
    <div class="modalDilog max-w-[620px]">
        <div class="modalContent bg-white py-12 px-14 rounded-lg z-100 border border-color-[#D2DDDB]">
            <div class="flex justify-between items-center mb-4">
                <button @click="changeImageModal = false" class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                        <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="bodyContent">
                <form method="post" action="{{ route('profile.updateAvatar') }}">
                @csrf
                    <div class="heading text-center">
                        <h4 class="text-xl xl:text-[28px] 2xl:text-[32px] font-semibold text-black mb-4">Profile Image</h4>
                        <h5 class="text-md xl:text-base font-semibold text-black mb-10">Select an image from our characters</h5>
                    </div>
                    <div class="avtarlists grid grid-cols-3 gap-8 mb-10">
                        @foreach($avatar as $avatars)
                        @php
                                $inputId = 'avatar' . $loop->iteration; // Create unique ID: avatar1, avatar2, etc.
                            @endphp
                        <div class="item">
                            <input type="radio" name="avatar" id="{{ $inputId }}" value="{{ $avatars->id }}" class="hidden"  {{ old('avatar', $user->avatar) == $avatars->id ? 'checked' : '' }} />
                            <label for="{{ $inputId }}" class="avtalLabel">
                                <img src="{{ asset('asset/front/images/' . $avatars->name)}}" alt="">
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <div class="text-center">
                        <button @click="changeImageModal = false" class="themeBtn">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>