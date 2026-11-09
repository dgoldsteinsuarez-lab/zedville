@extends('layouts.profile')

@section('title', __('compound.snowball.title'))

{{-- FinHero Task 6: compound interest, a chart and questions (finhero/_quiz, App\Services\Finhero\QuizTasks). --}}
@section('content')
    @include('finhero._quiz')
@endsection
