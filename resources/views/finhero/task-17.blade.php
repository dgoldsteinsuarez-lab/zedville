@extends('layouts.profile')

@section('title', __('compound.grows.title'))

{{-- FinHero Task 17: the student's own Money Market growing (App\Services\Finhero\CompoundInterest\YourMoneyGrows), a chart and questions (finhero/_quiz). --}}
@section('content')
    @include('finhero._quiz')
@endsection
