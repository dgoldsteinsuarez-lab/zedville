@extends('layouts.profile')

@section('title', __('inflation.left.title'))

{{-- FinHero Task 12: the price rise, a chart and questions (finhero/_quiz, App\Services\Finhero\QuizTasks). --}}
@section('content')
    @include('finhero._quiz')
@endsection
