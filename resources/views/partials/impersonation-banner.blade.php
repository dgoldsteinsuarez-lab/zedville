{{-- Shown on every page (both admin and student-facing layouts) while a
     Super Admin is "viewing as" another user - see ImpersonateController.
     Included from layouts/admin.blade.php and layouts/profile.blade.php
     so there's always a way back, even while viewing as a Student (who
     never sees the admin layout at all). --}}
@if (auth()->check() && session()->has('impersonator_id'))
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 p-3" style="background:#FEF3C7; border:1px solid #FCD34D; border-radius:10px;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge" style="background:#D97706; color:#fff; font-size:.8rem; padding:.45em .8em;">VIEWING AS</span>
            <span class="text-muted" style="font-size:.9rem;">
                {{ auth()->user()->name }} ({{ auth()->user()->role_name }})
            </span>
        </div>
        <form action="{{ url('/impersonate/stop') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm" style="background:#fff; border:1px solid #D97706; color:#D97706;">
                Return to my account
            </button>
        </form>
    </div>
@endif
