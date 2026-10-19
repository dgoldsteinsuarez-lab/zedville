@extends('layouts.profile')

@section('title', 'Supermarket Market List')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('asset/front/css/surveys.css') }}">
<style>
    .mall-map {
        position: relative;
        width: 100%;
    }

    .mall-img {
        width: 100%;
        display: block;
        pointer-events: none;
    }

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
        max-width: 800px;
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

    .category-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .survey-info-box {
    position: relative;
    z-index: 1000; /* higher than .store */
    pointer-events: auto;
}

    .survey-info-box {
    background: rgba(255, 255, 255, 0.95);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.survey-info-box h2 {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 8px;
}

.survey-info-box p {
    color: #555;
    font-size: 15px;
    margin-bottom: 15px;
}
.centered-box {
    max-width: 400px;       /* smaller width */
    margin: 60px auto 0;    /* top margin + center */
    text-align: center;     /* center align text */
    padding: 20px;
    border-radius: 10px;
    background: #f8f9fa;    /* light background */
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.centered-box h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.centered-box p {
    font-size: 14px;
    margin-bottom: 10px;
}
</style>
@endpush
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap">Supermarket Market List</h1>
</div>
<div class="grid grid-cols-1 gap-5">
    <div class="themeTabspills">
        <div class="w-full" style="position: relative; min-height: 400px;">
            <div class="mall-map" id="mallMapContainer" style="display: none;">
                

                <img src="{{ asset('asset/front/images/supermarket-list.jpg') }}" alt="City Mall" class="mall-img rounded-lg"> 
                 
                <div id="categoryContent">
                    <!-- Content will be dynamically loaded via JavaScript -->
                </div>
                <div class="survey-info-box centered-box">
                    <h3>Monthly Groceries</h3>
                    <p>
                        <strong>11 Products Compulsory.</strong><br>
                        Always next month after the survey is being done.<br>
                        Last surveys done are applied.
                    </p>

                    <button class="btn btn-primary btn-sm mt-2" id="openResultBtn">
                        View Survey Result
                    </button>
                </div>
            </div>
            
            <div id="surveyPlaceholder" class="survey-placeholder" style="position: static; transform: none;">
                <h3>Complete Your Food Survey</h3>
                <p>Please complete the food preference survey to access your personalized supermarket categories.</p>
                <!-- <button class="themeBtn" onclick="openSurveyModal()">Take Survey</button> -->
                <button class="themeBtn"  id="openSurveyBtn">Take Survey</button>
            </div>
        </div>
    </div>
</div>

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
<!--Sirevy Result Modal -->
<!-- Survey Result Modal -->
<div class="modal-overlay" id="resultModal">
    <div class="modal-content-wrapper">
        <!-- <button class="modal-close-btn" onclick="closeResultModal()"> -->
        <button class="modal-close-btn" id="closeResultBtn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        @include('profile.partials.surveys-results')
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('asset/front/js/surveys.js') }}"></script>
<script>
    const userData = {
        surveyCompleted: @json($hasSurvey),
        foodCategory: @json($diet ?? 'omnivore')
    };

    const categories = {
        omnivore:    { name: 'Omnivore',     class: 's1', url: '{{ route('supermarket.supermarket', 'omnivore') }}' },
        pescatarian: { name: 'Pescatarian',  class: 's2', url: '{{ route('supermarket.supermarket', 'pescatarian') }}' },
        vegetarian:  { name: 'Vegetarian',   class: 's3', url: '{{ route('supermarket.supermarket', 'vegetarian') }}' },
        vegan:       { name: 'Vegan',        class: 's4', url: '{{ route('supermarket.supermarket', 'vegan') }}' }
    };

    function openModal(id)  { document.getElementById(id)?.classList.add('show'); }
    function closeModal(id) { document.getElementById(id)?.classList.remove('show'); }

    /*function loadMarketList() {
        const container   = document.getElementById('categoryContent');
        const mallMap     = document.getElementById('mallMapContainer');
        const placeholder = document.getElementById('surveyPlaceholder');

        if (userData.surveyCompleted) {
            placeholder.style.display = 'none';
            mallMap.style.display     = 'block';

            const category = categories[userData.foodCategory];
            if (category) {
                container.innerHTML = `
                    <a class="store ${category.class}" href="${category.url}">
                        <span>${category.name}</span>
                    </a>
                `;
            }
        } else {
            placeholder.style.display = 'block';
            mallMap.style.display     = 'none';
            container.innerHTML       = '';
        }
    }*/
    function loadMarketList() {
    const container   = document.getElementById('categoryContent');
    const mallMap     = document.getElementById('mallMapContainer');
    const placeholder = document.getElementById('surveyPlaceholder');

    if (userData.surveyCompleted) {
        placeholder.style.display = 'none';
        mallMap.style.display     = 'block';

        let html = '';

        // 🔥 LOOP ALL CATEGORIES
        Object.keys(categories).forEach(key => {
            const cat = categories[key];

            html += `
                <a class="store ${cat.class}" href="${cat.url}">
                    <span>${cat.name}</span>
                </a>
            `;
        });

        container.innerHTML = html;

    } else {
        placeholder.style.display = 'block';
        mallMap.style.display     = 'none';
        container.innerHTML       = '';
    }
}

    // Intercept survey submission
    const _originalFetch = window.fetch;
    window.fetch = function(...args) {
        const url = args[0];
        if (typeof url === 'string' && url.includes('/profile/storesurvey')) {
            return _originalFetch.apply(this, args).then(response => {
                response.clone().json()
                    .then(() => {
                        alert('Survey completed! Page will refresh in 3 seconds...');
                        setTimeout(() => location.reload(), 3000);
                    })
                    .catch(err => console.error('Parse error:', err));
                return response;
            });
        }
        return _originalFetch.apply(this, args);
    };

    document.addEventListener('DOMContentLoaded', function () {
        loadMarketList();

        // ── Button bindings (NO onclick attributes needed in HTML) ──
        document.getElementById('openResultBtn')
            ?.addEventListener('click', () => openModal('resultModal'));

        document.getElementById('openSurveyBtn')
            ?.addEventListener('click', () => openModal('surveyModal'));

        document.getElementById('closeSurveyBtn')
            ?.addEventListener('click', () => closeModal('surveyModal'));

        document.getElementById('closeResultBtn')
            ?.addEventListener('click', () => closeModal('resultModal'));

        // Backdrop click closes modal
        ['surveyModal', 'resultModal'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', function (e) {
                if (e.target === this) closeModal(id);
            });
        });
    });
</script>
@endpush