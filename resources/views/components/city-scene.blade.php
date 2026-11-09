{{-- A place of the city (City Hall, City Mall, Supermarket, the Educational
     Finance Department, the market list) as one picture as big as the screen
     allows without scrolling (Daniel, 2026-10-26: "the whole picture fits in
     the browser and I would not need to scroll").
     On the picture, glass (Daniel, 2026-10-27; 2026-10-29: "put the text back
     on its positions like last time on the photo. And the back button as well,
     so you can maximize the photo"): the name in the middle at the top, the
     hint at the bottom - the picture takes the whole page. Back sits outside
     the picture, at the top left of the page, wherever there's white beside
     the picture for it (Daniel, 2026-10-30, City Mall: "move the back button
     outside"); only where the picture fills the width does it go on the
     picture, at its top left. The page's clickable areas go in the slot,
     positioned in % of the picture.
     crop-top / crop-bottom: rows of the picture (in its own pixels) left out
     of the scene, so the name and hint sit on what's worth seeing (Daniel,
     2026-10-27, lines drawn on screenshots). The whole picture and its
     clickable areas stay together in .zs-frame, shifted up by crop-top.
     hint: optional - a picture with nothing to click on the way in has none.
     class "zs--flat": a picture with its own edge on white (the City Map and
     its districts) - no frame or shadow around it.
     hide-title: no name on the picture where the picture already says it (the
     Supermarket's sign, Daniel 2026-10-29: "remove the top text 'supermarket'
     because its already redundant") - it stays for screen readers.
     labels-outside: Back and the name (and a subtitle under it) in a row
     above the picture, the hint under it - nothing on the picture (the City
     Map and its districts, Daniel 2026-10-27: "the text should not be on top
     of the map"). --}}
@props(['image', 'width', 'height', 'title', 'hint' => null, 'fallback', 'cropTop' => 0, 'cropBottom' => 0, 'labelsOutside' => false, 'hideTitle' => false, 'subtitle' => null, 'alt' => null])
@php($shown = $height - $cropTop - $cropBottom)
<div {{ $attributes->merge(['class' => $labelsOutside ? 'zs zs--outside' : 'zs']) }} data-zs>
    @include('partials.page-back', ['fallback' => $fallback, 'label' => __('layout.back'), 'buttonClass' => 'zvBackBtn zs-back'])
    @if ($labelsOutside)
        <div class="zs-head">
            <h1 class="zs-head-title">{{ $title }}</h1>
            @if ($subtitle)
                <p class="zs-head-sub">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div class="zs-stage">
        <div class="zs-scene" data-ratio="{{ round($width / $shown, 4) }}" style="aspect-ratio: {{ $width }} / {{ $shown }}">
            <div class="zs-frame" style="aspect-ratio: {{ $width }} / {{ $height }}; top: {{ round(-$cropTop / $shown * 100, 4) }}%">
                <img src="{{ $image }}" alt="{{ $alt ?? $title }}" class="zs-img">

                {{ $slot }}
            </div>

            @unless ($labelsOutside)
                <h1 class="{{ $hideTitle ? 'sr-only' : 'zs-title' }}">{{ $title }}</h1>
                @if ($hint)
                    <p class="zs-hint">{{ $hint }}</p>
                @endif
            @endunless
        </div>
    </div>
    @if ($labelsOutside && $hint)
        <p class="zs-foot">{{ $hint }}</p>
    @endif
</div>

@once
@push('styles')
<style>
    /* Less white around the picture on these pages, so it can be bigger; the
       picture (or the hint under it) ends 6px above the bottom: no scrollbar. */
    main:has([data-zs]) { padding: 14px 18px 6px; }

    /* container-type: the picture's width (set by zsFit) must not hold the
       page open. Without it, opening the sidebar couldn't narrow the page and
       the window scrolled sideways (Daniel, 2026-10-27). */
    .zs { --zs-green: #00A47D; --zs-dark: #0F2A22; position: relative; container-type: inline-size; }
    /* The scene is sized and placed by script (zsFit) in the room left on the screen.
       container-type: what's on the picture can be sized in cqw, % of the picture's
       width, so it grows and shrinks with it (Daniel, 2026-10-27). */
    .zs-scene { position: relative; width: 100%; max-width: 100%; margin: 0 auto; border-radius: 14px; overflow: hidden; container-type: inline-size;
        box-shadow: 0 18px 40px rgba(15, 42, 34, .18); }
    .zs--flat .zs-scene { box-shadow: none; border-radius: 0; }
    /* the whole picture; the scene's overflow cuts off the cropped rows */
    .zs-frame { position: absolute; left: 0; width: 100%; }
    .zs-img { display: block; width: 100%; height: 100%; object-fit: cover; pointer-events: none; user-select: none; }

    /* Back: at the top left of the page with the picture beside it or below it;
       where the picture fills the width, zsFit puts it on the picture's top left */
    .zs > .zv-page-back { position: absolute; top: 0; left: 0; z-index: 3; }
    /* its look is the app's Back button (.zvBackBtn, theme_style.css); this is its size */
    .zs-back { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; font-size: 14px; }

    /* The name on glass, in the middle at the top, clear of Back; the hint at the
       bottom - both close to the edge, to cover as little of the picture as
       possible (Daniel, 2026-10-30: the name "a little bit up", the hint "a
       little lower"). Neither catches clicks, so the places under them stay
       clickable. */
    .zs-title, .zs-hint { position: absolute; left: 50%; transform: translateX(-50%); pointer-events: none;
        width: max-content; max-width: calc(100% - 32px); text-align: center; color: var(--zs-dark); }
    .zs-title { top: 8px; max-width: calc(100% - 280px); margin: 0; padding: .3em 1em; white-space: nowrap; border-radius: 999px;
        font: 800 clamp(14px, 2cqw, 26px)/1.1 "Manrope", sans-serif; letter-spacing: -.01em;
        background: rgba(255, 255, 255, .62); border: 1px solid rgba(255, 255, 255, .85);
        backdrop-filter: blur(12px) saturate(1.3); -webkit-backdrop-filter: blur(12px) saturate(1.3); box-shadow: 0 8px 24px rgba(15, 42, 34, .18); }
    /* a long hint (German) goes onto two lines on a small picture rather than past its edges */
    .zs-hint { bottom: 8px; margin: 0; padding: 7px 16px; border-radius: 18px; font-size: clamp(12px, 1.05cqw, 13px); font-weight: 600;
        background: rgba(255, 255, 255, .7); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }

    /* labels-outside: the name with the subtitle under it, at the top, level
       with Back (kept clear of it either side); the picture starts just under
       them, beside Back; the hint just under the picture, near the
       bottom - as little height as possible, so the picture gets the rest
       (Daniel, 2026-10-27: "really maximize the space", marked on a screenshot). */
    /* the three gaps the eye sees - above the name, between the name and the
       subtitle, and above the picture - are all 10px of white (Daniel,
       2026-10-27: "the spacing ... should be all equal") */
    .zs-head { margin: -7px 0 6px; padding: 0 130px; text-align: center; }
    .zs-head-title { margin: 0; font: 800 21px/1.1 "Manrope", sans-serif; letter-spacing: -.01em; color: var(--zs-dark); }
    .zs-head-sub { margin: 8px 0 0; font-size: 14px; line-height: 1.25; color: #4B5B55; }
    .zs-foot { margin: 6px 0 0; text-align: center; font-size: 13px; font-weight: 600; line-height: 1.3; color: #4B5B55; }

    /* phones: the picture just takes the width; on it, Back at the left and the
       name at the right; with the labels outside, Back above */
    @media (max-width: 700px) {
        .zs--outside .zv-page-back { position: static; margin-bottom: 10px; }
        .zs:not(.zs--outside) > .zv-page-back { top: 8px; left: 8px; }
        .zs:not(.zs--outside) > .zv-page-back .zs-back { padding: 6px 12px; font-size: 13px; }
        .zs-title { top: 8px; left: auto; right: 8px; transform: none; max-width: calc(100% - 120px); white-space: normal; text-align: right; }
        .zs-hint { display: none; }
        .zs-head { margin-top: 0; padding: 0; }
    }
</style>
@endpush
@push('scripts')
<script>
    // The picture as big as the screen allows without scrolling: the room left
    // in the page's scrolling area. On the picture, Back, the name and the hint
    // take no room of their own. With the labels outside, Back shares the
    // name's row and the hint sits under the picture, so the picture gets
    // what's left between them, beside Back or under it - whichever is bigger.
    (function () {
        function zsFit() {
            document.querySelectorAll('[data-zs]').forEach(function (zs) {
                var scene = zs.querySelector('.zs-scene');
                var stage = scene.parentElement;
                var backBox = zs.querySelector(':scope > .zv-page-back');
                if (window.innerWidth <= 700) { scene.style.width = scene.style.marginLeft = stage.style.paddingTop = backBox.style.top = backBox.style.left = ''; return; }
                var main = zs.closest('main') || document.documentElement;
                var top = zs.getBoundingClientRect().top - main.getBoundingClientRect().top + main.scrollTop;
                var room = main.clientHeight - top - parseFloat(getComputedStyle(main).paddingBottom || 0) - 2;
                var ratio = parseFloat(scene.dataset.ratio);
                var full = zs.clientWidth;
                if (!zs.classList.contains('zs--outside')) {
                    var size = Math.floor(Math.max(320, Math.min(room * ratio, full)));
                    // Back beside the picture where the white around it has room for
                    // it (the picture moves right a little if it must, never smaller);
                    // otherwise on the picture, at its top left
                    var backRoom = zs.querySelector('.zs-back').offsetWidth + 16;
                    var outside = full - size >= backRoom;
                    var left = outside ? Math.max((full - size) / 2, backRoom) : (full - size) / 2;
                    scene.style.width = size + 'px';
                    stage.style.paddingTop = '';
                    scene.style.marginLeft = left + 'px';
                    backBox.style.top = outside ? '' : '16px';
                    backBox.style.left = outside ? '' : left + 16 + 'px';
                    return;
                }
                var back = zs.querySelector('.zs-back');
                var backW = back.offsetWidth + 16, backH = back.offsetHeight + 12;
                // the hint goes down to 6px above the bottom of the screen
                var outer = function (el) { if (!el) return 0; var cs = getComputedStyle(el); return el.offsetHeight + parseFloat(cs.marginTop) + parseFloat(cs.marginBottom); };
                var headH = outer(zs.querySelector('.zs-head'));
                var avail = room + parseFloat(getComputedStyle(main).paddingBottom || 0) - 6 - headH - outer(zs.querySelector('.zs-foot'));
                // beside Back (its foot reaches a little past the name's line), or all of it under Back
                var drop = Math.max(0, backH - headH);
                var besideBack = Math.min(avail * ratio, full - backW);
                var underBack = Math.min((avail - drop) * ratio, full);
                var fit = Math.floor(Math.max(320, besideBack, underBack));
                scene.style.width = fit + 'px';
                stage.style.paddingTop = underBack > besideBack ? drop + 'px' : '';
                // centred, unless that would put it under the Back button
                scene.style.marginLeft = (underBack > besideBack ? (full - fit) / 2 : Math.max((full - fit) / 2, backW)) + 'px';
            });
        }
        // the page area also changes size when the sidebar opens or closes
        var zs = document.querySelector('[data-zs]');
        var area = zs && zs.closest('main');
        if (area && window.ResizeObserver) new ResizeObserver(zsFit).observe(area);
        else window.addEventListener('resize', zsFit);
        document.addEventListener('DOMContentLoaded', zsFit);
        window.addEventListener('load', zsFit);
        zsFit();
    })();
</script>
@endpush
@endonce
