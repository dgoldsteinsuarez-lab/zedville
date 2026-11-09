@extends('layouts.profile')

@section('title', __('inflation.shift.title'))

{{-- FinHero Task 14: the macro chart behind the price rise - supply and demand, the student moves the line
     (finhero/_quiz, App\Services\Finhero\Inflation\MoveTheLine). --}}
@section('content')
    @include('finhero._quiz')
@endsection
