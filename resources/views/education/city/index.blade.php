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

<div class="grid grid-cols-1 gap-5">

    {{-- The real city overview image + positioned district hotspots go
    here once Daniel supplies the exported render - same clip-path-polygon
    technique as education/city-mall.blade.php. Until then this is a
    plain clickable directory so the routing/data layer is fully
    testable. --}}
    <div class="rounded-lg border border-dashed border-[#D2DDDB] bg-[#F8FAF9] p-10 text-center text-gray-500">
        City map image coming soon.
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

</div>
@endsection
