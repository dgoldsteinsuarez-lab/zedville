@extends('layouts.profile')

@section('title', 'Bank Account')

@section('content')
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">Bank Account</h1>
</div>
<div class="grid grid-cols-1 gap-5 mt-6"  x-data="{ confirmationModal: false }">
    <!-- Account Opening From -->
     <div class="w-full max-w-[650px]">
        <div class="bg-white rounded-lg border border-[#D2DDDB]">
            <h2 class="text-xl font-bold py-4 border-b border-[#D2DDDB] text-center px-6">Universal Bank Student Account Application</h2>
            <div class="cardBody p-6 userProfileDtls ">
                <form action="{{ route('bank.store') }}" method="post">
                     @csrf
                    <h3 class="text-lg font-bold mb-6">Personal Information</h3>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">Full Name</label>
                                <input class="form-control" name="fullName" placeholder="Enter Full Name" type="text" value="{{ $user->nickname }}" />
                            </div>
                        </div>
                        <!-- <div class="w-full lg:w-[50%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">Last Name</label>
                                <input class="form-control" placeholder="Enter last name" type="text" value="" />
                            </div>
                        </div> -->
                    </div>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4" >
                        <div class="w-full lg:w-[50%]" style="display: none;">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">Date of Birth</label>
                                <input class="form-control" name="dob" placeholder="Enter first name" type="date" />
                            </div>
                        </div>
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">Student ID</label>
                                <input class="form-control" name="studentId" placeholder="Student ID" type="text" value="{{ $user->citizenId }}" disabled />
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold my-6">Contact Information</h3>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full lg:w-[100%]">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">Email Address</label>
                                <input class="form-control" name="email" placeholder="john@zedville.com" type="email" value="{{ $user->email }}" />
                            </div>
                        </div>
                        <div class="w-full lg:w-[50%]" style="display: none;">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">Mobile Number</label>
                                <input class="form-control" name="phone" placeholder="+00 0000 000 000" type="text" />
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4">
                        <div class="w-full">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">Home Address</label>
                                <textarea name="homeAddress" class="form-control logheight" id="" placeholder="Home address...">{{ $user->address }}</textarea>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold my-6" style="display: none;">Account Preferance</h3>
                    <div class="flex flex-wrap lg:flex-nowrap gap-4" style="display: none;">
                        <div class="w-full">
                            <div class="mb-4 form-group">
                                <label class="block mb-2 text-base font-medium text-black">Prefered Username</label>
                                <input class="form-control" name="AccPreName" placeholder="" type="text" />
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-x-4 items-start form-group mb-4">
                        <input id="rcvaccemail" name="rcvaccemail" type="checkbox" class="rounded" value="1">
                        <label for="rcvaccemail" class="text-xs leading-4 text-black">
                            I agree receive account statements via email
                        </label>
                    </div>
                    <div class="flex gap-x-4 items-start form-group">
                        <input id="tandc" type="checkbox" name="tandc" class="rounded" value="1" @click="confirmationModal = true">
                        <label for="tandc" class="text-xs leading-4 text-black" @click="confirmationModal = true" style="cursor: pointer;">
                            I agree to terms & Conditions
                        </label>
                    </div>
                    <x-timezone-field />
                    <div class="mt-10 text-center">
                        <button type="submit" class="themeBtn flex items-center justify-center mx-auto gap-2">
                            Submit Application
                            <span class="icon">
                                <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.08008 10.3H15.0801M15.0801 10.3L9.80008 5.79999M15.0801 10.3L9.80008 14.8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
    <!-- Account Opening From -->
     <div
        x-show="confirmationModal"
        x-transition.opacity
        class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
        @keydown.escape.window="confirmationModal = false" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="confirmationModal = false"></div>
        <!-- Modal Box -->
        <div class="modalDilog max-w-[900px]">
            <div class="modalContent bg-white py-12 px-6 rounded-lg z-100 border border-color-[#D2DDDB]">
                <!-- <div class="flex justify-between items-center mb-4">
                    <button @click="confirmationModal = false" class="absolute top-4 right-4 text-gray-500 hover:opacity-80 focus:outline-none transition">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                            <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div> -->
                <div class="bodyContent">
                    <div class="flex items-center space-x-3 mb-4">
                        <!-- <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield text-red-600">
                                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                            </svg>
                        </div> -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Student Bank Account - Terms & Conditions</h3>
                            <!-- <p class="text-sm text-gray-600">Temporary Service Unavailability</p> -->
                        </div>
                    </div>
                    <!-- <div class="bg-red-50 p-4 rounded-xl border border-red-200 mb-4">
                        <div class="text-sm text-red-800 font-bold mb-1">Service Temporarily Unavailable</div>
                        <div class="text-sm text-red-800">We apologize, but our card freeze functionality is currently undergoing scheduled maintenance and is not available at this time.</div>
                    </div> -->
                    <div class="bg-blue-50 p-4 rounded border border-blue-200 mt-4">
                        <div class="text-sm text-blue-800 font-bold mb-3">Welcome to Zedville!</div>

                        <div class="text-sm text-blue-800 space-y-4">
                            <p><strong>By creating an account, you agree to the following:</strong></p>

                            <ul class="list-decimal list-inside space-y-2">
                                <li>
                                    <strong>Educational Use Only</strong>
                                    <ul class="list-disc list-inside ml-5">
                                        <li>This is a simulated bank for learning purposes.</li>
                                        <li>No real money is involved.</li>
                                        <li>All transactions are practice activities.</li>
                                    </ul>
                                </li>

                                <li>
                                    <strong>Your Account</strong>
                                    <ul class="list-disc list-inside ml-5">
                                        <li>Do not share your real name or personal information.</li>
                                        <li>Keep your login information private.</li>
                                    </ul>
                                </li>

                                <li>
                                    <strong>Appropriate Behavior</strong>
                                    <ul class="list-disc list-inside ml-5">
                                        <li>Be respectful to classmates and teachers.</li>
                                        <li>Use the platform only for class activities.</li>
                                        <li>Follow your teacher's instructions.</li>
                                        <li>Do not post, share, or upload inappropriate content.</li>
                                    </ul>
                                </li>

                                <li>
                                    <strong>Tutor Access</strong>
                                    <ul class="list-disc list-inside ml-5">
                                        <li>Your tutor can view your account activity.</li>
                                        <li>This helps them guide your learning.</li>
                                        <li>All activities are monitored for educational purposes.</li>
                                    </ul>
                                </li>

                                <li>
                                    <strong>Privacy</strong>
                                    <ul class="list-disc list-inside ml-5">
                                        <li>We protect your information.</li>
                                        <li>Only your teacher and school have access to your data.</li>
                                        <li>Your pseudonym keeps your identity private.</li>
                                        <li>We collect basic information (like your name, email, and school) only to help you use the platform.</li>
                                        <li>Your data will not be shared with others without your permission.</li>
                                    </ul>
                                </li>

                                <li>
                                    <strong>Account Rules</strong>
                                    <ul class="list-disc list-inside ml-5">
                                        <li>Complete activities honestly.</li>
                                        <li>Don’t share passwords.</li>
                                        <li>Report any problems to your teacher.</li>
                                    </ul>
                                </li>

                                <li>
                                    <strong>Changes to Terms</strong>
                                    <ul class="list-disc list-inside ml-5">
                                        <li>We may update these Terms and Conditions anytime to improve the platform.</li>
                                        <li>We’ll let you know if there are important changes.</li>
                                    </ul>
                                </li>
                            </ul>
                            <p><strong>By clicking "I Agree," you confirm you understand these rules and will follow them.</strong></p>
                        </div>
                    </div>

                    <div class="text-center mt-8">
                        <button id="agreeButton"  @click="confirmationModal = false" class="themeBtn w-full">I Agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
  const agreeButton = document.getElementById('agreeButton');
  const tandcCheckbox = document.getElementById('tandc');
  const tandcLabel = document.querySelector('label[for="tandc"]');

  agreeButton.addEventListener('click', function() {
    // Disable the checkbox
    tandcCheckbox.disabled = true;

    // Make the label unclickable
    tandcLabel.style.pointerEvents = 'none';
    tandcLabel.style.opacity = '0.6'; // optional: visual feedback
  });
});
</script>
</div>
@endsection