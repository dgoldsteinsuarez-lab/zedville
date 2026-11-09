{{-- Buildings with their own page, clickable right on the city image
     (CityController::buildingSpots). Must sit inside the `position: relative`
     wrapper around the image; drawn above the district areas, below the
     street names. Pointing at a building drops a map pin on it with its name
     (Daniel, 2026-10-26: a pin, not a box - boxes are for the districts), and
     an NPO's name is tagged "NPO". --}}
@foreach ($buildingSpots as $spot)
    @php
        [$left, $top, $width, $height] = $spot['area'];
        $label = $spot['npo'] ? __('city.npo_badge') . ' ' . $spot['name'] : $spot['name'];
    @endphp
    <a href="{{ $spot['route'] }}"
       class="zv-building{{ $spot['npo'] ? ' is-npo' : '' }}"
       style="left:{{ $left }}%;top:{{ $top }}%;width:{{ $width }}%;height:{{ $height }}%;"
       title="{{ $label }}"
       aria-label="{{ $label }}">
        <span class="zv-pin" aria-hidden="true">
            <span class="zv-building-name">
                @if ($spot['npo'])<b class="zv-npo">{{ __('city.npo_badge') }}</b>@endif
                {{ $spot['name'] }}
            </span>
            <svg viewBox="0 0 28 38" width="28" height="38">
                <path class="zv-pin-body" d="M14 1C6.8 1 1 6.6 1 13.6 1 23 14 37 14 37s13-14 13-23.4C27 6.6 21.2 1 14 1z"/>
                @if ($spot['npo'])
                    <path d="M14 19.2c-.2 0-.4-.1-.5-.2-2.4-2-4-3.4-4-5.2 0-1.4 1.1-2.5 2.4-2.5.9 0 1.6.4 2.1 1.1.5-.7 1.2-1.1 2.1-1.1 1.3 0 2.4 1.1 2.4 2.5 0 1.8-1.6 3.2-4 5.2-.1.1-.3.2-.5.2z" fill="#fff"/>
                @else
                    <circle cx="14" cy="13.6" r="4.6" fill="#fff"/>
                @endif
            </svg>
        </span>
    </a>
@endforeach
@once
<style>
    .zv-building { position: absolute; z-index: 1; display: block; outline: none; }
    /* The pin's tip sits on the middle of the building; its name floats above the pin. */
    .zv-pin { position: absolute; left: 50%; top: 50%; display: flex; flex-direction: column; align-items: center; gap: 3px;
        transform: translate(-50%, calc(-100% - 10px)); opacity: 0; pointer-events: none;
        transition: opacity .15s, transform .25s cubic-bezier(.3, 1.6, .5, 1); }
    .zv-building:hover .zv-pin, .zv-building:focus .zv-pin { opacity: 1; transform: translate(-50%, -100%); }
    .zv-pin svg { display: block; filter: drop-shadow(0 3px 3px rgba(0, 0, 0, .35)); }
    .zv-pin-body { fill: #00A47D; stroke: #fff; stroke-width: 2; }
    .zv-building.is-npo .zv-pin-body { fill: #E0523A; }
    .zv-building-name { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
        font-size: 12px; font-weight: 700; color: #fff; background: #00A47D; border-radius: 999px; padding: 3px 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .25); }
    .zv-building.is-npo .zv-building-name { background: #E0523A; }
    .zv-npo { font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #E0523A;
        background: #fff; border-radius: 999px; padding: 1px 6px; }
    @media (prefers-reduced-motion: reduce) { .zv-pin { transition: opacity .15s; } }
</style>
@endonce
