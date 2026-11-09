@extends('layouts.profile')
@section('no_page_back', '1'){{-- Back is beside the map --}}

@section('title', 'City Map')

@section('content')
<div class="grid grid-cols-1 gap-5" x-data="{ modalTitle: '', modalBody: '', showModal: false }">

    {{-- Daniel's top view of Zedville (ZED_Infra - 3D View - WHOLE). Each of
         his 9 divisions is a clickable area; positions come from
         CityController::districts() ('area' = left, top, width, height in %).
         The whole map fits the first screen like City Hall (Daniel,
         2026-10-27), but nothing on top of the map: the name and where you
         live above it, on Back's row, the hint under it. The picture's white
         strip at the bottom (its last 30 rows) is cut off. --}}
    <x-city-scene class="zs--map zs--flat zv-street-map zv-street-map--top" :image="asset('asset/front/images/city/overview.jpg')" :width="2400" :height="1465" :crop-bottom="30"
        :title="__('city.title')" :alt="__('city.map_alt')" :hint="__('city.overview_hint')" :fallback="route('dashboard')" labels-outside
        :subtitle="$myAddress ? __('city.you_live_at', ['number' => $myAddress->house_number, 'street' => $myAddress->street->name]) : null">
            @foreach ($districts as $district)
                @php [$left, $top, $width, $height] = $district['area']; @endphp
                <a href="{{ route('education.city.district', $district['key']) }}"
                   class="zv-district absolute block"
                   style="left:{{ $left }}%;top:{{ $top }}%;width:{{ $width }}%;height:{{ $height }}%;"
                   title="{{ __('city.district_number', ['number' => $district['number']]) }} - {{ $district['name'] }}"
                   aria-label="{{ __('city.district_number', ['number' => $district['number']]) }} - {{ $district['name'] }}"></a>
            @endforeach
            @include('education.city._building-spots')
            @include('education.city._street-labels')
    </x-city-scene>
    <style>
        .zv-district { border: 2px solid transparent; border-radius: 6px; transition: background .15s, border-color .15s; }
        .zv-district:hover, .zv-district:focus { background: rgba(0, 164, 125, .18); border-color: #00A47D; }
    </style>

    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-2">{{ __('city.basket_index') }}</h2>
        <p class="text-gray-500 mb-4">{{ __('city.basket_index_text') }}</p>

        @if (empty($basketTrend))
            <p class="text-gray-500">{{ __('city.basket_no_data') }}</p>
        @else
            @php $current = $basketTrend[0]; @endphp
            <div class="flex items-baseline gap-3 mb-4">
                <span class="text-2xl font-bold">{{ number_format($current['avgBasketCost'], 2) }} Z</span>
                @if ($current['pctChange'] !== null)
                    <span class="text-sm font-semibold {{ $current['pctChange'] > 0 ? 'text-red-600' : ($current['pctChange'] < 0 ? 'text-green-600' : 'text-gray-500') }}">
                        {{ __('city.vs_last_month', ['change' => ($current['pctChange'] > 0 ? '+' : '') . $current['pctChange']]) }}
                    </span>
                @endif
                <span class="text-sm text-gray-500">{{ trans_choice('city.students_count', $current['studentCount']) }}</span>
            </div>

            @if (count($basketTrend) > 1)
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="pb-2">{{ __('city.month') }}</th>
                            <th class="pb-2">{{ __('city.avg_basket_cost') }}</th>
                            <th class="pb-2">{{ __('city.change') }}</th>
                            <th class="pb-2">{{ __('city.students') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($basketTrend as $row)
                            <tr class="border-t border-[#D2DDDB]">
                                <td class="py-2">{{ \Carbon\Carbon::create($row['year'], $row['month'], 1)->translatedFormat('M Y') }}</td>
                                <td class="py-2">{{ number_format($row['avgBasketCost'], 2) }} Z</td>
                                <td class="py-2">
                                    @if ($row['pctChange'] === null)
                                        &mdash;
                                    @else
                                        {{ $row['pctChange'] > 0 ? '+' : '' }}{{ $row['pctChange'] }}%
                                    @endif
                                </td>
                                <td class="py-2">{{ $row['studentCount'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>

    {{-- The buildings you can go into, before the districts (Daniel, 2026-11-02). --}}
    @php
        $icons = ['senior-care-home' => '👵', 'bank' => '🏦', 'money-factory' => '🏭', 'city-mall' => '🛍️', 'community-center' => '🤝', 'efd' => '🎓',
            'library' => '📚', 'supermarket' => '🛒', 'orphanage' => '🧸', 'donation-center' => '🎁', 'city-hall' => '🏛️', 'post-office' => '📮'];
    @endphp
    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-1">{{ __('city.important_buildings') }}</h2>
        <p class="text-gray-500 mb-4">{{ __('city.important_buildings_text') }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach ($importantBuildings as $building)
                <a href="{{ $building['route'] }}" class="flex items-center gap-3 rounded-md border border-[#D2DDDB] px-4 py-3 hover:bg-[#E9F0E9]">
                    <span class="text-2xl leading-none" aria-hidden="true">{{ $icons[$building['key']] ?? '🏢' }}</span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold">
                            @if ($building['npo'] ?? false)<span class="text-xs font-bold text-[#B45309]">{{ __('city.npo_badge') }}</span>@endif
                            {{ $building['name'] }}
                        </span>
                        <span class="block text-xs text-gray-500">{{ __('city.district_number', ['number' => $building['district_number']]) }} · {{ $building['district_name'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-4">{{ __('city.districts_title') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach ($districts as $district)
                <a href="{{ route('education.city.district', $district['key']) }}"
                   class="block rounded-md border border-[#D2DDDB] px-4 py-3 text-sm font-semibold hover:bg-[#E9F0E9]">
                    {{ $district['number'] }}. {{ $district['name'] }}
                </a>
            @endforeach
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

    {{-- Shared modal for street bios --}}
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
