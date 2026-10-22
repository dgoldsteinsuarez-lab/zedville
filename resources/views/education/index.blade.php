@extends('layouts.profile')

@section('title', 'Needs, Wants & Savings Match')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('asset/front/css/mba.css') }}?ver={{ rand(111, 999) }}">
@endpush

<div class="flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">
        Needs, Wants &amp; Savings Match
    </h1>

    <a href="{{ url()->previous() }}"
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
        ← Back
    </a>
</div>

<div class="grid grid-cols-1 gap-5 ">
    <div class="themeTabspills">
        <div class="w-full">
            <div id="root"></div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        window.userRole = {{ $user->role }};
        window.templateData = @json($template);
    </script>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script type="text/babel" src="{{ asset('asset/front/js/mba.js') }}?ver={{ rand(111, 999) }}"></script>
@endpush