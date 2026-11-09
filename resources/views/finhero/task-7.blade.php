@extends('layouts.profile')

@section('title', __('compound.early.title'))

{{-- FinHero Task 7: compound interest, a chart and questions (finhero/_quiz, App\Services\Finhero\QuizTasks). --}}
@section('content')
    @include('finhero._quiz')
@endsection
