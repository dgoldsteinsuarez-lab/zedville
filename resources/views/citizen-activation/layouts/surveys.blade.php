{{-- Citizen Activation step 5: the same Consumer Profile survey as in
     Settings (resources/views/surveys/wizard.blade.php). Its last screen
     continues to step 6 once the answers are saved. --}}
@include('surveys.wizard', ['context' => 'activation'])
