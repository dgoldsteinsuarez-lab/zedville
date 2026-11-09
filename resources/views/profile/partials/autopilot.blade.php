{{-- Read-only for students (Daniel, 2026-10-22): only a Tutor, School Admin
     or Super Admin turns Autopilot on (Admin > Autopilot). The student sees
     any current or upcoming period set for them. --}}
@php
    $myAutopilot = \App\Models\AutopilotPeriod::where('student_id', auth()->id())
        ->where('status', 'active')->where('end_date', '>=', now()->toDateString())
        ->orderBy('start_date')->get();
@endphp
<div>
    <h3 class="text-lg font-semibold mb-2">{{ __('profile.autopilot') }}</h3>
    <p class="text-sm text-gray-600 mb-4">
        {{ __('profile.autopilot_text') }}
    </p>

    @forelse ($myAutopilot as $p)
        <div class="mb-3 p-3 rounded border border-[#D2DDDB]">
            <div class="font-medium">{{ __('profile.autopilot_on_until', ['date' => $p->end_date->translatedFormat('d M Y')]) }}</div>
            <div class="text-xs text-gray-500">{{ __('profile.autopilot_started', ['date' => $p->start_date->translatedFormat('d M Y'), 'reason' => $p->reason]) }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ __('profile.set_by_your_tutor') }}</div>
        </div>
    @empty
        <div class="p-3 rounded border border-[#D2DDDB] text-sm text-gray-600">{{ __('profile.autopilot_none') }}</div>
    @endforelse
</div>
