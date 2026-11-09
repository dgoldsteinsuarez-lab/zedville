@extends('layouts.profile')
@section('no_page_back', '1'){{-- Back is beside the picture --}}

@section('title', $district['name'])

@section('content')
@php
    $picture = \App\Http\Controllers\CityController::districtPicture($district);
    $where = __('city.district_number', ['number' => $district['number']])
        . ($myAddress ? ' · ' . __('city.you_live_at', ['number' => $myAddress->house_number, 'street' => $myAddress->street->name]) : '');
@endphp
<div class="grid grid-cols-1 gap-5" x-data="{ modalTitle: '', modalBody: '', showModal: false }">

    {{-- Daniel's 3D view of this division (ZED_Infra - 3D View - nD), the whole
         of it on the first screen like the City Map (Daniel, 2026-10-28: "make it
         in all photos that has this scrolling"): Back beside it, its name and
         where you live above it, the hint under it. --}}
    <x-city-scene class="zs--map zs--flat zv-street-map" labels-outside :image="$picture['url']"
        :width="$picture['width']" :height="$picture['height']" :crop-top="$picture['cropTop']" :crop-bottom="$picture['cropBottom']"
        :title="$district['name']" :subtitle="$where" :alt="__('city.district_alt', ['name' => $district['name']])"
        :hint="count($buildingSpots) || count($streetLabels) ? __('city.district_hint') : null" :fallback="route('education.city')">
            @include('education.city._building-spots')
            @include('education.city._street-labels')
    </x-city-scene>

    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-4">{{ __('city.buildings_title') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @forelse ($buildings as $building)
                @if ($building['route'])
                    <a href="{{ $building['route'] }}"
                       class="block rounded-md border border-[#D2DDDB] px-4 py-3 text-sm font-semibold hover:bg-[#E9F0E9]">
                        {{ $building['name'] }}
                    </a>
                @else
                    <button type="button"
                        @click="modalTitle = @js($building['name']); modalBody = @js(__('city.coming_soon')); showModal = true"
                        class="block w-full text-left rounded-md border border-[#D2DDDB] px-4 py-3 text-sm font-semibold text-gray-400 hover:bg-[#F1F1F1]">
                        {{ $building['name'] }}
                    </button>
                @endif
            @empty
                <p class="text-gray-500">{{ __('city.no_buildings') }}</p>
            @endforelse
        </div>
    </div>

    @if ($streets->isNotEmpty())
    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-2">{{ __('city.street_plaques') }}</h2>
        <p class="text-gray-500 mb-4">{{ __('city.street_plaques_text') }}</p>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach ($streets as $street)
                <button type="button"
                    @click="modalTitle = @js($street->name); modalBody = @js($street->localized_bio); showModal = true;
                        fetch('{{ route('participation.award') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ action: 'street_sign_press', detail: '{{ $street->id }}' }),
                        })"
                    class="block w-full text-left rounded-md border border-[#D2DDDB] px-4 py-3 text-sm font-semibold hover:bg-[#E9F0E9]">
                    {{ $street->name }}
                </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Shared modal for both "Coming soon" buildings and street bios --}}
    <div x-show="showModal"
         x-cloak
         x-transition.opacity
         @keydown.escape.window="showModal = false"
         class="fixed w-full h-full z-[100] overflow-y-auto top-0 left-0 themeModal">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showModal = false"></div>
        <div class="modalDilog max-w-[520px] mx-auto mt-24 relative">
            <div class="modalContent bg-white py-8 px-10 rounded-lg border border-[#D2DDDB]">
                <h4 class="text-xl font-semibold text-black mb-3" x-text="modalTitle"></h4>
                <p class="text-gray-600" x-text="modalBody"></p>
                <button type="button" @click="showModal = false" class="themeBtn w-full mt-6">{{ __('city.close') }}</button>
            </div>
        </div>
    </div>

</div>
@endsection
