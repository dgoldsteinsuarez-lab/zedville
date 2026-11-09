@extends('layouts.profile')

@section('title', 'City Hall - Educational Finance Department')
@section('no_page_back', '1'){{-- Back is on the picture --}}

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('asset/front/css/efd.css') }}?ver={{ rand(111, 999) }}">
    <style>
    .shop-front-card {
        background: white;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0, 164, 125, 0.15);
        width: 100%;
        max-width: 450px;
        border: 1px solid #D2DDDB;
        margin: 0 auto;
        text-align: center;
        font-family: 'Open Sans', sans-serif;
    }

    .shop-image-placeholder {
        width: 100%;
        height: 250px;
        background: #b2dfdb;
        border-radius: 16px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 60px;
        color: #009e91;
        position: relative;
        overflow: hidden;
    }

    .shop-image-placeholder::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 40px;
        background: repeating-linear-gradient(
            45deg,
            #00A47D,
            #00A47D 20px,
            white 20px,
            white 40px
        );
    }

    .shop-title {
        color: #222;
        margin-bottom: 10px;
        font-weight: 800;
        font-size: 24px;
        font-family: 'Manrope', sans-serif;
    }

    .shop-desc {
        color: #5C5C5C;
        margin-bottom: 30px;
        font-size: 14px;
    }

    .btn-large-enter {
        background: #00A47D;
        color: white;
        font-size: 18px;
        font-weight: 700;
        padding: 16px 40px;
        border-radius: 30px;
        border: 1px solid #016950;
        cursor: pointer;
        box-shadow: 0 3px 0 #016950;
        transition: 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-large-enter:hover {
        transform: translateY(3px);
        background: #016950;
        box-shadow: 0 1px 0 #016950;
    }
    
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
.city-map {
    position: relative;
    width: 100%;
}

.city-img {
    width: 100%;
    display: block;
}

.hotspot {
    position: absolute;
    display: block;
    /*border: 2px dashed red; /* Remove after testing */
}

/* Dashboard Board */
.board {
    left: 31%;
    top: 25%;
    width: 38%;
    height: 20%;
}

/* Left Room */
.left-room {
    left: 30%;
    top: 47%;
    width: 10%;
    height: 31%;
}

/* Right Room */
.right-room {
    left: 60%;
    top: 47%;
    width: 10%;
    height: 31%;
}

/* Reception Girl */
.girl {
    left: 12%;
    top: 58%;
    width: 12%;
    height: 20%;
}
    </style>
  @endpush
{{-- The whole hall fits the screen, its name on the picture, Back beside it (x-city-scene). --}}
<x-city-scene :image="asset('asset/front/images/city-hall.png')" :width="4096" :height="2288"
    :title="__('messages.nav_city_hall')" :hint="__('city.scene.hint_city_hall')" :fallback="route('education.city')">
    <a href="{{ route('education.main-hall') }}"
    class="hotspot board" title="{{ __('cityhall.main_hall') }}"></a>

    <a href="{{ route('education.civic-chamber') }}"
    class="hotspot left-room" title="{{ __('cityhall.civic_chamber') }}"></a>

    <a href="{{ route('education.well-being-room') }}"
    class="hotspot right-room" title="{{ __('cityhall.wellbeing_room') }}"></a>
</x-city-scene>
@endsection