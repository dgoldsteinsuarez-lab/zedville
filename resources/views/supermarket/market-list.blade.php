@extends('layouts.profile')
@if ($hasSurvey)
    @section('no_page_back', '1'){{-- Back is beside the picture --}}
@endif

@section('title', __('shop.market_list_title'))

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('asset/front/css/surveys.css') }}">
<style>


    /* main store hit containers */
    .store {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }
    
    .store span {
        display: none;
    }
    
    /* polygon area + clickable event lives here */
    .store::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: auto;
        clip-path: var(--poly);
        background: rgba(0, 0, 0, 0);
        outline: none;
    }
    
    /* Category positions */
    .s1 { --poly: polygon(0% 0%, 51% 7%, 51% 45%, 0% 43%); }
    .s2 { --poly: polygon(0% 28%, 51% 30%, 51% 69%, 0% 87%); top: 21%; }
    .s3 { --poly: polygon(33% 9%, 100% -7%, 100% 42%, 33% 44%); top: 0; left: 29%; }
    .s4 { --poly: polygon(52% 20%, 100% 16%, 100% 89%, 52% 67%); top: 33%; }

    /* Survey Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.show {
        display: flex;
    }

    .modal-content-wrapper {
        background: #fff;
        border: 1px solid #D2DDDB;
        border-radius: 8px;
        width: 100%;
        max-width: 1140px; /* the survey, its answers side by side (2026-10-28) */
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
    }

    .modal-close-btn {
        position: sticky;
        top: 10px;
        right: 10px;
        float: right;
        z-index: 10;
        background: white;
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .btn {
        padding: 12px 30px;
        border-radius: 30px;
        border: 1px solid transparent;
        cursor: pointer;
        font-weight: 600;
        font-size: 16px;
        text-decoration: none;
        display: inline-block;
        margin: 5px;
        transition: 0.2s;
    }

    .btn-primary {
        background: #00A47D;
        border-color: #016950;
        box-shadow: 0 3px 0 #016950;
        color: #fff;
    }

    .btn-primary:hover {
        background: #016950;
        color: #fff;
        transform: translateY(3px);
        box-shadow: 0 1px 0 #016950;
    }

    .btn-secondary {
        background: #fff;
        border-color: #D2DDDB;
        color: #5C5C5C;
    }

    .btn-secondary:hover {
        background: #F2F2F2;
        color: #222;
    }

    .survey-placeholder {
        padding: 40px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        max-width: 500px;
        width: 90%;
        margin: 80px auto;
        background: rgba(255, 255, 255, 0.95);
    }

    .survey-placeholder h3 {
        margin: 0 0 15px;
        font-size: 22px;
        color: #333;
    }

    .survey-placeholder p {
        margin: 0 0 20px;
        color: #666;
        font-size: 16px;
    }

</style>
@endpush
@if ($hasSurvey)
    {{-- The whole picture fits the screen, like City Hall (Daniel, 2026-10-28); each of
         its four sections is the way into that shop. --}}
    <x-city-scene :image="asset('asset/front/images/supermarket-list.jpg')" :width="4608" :height="3456"
        :title="__('shop.market_list_title')" :hint="__('shop.market_list_hint')" :fallback="route('supermarket.index')">
        @foreach (['omnivore' => 's1', 'pescatarian' => 's2', 'vegetarian' => 's3', 'vegan' => 's4'] as $section => $area)
            <a class="store {{ $area }}" href="{{ route('supermarket.supermarket', $section) }}"><span>{{ ucfirst($section) }}</span></a>
        @endforeach
    </x-city-scene>
@else
    <div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
        <h1 class="text-xl font-bold whitespace-nowrap">{{ __('shop.market_list_title') }}</h1>
    </div>
    <div id="surveyPlaceholder" class="survey-placeholder">
        <h3>{{ __('shop.complete_survey_title') }}</h3>
        <p>{{ __('shop.complete_survey_text') }}</p>
        <button class="themeBtn" id="openSurveyBtn">{{ __('shop.take_survey') }}</button>
    </div>
@endif

<!-- Survey Modal -->
<div class="modal-overlay" id="surveyModal">
    <div class="modal-content-wrapper">
        <!-- <button class="modal-close-btn" onclick="closeSurveyModal()"> -->
        <button class="modal-close-btn" id="closeSurveyBtn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        @include('profile.partials.surveys')
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openModal(id)  { document.getElementById(id)?.classList.add('show'); }
    function closeModal(id) { document.getElementById(id)?.classList.remove('show'); }

    // Intercept survey submission
    document.addEventListener('DOMContentLoaded', function () {
        // ── Button bindings (NO onclick attributes needed in HTML) ──
        document.getElementById('openSurveyBtn')
            ?.addEventListener('click', () => openModal('surveyModal'));

        document.getElementById('closeSurveyBtn')
            ?.addEventListener('click', () => closeModal('surveyModal'));

        // Backdrop click closes modal
        ['surveyModal'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', function (e) {
                if (e.target === this) closeModal(id);
            });
        });
    });
</script>
@endpush