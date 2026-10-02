@php
    $myAutopilot = \App\Models\AutopilotPeriod::where('student_id', auth()->id())
        ->where('status', 'active')->where('end_date', '>=', now()->toDateString())
        ->orderBy('start_date')->get();
@endphp
<div>
    <h3 class="text-lg font-semibold mb-2">Autopilot</h3>
    <p class="text-sm text-gray-600 mb-4">
        If you can't take part for a while (for example you are ill or away), you can turn Autopilot on.
        While it is on, your account follows your class's average at month-end so you don't fall behind.
        You choose when it ends, and you can switch it off again any time.
    </p>

    @forelse ($myAutopilot as $p)
        <div class="mb-3 p-3 rounded border border-[#D2DDDB]">
            <div class="font-medium">Autopilot is ON until {{ $p->end_date->format('d M Y') }}</div>
            <div class="text-xs text-gray-500">Started {{ $p->start_date->format('d M Y') }} &middot; {{ $p->reason }}</div>
            @if ($p->created_by == auth()->id())
                <form method="POST" action="{{ route('profile.autopilot.cancel', $p->id) }}" class="mt-2">
                    @csrf
                    <button class="px-3 py-1 text-sm border rounded">Turn off</button>
                </form>
            @else
                <div class="text-xs text-gray-500 mt-1">Set by your tutor.</div>
            @endif
        </div>
    @empty
        <form method="POST" action="{{ route('profile.autopilot.store') }}">
            @csrf
            <label class="block text-sm mb-1">Keep Autopilot on until</label>
            <input type="date" name="end_date" required min="{{ now()->toDateString() }}" max="{{ now()->addDays(90)->toDateString() }}" class="border rounded px-2 py-1 mb-3">
            <label class="block text-sm mb-1">Reason (optional)</label>
            <input type="text" name="reason" maxlength="1000" class="border rounded px-2 py-1 w-full mb-3" placeholder="e.g. I'm ill and can't play this week">
            @error('end_date')<div class="text-red-600 text-sm mb-2">{{ $message }}</div>@enderror
            <button class="px-4 py-2 rounded bg-blue-600 text-white">Turn on Autopilot</button>
        </form>
    @endforelse
</div>
