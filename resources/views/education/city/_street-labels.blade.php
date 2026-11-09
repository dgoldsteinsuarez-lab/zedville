{{-- Street names drawn over a city image (Daniel's street plan,
     App\Support\CityStreetLabels). Must sit inside a `position: relative`
     wrapper around the image and inside the page's modal x-data. Clicking a
     name opens the street's bio, same as the plaques. --}}
@foreach ($streetLabels as $label)
    <button type="button"
        class="zv-street"
        style="left:{{ $label['x'] }}%;top:{{ $label['y'] }}%;transform:translate(-50%, -50%) rotate({{ $label['angle'] }}deg);"
        @click.prevent.stop="modalTitle = @js($label['street']->name); modalBody = @js($label['street']->localized_bio); showModal = true;
            fetch('{{ route('participation.award') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'street_sign_press', detail: '{{ $label['street']->id }}' }),
            })">{{ $label['street']->name }}</button>
@endforeach
@once
<style>
    /* isolation: the map's own layers (street names, pins, districts) stay inside the map, under any pop-up. */
    .zv-street-map { container-type: inline-size; isolation: isolate; --zv-road: rgb(128, 129, 121); }
    .zv-street-map--top { --zv-road: rgb(108, 108, 98); }
    /* Map-style lettering, small, printed on the road. The backing is the
       asphalt's own grey: invisible on open road, but where a name has to
       cross a lane dash, a lamp post or a car it keeps the white readable
       (Daniel, 2026-10-24). */
    .zv-street { position: absolute; z-index: 2; white-space: nowrap; line-height: 1;
        font-size: clamp(6px, .8cqw, 11px); font-weight: 600; letter-spacing: .02em;
        color: #fff; text-shadow: 0 0 2px rgba(0, 0, 0, .6);
        background: var(--zv-road); padding: .15em .3em; border-radius: 3px; cursor: pointer; }
    .zv-street:hover, .zv-street:focus { color: #016950; text-shadow: none;
        background: rgba(255, 255, 255, .92); outline: none; }
</style>
@endonce
