{{-- A fake email from the "Education Finance Department" (App\Services\FraudService): the verify script. --}}
@include('mailbox_templates.partials.fraud-email', ['script' => 'verify'])
