@extends('layouts.profile')

@section('title', __('credit.compare.title'))

{{-- FinHero Task 16: two loans compared (App\Services\Finhero\Credit\CompareLoans), a chart and questions (finhero/_quiz). --}}
@section('content')
    @include('finhero._quiz')
@endsection
