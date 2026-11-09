@extends('layouts.profile')

@section('title', __('inflation.basket.title'))

{{-- FinHero Task 10: the price rise, a chart and questions (finhero/_quiz, App\Services\Finhero\QuizTasks). --}}
@section('content')
    @include('finhero._quiz')
@endsection
