{{-- Rendered in the reader's language when the message is opened (App\Models\Mailbox). --}}
@php $bankAccount = $bankAccount ?? \App\Models\BankAccount::where('student_id', $user->id)->first(); @endphp
<p><strong>{{ __('mail.dear', ['name' => $user->nickname ?: $user->name]) }}</strong></p>
<p>{{ __('mail.salary-deposite.text') }}</p>
<p><strong>{{ __('mail.salary-deposite.amount') }}</strong> Ƶ{{ number_format((float) optional($bankAccount)->primary_savings_account_amount, 2) }}</p>
<br><br>{{ __('mail.regards') }}
<br>{{ __('mail.treasury') }}
