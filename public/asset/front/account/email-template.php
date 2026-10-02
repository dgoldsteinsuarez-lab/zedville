<?php include 'head.php'; ?>
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">Email Template - Example</h1>
</div>

<div class="grid grid-cols-1 gap-5 mt-6">
    <div class="max-w-[900px] w-full bg-white border border-color-[#D2DDDB] rounded p-10">
        <h3 class="text-center text-[24px] md:text-[28px] lg:text-[32px] font-semibold leading-tight border-b border-dashed border-color-[#D2DDDB] pb-8">🌟 Welcome to Virtual City! 🌟</h3>
        <div class="mailBodyTxt mt-10">
            <div class="flex gap-4 flex-wrap xl:flex-nowrap items-start justify-between">
                <div class="avtarImg max-w-[120px] w-full">
                    <img src="../images/avtar1.svg" class="w-full rounded-full" alt="">
                </div>
                <div class="contentSection text-md xl:text-base leading-[18px] text-black space-y-4">
                    <p>Learn to manage money and live independently in our safe, virtual environment!</p>
                    <p>You'll receive a monthly salary in zeds and practice making adult decisions - from paying bills to choosing how to spend your money. Every choice teaches you valuable life skills for the real world.</p>
                    <p>Click <strong>"Begin Setup"</strong> to start your adventure! 🎯</p>
                    <p class="font-semibold">Begin Setup: Citizen profile</p>
                    <p>User Name (Not your real name):</p>
                    <p>Age (real age, no need to write real date): </p>
                </div>
            </div>
            <div class="mt-6 bottomDescriptionTxt pt-6 border-t border-dashed border-color-[#D2DDDB]">
                <h4 class="text-[20px] md:text-[22px] lg:text-[24px] font-semibold leading-tight mb-4">Name the City (Activity)</h4>
                <p class="text-sm text-gray-500"><strong>IMPROVE NOTIFICATION:</strong> Students propose and vote. Tutor should accept (IT GUYS)</p>
            </div>
        </div>
    </div>
</div>

<?php include 'bottom.php'; ?>