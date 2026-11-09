@extends('layouts.profile')

@section('title', __('efd.title'))
@section('no_page_back', '1')

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

      /* The department as one picture that fits the screen (x-city-scene,
         Daniel 2026-10-27). Its name sits above the board, sized with the
         picture (cqw), so the board moves down a little to make room. */
      .zs--efd .zs-title { top: .8cqw; padding: .25em .9em; font-size: clamp(10px, 1.9cqw, 26px); }
      /* The board is an exact smaller copy of itself on a smaller picture
         (Daniel, 2026-10-27: "all is viewable when I adjust the size of the
         browser"): every size on it is in design pixels x --bb-u, where
         --bb-u is 1/1280 of the picture's width, so on a 1280px-wide picture
         it looks exactly as it was designed. */
      .zs--efd .blackboardHolder { top: 8.5%; height: 33.5%;
        --bb-u: .078125cqw;
        --bb-head: max(11px, calc(24 * var(--bb-u))); --bb-name: max(8px, calc(17 * var(--bb-u)));
        --bb-text: max(7px, calc(14 * var(--bb-u))); --bb-icon: max(26px, calc(60 * var(--bb-u))); }
      .zs--efd .blackboard-frame { padding: calc(4 * var(--bb-u)); border-radius: calc(14 * var(--bb-u)); }
      .zs--efd .blackboard-surface { padding: calc(10 * var(--bb-u)); border-radius: calc(10 * var(--bb-u)); }
      .zs--efd .blackboard-surface .mb-2 { margin-bottom: calc(8 * var(--bb-u)); }
      .zs--efd .blackboardFiled { padding: calc(10 * var(--bb-u)); border-width: max(1.5px, calc(3 * var(--bb-u)));
        margin-bottom: calc(5 * var(--bb-u)); border-radius: calc(5 * var(--bb-u)); }
      .zs--efd .blackboardFiled .gap-2 { gap: calc(8 * var(--bb-u)); }
      .zs--efd .blackboardFiled .w-14 { width: max(24px, calc(56 * var(--bb-u))); height: max(10px, calc(24 * var(--bb-u))); }
      .zs--efd .blackboardFiled .text-lg { font-size: max(9px, calc(18 * var(--bb-u))); line-height: 1.55; }
      .zs--efd .blackboardFiled .text-lg span { padding: calc(2 * var(--bb-u)) calc(15 * var(--bb-u)) !important;
        border-radius: calc(5 * var(--bb-u)) !important; margin-top: calc(3 * var(--bb-u)) !important; }
      /* the days left to do an activity, and what a Required one not done in time costs (2026-11-03) */
      .zs--efd .bb-days { margin-top: calc(3 * var(--bb-u)); font-size: max(7px, calc(12 * var(--bb-u))); font-weight: 700; text-align: center; color: #8A4B08; }
      .zs--efd .bb-fine { margin: calc(-4 * var(--bb-u)) 0 calc(4 * var(--bb-u)); font-size: max(7px, calc(12 * var(--bb-u))); font-weight: 600; text-align: center; color: #A11D1D; }
      .zs--efd .bb-why { margin: 0 0 calc(8 * var(--bb-u)); font-size: max(7px, calc(12 * var(--bb-u))); text-align: center; color: #3B3B3B; }
      .zs--efd .bb-why summary { cursor: pointer; font-weight: 700; color: #A11D1D; text-decoration: underline; list-style: none; }
      .zs--efd .bb-why summary::-webkit-details-marker { display: none; }
      .zs--efd .bb-why p { margin: calc(4 * var(--bb-u)) 0 0; padding: calc(6 * var(--bb-u)); border-radius: calc(5 * var(--bb-u)); background: #FFF6E5; text-align: left; line-height: 1.4; }
      /* Phones: the picture is too small for a readable board on the wall, so the
         board sits under the picture at its designed size (--bb-u a real pixel).
         The scene then grows below the picture, so the picture keeps its own cut
         (the frame no longer has the picture's shape), and the doors' spots are
         placed in cqw (% of the picture's width = its full height / 1.5). */
      @media (max-width: 700px) {
        .zs--efd .zs-scene { aspect-ratio: auto !important; overflow: visible; box-shadow: none; border-radius: 0; }
        .zs--efd .zs-frame { position: relative; aspect-ratio: auto !important; }
        .zs--efd .zs-img { height: auto; aspect-ratio: 1536 / 864; object-position: top; border-radius: 14px; box-shadow: 0 12px 28px rgba(15, 42, 34, .16); }
        .zs--efd .blackboardHolder { position: static; width: auto; height: auto; margin: 14px 0 0; --bb-u: 1px; }
        .zs--efd .floatingContainer.library { top: 29.667cqw; min-height: 18.667cqw; }
        .zs--efd .floatingContainer.reception { top: 40.333cqw; min-height: 9.333cqw; }
        .zs--efd .floatingContainer.door { top: 33.333cqw; min-height: 14.667cqw; }
      }
      /* Full screen (the expand button): the picture fills the window instead. */
      .imgparentEFDmain.expanded { display: grid; place-items: center; background: #fff; }
      .imgparentEFDmain.expanded .zs { width: 100%; }
      .imgparentEFDmain.expanded .zv-page-back, .imgparentEFDmain.expanded .zs-head, .imgparentEFDmain.expanded .zs-foot { display: none; }
      .imgparentEFDmain.expanded .zs-stage { padding-top: 0 !important; }
      .imgparentEFDmain.expanded .zs-scene { width: min(100vw, 100vh * 1536 / 864) !important; margin: 0 auto !important; border-radius: 0; }

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
  {{-- ?open=emergency-fund (the Emergency Fund activity on the dashboard)
       opens it straight away: the opening form, or the fund once it's open. --}}
  @php $openFund = request('open') === 'emergency-fund' && $isBankAccountOpen; @endphp
  <div class="grid grid-cols-1 gap-5" x-data="{openEmergencyModal: @js($openFund && optional($bankAccount)->is_open_emergency_account == 0), emerFundmodal: @js($openFund && optional($bankAccount)->is_open_emergency_account == 1)}">
    <div class="themeTabspills">
      <div class="w-full">
        <div class="imgparentEFDmain">
          <!-- Library Modal -->
          <div id="libraryModal"
            class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white w-full h-full max-w-full max-h-full rounded-lg shadow-xl  relative overflow-auto">

              {{-- Stays in the corner while the Library scrolls; the "Back"
                   button at the top of each screen is the main way out. --}}
              <button type="button" onclick="closeLibraryModal()" class="lib-close"
                      title="{{ __('efd.library.close') }}" aria-label="{{ __('efd.library.close') }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M17 7L7 17M7 7l10 10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
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

              <h2 class="text-xl font-bold mb-4">{{ __('efd.reception_panel') }}</h2>

              <!-- <div class="text-gray-700 space-y-2">
                  <p>Handle visitors & inquiries</p>
                  <p>Front desk operations</p>
                  <p>Visitor pass management</p>
              </div> -->
              <div style="max-height: 60vh; overflow: auto;" class="max-w-xl p-4 border rounded space-y-4">
                <div id="displayText" class="fade text-gray-800"></div>


              </div>
              <div class="flex gap-2 justify-end mt-3">
                <button id="backBtn" class="zvBackBtn" disabled>{{ __('efd.back') }}</button>
                <button id="nextBtn" class="themeBtn">{{ __('efd.next') }}</button>
                <button id="finishBtn" style="display: none;" onclick="closeReceptionModal()" class="secondaryBtn">
                  {{ __('efd.finish') }}
                </button>
              </div>


            </div>
          </div>
          <!-- Door Modal -->
          <div id="doorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white w-full h-full rounded-lg shadow-xl py-12 px-6 relative overflow-auto">

              {{-- Same way out as the Library: a Back button at the top, and an
                   X that stays visible in the corner (Daniel, 2026-10-22). --}}
              <button type="button" onclick="closeDoorModal()" class="lib-close"
                      title="{{ __('efd.library.close') }}" aria-label="{{ __('efd.library.close') }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M17 7L7 17M7 7l10 10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
              </button>

              <button type="button" onclick="closeDoorModal()" class="zvBackBtn inline-flex items-center gap-2 py-2 px-4 mb-4 text-sm font-semibold">
                <span aria-hidden="true">&larr;</span> {{ __('efd.library.back_to_department') }}
              </button>

              <h2 class="text-xl font-bold mb-4">{{ __('efd.door_access_panel') }}</h2>

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
            <button type="button" id="bannerBackBtn" class="zvBackBtn absolute top-5 left-5 inline-flex items-center gap-2 py-2 px-4 text-sm font-semibold">
              <span aria-hidden="true">&larr;</span> {{ __('efd.back_to_posters') }}
            </button>
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

          {{-- The whole department fits the screen, its name on the picture
               above the board, Back beside it (x-city-scene). The empty floor
               at the bottom is cut off, so the hint sits under the furniture. --}}
          <x-city-scene class="zs--efd" :image="asset('asset/front/images/Educational-Finance-Department2.jpg')" :width="1536" :height="1024" :crop-bottom="160"
            :title="__('efd.title')" :hint="__('efd.scene_hint')" :fallback="route('dashboard')">
            <button class="expandicon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path fill="#ffffff"
                  d="M128 96C110.3 96 96 110.3 96 128L96 224C96 241.7 110.3 256 128 256C145.7 256 160 241.7 160 224L160 160L224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L128 96zM160 416C160 398.3 145.7 384 128 384C110.3 384 96 398.3 96 416L96 512C96 529.7 110.3 544 128 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480L160 416zM416 96C398.3 96 384 110.3 384 128C384 145.7 398.3 160 416 160L480 160L480 224C480 241.7 494.3 256 512 256C529.7 256 544 241.7 544 224L544 128C544 110.3 529.7 96 512 96L416 96zM544 416C544 398.3 529.7 384 512 384C494.3 384 480 398.3 480 416L480 480L416 480C398.3 480 384 494.3 384 512C384 529.7 398.3 544 416 544L512 544C529.7 544 544 529.7 544 512L544 416z" />
              </svg>

            </button>
            <div class="blackboardHolder">
              <div class="blackboard">
                <div class="blackboard-frame">
                  <div class="blackboard-surface">
                    <div style="display:flex; width:100%; min-height: 100%">
                      <div class=" w-1/2" style=" padding-right:calc(10 * var(--bb-u)); border-right:1px solid #333">
                        <div class="h-full">
                          <div style="font-size:var(--bb-head); font-weight: 600" class="text-center mb-2">{{ __('efd.required') }}</div>
                          {{-- each activity's days left (App\Support\Deadline) and what one not done in time costs (ActivityFineService) --}}
                          @php $bbDays = fn ($end) => \App\Support\Deadline::label($end); $bbLeft = fn ($end) => \App\Support\Deadline::daysLeft($end); @endphp
                          @if ($fineAmount > 0)
                            <div class="bb-fine">{{ __('efd.fine_note', ['amount' => zeds($fineAmount)]) }}</div>
                            {{-- why (Daniel, 2026-11-03: the salary is for studying, an investment in their future - "explícalo más fácil y mejor"); a hybrid class is only informed --}}
                            @unless (\App\Support\Hybrid::on())
                              <details class="bb-why"><summary>{{ __('efd.fine_why') }}</summary><p>{{ __('efd.fine_why_text') }}</p></details>
                            @endunless
                          @endif
                          <div>
                            {{-- the student's unexpected expense, first: it has its own 30 days and no fine (App\Services\EmergencyService) --}}
                            @if (!empty($emergency))
                              <a href="{{ route('emergency.show') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: var(--bb-text); text-align: center; ">{{ \App\Services\EmergencyService::KINDS[$emergency->kind]['icon'] }} {{ __('emergency.title') }}</div>
                                  @if ($days = $bbDays($emergency->due_on))<div class="bb-days">{{ $days }}</div>@endif
                                </div>
                              </a>
                            @endif
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
                                      style="font-size: var(--bb-name); text-align: center; font-weight: 600; color: #000;">{{ __('efd.open_emergency_fund') }}</div>
                                  </div>
                                  <div class="flex justify-center gap-2">
                                    <div class="w-14 h-6">
                                      <svg style="width:var(--bb-icon); height:var(--bb-icon)" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 640 640">
                                        <path fill="#00A47D"
                                          d="M320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576zM320 112C205.1 112 112 205.1 112 320C112 434.9 205.1 528 320 528C434.9 528 528 434.9 528 320C528 205.1 434.9 112 320 112zM390.7 233.9C398.5 223.2 413.5 220.8 424.2 228.6C434.9 236.4 437.3 251.4 429.5 262.1L307.4 430.1C303.3 435.8 296.9 439.4 289.9 439.9C282.9 440.4 276 437.9 271.1 433L215.2 377.1C205.8 367.7 205.8 352.5 215.2 343.2C224.6 333.9 239.8 333.8 249.1 343.2L285.1 379.2L390.7 234z" />
                                      </svg>
                                    </div>

                                    @if (($left = $bbLeft($emmfundPosition->end_date)) !== null)
                                    <div class="leading-tight">
                                      <div style="font-size: var(--bb-text); font-weight: 600; color: #000;">{{ __('efd.days_to_complete') }}</div>
                                      <div class="text-lg font-semibold"><span
                                          style="background:#00A47D;  padding: 2px 15px; border-radius: 5px;   margin-top: 3px; display: inline-block; color: #000;">{{ $left }}</span>
                                      </div>
                                    </div>
                                    @endif
                                  </div>

                                </div>
                              </a>
                            @endif
                            @if(optional($donationPosition)->required == 1)
                              <a href="{{ route('bank.transfer', ['to' => 'donation']) }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: var(--bb-text); text-align: center; ">{{ __('npos.activity') }}</div>
                                  @if ($days = $bbDays($donationPosition->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                </div>
                              </a>
                            @endif
                            @if(optional($mbaPosition)->required == 1 && !in_array('mba', $completedActivities))
                              <a href="{{ route('education.index') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: var(--bb-text); text-align: center; ">{{ __('efd.needs_wants_savings') }}</div>
                                  @if ($days = $bbDays($mbaPosition->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                </div>
                              </a>
                            @endif
                            @if($hbaPosition && $hbaPosition->required == 1 && !in_array('high_budget_activity', $completedActivities))
                              <a href="{{ route('education.high-spending-activities') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: var(--bb-text); text-align: center; ">{{ __('efd.spending_activity') }}</div>
                                  @if ($days = $bbDays($hbaPosition->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                </div>
                              </a>
                            @endif
                            @if($lbaPosition && $lbaPosition->required == 1 && !in_array('low_budget_activity', $completedActivities))
                              <a href="{{ route('education.high-spending-activities') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: var(--bb-text); text-align: center; ">{{ __('efd.low_spending_activity') }}</div>
                                  @if ($days = $bbDays($lbaPosition->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                </div>
                              </a>
                            @endif
                            @foreach($taskActivities as $task)
                              @if($task->position == 'required' && !in_array($task->activity_key, $completedActivities))

                                <a href="{{ route('task.page', ['task' => str_replace('_', '-', $task->activity_key)]) }}">
                                  <div class="blackboardFiled">
                                    <div style="font-size: var(--bb-text); text-align: center;">
                                      {{ ct('activities', $task->activity_name) }}
                                    </div>
                                    @if ($days = $bbDays($task->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                  </div>
                                </a>
                              @endif
                            @endforeach
                            @foreach($libraryActivities as $library)
                              @if($library->position == 'required' && !in_array('library_' . $library->activity_key, $completedActivities))

                                <a href="{{ route('library.page', ['key' => str_replace('_', '-', $library->activity_key)]) }}">
                                  <div class="blackboardFiled">
                                    <div style="font-size: var(--bb-text); text-align: center;">
                                      {{ ct('activities', $library->activity_name) }}
                                    </div>
                                    @if ($days = $bbDays($library->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                  </div>
                                </a>
                              @endif
                            @endforeach
                            @foreach($easyActivities as $easy)
                              @if($easy->position == 'required')
                                <a href="{{ $easy->studentUrl() }}">
                                  <div class="blackboardFiled">
                                    <div style="font-size: var(--bb-text); text-align: center;">{{ $easy->studentTitle() }}</div>
                                    @if ($days = $bbDays($easy->until))<div class="bb-days">{{ $days }}</div>@endif
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
                      <div class="w-1/2" style=" padding-left:calc(10 * var(--bb-u))">
                        <div class="h-full">
                          <div style="font-size:var(--bb-head); font-weight: 600" class="text-center mb-2">{{ __('efd.optional') }}</div>
                          <div>
                            @if(optional($emmfundPosition)->optional == 1 && isset($bankAccount) && $bankAccount->is_open_emergency_account == 0)
                              <a href="javascript:void(0);" @click="@if (!$isBankAccountOpen)
                                window.location.href = '{{ route('bank.index') }}';
                              @elseif ($bankAccount->is_open_emergency_account == 0)
                                        openEmergencyModal = true;
                                      @else
                                        emerFundmodal = true;
                                      @endif" class="text-md text-yellow-600 mt-1">
                                <div class="blackboardFiled">
                                  <div class="flex">
                                    <div class="mb-2" style="font-size: var(--bb-name); text-align: center; font-weight: 600;">{{ __('efd.open_emergency_fund') }}</div>
                                  </div>
                                  <div class="flex justify-center gap-2">
                                    <div class="w-14 h-6">
                                      <svg style="width:var(--bb-icon); height:var(--bb-icon)" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 640 640">
                                        <path fill="#00A47D"
                                          d="M320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576zM320 112C205.1 112 112 205.1 112 320C112 434.9 205.1 528 320 528C434.9 528 528 434.9 528 320C528 205.1 434.9 112 320 112zM390.7 233.9C398.5 223.2 413.5 220.8 424.2 228.6C434.9 236.4 437.3 251.4 429.5 262.1L307.4 430.1C303.3 435.8 296.9 439.4 289.9 439.9C282.9 440.4 276 437.9 271.1 433L215.2 377.1C205.8 367.7 205.8 352.5 215.2 343.2C224.6 333.9 239.8 333.8 249.1 343.2L285.1 379.2L390.7 234z" />
                                      </svg>
                                    </div>

                                    @if (($left = $bbLeft($emmfundPosition->end_date)) !== null)
                                    <div class="leading-tight">
                                      <div style="font-size: var(--bb-text); font-weight: 600;">{{ __('efd.days_to_complete') }}</div>
                                      <div class="text-lg font-semibold"><span
                                          style="background:#00A47D;  padding: 2px 15px; border-radius: 5px;   margin-top: 3px; display: inline-block;">{{ $left }}</span>
                                      </div>
                                    </div>
                                    @endif
                                  </div>

                                </div>
                              </a>
                            @endif
                            <!-- <div class="blackboardFiled">
                                <div>
                                  <div class="mb-1" style="font-size: var(--bb-name); text-align: center; font-weight: 600;">Title
                                  </div>
                                  <div style="font-size: var(--bb-text); text-align: center; ">Literacy Badge Point</div>
                                </div>

                              </div>
                              <div class="blackboardFiled">
                                <div>
                                  <div class="mb-1" style="font-size: var(--bb-name); text-align: center; font-weight: 600;">Title
                                  </div>
                                  <div style="font-size: var(--bb-text); text-align: center;">Engagement Badge Point</div>
                                </div>
                              </div> -->
                            @if(optional($donationPosition)->optional == 1)
                              <a href="{{ route('bank.transfer', ['to' => 'donation']) }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: var(--bb-text); text-align: center; ">{{ __('npos.activity') }}</div>
                                  @if ($days = $bbDays($donationPosition->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                </div>
                              </a>
                            @endif
                            @if(optional($mbaPosition)->optional == 1 && !in_array('mba', $completedActivities))
                              <a href="{{ route('education.index') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: var(--bb-text); text-align: center; ">{{ __('efd.needs_wants_savings') }}</div>
                                  @if ($days = $bbDays($mbaPosition->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                </div>
                              </a>
                            @endif
                            @if($hbaPosition && $hbaPosition->optional == 1 && !in_array('high_budget_activity', $completedActivities))
                              <a href="{{ route('education.high-spending-activities') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: var(--bb-text); text-align: center; ">{{ __('efd.spending_activity') }}</div>
                                  @if ($days = $bbDays($hbaPosition->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                </div>
                              </a>
                            @endif
                            @if($lbaPosition && $lbaPosition->optional == 1 && !in_array('low_budget_activity', $completedActivities))
                              <a href="{{ route('education.high-spending-activities') }}">
                                <div class="blackboardFiled">
                                  <div style="font-size: var(--bb-text); text-align: center; ">{{ __('efd.low_spending_activity') }}</div>
                                  @if ($days = $bbDays($lbaPosition->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                </div>
                              </a>
                            @endif
                            @foreach($taskActivities as $task)
                              @if($task->position == 'optional' && !in_array($task->activity_key, $completedActivities))

                                <a href="{{ route('task.page', ['task' => str_replace('_', '-', $task->activity_key)]) }}">
                                  <div class="blackboardFiled">
                                    <div style="font-size: var(--bb-text); text-align: center;">
                                      {{ ct('activities', $task->activity_name) }}
                                    </div>
                                    @if ($days = $bbDays($task->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                  </div>
                                </a>
                              @endif
                            @endforeach
                            @foreach($libraryActivities as $library)
                              @if($library->position == 'optional' && !in_array('library_' . $library->activity_key, $completedActivities))

                                <a href="{{ route('library.page', ['key' => str_replace('_', '-', $library->activity_key)]) }}">
                                  <div class="blackboardFiled">
                                    <div style="font-size: var(--bb-text); text-align: center;">
                                      {{ ct('activities', $library->activity_name) }}
                                    </div>
                                    @if ($days = $bbDays($library->end_date))<div class="bb-days">{{ $days }}</div>@endif
                                  </div>
                                </a>
                              @endif
                            @endforeach
                            @foreach($easyActivities as $easy)
                              @if($easy->position == 'optional')
                                <a href="{{ $easy->studentUrl() }}">
                                  <div class="blackboardFiled">
                                    <div style="font-size: var(--bb-text); text-align: center;">{{ $easy->studentTitle() }}</div>
                                    @if ($days = $bbDays($easy->until))<div class="bb-days">{{ $days }}</div>@endif
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
          </x-city-scene>

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
                  <h4 class="text-2xl font-bold text-gray-900">{{ __('efd.ef_opening_title') }}</h4>
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
                    <p><strong>{{ __('efd.dear_citizen') }}</strong></p>
                    <p>{{ __('efd.ef_intro') }}</p>
                    <p><strong>{{ __('efd.important') }}</strong></p>
                    <ul class="list-disc ml-6">
                      @foreach (__('efd.ef_important') as $line)
                        <li>{{ $line }}</li>
                      @endforeach
                    </ul>

                  </div>
                </div>

                <!-- Account Information -->
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                  <h4 class="font-semibold text-blue-900 mb-3 text-lg">{{ __('efd.account_information') }}</h4>
                  <div class="space-y-2 text-sm">
                    <p><strong>{{ __('efd.account_type_label') }}</strong> {{ __('efd.account_type_value') }}</p>
                    <p><strong>{{ __('efd.interest_rate_label') }}</strong> {{ __('efd.interest_rate_value') }}</p>
                    <p><strong>{{ __('efd.purpose_label') }}</strong> {{ __('efd.purpose_value') }}</p>
                    <p><strong>{{ __('efd.withdrawal_label') }}</strong> {{ __('efd.withdrawal_value') }}</p>
                  </div>
                </div>

                <!-- Financial Education -->
                <div class="bg-gray-50 p-6 rounded-lg">
                  <h4 class="font-semibold text-gray-900 mb-4 text-lg">{{ __('efd.financial_education_information') }}</h4>
                  <div class="text-sm text-gray-700 leading-relaxed">
                    <ul class="list-disc ml-6">
                      @foreach (__('efd.ef_education') as $line)
                        <li>{{ $line }}</li>
                      @endforeach
                    </ul>
                  </div>
                </div>

                <!-- Instructions -->
                <div class="bg-gray-50 p-6 rounded-lg">
                  <h4 class="font-semibold text-gray-900 mb-4 text-lg">{{ __('efd.instructions') }}</h4>
                  <div class="text-sm text-gray-700 leading-relaxed">
                    <ul class="list-disc ml-6">
                      @foreach (__('efd.ef_instructions') as $line)
                        <li>{{ $line }}</li>
                      @endforeach
                    </ul>
                  </div>
                </div>

                <!-- Auto-Debit Authorization Form -->
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                  <h4 class="font-semibold text-gray-900 mb-4 text-lg">{{ __('efd.ef_auth_title') }}</h4>
                  <div class="text-sm text-gray-700 leading-relaxed">
                    <p class="text-sm leading-relaxed">
                      {!! __('efd.ef_auth_text', [
                        'name' => '<span class="inline-block border-b border-gray-600 px-2">' . e($bankAccount->student_name ?? '') . '</span>',
                        'amount' => '<span class="inline-block border-b border-gray-600 px-10">' . e($emmsavingsAmount) . '</span>',
                        'account' => '<span class="inline-block border-b border-gray-600 px-16">' . e($bankAccount->primary_savings_account_number ?? '') . '</span>',
                      ]) !!}
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
                  <label for="emeragree-checkbox" class="text-sm text-gray-700 font-medium"><strong>{{ __('efd.agree_terms') }}</strong></label>
                </div>
              </div>
              <div class="w-full mt-4 text-center flex flex-wrap gap-4 justify-center">
                <button type="button" @click="openEmergencyModal = false" class="whiteBtn">{{ __('efd.cancel') }}</button>
                <button type="submit" class="themeBtn">{{ __('efd.accept_open') }}</button>
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
                    class="text-gray-400 hover:text-gray-600 p-3 rounded-full transition-colors" title="{{ __('efd.go_back') }}">
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
                      <h4 class="text-2xl font-bold text-gray-900">{{ __('efd.ef_account') }}</h4>
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
                    <div class="text-themegreen text-sm mb-1">{{ __('efd.available_balance') }}</div>
                    <div class="flex items-center space-x-3">
                      <span
                        class="text-3xl font-bold text-themegreen">{{ $bankAccount ? zeds($bankAccount->emergency_fund_account_amount) : '' }}</span>
                    </div>
                    <div class="text-dark-800 text-sm mt-1">{{ __('efd.interest_rate_line') }}</div>
                  </div>
                  <div class="text-right">
                    <div class="text-dark-800 opacity-90 text-sm">{{ __('efd.account_type') }}</div>
                    <div class="text-xl text-dark-800 font-semibold">{{ __('efd.emergency_fund') }}</div>
                  </div>
                </div>
              </div>
              <div class="fundProgressSec mt-8 userProfileDtls">
                <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('efd.progress_tracker') }}</h2>
                <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 mb-6">
                  <p class="text-sm text-gray-600 mb-4">{!! __('efd.ef_explainer') !!}</p>
                  <!-- <div class="mb-4 form-group">
                                      <label class="block mb-2 text-base font-medium text-black">Monthly Essential Expenses (ZEDS)</label>
                                      <input type="number" placeholder="Enter your monthly expenses" class="form-control" value="1000" />
                                  </div> -->

                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="p-4 bg-white rounded-lg border border-gray-200">
                      <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-900">{{ __('efd.reserve_3') }}</h4>
                        <span class="text-sm text-orange-600 font-medium">{{ __('efd.basic_safety_net') }}</span>
                      </div>
                      <div class="mb-3">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                          <span>{{ __('efd.progress') }}</span>
                          <span>{{ __('efd.progress_of', ['current' => \App\Support\Money::number($efGoals['balance'], 0), 'goal' => \App\Support\Money::number($efGoals['goals'][3]['goal'], 0)]) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                          <div class="bg-orange-500 h-2 rounded-full transition-all duration-300"
                            style="width: {{ $efGoals['goals'][3]['pct'] }}%;"></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">{{ __('efd.pct_complete', ['pct' => \App\Support\Money::number($efGoals['goals'][3]['pct'], 1)]) }}</div>
                      </div>
                      <p class="text-xs text-gray-600">{{ __('efd.covers_3') }}</p>
                    </div>
                    <div class="p-4 bg-white rounded-lg border border-gray-200">
                      <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-900">{{ __('efd.reserve_6') }}</h4>
                        <span class="text-sm text-green-600 font-medium">{{ __('efd.optimal_security') }}</span>
                      </div>
                      <div class="mb-3">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                          <span>{{ __('efd.progress') }}</span>
                          <span>{{ __('efd.progress_of', ['current' => \App\Support\Money::number($efGoals['balance'], 0), 'goal' => \App\Support\Money::number($efGoals['goals'][6]['goal'], 0)]) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                          <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: {{ $efGoals['goals'][6]['pct'] }}%;">
                          </div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">{{ __('efd.pct_complete', ['pct' => \App\Support\Money::number($efGoals['goals'][6]['pct'], 1)]) }}</div>
                      </div>
                      <p class="text-xs text-gray-600">{{ __('efd.covers_6') }}</p>
                    </div>
                  </div>

                  <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h5 class="font-medium text-blue-900 mb-2">{{ __('efd.savings_recommendations') }}</h5>
                    <div class="text-sm text-blue-800 space-y-1">
                      @foreach ([3, 6] as $reserve)
                        @if ($efGoals['goals'][$reserve]['missing'] > 0)
                          <p>• {!! __('efd.need_more_' . $reserve, ['amount' => e(\App\Support\Money::number($efGoals['goals'][$reserve]['missing'], 0))]) !!}</p>
                        @else
                          <p>• ✓ {{ __('efd.reserve_reached_' . $reserve) }}</p>
                        @endif
                      @endforeach
                      @if ($efGoals['goals'][3]['missing'] > 0)
                        <p>• {!! __('efd.consider_saving', ['range' => e(\App\Support\Money::number($efGoals['monthly_saving'], 0)), 'months' => $efGoals['goals'][3]['months']]) !!}</p>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
              <div class="intEarningSection mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('efd.interest_earnings') }}</h2>
                <div class="space-y-4">
                  <div class="w-full">
                    <div
                      class="flex items-center justify-between p-4 border rounded-lg transition-colors cursor-pointer hover:opacity-90 bg-purple-50 border-purple-200 hover:bg-purple-100">
                      <div class="flex items-center space-x-3">
                        <div>
                          <h4 class="font-semibold text-md text-gray-900">{{ __('efd.this_month_estimated') }}</h4>
                          <p class="text-2xl font-bold text-themegreen">
                            {{ zeds($emergencyFundInterest['estimated_interest']) }}</p>
                          <div class="flex items-center justify-start space-x-1">
                            <span class="text-xs font-medium text-gray-700">{{ __('efd.based_on_current_balance') }}</span>
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
                          <h4 class="font-semibold text-md text-gray-700">{{ __('efd.annual_projection') }}</h4>
                          <p class="text-2xl font-bold text-blue-500">
                            {{ zeds($emergencyFundInterest['annual_projection']) }}</p>
                          <div class="flex items-center justify-start space-x-1">
                            <span class="text-xs font-medium text-gray-700">{{ __('efd.at_rate', ['rate' => $emergencyFundInterest['annual_rate']]) }}</span>
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
                          <h4 class="font-semibold text-md text-gray-700">{{ __('efd.next_payment') }}</h4>
                          <p class="text-2xl font-bold text-yellow-500">{{ $emergencyFundInterest['next_payment_date'] }}
                          </p>
                          <div class="flex items-center justify-start space-x-1">
                            <span class="text-xs font-medium text-gray-700">{{ __('efd.interest_credit_date') }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="accountActivity mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('efd.recent_activity') }}</h2>
                <div class="border border-gray-100 rounded-lg">
                  @foreach($emmengercyfundtransactions as $emmtxn)
                    <div
                      class="flex flex-wrap gap-y-4 items-center justify-between p-3 border-b border-gray-100 last:border-b-0">
                      <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"><span
                            class="text-lg">🔄</span></div>
                        <div>
                          <p class="font-medium text-gray-900 text-sm">{{ txn_text($emmtxn->description) }}</p>
                          <div class="flex items-center space-x-2 text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($emmtxn->transaction_date)->locale(app()->getLocale())->isoFormat('LL') }}</div>
                        </div>
                      </div>
                      <div class="text-right">
                        {{-- the fund's own lines: money in is green --}}
                        <p class="text-lg font-bold {{ $emmtxn->type == 'credit' ? 'text-green-600' : 'text-red-600' }}">
                          {{ zeds($emmtxn->type == 'credit' ? $emmtxn->amount : -$emmtxn->amount, true) }}</p>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
              <div class="accFeaturesLists">
                <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('efd.features_purpose') }}</h2>
                <div class="p-6 bg-blue-50 rounded-xl border border-blue-200">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <h4 class="text-lg font-semibold text-gray-900 mb-3">{{ __('efd.purpose_benefits') }}</h4>
                      <ul class="text-sm text-gray-700 space-y-2">
                        @foreach (__('efd.purpose_benefits_list') as $line)
                          <li>• {{ $line }}</li>
                        @endforeach
                      </ul>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-gray-900 mb-3">{{ __('efd.interest_calculation') }}</h4>
                      <ul class="text-sm text-gray-700 space-y-2">
                        @foreach (__('efd.interest_calculation_list') as $line)
                          <li>• {{ $line }}</li>
                        @endforeach
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

    /* the look is the app's Back button (.zvBackBtn) */
    #libraryModalapp .efd-back-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 14px;
      padding: 8px 16px;
      font-size: 14px;
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

    /* One question at a time (2026-10-26): progress bar, Back / Continue,
       and the acknowledgement once the answers are checked. */
    #libraryModalapp .lib-step-bar { height: 6px; border-radius: 99px; background: #E6ECEA; overflow: hidden; margin: -6px 0 18px; }
    #libraryModalapp .lib-step-bar span { display: block; height: 100%; background: #00A47D; border-radius: 99px; transition: width .3s; }
    #libraryModalapp .lib-step-nav { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
    #libraryModalapp .lib-step-nav .themeBtn { margin-left: auto; }
    #libraryModalapp .lib-step-nav button:disabled { opacity: .45; cursor: not-allowed; transform: none; }
    #libraryModalapp .lib-step-hint { flex: 1; text-align: center; font-size: 13px; color: #5E7069; min-width: 120px; }
    #libraryModalapp .lib-ack { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 6px; padding: 6px 0 18px; margin-bottom: 16px; border-bottom: 1px solid #E6ECEA; color: #016950; }
    #libraryModalapp .lib-ack-icon { width: 48px; height: 48px; border-radius: 50%; display: grid; place-items: center; font-size: 24px; font-weight: 800; color: #00A47D; background: #E2F6EF; border: 2px solid #00A47D; }
    #libraryModalapp .lib-ack-score { font-size: 18px; color: #5E7069; margin: 0; }
    #libraryModalapp .lib-ack-score strong { font-size: 34px; color: #0F2A22; }
    @media (prefers-reduced-motion: reduce) { #libraryModalapp .lib-step-bar span { transition: none; } }

    /* ── Library redesign (2026-10-22): compact header + progress bar,
          status-led module cards, lesson/quiz panels ── */
    #libraryModalapp { min-height: 100%; background: #F4F8F6; }

    .lib-close {
      position: fixed; top: 16px; right: 20px; z-index: 60; width: 42px; height: 42px; border-radius: 999px;
      display: flex; align-items: center; justify-content: center;
      background: #fff; color: #016950; border: 1px solid #D2DDDB; box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
      transition: background .15s, color .15s, transform .15s;
    }
    .lib-close:hover, .lib-close:focus-visible { background: #00A47D; color: #fff; border-color: #00A47D; transform: scale(1.05); outline: none; }

    #libraryModalapp .lib-wrap { max-width: 1180px; margin: 0 auto; padding: 32px 24px 48px; }

    #libraryModalapp .lib-head {
      display: grid; grid-template-columns: auto 1fr minmax(220px, 320px); gap: 20px; align-items: center;
      background: #fff; border: 1px solid #D2DDDB; border-radius: 16px; padding: 22px 24px;
      box-shadow: 0 1px 2px rgba(1, 105, 80, .06); margin-bottom: 24px;
    }
    #libraryModalapp .lib-head-module { grid-template-columns: auto 1fr; }
    #libraryModalapp .lib-head-icon {
      width: 64px; height: 64px; border-radius: 16px; background: #E7FBF3; display: flex;
      align-items: center; justify-content: center; font-size: 34px;
    }
    #libraryModalapp .lib-eyebrow {
      font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #00A47D; margin: 0 0 4px;
    }
    #libraryModalapp .lib-dot { color: #B7E3D6; margin: 0 4px; }
    #libraryModalapp .lib-title { font-size: 28px; line-height: 1.15; font-weight: 800; color: #0F2A22; margin: 0; }
    #libraryModalapp .lib-sub { font-size: 14px; color: #5B6B66; margin: 4px 0 0; }

    #libraryModalapp .lib-progress-label { display: flex; align-items: baseline; gap: 8px; margin-bottom: 8px; font-size: 13px; color: #5B6B66; }
    #libraryModalapp .lib-progress-label strong { font-size: 22px; color: #016950; }
    #libraryModalapp .lib-progress-track { height: 10px; border-radius: 999px; background: #E3ECE8; overflow: hidden; }
    #libraryModalapp .lib-progress-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #00A47D, #2BC79F); transition: width .4s ease; }

    #libraryModalapp .lib-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }

    #libraryModalapp .lib-card {
      display: flex; flex-direction: column; gap: 14px; text-align: left; width: 100%;
      background: #fff; border: 1px solid #D2DDDB; border-radius: 14px; padding: 16px 18px;
      transition: border-color .15s, box-shadow .15s, transform .15s; cursor: pointer;
    }
    #libraryModalapp .lib-card:hover, #libraryModalapp .lib-card:focus-visible {
      border-color: #00A47D; box-shadow: 0 6px 18px rgba(1, 105, 80, .12); transform: translateY(-2px); outline: none;
    }
    #libraryModalapp .lib-card.is-done { border-color: #B7E3D6; background: linear-gradient(180deg, #F3FBF8, #fff 60%); }

    #libraryModalapp .lib-card-top { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    #libraryModalapp .lib-num { font-size: 12px; font-weight: 700; color: #8A9A95; text-transform: uppercase; letter-spacing: .05em; }
    #libraryModalapp .lib-tag { font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 999px; background: #F1F4F3; color: #5B6B66; }
    #libraryModalapp .lib-tag-points { background: #FFF5D4; color: #8a6d1d; }

    #libraryModalapp .lib-card-body { display: flex; align-items: center; gap: 14px; }
    #libraryModalapp .lib-icon {
      flex: none; width: 56px; height: 56px; border-radius: 14px; background: #E7FBF3;
      display: flex; align-items: center; justify-content: center; font-size: 30px;
    }
    #libraryModalapp .lib-card-title { font-size: 17px; font-weight: 700; color: #0F2A22; line-height: 1.25; margin: 0; }
    #libraryModalapp .lib-card-meta { font-size: 13px; color: #5B6B66; margin: 2px 0 0; }

    #libraryModalapp .lib-card-foot {
      display: flex; justify-content: space-between; align-items: center; gap: 8px;
      border-top: 1px dashed #D2DDDB; padding-top: 12px; margin-top: auto;
    }
    #libraryModalapp .lib-chip { font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; }
    #libraryModalapp .lib-chip-new { background: #F1F4F3; color: #5B6B66; }
    #libraryModalapp .lib-chip-score { background: #FFF5D4; color: #8a6d1d; }
    #libraryModalapp .lib-chip-done { background: #E7FBF3; color: #016950; }
    #libraryModalapp .lib-go { font-size: 14px; font-weight: 700; color: #00A47D; white-space: nowrap; }
    #libraryModalapp .lib-card:hover .lib-go { color: #016950; }

    #libraryModalapp .lib-module-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px; align-items: start; }
    #libraryModalapp .lib-panel { background: #fff; border: 1px solid #D2DDDB; border-radius: 16px; padding: 24px; }
    #libraryModalapp .lib-panel-label {
      font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #00A47D; margin: 0 0 8px;
    }
    #libraryModalapp .lib-lesson { font-size: 15px; line-height: 1.65; color: #2A3B36; }
    #libraryModalapp .lib-lesson h2 { font-size: 20px; color: #0F2A22; }
    #libraryModalapp .lib-option { border-color: #E3ECE8; transition: border-color .15s, background .15s; }
    /* a module: the question, its answers and Continue on one screen (Daniel, 2026-10-28:
       "no scrolling up and down") - a small header, the lesson scrolling in its own panel
       beside the quiz, the answers two to a row */
    #libraryModalapp .lib-wrap:has(.lib-head-module) { padding-top: 18px; padding-bottom: 18px; }
    #libraryModalapp .lib-wrap:has(.lib-head-module) .efd-back-btn { margin-bottom: 10px; }
    #libraryModalapp .lib-head-module { padding: 12px 18px; margin-bottom: 14px; gap: 14px; }
    #libraryModalapp .lib-head-module .lib-head-icon { width: 44px; height: 44px; font-size: 24px; border-radius: 12px; }
    #libraryModalapp .lib-head-module .lib-title { font-size: 22px; }
    #libraryModalapp .lib-head-module .lib-sub { display: none; }
    #libraryModalapp .lib-module-grid > .lib-panel { padding: 18px 20px; }
    #libraryModalapp .lib-module-grid > .lib-lesson-panel { max-height: calc(100vh - 190px); overflow-y: auto; }
    #libraryModalapp .lib-options { display: grid; gap: 8px; } /* one under the other (2026-10-29: "not two columns of the choices") */
    #libraryModalapp .lib-options .lib-option { margin-bottom: 0; padding: 10px 14px; }
    #libraryModalapp .lib-option:hover { border-color: #00A47D; }

    @media (max-width: 768px) {
      #libraryModalapp .lib-wrap { padding: 20px 16px 32px; }
      #libraryModalapp .lib-head { grid-template-columns: auto 1fr; }
      #libraryModalapp .lib-progress { grid-column: 1 / -1; }
      #libraryModalapp .lib-title { font-size: 22px; }
      #libraryModalapp .lib-module-grid { grid-template-columns: 1fr; }
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
    window.citizenName = "{{ auth()->user()->display_name }}";
    // Texts for efd.js (Ms. Rodriguez at the reception) in the student's language.
    window.ZV_EFD = {{ \Illuminate\Support\Js::from([
      'next' => __('efd.next'),
      'completed' => __('efd.completed'),
      'reception_steps' => __('efd.reception_steps'),
      'reception_final' => __('efd.reception_final'),
    ]) }};
  </script>
  <script src="{{ asset('asset/front/js/efd.js') }}?ver={{ rand(111, 999) }}"></script>
  <script>
    const modules = @json($libraryModalModules);
    const LIB_T = @json(__('efd.library'));
    const LIB_LEVEL = @json($libraryLevel ? e($libraryLevel) : null);
    function libT(key, params) {
      let text = LIB_T[key] || key;
      Object.keys(params || {}).forEach(name => { text = text.split(':' + name).join(params[name]); });
      return text;
    }
  </script>
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
      // A module counts as done when the server recorded it this month, or
      // when the student just got every answer right in this visit.
      function isDone(m) {
        const score = getScore(m.id);
        return m.done || (showResults[m.id] && score.correct === score.total);
      }

      window.renderHome = function () {
        const done = modules.filter(isDone).length;
        const pct = modules.length ? Math.round(done / modules.length * 100) : 0;

        const root = document.getElementById('libraryModalapp');
        if (!root) return;

        const badge = LIB_LEVEL ? libT('level_named', { level: LIB_LEVEL }) : libT('level_badge');

        root.innerHTML = `
      <div class="lib-wrap">
        <button type="button" onclick="closeLibraryModal()" class="zvBackBtn efd-back-btn">
          <span class="efd-back-arrow">&larr;</span> ${libT('back_to_department')}
        </button>
        <header class="lib-head">
          <div class="lib-head-icon" aria-hidden="true">📚</div>
          <div class="lib-head-text">
            <p class="lib-eyebrow">${libT('shelf')} <span class="lib-dot">•</span> ${badge}</p>
            <h1 class="lib-title">${libT('title')}</h1>
            <p class="lib-sub">${libT('subtitle')}</p>
          </div>
          <div class="lib-progress" role="progressbar" aria-valuemin="0" aria-valuemax="${modules.length}" aria-valuenow="${done}">
            <div class="lib-progress-label">
              <strong>${done}/${modules.length}</strong>
              <span>${libT('progress', { done: done, total: modules.length })}</span>
            </div>
            <div class="lib-progress-track"><div class="lib-progress-fill" style="width:${pct}%"></div></div>
          </div>
        </header>

        <div class="lib-grid">
          ${modules.map((module, index) => createModuleCard(module, index)).join("")}
        </div>
      </div>
    `;
      };

      /* ======================================================================
         MODULE CARD
      ====================================================================== */
      function createModuleCard(module, index) {
        const score = getScore(module.id);
        const done = isDone(module);
        const tried = showResults[module.id];

        let status;
        if (done) {
          status = `<span class="lib-chip lib-chip-done">✓ ${libT('status_done')}</span>`;
        } else if (tried) {
          status = `<span class="lib-chip lib-chip-score">${libT('score', { correct: score.correct, total: score.total })}</span>`;
        } else {
          status = `<span class="lib-chip lib-chip-new">${libT('status_new')}</span>`;
        }

        const kind = module.assigned
          ? `<span class="lib-tag lib-tag-points">★ ${libT('earns_points')}</span>`
          : `<span class="lib-tag">${libT('practice')}</span>`;

        return `
      <button type="button" class="lib-card ${done ? 'is-done' : ''}" onclick="openModule(${module.id})">
        <div class="lib-card-top">
          <span class="lib-num">${libT('module_number', { number: index + 1 })}</span>
          ${kind}
        </div>
        <div class="lib-card-body">
          <div class="lib-icon" aria-hidden="true">${module.icon}</div>
          <div>
            <h3 class="lib-card-title">${module.title}</h3>
            <p class="lib-card-meta">${libT('questions', { count: module.content.questions.length })}</p>
          </div>
        </div>
        <div class="lib-card-foot">
          ${status}
          <span class="lib-go">${tried || done ? libT('review') : libT('start_learning')} →</span>
        </div>
      </button>
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

        const number = modules.indexOf(module) + 1;

        document.getElementById('libraryModalapp').innerHTML = `
            <div class="lib-wrap">
              <button type="button" onclick="goBack()" class="zvBackBtn efd-back-btn">
                  <span class="efd-back-arrow">&larr;</span> ${libT('back')}
              </button>
              <header class="lib-head lib-head-module">
                  <div class="lib-head-icon" aria-hidden="true">${module.icon}</div>
                  <div class="lib-head-text">
                      <p class="lib-eyebrow">${libT('shelf')} <span class="lib-dot">•</span> ${libT('module_number', { number: number })}</p>
                      <h1 class="lib-title">${module.title}</h1>
                      <p class="lib-sub">${libT('questions', { count: module.content.questions.length })}</p>
                  </div>
              </header>

              <div class="lib-module-grid">
                  ${renderLearning(module)}
                  ${renderQuiz(module, score, allAnswered)}
              </div>
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
              <div class="lib-panel lib-lesson lib-lesson-panel">
                  <p class="lib-panel-label">${libT('lesson')}</p>
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
      // Daniel, 2026-10-26: one question at a time with Back / Continue,
      // then - once checked - the acknowledgement, the score and every
      // answer reviewed. libStep[moduleId] is the question on screen.
      window.libStep = window.libStep ?? {};

      function renderQuiz(m, score, allAnswered) {
        const total = m.content.questions.length;

        if (!total) {
          return `<div class="lib-panel"><p>${libT('no_questions')}</p></div>`;
        }

        if (showResults[m.id]) {
          return `
              <div class="lib-panel">
                  <div class="lib-ack" role="status">
                    <span class="lib-ack-icon" aria-hidden="true">✓</span>
                    <h2 class="text-xl font-bold">${libT('submitted')}</h2>
                    <p class="lib-ack-score"><strong>${score.correct}</strong>/${total}</p>
                    ${libraryResultNote(m)}
                  </div>

                  <div class="quiz-scroll-box space-y-4 max-h-[600px] overflow-y-auto pr-2 scrollbar-hide">
                    ${m.content.questions.map((q, i) => renderQuestion(m, q, i)).join("")}
                  </div>

                  <div class="mt-6 border-t pt-4 button-group">
                    <button onclick="retry(${m.id})" class="themeBtn">${libT('try_again')}</button>
                    <button onclick="goBack()" class="themeBtn">${libT('next_module')}</button>
                  </div>
              </div>
          `;
        }

        const i = Math.min(libStep[m.id] || 0, total - 1);
        const chosen = answers[`${m.id}-${i}`] !== undefined;
        const last = i === total - 1;

        return `
              <div class="lib-panel">
                  <div class="quiz-head">
                    <h2 class="text-2xl font-bold">${libT('quiz_questions')}</h2>
                    <span class="quiz-progress">${libT('question_of', { current: i + 1, total: total })}</span>
                  </div>
                  <div class="lib-step-bar" aria-hidden="true"><span style="width:${Math.round((i + 1) / total * 100)}%"></span></div>

                  <div class="quiz-scroll-box">
                    ${renderQuestion(m, m.content.questions[i], i)}
                  </div>

                  <div class="mt-6 border-t pt-4 lib-step-nav">
                    <button type="button" class="secondaryBtn" onclick="libGo(${m.id}, ${i - 1})" ${i === 0 ? 'disabled' : ''}>&larr; ${libT('step_back')}</button>
                    ${chosen ? '' : `<span class="lib-step-hint">${libT('choose')}</span>`}
                    ${last
            ? `<button type="button" class="themeBtn" onclick="checkAnswers(${m.id})" ${chosen ? '' : 'disabled'}>${libT('check_answers')} ✓</button>`
            : `<button type="button" class="themeBtn" onclick="libGo(${m.id}, ${i + 1})" ${chosen ? '' : 'disabled'}>${libT('continue')} &rarr;</button>`}
                  </div>
                  <p id="quizWarn-${m.id}" class="quiz-warn" hidden>${libT('answer_all_warning', { total: total })}</p>
              </div>
          `;
      }

      window.libGo = function (moduleId, step) {
        const m = modules.find(x => x.id === moduleId);
        libStep[moduleId] = Math.max(0, Math.min(step, m.content.questions.length - 1));
        renderModule();
      };

      /* ======================================================================
         QUESTION ITEM
      ====================================================================== */
      function renderQuestion(m, q, i) {
        const selected = answers[`${m.id}-${i}`];
        const show = showResults[m.id];

        return `
              <div>
                  <p class="font-semibold mb-2">${i + 1}. ${q.q}</p>

                  <div class="lib-options">
                  ${q.options.map((option, index) => {
          let bg = "bg-slate-50";

          if (show) {
            if (index === q.correct) bg = "bg-green-100";
            else if (index === selected) bg = "bg-red-100";
          } else if (index === selected) {
            bg = "bg-emerald-100";
          }

          return `
                          <button class="lib-option w-full p-3 text-left rounded-xl border-2 mb-2 ${bg}"
                                  ${show ? "" : `onclick="selectAnswer(${m.id}, ${i}, ${index})"`}>
                                  <span class="flex items-center gap-3">
                            <span class="font-medium text-sm bg-white px-2.5 py-1 rounded-lg border border-slate-200">${String.fromCharCode(97 + index)})</span>
                            <span>${option}</span>
                          </span>
                            </button>
                      `;
        }).join("")}
                  </div>
              </div>
          `;
      }

      /* ======================================================================
         CONTROLLERS
      ====================================================================== */
      window.selectAnswer = function (moduleId, qIndex, optIndex) {
        answers[`${moduleId}-${qIndex}`] = optIndex;
        renderModule();
      };

      // FinHero/Engagement credit: the server grades the whole module when
      // the student checks their answers (same endpoint as the Library page),
      // and only for modules assigned to their class or grade.
      window.libraryResult = window.libraryResult ?? {};

      function sendLibraryAnswers(module) {
        if (!module.assigned) {
          libraryResult[module.id] = { practice: true };
          return;
        }

        const payload = {};
        module.content.questions.forEach((q, i) => {
          payload[q.qid] = q.option_ids[answers[`${module.id}-${i}`]];
        });

        fetch('/finhero/library/' + module.activity_key.replace(/_/g, '-') + '/submit', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ answers: payload })
        })
          .then(res => res.ok ? res.json() : Promise.reject(res.status))
          .then(data => {
            libraryResult[module.id] = data;
            if (currentModule === module.id) renderModule();
          })
          .catch(() => {
            libraryResult[module.id] = { error: true };
            if (currentModule === module.id) renderModule();
          });
      }

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
        delete libraryResult[moduleId];
        sendLibraryAnswers(module);
        renderModule();
      };

      window.retry = function (moduleId) {
        const m = modules.find(x => x.id === moduleId);
        m.content.questions.forEach((q, i) => {
          delete answers[`${moduleId}-${i}`];
        });
        showResults[moduleId] = false;
        libStep[moduleId] = 0;
        delete libraryResult[moduleId];
        renderModule();
      };

      function libraryResultNote(m) {
        const r = libraryResult[m.id];
        if (!r) return '';
        let text;
        if (r.practice) {
          text = libT('practice_only');
        } else if (r.error) {
          text = libT('not_saved');
        } else if (r.first_attempt) {
          text = libT(r.points === 1 ? 'saved_one' : 'saved_many', { points: r.points });
        } else {
          text = libT('saved_practice');
        }
        return `<p class="text-sm mt-3" style="color:#1B6B3A">${text}</p>`;
      }

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

    // Escape steps back one screen: module -> Library home -> Department,
    // and Poster Room -> Department (an open poster closes itself first).
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      const modal = document.getElementById('libraryModal');
      if (modal && !modal.classList.contains('hidden')) {
        if (window.currentModule) {
          goBack();
        } else {
          closeLibraryModal();
        }
        return;
      }
      const door = document.getElementById('doorModal');
      const poster = document.getElementById('bannerPreviewModal');
      if (door && !door.classList.contains('hidden') && (!poster || poster.classList.contains('hidden'))) {
        closeDoorModal();
      }
    });
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
      document.getElementById("bannerBackBtn").addEventListener("click", closeBannerModal);

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