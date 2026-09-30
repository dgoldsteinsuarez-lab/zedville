@php
use App\Models\BankAccount;
$bankAccount = BankAccount::find($bank_account_id ?? null);
@endphp

<p>Great news! Your Universal Bank account is ready to use. </p>
<p>Visit the "Universal Bank" tab in your profile to access:</p>
<ul style="list-style: none;">
    <li> - Account summary & balance</li>
    <li> - Debit card details</li>
    <li> - Transaction history</li>
    <li> - Bill payments</li>
    <li> - Savings tools</li>
    <li> - Account alerts</li>
</ul>
<p>Your banking journey starts now!</p>
<p><strong>Banco Nacional Joven - Online Banking</strong></p>
<p><strong>Welcome, {{ $user->nickname }}</strong></p>
<p><strong>Account:</strong> Student</p>
<p><strong>Account Name:</strong></p>
<p><strong>Account Summary</strong></p>
<ul>
    <li><strong>Available Balance:</strong> ${{ $bankAccount->primary_savings_account_amount }}</li>
    <li><strong>Account number:</strong> XXXX-XXXX-XXXX-{{ substr($bankAccount->primary_savings_account_number, -4) }}</li>
    <li><strong>Debit Card:</strong>  XXXX-XXXX-XXXX-{{ substr($bankAccount->card_number, -4) }}</li>
    <li><strong>Latest Activity:</strong> N/A</li>
</ul>
<p><strong>Available Features</strong></p>
<p><strong>View Account Statement</strong></p>
<ul>
    <ol>Check the movement history and download it in PDF.</ol>
</ul>
<p><strong>Debit Card Management</strong></p>
<ul>
    <ol>Activate/Deactivate Card</ol>
    <ol>Change PIN</ol>
    <ol>Block Card in case of loss</ol>
</ul>
<p><strong>Transfers</strong></p>
<ul>
    <ol>Internal and external transfers</ol>
    <ol>Payment of services (electricity, water, telephone)</ol>
</ul>
<p><strong>Savings Tools</strong></p>
<ul>
    <ol>Set savings goals</ol>
    <ol>Set automatic reminders</ol>
</ul>
<p><strong>Notifications and Alerts</strong></p>
<ul>
    <ol>Set up balance notifications and activity alerts</ol>
</ul>
<p>Got questions? Visit our FAQ at <strong><a href="https://universalbank.com/faq" target="_blank">universalbank.com/faq</a></strong></p>
<p><strong>Thank you for being part of Universal Bank</strong></p>