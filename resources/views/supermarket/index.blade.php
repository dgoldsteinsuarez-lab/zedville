@extends('layouts.profile')

@section('title', 'Supermarket ')
@section('no_page_back', '1'){{-- Back is on the picture --}}

@section('content')
@push('styles')
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
  background: rgba(0, 0, 0, .6);
 
  /* Debug (Option B) */
  outline: 1px dashed rgba(255,0,0,.7);
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
{{-- The whole supermarket fits the screen, its name on the picture, Back beside it (x-city-scene);
     the picture itself is the way in. Most of the sky and the street are cut off. --}}
<x-city-scene :image="asset('asset/front/images/supermarket.png')" :width="2368" :height="1728" :crop-top="236" :crop-bottom="120" hide-title
    :title="__('messages.nav_supermarket')" :hint="__('city.scene.hint_supermarket')" :fallback="route('education.city')">
    <a href="{{ route('supermarket.market-list') }}" class="absolute inset-0" aria-label="{{ __('city.scene.go_shopping') }}"></a>
</x-city-scene>
@endsection