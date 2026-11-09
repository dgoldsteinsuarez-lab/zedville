@extends('layouts.profile')

@section('title', __('inflation.salary.title'))

{{-- FinHero Task 11: the price rise, a chart and questions (finhero/_quiz, App\Services\Finhero\QuizTasks). --}}
@section('content')
    @include('finhero._quiz')
@endsection
