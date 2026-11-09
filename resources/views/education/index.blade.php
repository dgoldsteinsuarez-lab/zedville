@extends('layouts.profile')

@section('title', __('mba.title'))

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('asset/front/css/mba.css') }}?ver={{ rand(111, 999) }}">
@endpush

<div class="flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">
        {{ __('mba.title') }}
    </h1>

</div>

{{-- Instructions and what the four boxes mean (Daniel, 2026-10-22). --}}
<div class="mb-5 rounded-lg border border-[#D2DDDB] bg-white p-5">
    <h2 class="text-lg font-bold mb-3">{{ __('mba.how_title') }}</h2>
    <div class="grid gap-5 lg:grid-cols-2">
        <ol class="list-decimal pl-5 space-y-1 text-gray-700">
            <li>{{ __('mba.step_1') }}</li>
            <li>{{ __('mba.step_2') }}</li>
            <li>{{ __('mba.step_3') }}</li>
        </ol>
        <div class="space-y-1 text-gray-700">
            @foreach (['what_income', 'what_needs', 'what_wants', 'what_savings'] as $key)
                @php $line = __('mba.' . $key); @endphp
                <p class="mb-0"><strong class="text-[#016950]">{{ \Illuminate\Support\Str::before($line, ':') }}:</strong>{{ \Illuminate\Support\Str::after($line, ':') }}</p>
            @endforeach
            <p class="mb-0 text-sm text-gray-500 pt-1">{{ __('mba.rule') }}</p>
        </div>
    </div>
    @if ($simulated)
        <div id="mbaSimulatedNote" class="mt-4 rounded-md p-3" style="background:#FFF5D4;border:1px solid #F1E2B0;">
            <p class="font-semibold mb-1" style="color:#8a6d1d;">{{ __('mba.simulated_title') }}</p>
            <p class="text-sm mb-0" style="color:#6b5417;">{{ __('mba.simulated_text') }}</p>
        </div>
    @endif
</div>

<div class="grid grid-cols-1 gap-5 ">
    <div class="themeTabspills">
        <div class="w-full">
            <div id="root"></div>
        </div>
    </div>
</div>
@include('partials.finish')
@endsection
@push('scripts')
    <script>
        window.userRole = {{ $user->role }};
        window.templateData = @json($template);
        window.mbaSimulated = @json($simulated);
        // Texts for mba.js in the student's language (lang/<language>/mba.php).
        window.ZV_MBA = @json(__('mba'));
    </script>
    {{-- Exact versions: the unversioned Babel URL started serving Babel 8,
         which compiles JSX into `import` statements a plain script can't run
         - the activity stayed blank. --}}
    <script crossorigin src="https://unpkg.com/react@18.3.1/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone@7.26.10/babel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script type="text/babel" src="{{ asset('asset/front/js/mba.js') }}?ver={{ rand(111, 999) }}"></script>
@endpush