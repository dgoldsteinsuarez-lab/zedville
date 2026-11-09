{{-- The Back button every page gets from its layout (Daniel, 2026-10-22:
     "all pages should have a Back button, to return, sometimes it's
     difficult to find the X"). Goes to the previous Zedville page; without
     one (opened directly, or the previous page is this same page after a
     form was sent) it goes to $fallback. A page with its own back link at
     the top hides this one with @section('no_page_back', '1').
     Expects $fallback, $label and $buttonClass. --}}
<div class="zv-page-back">
    <a href="{{ $fallback }}" class="{{ $buttonClass }}" data-fallback="{{ $fallback }}" onclick="return zvPageBack(this)">
        <span aria-hidden="true">&larr;</span> {{ $label }}
    </a>
</div>
@once
<script>
    function zvPageBack(link) {
        try {
            const from = document.referrer ? new URL(document.referrer) : null;
            const samePage = from && from.pathname === location.pathname;
            if (from && from.origin === location.origin && !samePage && history.length > 1) {
                history.back();
                return false;
            }
        } catch (e) { /* unreadable referrer: use the link's own address */ }
        location.href = link.dataset.fallback;
        return false;
    }
</script>
@endonce
