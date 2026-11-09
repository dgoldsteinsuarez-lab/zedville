@extends('layouts.profile')

@section('title', 'City Mall - Educational Finance Department')
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
.store {
  position: absolute;
  inset: 0;
  pointer-events: none; /* important fix */
}
.store span{
    display: none;
}
/* polygon area + clickable event lives here */
.store::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: auto; /* click allowed only inside polygon */
  clip-path: var(--poly);
  background: rgba(0, 0, 0, 0);
  outline: none;
}
/* Top row */
.s1 { --poly: polygon(0% 6%, 16% 27%, 16% 53%, 0% 43%); }
.s2 { --poly: polygon(19% 12%, 32% 25%, 32% 48%, 19% 42%); top: 21%; }
.s3 { --poly: polygon(33% 26%, 44% 37%, 44% 54%, 33% 49%); top: 20%; }
.s4 { --poly: polygon(69% 10%, 80% -2%, 80% 32%, 69% 38%) ;top: 33%;; }
.s5 { --poly: polygon(83% 28%,  100% 6%,  100% 43%, 83% 53%); }

/* Bottom row */
.s6 { --poly: polygon(0% 30%, 16% 40%, 16% 85%, 0% 96%);      top: 31%;}
.s7 { --poly: polygon(19% 27%, 31% 33%, 31% 71%, 19% 77%); top: 43%; }
.s8 { --poly: polygon(59% 28%, 67% 22%, 67% 64%, 59% 61%);  top: 51%;}
.s9 { --poly: polygon(68% 31%, 80% 25%, 80% 77%, 68% 71%); top: 45%; }
.s10 { --poly: polygon(83% 48%, 100% 39%, 100% 98%, 83% 87%); top: 20%;}


    </style>
  @endpush
{{-- The whole mall fits the screen, its name on the picture, Back beside it (x-city-scene).
     The top of the dome and the bottom of the floor are cut off. --}}
<x-city-scene :image="asset('asset/front/images/citymall.png')" :width="3344" :height="2496" :crop-top="238" :crop-bottom="165"
    :title="__('messages.nav_city_mall')" :hint="__('city.scene.hint_city_mall')" :fallback="route('education.city')">
       <a class="store s1" href="{{ route('education.spending-tracker', ['type' => 'basicco']) }}"><span>The Basics Co</span></a>
  <a class="store s2" href="{{ route('education.spending-tracker', ['type' => 'stationery-store']) }}"><span>Stationery Store</span></a>
  <a class="store s3" href="{{ route('education.spending-tracker', ['type' => 'daily-essentials']) }}"><span>Daily Essentials (L)</span></a>
  <a class="store s4" href="{{ route('education.spending-tracker', ['type' => 'tech-hub']) }}"><span>Tech Hub</span></a>
  <a class="store s5" href="{{ route('education.spending-tracker', ['type' => 'daily-essentials']) }}"><span>Daily Essentials (R)</span></a>
  <a class="store s6" href="{{ route('education.spending-tracker', ['type' => 'comfort-zone']) }}"><span>Comfort Zone</span></a>
  <a class="store s7" href="{{ route('education.spending-tracker', ['type' => 'beu-beLuxury']) }}"><span>BeU-BeLuxury</span></a>
  <a class="store s8" href="{{ route('education.spending-tracker', ['type' => 'accessories']) }}"><span>Accessories</span></a>
  <a class="store s9" href="{{ route('education.spending-tracker', ['type' => 'bespirit-sport-shop']) }}"><span>BeSpint-Sport Shop</span></a>
  <a class="store s10" href="{{ route('education.spending-tracker', ['type' => 'beats-music-store']) }}"><span>Beats-Music Store</span></a>
</x-city-scene>
<script>
function enterShop(storeType) {
    const baseUrl = "{{ url('/education/spending-tracker') }}";
    window.location.href = baseUrl + '/' + storeType;
}
</script>
@endsection