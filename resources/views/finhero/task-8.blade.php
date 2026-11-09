@extends('layouts.profile')

@section('title', __('compound.rule72.title'))

{{-- FinHero Task 8: compound interest, a chart and questions (finhero/_quiz, App\Services\Finhero\QuizTasks). --}}
@section('content')
    @include('finhero._quiz')
@endsection
