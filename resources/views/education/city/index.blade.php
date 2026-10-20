@extends('layouts.profile')

@section('title', 'City Map')

@section('content')
<div class="flex items-center justify-between pb-6">
    <div>
        <h1 class="text-xl font-bold whitespace-nowrap">City Map</h1>
        @if ($myAddress)
            <p class="text-gray-500 mb-0">You live at {{ $myAddress->house_number }} {{ $myAddress->street->name }}</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 gap-5" x-data="{ modalTitle: '', modalBody: '', showModal: false }">

    {{-- The real city overview image + positioned district hotspots go
    here once Daniel supplies the exported render - same clip-path-polygon
    technique as education/city-mall.blade.php. Until then this is a
    plain clickable directory so the routing/data layer is fully
    testable. --}}
    <div class="rounded-lg border border-dashed border-[#D2DDDB] bg-[#F8FAF9] p-10 text-center text-gray-500">
        City map image coming soon.
    </div>

    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-2">City Basket Index</h2>
        <p class="text-gray-500 mb-4">
            This tracks the same idea as a real Consumer Price Index: the average cost of a fixed
            basket of groceries over time is one of the main ways economists measure inflation.
        </p>

        @if (empty($basketTrend))
            <p class="text-gray-500">Not enough data yet - once students in your grade submit their
                monthly grocery survey, the average basket cost will show up here.</p>
        @else
            @php $current = $basketTrend[0]; @endphp
            <div class="flex items-baseline gap-3 mb-4">
                <span class="text-2xl font-bold">{{ number_format($current['avgBasketCost'], 2) }} Z</span>
                @if ($current['pctChange'] !== null)
                    <span class="text-sm font-semibold {{ $current['pctChange'] > 0 ? 'text-red-600' : ($current['pctChange'] < 0 ? 'text-green-600' : 'text-gray-500') }}">
                        {{ $current['pctChange'] > 0 ? '+' : '' }}{{ $current['pctChange'] }}% vs last month
                    </span>
                @endif
                <span class="text-sm text-gray-500">({{ $current['studentCount'] }} student{{ $current['studentCount'] === 1 ? '' : 's' }})</span>
            </div>

            @if (count($basketTrend) > 1)
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="pb-2">Month</th>
                            <th class="pb-2">Avg. basket cost</th>
                            <th class="pb-2">Change</th>
                            <th class="pb-2">Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($basketTrend as $row)
                            <tr class="border-t border-[#D2DDDB]">
                                <td class="py-2">{{ \Carbon\Carbon::create($row['year'], $row['month'], 1)->format('M Y') }}</td>
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

    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-4">Districts</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach ($districts as $district)
                <a href="{{ route('education.city.district', $district['key']) }}"
                   class="block rounded-md border border-[#D2DDDB] px-4 py-3 text-sm font-semibold hover:bg-[#E9F0E9]">
                    {{ $district['name'] }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($streets->isNotEmpty())
    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-2">Street Plaques</h2>
        <p class="text-gray-500 mb-4">Click a street to read who it's named after.</p>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach ($streets as $street)
                <button type="button"
                    @click="modalTitle = @js($street->name); modalBody = @js($street->bio); showModal = true;
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
                <button type="button" @click="showModal = false" class="themeBtn w-full mt-6">Close</button>
            </div>
        </div>
    </div>

</div>
@endsection
