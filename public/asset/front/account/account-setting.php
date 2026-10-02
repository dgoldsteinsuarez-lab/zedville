<?php include 'head.php'; ?>


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
                    Diet
                </button>
                <button class="tabitems tab-button" data-tab="tab4">
                    Closet
                </button>
                <button class="tabitems tab-button" data-tab="tab5">
                    Badges
                </button>
            </div>

            <!-- Tabs Content -->
            <div class="tailCard py-6 lg:py-10 px-6 lg:px-8 max-w-[900px] w-full mt-4 border border-[#D2DDDB] rounded-lg" x-data="{ changeImageModal: false, moodMeater: false }">
                <div id="tab1" class="tab-content active">
                    <form action="#" method="post">
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
                                                <img src="../images/user2.png" alt="User Image" class="w-24 h-24 rounded-full object-cover" id="previewImage">
                                                <button type="button" @click="changeImageModal = true" class="changeImg w-6 h-6 absolute top-0 right-0 rounded-full bg-[#F9F9F9] border border-color-[#E9EBF0] text-black hover:text-themegreen focus:outline-none transition flex items-center justify-center">
                                                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M8.66797 3.95605C9.10473 3.70046 9.64527 3.70046 10.082 3.95605C10.2487 4.05358 10.3992 4.20784 10.5957 4.4043C10.7922 4.60075 10.9464 4.75132 11.0439 4.91797C11.2995 5.35473 11.2995 5.89527 11.0439 6.33203C10.9493 6.49368 10.8004 6.63924 10.6123 6.82715L10.6113 6.83008L6.2998 11.1416C6.12025 11.3212 5.98009 11.4655 5.80371 11.5654C5.62725 11.6653 5.43103 11.7108 5.18457 11.7725L4.03027 12.0605L4.0166 12.0645L4.00195 12.0674C3.84377 12.107 3.68525 12.1473 3.55371 12.1602C3.40937 12.1743 3.18632 12.1687 3.00879 11.9912C2.83126 11.8137 2.82573 11.5906 2.83984 11.4463C2.85348 11.3069 2.89759 11.1372 2.93945 10.9697L3.22754 9.81543C3.28915 9.56897 3.33466 9.37275 3.43457 9.19629C3.53446 9.01991 3.67885 8.87975 3.8584 8.7002L8.1543 4.4043C8.35075 4.20784 8.50132 4.05358 8.66797 3.95605Z" stroke="currentColor" stroke-width="0.8" />
                                                        <path d="M7.8125 4.6875L9.6875 3.4375L11.5625 5.3125L10.3125 7.1875L7.8125 4.6875Z" fill="currentColor" />
                                                    </svg>
                                                </button>
                                                <span class="batchImg absolute bottom-0 right-[-8px]">
                                                    <img src="../images/lavel1.svg" class="w-8" alt="">
                                                </span>
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
                                        <h4 class="text-base font-medium text-black">Full Name</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <input type="text" id="editableName" class="form-control" placeholder="Enter your full name" value="Daniel Goldstein" readonly>
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
                                        <h4 class="text-base font-medium text-black">Email Address</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <input type="email" id="noneditaleEmail" class="form-control" placeholder="Enter your email" value="danielgoldstein@gmail.com" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="fieldsItems border-b border-[#D2DDDB] py-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">Age</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <input type="text" id="editableAge" class="form-control" placeholder="Enter your Age" value="16 Years" readonly>
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
                            <div class="fieldsItems border-b border-[#D2DDDB] py-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">Grade Level</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <input type="text" id="editableGradelabel" class="form-control" placeholder="Enter your grade level" value="Grade 8" readonly>
                                            <button type="button" class="bg-transparent text-black hover:text-themegreen focus:outline-none transition absolute inset-y-3 right-3 flex items-center" onclick="
                                                    const gradeinput = document.getElementById('editableGradelabel');
                                                    gradeinput.removeAttribute('readonly');
                                                    this.setAttribute('disabled', true);
                                                    gradeinput.focus();
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
                                        <h4 class="text-base font-medium text-black">Citizen ID</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <input type="email" id="citizenID" class="form-control" placeholder="Enter Citizen ID" value="ZV-2025-36521" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="fieldsItems border-b border-[#D2DDDB] py-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">Address</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <input type="text" id="editAddress" class="form-control" placeholder="Enter your address" value="234 Street, Avenue Park, South Spain 56987" readonly>
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
                            <div class="fieldsItems border-b border-[#D2DDDB] py-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">Select Option</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <select name="" class="form-select" id="">
                                                <option value="Spain" selected>Spain</option>
                                                <option value="USA">USA</option>
                                                <option value="UK">UK</option>
                                                <option value="Canada">Canada</option>
                                                <option value="Australia">Australia</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="fieldsItems border-b border-[#D2DDDB] py-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">Radio Box</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex items-center">
                                                    <input
                                                        type="radio"
                                                        id="option1"
                                                        name="choice"
                                                        value="option1"
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                                                    <label for="option1" class="ml-2 text-gray-700">Option 1</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input
                                                        type="radio"
                                                        id="option2"
                                                        name="choice"
                                                        value="option2"
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                                                    <label for="option2" class="ml-2 text-gray-700">Option 2</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="fieldsItems border-b border-[#D2DDDB] py-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">Check Box</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        id="check1"
                                                        name="choice"
                                                        value="check1"
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                                                    <label for="check1" class="ml-2 text-gray-700">Option 1</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        id="check2"
                                                        name="choice"
                                                        value="check2"
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                                                    <label for="check2" class="ml-2 text-gray-700">Option 2</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="fieldsItems border-b border-[#D2DDDB] py-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">Textarea</h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative">
                                            <textarea name="" class="form-control min-h-[120px]" id="" placeholder="Type your message"></textarea>
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
                </div>
                <div id="tab2" class="tab-content">
                    <form action="#" method="post">
                        <div class="userProfileDtls themeform">
                            <div class="fieldsItems border-b border-[#D2DDDB] pb-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">Old Password <span class="text-gray-800">*</span></h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative pswtoggle">
                                            <input type="password" id="showpswd" class="form-control" placeholder="********" />
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
                                    </div>
                                </div>
                            </div>
                            <div class="fieldsItems border-b border-[#D2DDDB] py-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">New Password <span class="text-gray-800">*</span></h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative pswtoggle">
                                            <input type="password" id="newshowpswd" class="form-control" placeholder="********" />
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
                                    </div>
                                </div>
                            </div>
                            <div class="fieldsItems border-b border-[#D2DDDB] py-8">
                                <div class="fieldsSec flex flex-wrap lg:flex-nowrap items-center gap-y-4">
                                    <div class="leftContent max-w-full lg:max-w-[300px] w-full">
                                        <h4 class="text-base font-medium text-black">Re-confirm password <span class="text-gray-800">*</span></h4>
                                    </div>
                                    <div class="rightFields w-full">
                                        <div class="relative pswtoggle">
                                            <input type="password" id="renewshowpswd" class="form-control" placeholder="********" />
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
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="buttons text-center flex items-center justify-center gap-4 mt-8">
                            <button type="submit" class="themeBtn ">
                                Update Password
                            </button>
                            <button type="reset" class="whiteBtn ">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
                <div id="tab3" class="tab-content">
                    <p>Diet Not available</p>
                </div>
                <div id="tab4" class="tab-content">
                    <p>Closet Not available</p>
                </div>
                <div id="tab5" class="tab-content">
                    <p>Badges Not available <button type="button" @click="moodMeater = true">Mood Meater</button></p>
                </div>

                <!-- Modals -->

                <div
                    x-show="changeImageModal"
                    x-transition.opacity
                    class="fixed inset-0 z-100 flex my-10 items-center justify-center"
                    @keydown.escape.window="changeImageModal = false" style="display: none;">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="changeImageModal = false"></div>

                    <!-- Modal Box -->
                    <div class="relative overflow-y-auto bg-white w-full max-w-[620px] py-12 px-14 rounded-lg z-100 border border-color-[#D2DDDB]">
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
                            <form action="#" method="post">
                                <div class="heading text-center">
                                    <h4 class="text-xl xl:text-[28px] 2xl:text-[32px] font-semibold text-black mb-4">Profile Image</h4>
                                    <h5 class="text-md xl:text-base font-semibold text-black mb-10">Select an image from our characters</h5>
                                </div>
                                <div class="avtarlists grid grid-cols-3 gap-8 mb-10">
                                    <div class="item">
                                        <input type="radio" name="avatar" id="avatar1" class="hidden" />
                                        <label for="avatar1" class="avtalLabel">
                                            <img src="../images/avtar1.svg" alt="">
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input type="radio" name="avatar" id="avatar2" class="hidden" />
                                        <label for="avatar2" class="avtalLabel">
                                            <img src="../images/avtar2.svg" alt="">
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input type="radio" name="avatar" id="avatar3" class="hidden" />
                                        <label for="avatar3" class="avtalLabel">
                                            <img src="../images/avtar3.svg" alt="">
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input type="radio" name="avatar" id="avatar4" class="hidden" />
                                        <label for="avatar4" class="avtalLabel">
                                            <img src="../images/avtar4.svg" alt="">
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input type="radio" name="avatar" id="avatar5" class="hidden" />
                                        <label for="avatar5" class="avtalLabel">
                                            <img src="../images/avtar5.svg" alt="">
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input type="radio" name="avatar" id="avatar6" class="hidden" />
                                        <label for="avatar6" class="avtalLabel">
                                            <img src="../images/avtar6.svg" alt="">
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input type="radio" name="avatar" id="avatar7" class="hidden" />
                                        <label for="avatar7" class="avtalLabel">
                                            <img src="../images/avtar7.svg" alt="">
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input type="radio" name="avatar" id="avatar8" class="hidden" />
                                        <label for="avatar8" class="avtalLabel">
                                            <img src="../images/avtar8.svg" alt="">
                                        </label>
                                    </div>
                                    <div class="item">
                                        <input type="radio" name="avatar" id="avatar9" class="hidden" />
                                        <label for="avatar9" class="avtalLabel">
                                            <img src="../images/avtar9.svg" alt="">
                                        </label>
                                    </div>
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

                <!-- Mood Meater -->

                <div
                    x-show="moodMeater"
                    x-transition.opacity
                    class="fixed my-10 inset-0 z-100 flex items-center justify-center"
                    @keydown.escape.window="moodMeater = false" style="display: none;">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="moodMeater = false"></div>

                    <!-- Modal Box -->
                    <div class="relative overflow-y-auto bg-white w-full max-w-[620px] py-12 px-14 rounded-lg z-100 border border-color-[#D2DDDB]">
                        <div class="flex justify-between items-center mb-4">
                            <button @click="moodMeater = false" class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                                    <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="bodyContent">
                            <form action="#" method="post">
                                <div class="heading text-center">
                                    <h4 class="text-xl xl:text-[28px] 2xl:text-[32px] font-semibold text-black mb-4">Mood Meter</h4>
                                    <h5 class="text-md xl:text-base font-semibold text-black mb-4">How are you feeling today?</h5>
                                    <p class="text-xs max-w-[350px] w-full mx-auto">Your mood selection will help us understand and this practice will help you build your emotional vocabulary and enhance your emotional wellness.</p>
                                </div>
                                <div class="moodemoji grid grid-cols-2 grid-rows-2 gap-16 mt-10 mb-10">
                                    <div class="emojigroup egroup1 grid grid-cols-2 grid-rows-2 gap-2">
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj1">
                                            <label for="mdemj1">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji1.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#D10500]">Furious</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj2">
                                            <label for="mdemj2">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji2.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#D10500]">Nervous</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj3">
                                            <label for="mdemj3">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji3.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#D10500]">Worried</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj4">
                                            <label for="mdemj4">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji4.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#D10500]">Angry</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="emojigroup egroup2 grid grid-cols-2 grid-rows-2 gap-2">
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj5">
                                            <label for="mdemj5">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji5.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#FFB100]">Furious</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj6">
                                            <label for="mdemj6">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji6.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#FFB100]">Happy</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj7">
                                            <label for="mdemj7">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji7.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#FFB100]">Serene</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj8">
                                            <label for="mdemj8">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji8.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#FFB100]">Calm</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="emojigroup egroup3 grid grid-cols-2 grid-rows-2 gap-2">
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj9">
                                            <label for="mdemj9">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji9.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#023181]">Lonely</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj10">
                                            <label for="mdemj10">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji10.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#023181]">Sad</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj11">
                                            <label for="mdemj11">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji11.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#023181]">Hopeless</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj12">
                                            <label for="mdemj12">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji12.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#023181]">Disappointed</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="emojigroup egroup4 grid grid-cols-2 grid-rows-2 gap-2">
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj13">
                                            <label for="mdemj13">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji13.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#279348]">At Ease</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj14">
                                            <label for="mdemj14">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji14.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#279348]">Content</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj15">
                                            <label for="mdemj15">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji15.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#279348]">Ecstatic</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="items">
                                            <input type="radio" class="hidden" name="modeemoji" id="mdemj16">
                                            <label for="mdemj16">
                                                <div class="mdinner">
                                                    <img src="../images/emoji/mood-emoji16.svg" alt="">
                                                    <div class="heading text-xs font-semobold text-[#279348]">Excited</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button @click="moodMeater = false" class="themeBtn">
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'bottom.php'; ?>