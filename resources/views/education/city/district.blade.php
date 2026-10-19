@extends('layouts.profile')

@section('title', $district['name'])

@section('content')
<div class="flex items-center justify-between pb-6">
    <div>
        <a href="{{ route('education.city') }}" class="text-sm text-gray-500">← Back to City Map</a>
        <h1 class="text-xl font-bold whitespace-nowrap">{{ $district['name'] }}</h1>
        @if ($myAddress)
            <p class="text-gray-500 mb-0">You live at {{ $myAddress->house_number }} {{ $myAddress->street->name }}</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 gap-5" x-data="{ modalTitle: '', modalBody: '', showModal: false }">

    {{-- The real zoomed-in district image + positioned building hotspots
    go here once Daniel supplies it - same technique as education/city-mall
    .blade.php. Until then this is a plain clickable directory. --}}
    <div class="rounded-lg border border-dashed border-[#D2DDDB] bg-[#F8FAF9] p-10 text-center text-gray-500">
        {{ $district['name'] }} image coming soon.
    </div>

    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-4">Buildings</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @forelse ($buildings as $building)
                @if ($building['route'])
                    <a href="{{ $building['route'] }}"
                       class="block rounded-md border border-[#D2DDDB] px-4 py-3 text-sm font-semibold hover:bg-[#E9F0E9]">
                        {{ $building['name'] }}
                    </a>
                @else
                    <button type="button"
                        @click="modalTitle = @js($building['name']); modalBody = 'Coming soon.'; showModal = true"
                        class="block w-full text-left rounded-md border border-[#D2DDDB] px-4 py-3 text-sm font-semibold text-gray-400 hover:bg-[#F1F1F1]">
                        {{ $building['name'] }}
                    </button>
                @endif
            @empty
                <p class="text-gray-500">No buildings listed for this district yet.</p>
            @endforelse
        </div>
    </div>

    @if ($streets->isNotEmpty())
    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-2">Street Plaques</h2>
        <p class="text-gray-500 mb-4">Click a street to read who it's named after.</p>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach ($streets as $street)
                <button type="button"
                    @click="modalTitle = @js($street->name); modalBody = @js($street->bio); showModal = true"
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
                <button type="button" @click="showModal = false" class="themeBtn w-full mt-6">Close</button>
            </div>
        </div>
    </div>

</div>
@endsection
