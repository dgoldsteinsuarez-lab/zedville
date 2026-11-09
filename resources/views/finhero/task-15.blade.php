@extends('layouts.profile')

@section('title', __('insurance.choose.title'))

{{-- FinHero Task 15: choose your health insurance - the two plans, the cases to work out and then the choice
     (finhero/_quiz, App\Services\Finhero\Insurance\ChoosePlan, App\Services\InsuranceService). --}}
@section('content')
    @include('finhero._quiz')
@endsection
