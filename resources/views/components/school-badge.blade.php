{{-- Reusable "which school am I looking at" badge — no "Current School:" label,
     just the name, styled. The edit pencil (switch school) only makes sense for
     Super Admin; everyone else is pinned to one school by IsAdmin middleware. --}}
@php
    $school = \App\Models\SchoolDomain::find(session('selected_school'));
@endphp
@if ($school)
    <div class="d-inline-flex align-items-center gap-2 ps-2 pe-3 py-1" style="background:#F1FCF8; border:1px solid #CFE9DE; border-radius:999px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 21h18M5 21V10l7-5 7 5v11M9 21v-6h6v6" stroke="#1B6B3A" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="fw-600" style="color:#1B6B3A; font-size:.95rem; letter-spacing:.01em;">{{ $school->school_name }}</span>
        @if (auth()->user()->isSuperAdmin())
            <a href="{{ url('admin/dashboard') }}" title="Switch school" style="color:#1B6B3A; opacity:.6; line-height:1;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 4.99994L19 7.99994" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M4.50001 16.4994L18.4999 2.49994L21.5004 5.49994L7.50041 19.4999L3 20.9995L4.50001 16.4994Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M5 16.9999L7 18.9999" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </a>
        @endif
    </div>
@endif
