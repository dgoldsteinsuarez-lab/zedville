<p>Hello <strong>{{ $user->nickname }}</strong>,</p>
<p>Please take <span style="color:#3c41fb">two quick steps</span> to authorize your salary deposits and automatic bill payments.</p>
<p><strong>Set Up Direct Deposit for Your Salary</strong></p>
<p>Authorize the city to deposit your monthly salary directly into your account—it's the fastest and most secure way to get paid.</p>
<p>You'll authorize:</p>
<ul>
    <li>Deposit 100% of your salary into your Universal Bank account</li>
</ul>
<br>
<a href="{{ route('bank.direct_deposite') }}" class="themeBtn mt-6 inline-block">Set Up Direct Deposit</a>