@extends('layouts.profile')

@section('title', 'Educational Finance Department')

@section('content')
  @push('styles')
    <link rel="stylesheet" href="{{ asset('asset/front/css/efd.css') }}?ver={{ rand(111, 999) }}" />
    <style>
      .phptoPharane {
        width: 100%;
        height: 100%;
        z-index: 1;
        position: absolute;
        top: 0;
        left: 0;
      }

      .phptoPharane .photoholder {
        overflow: hidden;
        background: #000;
      }

      .phptoPharane .photoholder img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .phptoPharane .photoholder:first-child {
        position: absolute;
        width: 8%;
        height: 19%;
        left: 22%;
        top: 19.7%;
      }

      .phptoPharane .photoholder:nth-child(2) {
        position: absolute;
        top: 16%;
        left: 31.5%;
        width: 7%;
        height: 20%;
      }

      .phptoPharane .photoholder:nth-child(3) {
        position: absolute;
        top: 43.5%;
        left: 17.5%;
        width: 9.2%;
        height: 22.3%;
      }

      .phptoPharane .photoholder:nth-child(4) {
        position: absolute;
        top: 43%;
        left: 29%;
        width: 9.7%;
        height: 29%;
      }

      .phptoPharane .photoholder:nth-child(5) {
        position: absolute;
        top: 61.9%;
        left: 42.7%;
        width: 7.6%;
        height: 21%;
      }

      .phptoPharane .photoholder:nth-child(6) {
        position: absolute;
        top: 29%;
        left: 41.4%;
        width: 10%;
        height: 25%;
      }

      .phptoPharane .photoholder:nth-child(7) {
        position: absolute;
        top: 6%;
        left: 41.4%;
        width: 7%;
        height: 17%;
      }

      .phptoPharane .photoholder:nth-child(8) {
        position: absolute;
        top: 63%;
        left: 54.7%;
        width: 6%;
        height: 17.2%;
      }

      .phptoPharane .photoholder:nth-child(9) {
        position: absolute;
        top: 35%;
        left: 55.3%;
        width: 7%;
        height: 19%;
      }

      .phptoPharane .photoholder:nth-child(10) {
        position: absolute;
        top: 6.7%;
        left: 54.8%;
        width: 8%;
        height: 18%;
      }

      .phptoPharane .photoholder:nth-child(11) {
        position: absolute;
        top: 16.5%;
        left: 67.6%;
        width: 9.3%;
        height: 27.5%;
      }

      .phptoPharane .photoholder:nth-child(12) {
        position: absolute;
        top: 50%;
        left: 65.7%;
        width: 9%;
        height: 25%;
      }

      .phptoPharane .photoholder:nth-child(13) {
        position: absolute;
        top: 50%;
        left: 75.9%;
        width: 5.5%;
        height: 14%;
      }

      /* Transparent clickable overlays */
      .posterClickOverlay {
        position: absolute;
        cursor: zoom-in;
        z-index: 10;
        transition: background-color 0.2s;
      }

      .posterClickOverlay:hover {
        background-color: rgba(255, 255, 255, 0.1);
      }

      .posterClickOverlay:nth-child(1) {
        width: 8%;
        height: 19%;
        left: 22%;
        top: 19.7%;
      }

      .posterClickOverlay:nth-child(2) {
        top: 16%;
        left: 31.5%;
        width: 7%;
        height: 20%;
      }

      .posterClickOverlay:nth-child(3) {
        top: 43.5%;
        left: 17.5%;
        width: 9.2%;
        height: 22.3%;
      }

      .posterClickOverlay:nth-child(4) {
        top: 43%;
        left: 29%;
        width: 9.7%;
        height: 29%;
      }

      .posterClickOverlay:nth-child(5) {
        top: 61.9%;
        left: 42.7%;
        width: 7.6%;
        height: 21%;
      }

      .posterClickOverlay:nth-child(6) {
        top: 29%;
        left: 41.4%;
        width: 10%;
        height: 25%;
      }

      .posterClickOverlay:nth-child(7) {
        top: 6%;
        left: 41.4%;
        width: 7%;
        height: 17%;
      }

      .posterClickOverlay:nth-child(8) {
        top: 63%;
        left: 54.7%;
        width: 6%;
        height: 17.2%;
      }

      .posterClickOverlay:nth-child(9) {
        top: 35%;
        left: 55.3%;
        width: 7%;
        height: 19%;
      }

      .posterClickOverlay:nth-child(10) {
        top: 6.7%;
        left: 54.8%;
        width: 8%;
        height: 18%;
      }

      .posterClickOverlay:nth-child(11) {
        top: 16.5%;
        left: 67.6%;
        width: 9.3%;
        height: 27.5%;
      }

      .posterClickOverlay:nth-child(12) {
        top: 50%;
        left: 65.7%;
        width: 9%;
        height: 25%;
      }

      .posterClickOverlay:nth-child(13) {
        top: 50%;
        left: 75.9%;
        width: 5.5%;
        height: 14%;
      }
    </style>

  @endpush
  <div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">Educational Finance Department</h1>
  </div>
  <div class="grid grid-cols-1 gap-5 mt-6" x-data="{openEmergencyModal: false, emerFundmodal: false}">
    <div class="themeTabspills">
      <div class="w-full">
        <div class="imgparentEFDmain">
          <!-- Library Modal -->
          <div id="libraryModal"
            class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white w-full h-full max-w-full max-h-full rounded-lg shadow-xl  relative overflow-auto">

              <button onclick="closeLibraryModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="9" fill="#E7FBF3"></circle>
                  <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                  </path>
                  <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                  </path>
                </svg>
              </button>
              <div id="libraryModalapp"></div>


            </div>
          </div>
          <!-- Reception Modal -->
          <div id="receptionModal"
            class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white w-[500px] rounded-lg shadow-xl py-12 px-6 relative">

              <button onclick="closeReceptionModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="9" fill="#E7FBF3"></circle>
                  <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                  </path>
                  <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                  </path>
                </svg></button>

              <h2 class="text-xl font-bold mb-4">Reception Panel</h2>

              <!-- <div class="text-gray-700 space-y-2">
                  <p>Handle visitors & inquiries</p>
                  <p>Front desk operations</p>
                  <p>Visitor pass management</p>
              </div> -->
              <div style="max-height: 60vh; overflow: auto;" class="max-w-xl p-4 border rounded space-y-4">
                <div id="displayText" class="fade text-gray-800"></div>


              </div>
              <div class="flex gap-2 justify-end mt-3">
                <button id="backBtn" class="zvBackBtn" disabled>Back</button>
                <button id="nextBtn" class="themeBtn">Next</button>
                <button id="finishBtn" style="display: none;" onclick="closeReceptionModal()" class="secondaryBtn">
                  Finish
                </button>
              </div>


            </div>
          </div>
          <!-- Door Modal -->
          <div id="doorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white w-full h-full rounded-lg shadow-xl py-12 px-6 relative overflow-auto">

              <button onclick="closeDoorModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black"><svg
                  width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="9" fill="#E7FBF3"></circle>
                  <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                  </path>
                  <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                  </path>
                </svg></button>

              <h2 class="text-xl font-bold mb-4">Door Access Panel</h2>

              <div style="position:relative" class="text-gray-700 space-y-2">
                <div class="relative w-full">
                  <div class="phptoPharane">
                    @foreach ($posters as $list)
                      <div class="photoholder">
                        <img class='previewEnabled'
                          data-src="{{ asset('public/uploads/room_poster/' . $list->poster_image) }}" alt="Poster"
                          loading="lazy">
                      </div>
                    @endforeach

                    <!-- <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div>
                      <div class="photoholder"></div> -->
                  </div>
                  <img style="position:relative; z-index:1"
                    src="{{ asset('asset/front/images/PosterlayoutZedville.png') }}" alt="Wall"
                    class="w-full h-auto block" />

                  <!-- Transparent clickable overlays -->
                  <div id="posterOverlaysContainer"
                    style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:10; pointer-events:none;">
                    @foreach ($posters as $index => $list)
                      <div class="posterClickOverlay" data-poster-index="{{ $index }}" style="pointer-events:auto;"></div>
                    @endforeach
                  </div>

                  <!-- Banner slider overlay -->
                  <div style="display:none !important"
                    class="absolute d-none inset-0 flex items-center justify-center pointer-events-none">
                    <div id="bannerSlider"
                      class="w-[80%] max-w-5xl overflow-x-hidden overflow-y-visible pointer-events-auto">


                      <div id="bannerTrack" class="flex space-x-6 py-10">
                        <!-- Each banner -->
                        @foreach ($posters as $list)
                          <div
                            class="banner-item relative flex-shrink-0 w-1/3 px-5 origin-center transition-transform duration-300 ease-out">
                            <div class="relative w-full h-full">
                              <img class="w-full " src="{{ asset('asset/front/images/PosterFrame.svg') }}" />
                              <div style="padding: 18% 3% 3% 3%" class="absolute w-full h-full top-0 left-0 ">
                                <div class="w-full h-full overflow-hidden">
                                  <img class="h-full w-auto object-cover previewEnabled"
                                    data-src="{{ asset('public/uploads/room_poster/' . $list->poster_image) }}"
                                    loading="lazy" />
                                </div>
                              </div>
                            </div>
                          </div>
                        @endforeach
                        <!-- add more banners here -->
                      </div>
                    </div>
                  </div>
                </div>


              </div>

            </div>
          </div>
          <div id="bannerPreviewModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-[99999]">
            <button id="bannerCloseBtn"
              class="absolute top-5 right-5 text-white text-4xl font-bold leading-none hover:text-gray-300">
              ×
            </button>
            <button id="bannerPrev"
              class="absolute left-6 top-1/2 -translate-y-1/2 text-white text-4xl font-bold">‹</button>

            <img id="bannerPreviewImg" src=""
              class="max-h-[90vh] max-w-[90vw] rounded-xl shadow-2xl border-2 border-white" />

            <button id="bannerNext"
              class="absolute right-6 top-1/2 -translate-y-1/2 text-white text-4xl font-bold">›</button>
          </div>

          <div class="imgparentEFD">
            <button class="expandicon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path fill="#ffffff"
                  d="M128 96C110.3 96 96 110.3 96 128L96 224C96 241.7 110.3 256 128 256C145.7 256 160 241.7 160 224L160 160L224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L128 96zM160 416C160 398.3 145.7 384 128 384C110.3 384 96 398.3 96 416L96 512C96 529.7 110.3 544 128 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480L160 416zM416 96C398.3 96 384 110.3 384 128C384 145.7 398.3 160 416 160L480 160L480 224C480 241.7 494.3 256 512 256C529.7 256 544 241.7 544 224L544 128C544 110.3 529.7 96 512 96L416 96zM544 416C544 398.3 529.7 384 512 384C494.3 384 480 398.3 480 416L480 480L416 480C398.3 480 384 494.3 384 512C384 529.7 398.3 544 416 544L512 544C529.7 544 544 529.7 544 512L544 416z" />
              </svg>

            </button>
            <img src="{{ asset('asset/front/images/Educational-Finance-Department2.jpg') }}"
              alt="Educational Finance Department">
            <div class="blackboardHolder">
              <div class="blackboard">
                <div class="blackboard-frame">
                  <div class="blackboard-surface">
                    <div style="display:flex; width:100%; min-height: 100%">
                      <div class=" w-1/2" style=" padding-right:10px; border-right:1px solid #333">
                        <div class="h-full">
                          <div style="font-size:24px; font-weight: 600" class="text-center mb-2">Required</div>
                          <div>
                            @if(optional($emmfundPosition)->required == 1 && isset($bankAccount) && $bankAccount->is_open_emergency_account == 0)
                              <a href="javascript:void(0);" @click="@if (!$isBankAccountOpen)
                                window.location.href = '{{ route('bank.index') }}';
                              @elseif ($bankAccount->is_open_emergency_account == 0)
                                            openEmergencyModal = true;
                                          @else
                                            emerFundmodal = true;
                                          @endif" class="text-md text-yellow-600 mt-1">
                                <div class="blackboardFiled">
                                  <div class="flex">
                                    <div class="mb-2"
                                      style="font-size: 17px; text-align: center; font-weight: 600; color: #000;">Open
                                      Emergency Fund Account</div>
                                  </div>
                                  <div class="flex justify-center gap-2">
                                    <div class="w-14 h-6">
                                      <svg style="width:60px; height:60px" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 640 640">
                                        <path fill="#00A47D"
                                          d="M320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576zM320 112C205.1 112 112 205.1 112 320C112 434.9 205.1 528 320 528C434.9 528 528 434.9 528 320C528 205.1 434.9 112 320 112zM390.7 233.9C398.5 223.2 413.5 220.8 424.2 228.6C434.9 236.4 437.3 251.4 429.5 262.1L307.4 430.1C303.3 435.8 296.9 439.4 289.9 439.9C282.9 440.4 276 437.9 271.1 433L215.2 377.1C205.8 367.7 205.8 352.5 215.2 343.2C224.6 333.9 239.8 333.8 249.1 343.2L285.1 379.2L390.7 234z" />
                                      </svg>
                                    </div>

                                    <div class="leading-tight">
                                      <div style="font-size: 14px; font-weight: 600; color: #000;">Days to complete</div>
                                      <div class="text-lg font-semibold"><span
                                          style="background:#00A47D;  padding: 2px 15px; border-radius: 5px;   margin-top: 3px; display: inline-block; color: #000;">30</span>
                                      </div>
                                    </div>
                                  </div>

                                </div>
                              </a>
                            @endif
                            @if(optional($mbaPosition)->required == 1 && !in_array('mba', $completedActivities))
                              <a href="{{ route('education.index') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: 14px; text-align: center; ">Monthly Budget Activity</div>
                                </div>
                              </a>
                            @endif
                            @if($hbaPosition && $hbaPosition->required == 1 && !in_array('high_budget_activity', $completedActivities))
                              <a href="{{ route('education.high-spending-activities') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: 14px; text-align: center; ">Spending Activity</div>
                                </div>
                              </a>
                            @endif
                            @foreach($taskActivities as $task)
                              @if($task->position == 'required' && !in_array($task->activity_key, $completedActivities))

                                <a href="{{ route('task.page', ['task' => str_replace('_', '-', $task->activity_key)]) }}">
                                  <div class="blackboardFiled">
                                    <div style="font-size: 14px; text-align: center;">
                                      {{ $task->activity_name }}
                                    </div>
                                  </div>
                                </a>
                              @endif
                            @endforeach

                            <!-- <div class="blackboardFiled"></div>
                                <div class="blackboardFiled"></div>
                                <div class="blackboardFiled"></div> -->
                          </div>
                        </div>
                      </div>
                      <div class="w-1/2" style=" padding-left:10px">
                        <div class="h-full">
                          <div style="font-size:24px; font-weight: 600" class="text-center mb-2">Optional</div>
                          <div>
                            @if(optional($emmfundPosition)->optional == 1 && isset($bankAccount) && $bankAccount->is_open_emergency_account == 0)
                              <a href="javascript:void(0);" @click="@if (!$isBankAccountOpen)
                                window.location.href = '{{ route('bank.account.create') }}';
                              @elseif ($bankAccount->is_open_emergency_account == 0)
                                        openEmergencyModal = true;
                                      @else
                                        emerFundmodal = true;
                                      @endif" class="text-md text-yellow-600 mt-1">
                                <div class="blackboardFiled">
                                  <div class="flex">
                                    <div class="mb-2" style="font-size: 17px; text-align: center; font-weight: 600;">Open
                                      Emergency Fund Account</div>
                                  </div>
                                  <div class="flex justify-center gap-2">
                                    <div class="w-14 h-6">
                                      <svg style="width:60px; height:60px" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 640 640">
                                        <path fill="#00A47D"
                                          d="M320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576zM320 112C205.1 112 112 205.1 112 320C112 434.9 205.1 528 320 528C434.9 528 528 434.9 528 320C528 205.1 434.9 112 320 112zM390.7 233.9C398.5 223.2 413.5 220.8 424.2 228.6C434.9 236.4 437.3 251.4 429.5 262.1L307.4 430.1C303.3 435.8 296.9 439.4 289.9 439.9C282.9 440.4 276 437.9 271.1 433L215.2 377.1C205.8 367.7 205.8 352.5 215.2 343.2C224.6 333.9 239.8 333.8 249.1 343.2L285.1 379.2L390.7 234z" />
                                      </svg>
                                    </div>

                                    <div class="leading-tight">
                                      <div style="font-size: 14px; font-weight: 600;">Days to complete</div>
                                      <div class="text-lg font-semibold"><span
                                          style="background:#00A47D;  padding: 2px 15px; border-radius: 5px;   margin-top: 3px; display: inline-block;">30</span>
                                      </div>
                                    </div>
                                  </div>

                                </div>
                              </a>
                            @endif
                            <!-- <div class="blackboardFiled">
                                <div>
                                  <div class="mb-1" style="font-size: 17px; text-align: center; font-weight: 600;">Title
                                  </div>
                                  <div style="font-size: 14px; text-align: center; ">Literacy Badge Point</div>
                                </div>

                              </div>
                              <div class="blackboardFiled">
                                <div>
                                  <div class="mb-1" style="font-size: 17px; text-align: center; font-weight: 600;">Title
                                  </div>
                                  <div style="font-size: 14px; text-align: center;">Engagement Badge Point</div>
                                </div>
                              </div> -->
                            @if(optional($mbaPosition)->optional == 1 && !in_array('mba', $completedActivities))
                              <a href="{{ route('education.index') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: 14px; text-align: center; ">Monthly Budget Activity</div>
                                </div>
                              </a>
                            @endif
                            @if($hbaPosition && $hbaPosition->optional == 1 && !in_array('high_budget_activity', $completedActivities))
                              <a href="{{ route('education.high-spending-activities') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: 14px; text-align: center; ">Spending Activity</div>
                                </div>
                              </a>
                            @endif
                            @foreach($taskActivities as $task)
                              @if($task->position == 'optional' && !in_array($task->activity_key, $completedActivities))

                                <a href="{{ route('task.page', ['task' => str_replace('_', '-', $task->activity_key)]) }}">
                                  <div class="blackboardFiled">
                                    <div style="font-size: 14px; text-align: center;">
                                      {{ $task->activity_name }}
                                    </div>
                                  </div>
                                </a>
                              @endif
                            @endforeach
                            <!-- <div class="blackboardFiled"></div>
                                <div class="blackboardFiled"></div> -->
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
            <div onclick="openLibraryModal()" class="floatingContainer library"></div>
            <div onclick="openReceptionModal()" class="floatingContainer reception"></div>
            <div onclick="openDoorModal()" class="floatingContainer door"></div>
          </div>

        </div>
      </div>
    </div>
    <!-- Open Emergency Fund Account Modal -->
    <div x-show="openEmergencyModal" x-transition.opacity
      class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
      @keydown.escape.window="openEmergencyModal = false" style="display: none;">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="openEmergencyModal = false"></div>
      <!-- Modal Box -->
      <div class="modalDilog max-w-[700px]">
        <div class="modalContent bg-white z-100 rounded-lg border border-color-[#D2DDDB]">
          <form action="{{ route('bank.emengercyfundaccount') }}" method="post">
            @csrf
            <div
              class="sticky top-0 bg-white z-10 border-b border-gray-200 p-6 flex items-center justify-between shadow-sm">
              <div class="flex justify-between align-center w-full">
                <div class="items-center">
                  <!-- <h4 class="text-2xl font-bold text-gray-900">Emergency Fund Account Agreement</h4> -->
                  <h4 class="text-2xl font-bold text-gray-900">Opening Your Emergency Fund Account</h4>
                  <p class="text-sm text-gray-600">Universal Bank</p>
                </div>
                <button type="button" @click="openEmergencyModal = false"
                  class="text-gray-500 hover:opacity-80 focus:outline-none transition">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                    <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                      stroke-linejoin="round" />
                    <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="bodyContent p-6">
              <div class="space-y-6">
                <!-- Intro Section -->
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                  <!-- <h4 class="font-semibold text-blue-900 mb-3 text-lg">Opening Your Emergency Fund Account</h4> -->
                  <div class="space-y-2 text-sm text-gray-800 leading-relaxed">
                    <p><strong>Dear Valued Citizen,</strong></p>
                    <p>Emergency Fund Accounts help you prepare for unexpected expenses.</p>
                    <p><strong>Important:</strong></p>
                    <ul class="list-disc ml-6">
                      <li>The mandatory 20% auto-debit will help build your emergency fund.</li>
                      <li>This account should be used only for emergencies.</li>
                      <li>Your goal should be to save 6 months of your salary.</li>
                      <li>Opening this account is mandatory as part of our new financial responsibility program.</li>
                    </ul>

                  </div>
                </div>

                <!-- Account Information -->
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                  <h4 class="font-semibold text-blue-900 mb-3 text-lg">Account Information</h4>
                  <div class="space-y-2 text-sm">
                    <p><strong>Account Type:</strong> Emergency Fund Account</p>
                    <p><strong>Interest Rate:</strong> 2% per annum</p>
                    <p><strong>Purpose:</strong> Emergency expenses and short-term savings</p>
                    <p><strong>Withdrawal Flexibility:</strong> Flexible withdrawals, no penalties for students</p>
                  </div>
                </div>

                <!-- Financial Education -->
                <div class="bg-gray-50 p-6 rounded-lg">
                  <h4 class="font-semibold text-gray-900 mb-4 text-lg">Financial Education Information</h4>
                  <div class="text-sm text-gray-700 leading-relaxed">
                    <ul class="list-disc ml-6">
                      <li>20% of your salary will be automatically transferred to this account each month.</li>
                      <li>Use this account only for real emergencies.</li>
                      <li>Goal: Save 6 months' worth of salary.</li>
                      <li>Opening this account is mandatory.</li>
                    </ul>
                  </div>
                </div>

                <!-- Instructions -->
                <div class="bg-gray-50 p-6 rounded-lg">
                  <h4 class="font-semibold text-gray-900 mb-4 text-lg">Instructions</h4>
                  <div class="text-sm text-gray-700 leading-relaxed">
                    <ul class="list-disc ml-6">
                      <li>Fill out the Auto-Debit Authorization Form below.</li>
                      <li>Click Submit to complete your application.</li>
                    </ul>
                  </div>
                </div>

                <!-- Auto-Debit Authorization Form -->
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                  <h4 class="font-semibold text-gray-900 mb-4 text-lg">Emergency Fund Account – Auto-Debit Authorization
                    Form</h4>
                  <div class="text-sm text-gray-700 leading-relaxed">
                    <p class="text-sm leading-relaxed">
                      I,
                      <span class="inline-block border-b border-gray-600 px-2">
                        {{$bankAccount->student_name ?? ''}}
                      </span>, authorize the bank to automatically debit
                      20% of my monthly salary
                      <span class="inline-block border-b border-gray-600 px-10">{{ $emmsavingsAmount }}</span> zed
                      from my primary account

                      <span
                        class="inline-block border-b border-gray-600 px-16">{{$bankAccount->emergency_fund_account_number ?? ''}}</span>
                      and transfer it to my Emergency Fund Account
                      every first Friday of the month.
                    </p>

                    <!-- <p>
                                          I, _{{$bankAccount->student_name ?? ''}}________________________ (full name, automatic), authorize the bank to automatically debit 
                                          20% of my monthly salary (_________________________ zed, automatic) from my primary account 
                                          (Account #: ______automatic___________________) and transfer it to my Emergency Fund Account 
                                          every first Friday of the month.
                                      </p> -->
                  </div>
                </div>
              </div>
            </div>
            <div
              class="sticky bottom-0 z-10 bg-white border-t border-gray-200 p-6 flex flex-wrap items-center justify-between shadow-sm">
              <div class="w-full">
                <div class="flex items-center space-x-3 p-4 bg-blue-50 rounded-xl border border-blue-200 w-full">
                  <input id="emeragree-checkbox"
                    class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" required
                    type="checkbox">
                  <label for="emeragree-checkbox" class="text-sm text-gray-700 font-medium"><strong>I have read and agree
                      to the terms above.</strong></label>
                </div>
              </div>
              <div class="w-full mt-4 text-center flex flex-wrap gap-4 justify-center">
                <button type="button" @click="openEmergencyModal = false" class="whiteBtn">Cancel</button>
                <!-- <button type="button" @click="openEmergencyModal = false; emerFundmodal = true" class="themeBtn">Accept & Open Account</button> -->
                <button type="submit" class="themeBtn">Accept & Open Account</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Emergency Fund View -->
    <div x-show="emerFundmodal" x-transition.opacity
      class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal"
      @keydown.escape.window="emerFundmodal = false" style="display: none;">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50" @click="emerFundmodal = false"></div>
      <!-- Modal Box -->
      <div class="modalDilog max-w-full fullscreen">
        <div class="modalContent bg-white z-100 border border-color-[#D2DDDB] h-full">
          <div class="sticky z-10 top-0 bg-white border-b border-gray-200 py-6 px-6 shadow-sm">
            <div class="container mx-auto">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-x-4">
                  <button @click="emerFundmodal = false"
                    class="text-gray-400 hover:text-gray-600 p-3 rounded-full transition-colors" title="Go back">
                    <span class="text-xl font-bold">←</span>
                  </button>
                  <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="lucide lucide-shield text-orange-600">
                      <path
                        d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                      </path>
                    </svg>
                  </div>
                  <div class="flex items-center">
                    <div>
                      <h4 class="text-2xl font-bold text-gray-900">Emergency Fund Account</h4>
                      <p class="text-sm text-gray-600">UB-2024-EMG-1425</p>
                    </div>
                  </div>
                </div>
                <button @click="emerFundmodal = false"
                  class="text-gray-500 hover:opacity-80 focus:outline-none transition">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="9" fill="#E7FBF3" />
                    <path d="M16 8L8 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                      stroke-linejoin="round" />
                    <path d="M8 8L16 16" stroke="#016950" stroke-width="1.2" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div class="bodyContent p-6">
            <div class="container mx-auto max-w-[1040px]">
              <div class="bg-themeyellow rounded-xl p-6 mb-8">
                <div class="flex justify-between items-center">
                  <div>
                    <div class="text-themegreen text-sm mb-1">Available Balance</div>
                    <div class="flex items-center space-x-3">
                      <span
                        class="text-3xl font-bold text-themegreen">{{ $bankAccount ? number_format($bankAccount->emergency_fund_account_amount, 2) : '' }}
                        ZEDS</span>
                    </div>
                    <div class="text-dark-800 text-sm mt-1">Interest Rate: 2% per annum</div>
                  </div>
                  <div class="text-right">
                    <div class="text-dark-800 opacity-90 text-sm">Account Type</div>
                    <div class="text-xl text-dark-800 font-semibold">Emergency Fund</div>
                  </div>
                </div>
              </div>
              <div class="fundProgressSec mt-8 userProfileDtls">
                <h2 class="text-xl font-bold text-gray-900 mb-6">🎯 Emergency Fund Progress Tracker</h2>
                <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 mb-6">
                  <!-- <p class="text-sm text-gray-600 mb-4">Financial experts recommend saving 3-6 months' worth of essential living expenses for your emergency reserve.</p> -->
                  <p class="text-sm text-gray-600 mb-4">An <b>emergency</b> fund is your safety net for unexpected costs
                    like medical bills, and it should cover 3-6 months of essential living expenses like rent, groceries,
                    transportation, and utilities.</p>
                  <!-- <div class="mb-4 form-group">
                                      <label class="block mb-2 text-base font-medium text-black">Monthly Essential Expenses (ZEDS)</label>
                                      <input type="number" placeholder="Enter your monthly expenses" class="form-control" value="1000" />
                                  </div> -->

                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="p-4 bg-white rounded-lg border border-gray-200">
                      <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-900">3-Month Emergency Reserve</h4>
                        <span class="text-sm text-orange-600 font-medium">Basic Safety Net</span>
                      </div>
                      <div class="mb-3">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                          <span>Progress</span>
                          <span>500 / 3,000 ZEDS</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                          <div class="bg-orange-500 h-2 rounded-full transition-all duration-300"
                            style="width: 16.6667%;"></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">16.7% Complete</div>
                      </div>
                      <!-- <p class="text-xs text-gray-600">Covers basic living expenses for short-term emergencies like medical bills or car repairs.</p> -->
                      <p class="text-xs text-gray-600">Covers 3 months of essential living expenses</p>
                    </div>
                    <div class="p-4 bg-white rounded-lg border border-gray-200">
                      <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-900">6-Month Emergency Reserve</h4>
                        <span class="text-sm text-green-600 font-medium">Optimal Security</span>
                      </div>
                      <div class="mb-3">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                          <span>Progress</span>
                          <span>500 / 6,000 ZEDS</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                          <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: 8.33333%;">
                          </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">8.3% Complete</div>
                      </div>
                      <!-- <p class="text-xs text-gray-600">Provides comprehensive protection against major life disruptions like job loss or income reduction.</p> -->
                      <p class="text-xs text-gray-600">Covers 6 months of essential living expenses</p>
                    </div>
                  </div>

                  <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h5 class="font-medium text-blue-900 mb-2">💡 Savings Recommendations</h5>
                    <div class="text-sm text-blue-800 space-y-1">
                      <p>• <strong>Need 2,500 more ZEDS</strong> to reach your 3-month emergency reserve goal</p>
                      <p>• <strong>Need 5,500 more ZEDS</strong> to reach your optimal 6-month emergency reserve</p>
                      <p>• Consider saving <strong>250-500 ZEDS monthly</strong> to reach your 3-month goal in 5-10 months
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="intEarningSection mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">💰 Your Interest Earnings</h2>
                <div class="space-y-4">
                  <div class="w-full">
                    <div
                      class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-purple-50 border-purple-200 hover:bg-purple-100">
                      <div class="flex items-center space-x-3">
                        <div>
                          <h4 class="font-semibold text-md text-gray-900">This Month (Estimated)</h4>
                          <p class="text-2xl font-bold text-themegreen">
                            {{ number_format($emergencyFundInterest['estimated_interest'], 2) }} ZEDS</p>
                          <div class="flex items-center justify-start space-x-1">
                            <span class="text-xs font-medium text-gray-700">Based on current balance</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="w-full">
                    <div
                      class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-blue-50 border-blue-200 hover:bg-blue-100">
                      <div class="flex items-center space-x-3">
                        <div>
                          <h4 class="font-semibold text-md text-gray-700">Annual Projection</h4>
                          <p class="text-2xl font-bold text-blue-500">
                            {{ number_format($emergencyFundInterest['annual_projection'], 2) }} ZEDS</p>
                          <div class="flex items-center justify-start space-x-1">
                            <span class="text-xs font-medium text-gray-700">At
                              {{ $emergencyFundInterest['annual_rate'] }}% rate</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="w-full">
                    <div
                      class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-yellow-50 border-yellow-200 hover:bg-yellow-100">
                      <div class="flex items-center space-x-3">
                        <div>
                          <h4 class="font-semibold text-md text-gray-700">Next Payment</h4>
                          <p class="text-2xl font-bold text-yellow-500">{{ $emergencyFundInterest['next_payment_date'] }}
                          </p>
                          <div class="flex items-center justify-start space-x-1">
                            <span class="text-xs font-medium text-gray-700">Interest credit date</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="accountActivity mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">📋 Recent Activity</h2>
                <div class="border border-gray-100 rounded-lg">
                  @foreach($emmengercyfundtransactions as $emmtxn)
                    <div
                      class="flex flex-wrap gap-y-4 items-center justify-between p-3 border-b border-gray-100 last:border-b-0">
                      <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"><span
                            class="text-lg">🔄</span></div>
                        <div>
                          <p class="font-medium text-gray-900 text-sm">{{ $emmtxn->description }}</p>
                          <div class="flex items-center space-x-2 text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($emmtxn->transaction_date)->diffForHumans() }}
                            {{ \Carbon\Carbon::parse($emmtxn->transaction_date)->format('g:i A') }}</div>
                        </div>
                      </div>
                      <div class="text-right">
                        <p class="text-lg font-bold {{ $emmtxn->type == 'credit' ? 'text-red-600' : 'text-green-600' }}">
                          {{ $emmtxn->type == 'credit' ? '-' . number_format($emmtxn->amount, 2) : '+' . number_format($emmtxn->amount, 2) }}
                          ZEDS</p>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
              <div class="accFeaturesLists">
                <h2 class="text-xl font-bold text-gray-900 mb-6">ℹ️ Account Features & Purpose</h2>
                <div class="p-6 bg-blue-50 rounded-xl border border-blue-200">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <h4 class="text-lg font-semibold text-gray-900 mb-3">Purpose & Benefits</h4>
                      <ul class="text-sm text-gray-700 space-y-2">
                        <li>• Emergency expenses coverage</li>
                        <li>• Short-term savings growth</li>
                        <li>• 2% annual interest rate</li>
                        <li>• No fees for students</li>
                      </ul>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-gray-900 mb-3">Interest Calculation</h4>
                      <ul class="text-sm text-gray-700 space-y-2">
                        <li>• Computed on ending monthly balance</li>
                        <li>• Credited on 1st of each month</li>
                        <li>• Flexible withdrawals allowed</li>
                        <li>• Compound monthly growth</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <style>
    .scrollbar-hide::-webkit-scrollbar {
      display: none;
    }

    .scrollbar-hide {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    /* Bring the EFD modals in line with the app's green theme
         (scoped to .themeModal so nothing else is affected) */
    .themeModal .modalContent {
      border-radius: 0.5rem;
    }

    .themeModal .modalContent .bg-blue-50 {
      background-color: #EEF9F5 !important;
    }

    .themeModal .modalContent .border-blue-200 {
      border-color: #B7E3D6 !important;
    }

    .themeModal .modalContent .text-blue-900 {
      color: #016950 !important;
    }

    .themeModal .modalContent .text-blue-600 {
      color: #00A47D !important;
    }

    .themeModal .modalContent .focus\:ring-blue-500:focus {
      --tw-ring-color: rgba(0, 164, 125, 0.35) !important;
    }

    /* ── Financial Literacy (library modal) — theme alignment ── */

    /* Card radius in line with the rest of the app (8-12px, not 2xl) */
    #libraryModalapp .rounded-2xl {
      border-radius: 12px;
    }

    /* Module card action button = same identity as .themeBtn, full width */
    #libraryModalapp .efd-card-btn {
      display: block;
      width: 100%;
      text-align: center;
      text-transform: none;
      cursor: pointer;
    }

    /* Module page green header */
    #libraryModalapp .efd-module-head {
      color: #fff;
    }

    #libraryModalapp .efd-module-title {
      color: #fff !important;
      line-height: 1.2;
    }

    #libraryModalapp .efd-module-sub {
      margin-top: 4px;
      font-size: 14px;
      color: rgba(255, 255, 255, 0.85) !important;
    }

    #libraryModalapp .efd-back-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 14px;
      padding: 6px 14px;
      border-radius: 30px;
      background: #fff;
      border: 1px solid #000;
      box-shadow: 0 3px 0 #000;
      color: #000;
      font-size: 13px;
      font-weight: 600;
      transition: 0.2s;
    }

    #libraryModalapp .efd-back-btn:hover {
      background: #fff;
      color: #000;
      transform: translateY(3px);
      box-shadow: 0 1px 0 #000;
    }

    #libraryModalapp .efd-back-arrow {
      font-size: 16px;
      line-height: 1;
    }

    /* Quiz heading + progress counter */
    #libraryModalapp .quiz-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 16px;
    }

    #libraryModalapp .quiz-progress {
      display: inline-flex;
      align-items: center;
      padding: 4px 12px;
      border-radius: 30px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.02em;
      background: #FFF5D4;
      color: #8a6d1d;
      border: 1px solid #F1E2B0;
      white-space: nowrap;
    }

    #libraryModalapp .quiz-progress.is-complete {
      background: #EEF9F5;
      color: #016950;
      border-color: #B7E3D6;
    }

    /* "answer all questions" warning */
    #libraryModalapp .quiz-warn {
      margin-top: 10px;
      font-size: 13px;
      font-weight: 600;
      color: #B4232B;
    }

    #libraryModalapp .quiz-warn.shake {
      animation: efdWarnShake 0.4s ease;
    }

    @keyframes efdWarnShake {

      0%,
      100% {
        transform: translateX(0);
      }

      25% {
        transform: translateX(-4px);
      }

      75% {
        transform: translateX(4px);
      }
    }
  </style>
@endsection
@push('scripts')
  <script>
    window.citizenName = "{{ auth()->user()->nickname }}";
  </script>
  <script src="{{ asset('asset/front/js/efd.js') }}?ver={{ rand(111, 999) }}"></script>
  <script src="{{ asset('asset/front/js/modules.js') }}?ver={{ rand(111, 999) }}"></script>
  <script>
    /* ==========================================================================
       FINANCIAL LITERACY APP — Loads ONLY When Modal Opens
       Root element: #libraryModalapp
    ========================================================================== */

    window.currentModule = window.currentModule ?? null;
    window.answers = window.answers ?? {};
    window.showResults = window.showResults ?? {};

    function loadFinancialLiteracyUI() {
      // Prevent reloading if UI already exists
      if (window.__FINANCE_UI_LOADED__) return;
      window.__FINANCE_UI_LOADED__ = true;

      /* ======================================================================
         HOME PAGE
      ====================================================================== */
      window.renderHome = function () {
        const completed = modules.filter(m => {
          const score = getScore(m.id);
          return showResults[m.id] && score.correct === score.total;
        }).length;

        const root = document.getElementById('libraryModalapp');
        if (!root) return;

        const cardsHtml = modules
          .map((module, index) => createModuleCard(module, index))
          .join("");

        root.innerHTML = `
      <div class="bg-gradient-to-r from-[#06795d] to-[#00c29a] text-white shadow-xl">
        <div class="max-w-7xl mx-auto px-6 py-16 text-center">
          <div class="bg-white/20 px-6 py-2 rounded-full inline-block mb-6 uppercase text-sm font-semibold">
            Rookie Level
          </div>

          <h1 class="text-5xl text-blue-100 font-bold mb-3">Financial Literacy</h1>
          <p class="text-blue-100 mb-6">Learn financial skills at your own pace</p>

          <div class="flex justify-center gap-8">
            <div class="bg-white/20 px-6 py-3 rounded-xl">
              <div class="text-3xl font-bold">${completed}/${modules.length}</div>
              <div class="text-sm">Completed</div>
            </div>

            <div class="bg-white/20 px-6 py-3 rounded-xl">
              <div class="text-3xl font-bold">${modules.length}</div>
              <div class="text-sm">Modules</div>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-6 py-12 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        ${cardsHtml}
      </div>
    `;
      };

      /* ======================================================================
         MODULE CARD
      ====================================================================== */
      function createModuleCard(module, index) {
        const score = getScore(module.id);
        const completed = showResults[module.id] && score.correct === score.total;
        const hasAttempt = showResults[module.id];

        return `
      <div
        class="group bg-white text-center relative rounded-2xl shadow-lg  border-2 hover:border-[#00A47D] hover:shadow-2xl transition cursor-pointer overflow-hidden"
        onclick="openModule(${module.id})"
      >
        <!-- Hover gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-br ${module.color} opacity-0 group-hover:opacity-10 transition-opacity"></div>

        <div class="relative p-8">
          <div class="absolute top-4 right-4 w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center font-bold text-slate-600 text-sm">
            ${index + 1}
          </div>

          <div class="text-6xl mb-4">${module.icon}</div>
          <h3 class="text-xl font-bold mb-1">${module.title}</h3>
          <p class="text-sm text-slate-600 mb-4">${module.content.questions.length} Questions</p>

          ${showResults[module.id]
            ? `
            <div class="${completed ? "text-green-600" : "text-amber-600"} font-semibold">
              ${completed
              ? "Completed ✔️"
              : `Score: ${score.correct}/${score.total}`
            }
            </div>
          `
            : ""
          }

          ${hasAttempt
            ? `<div class="themeBtn efd-card-btn mt-4">Review</div>`
            : `<div class="themeBtn efd-card-btn mt-4">Start Learning</div>`
          }
        </div>
      </div>
    `;
      }

      /* ======================================================================
         OPEN MODULE
      ====================================================================== */
      window.openModule = function (id) {
        currentModule = id;
        renderModule();
      };

      /* SCORE CALCULATION */
      function getScore(moduleId) {
        const m = modules.find(x => x.id === moduleId);
        let correct = 0;

        m.content.questions.forEach((q, i) => {
          if (answers[`${moduleId}-${i}`] === q.correct) correct++;
        });

        return { correct, total: m.content.questions.length };
      }

      /* ======================================================================
         MODULE PAGE
      ====================================================================== */
      function renderModule() {
        const root = document.getElementById('libraryModalapp');
        let quizBox = root.querySelector('.quiz-scroll-box');
        let savedScroll = quizBox ? quizBox.scrollTop : 0;
        const module = modules.find(m => m.id === currentModule);

        const allAnswered = module.content.questions.every((q, i) =>
          answers[`${module.id}-${i}`] !== undefined
        );

        const score = getScore(module.id);

        document.getElementById('libraryModalapp').innerHTML = `
              <div class="bg-gradient-to-r ${module.color} shadow-lg px-6 py-6 rounded-b-2xl efd-module-head">
                  <button type="button" onclick="goBack()" class="efd-back-btn">
                      <span class="efd-back-arrow">&larr;</span> Back
                  </button>
                  <h1 class="text-3xl font-bold efd-module-title" style="color:#fff">${module.title}</h1>
                  <p class="efd-module-sub" style="color:rgba(255,255,255,.85)">${module.content.questions.length} Questions</p>
              </div>

              <div class="max-w-7xl mx-auto p-6 grid lg:grid-cols-2 gap-6">
                  ${renderLearning(module)}
                  ${renderQuiz(module, score, allAnswered)}
              </div>
          `;
        requestAnimationFrame(() => {
          let newQuizBox = root.querySelector('.quiz-scroll-box');
          if (newQuizBox) newQuizBox.scrollTop = savedScroll;
        });
      }

      /* ======================================================================
         LEARNING CONTENT
      ====================================================================== */
      function renderLearning(m) {
        return `
              <div class="bg-white rounded-2xl shadow p-6">
                  <h2 class="text-2xl font-bold mb-2">${m.content.subtopic1.title}</h2>
                  ${m.content.subtopic1.text.map(t => `<p class="mb-2">${t}</p>`).join("")}

                  ${m.content.subtopic1.subtitle ? `
                    <h2 class="text-2xl font-bold mb-2">${m.content.subtopic1.subtitle}</h2>
                ` : ""}

                ${m.content.subtopic1.subtext
            ? Array.isArray(m.content.subtopic1.subtext)
              ? m.content.subtopic1.subtext
                .map(t => `<p class="mb-2">${t}</p>`)
                .join("")
              : `<p class="mb-2">${m.content.subtopic1.subtext}</p>`
            : ""
          }



                  <h2 class="text-2xl font-bold mt-6 mb-2">${m.content.subtopic2.title}</h2>
                  ${m.content.subtopic2.text.map(t => `<p class="mb-2">${t}</p>`).join("")}
              </div>
          `;
      }

      /* ======================================================================
         QUIZ SECTION
      ====================================================================== */
      function renderQuiz(m, score, allAnswered) {
        const total = m.content.questions.length;
        const answered = m.content.questions.filter((q, i) =>
          answers[`${m.id}-${i}`] !== undefined
        ).length;

        return `
              <div class="bg-white rounded-2xl shadow p-6">

                  <div class="quiz-head">
                    <h2 class="text-2xl font-bold">Quiz Questions</h2>
                    <span class="quiz-progress ${answered === total ? 'is-complete' : ''}">
                      ${answered}/${total} questions answered
                    </span>
                  </div>

                  <div class="quiz-scroll-box space-y-4 max-h-[600px] overflow-y-auto pr-2 scrollbar-hide">
                    ${m.content.questions.map((q, i) => renderQuestion(m, q, i)).join("")}
                  </div>

                  <div class="mt-6 border-t pt-4 button-group">
                    ${showResults[m.id]
            ? `<button onclick="retry(${m.id})" class="themeBtn">Try Again</button> <button  onclick="goBack()" class="themeBtn">Next Module</button>`
            : `<button type="button" onclick="checkAnswers(${m.id})"
                                 class="themeBtn"
                                 title="${allAnswered ? 'Check your answers' : 'Answer all ' + total + ' questions before checking'}">
                           Check Answers
                         </button>
                         <p id="quizWarn-${m.id}" class="quiz-warn" hidden>
                           Please answer all ${total} questions before checking answers.
                         </p>`
          }
                  </div>
              </div>
          `;
      }

      /* ======================================================================
         QUESTION ITEM
      ====================================================================== */
      function renderQuestion(m, q, i) {
        const selected = answers[`${m.id}-${i}`];
        const show = showResults[m.id];

        return `
              <div>
                  <p class="font-semibold mb-2">${i + 1}. ${q.q}</p>

                  ${q.options.map((option, index) => {
          let bg = "bg-slate-50";

          if (show) {
            if (index === q.correct) bg = "bg-green-100";
            else if (index === selected) bg = "bg-red-100";
          } else if (index === selected) {
            bg = "bg-emerald-100";
          }

          return `
                          <button class="w-full p-3 text-left rounded-xl border-2 mb-2 ${bg}"
                                  ${show ? "" : `onclick="selectAnswer(${m.id}, ${i}, ${index})"`}>
                                  <span class="flex items-center gap-3">
                            <span class="font-medium text-sm bg-white px-2.5 py-1 rounded-lg border border-slate-200">${String.fromCharCode(97 + index)})</span>
                            <span>${option}</span>
                          </span>
                            </button>
                      `;
        }).join("")}
              </div>
          `;
      }

      /* ======================================================================
         CONTROLLERS
      ====================================================================== */
      window.selectAnswer = function (moduleId, qIndex, optIndex) {
        const module = modules.find(m => m.id === moduleId);
        const question = module.content.questions[qIndex];
        answers[`${moduleId}-${qIndex}`] = optIndex;
        //fineHero
        // ✅ Only call API if correct
        if (optIndex === question.correct) {

          fetch('/finhero/award-points', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              activity_key: module.activity_key, // ✅ REQUIRED
              points: 1                           // ✅ REQUIRED
            })
          })
            .then(res => res.json())
            .then(data => {
              console.log('Response:', data);
            })
            .catch(err => console.error('Error:', err));
        }
        //fineHero
        renderModule();
      };

      window.checkAnswers = function (moduleId) {
        const module = modules.find(m => m.id === moduleId);
        const allAnswered = module.content.questions.every((q, i) =>
          answers[`${moduleId}-${i}`] !== undefined
        );

        if (!allAnswered) {
          const warn = document.getElementById('quizWarn-' + moduleId);
          if (warn) {
            warn.hidden = false;
            warn.classList.remove('shake');
            void warn.offsetWidth;
            warn.classList.add('shake');
          }
          return;
        }

        showResults[moduleId] = true;
        renderModule();
      };

      window.retry = function (moduleId) {
        const m = modules.find(x => x.id === moduleId);
        m.content.questions.forEach((q, i) => {
          delete answers[`${moduleId}-${i}`];
        });
        showResults[moduleId] = false;
        renderModule();
      };

      window.goBack = function () {
        currentModule = null;
        renderHome();
      };
    }

    /* ==========================================================================
       OPEN MODAL (Triggers UI Load)
    ========================================================================== */
    function openLibraryModal() {
      document.getElementById('libraryModal').classList.remove('hidden');

      // Load UI only when needed
      loadFinancialLiteracyUI();

      // Render home after load
      renderHome();
    }

    function closeLibraryModal() {
      document.getElementById('libraryModal').classList.add('hidden');
    }
  </script>

  <script>
    const slider = document.getElementById('bannerSlider');
    const track = document.getElementById('bannerTrack');
    const items = Array.from(document.querySelectorAll('.banner-item'));

    // 1) Move banners when cursor moves left/right over slider
    slider.addEventListener('mousemove', (e) => {
      const rect = slider.getBoundingClientRect();
      const x = e.clientX - rect.left;          // cursor position inside slider
      const ratio = x / rect.width;                // 0..1
      const maxScroll = track.scrollWidth - slider.clientWidth;
      slider.scrollLeft = maxScroll * ratio;
    });

    // 2) Zoom the banner that is closest to the center
    function updateZoom() {
      const rect = slider.getBoundingClientRect();
      const centerX = rect.left + rect.width / 2;
      const maxDist = rect.width / 2;

      items.forEach(item => {
        const itemRect = item.getBoundingClientRect();
        const itemCenterX = itemRect.left + itemRect.width / 2;
        const dist = Math.abs(centerX - itemCenterX);
        const t = Math.min(dist / maxDist, 1);     // 0 (center) -> 1 (far)
        const scale = 1.1 - t * 0.2;               // 1.1 in center, down to 0.9
        item.style.transform = `scale(${scale})`;
        item.style.opacity = String(1 - t * 0.2);  // small fade for depth
      });
    }
    document.addEventListener("DOMContentLoaded", () => {

      const modal = document.getElementById("bannerPreviewModal");
      const modalImg = document.getElementById("bannerPreviewImg");
      const prevBtn = document.getElementById("bannerPrev");
      const nextBtn = document.getElementById("bannerNext");

      // Select ONLY images that have previewEnabled class
      let banners = Array.from(document.querySelectorAll(".previewEnabled"));
      let currentIndex = 0;

      function openBanner(index) {
        currentIndex = index;
        modalImg.src = banners[index].src;
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
      }
      const closeBtn = document.getElementById("bannerCloseBtn");

      function closeBannerModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        document.body.style.overflow = ''; // Restore scrolling
      }

      closeBtn.addEventListener("click", closeBannerModal);

      modal.addEventListener("click", (e) => {
        if (e.target === modal) closeBannerModal();
      });

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !modal.classList.contains("hidden")) closeBannerModal();
      });

      nextBtn.addEventListener("click", () => {
        currentIndex = (currentIndex + 1) % banners.length;
        modalImg.src = banners[currentIndex].src;
      });

      prevBtn.addEventListener("click", () => {
        currentIndex = (currentIndex - 1 + banners.length) % banners.length;
        modalImg.src = banners[currentIndex].src;
      });

      // Attach click event to transparent overlay divs
      const overlays = document.querySelectorAll('.posterClickOverlay');
      overlays.forEach((overlay) => {
        const index = parseInt(overlay.getAttribute('data-poster-index'));
        overlay.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          if (banners[index]) {
            openBanner(index);
          }
        });
      });
    });
    // Update zoom on scroll + on resize
    slider.addEventListener('scroll', () => {
      window.requestAnimationFrame(updateZoom);
    });
    window.addEventListener('resize', updateZoom);

    // Initial state
    window.addEventListener('load', updateZoom);


  </script>

@endpush