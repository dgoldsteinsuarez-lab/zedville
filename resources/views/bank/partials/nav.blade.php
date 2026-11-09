{{-- The bank's pages (bank review, Daniel 2026-10-29: one way through the bank
     instead of six tabs, a second tab bar, Quick Actions and buttons to the same
     places): Overview, Pay & transfer, Statements, Card & details, and Help.
     Shows what a form couldn't accept - bank forms were silently sent back before. --}}
@include('bank.partials.styles')
<div class="zb-top">
    <h1 class="zb-h1">{{ __('messages.nav_bank_account') }}</h1>
    <nav class="zb-tabs" aria-label="{{ __('messages.nav_bank_account') }}">
        @php
            $zbTabs = ['bank.index' => 'bank_ui.tab_overview', 'bank.transfer' => 'bank_ui.tab_pay'];
            // from the Intermediate, the personal loan and the credit card (App\Services\LoanService, CreditCardService)
            if (\App\Services\LoanService::offered(auth()->user())) {
                $zbTabs['bank.loans'] = 'bank_ui.tab_loans';
                $zbTabs['bank.card'] = 'bank_ui.tab_card';
            }
            $zbTabs += ['bank.bank_statement_show' => 'bank_ui.tab_statements', 'bank.my_account' => 'bank_ui.tab_details'];
        @endphp
        @foreach ($zbTabs as $route => $label)
            <a href="{{ route($route) }}" class="zb-tab{{ request()->routeIs($route) ? ' is-on' : '' }}" @if (request()->routeIs($route)) aria-current="page" @endif>{{ __($label) }}</a>
        @endforeach
        <a href="{{ route('bank.help') }}" class="zb-tab zb-tab--help{{ request()->routeIs('bank.help') ? ' is-on' : '' }}">
            <span aria-hidden="true">?</span> {{ __('bank_ui.help') }}
        </a>
    </nav>
</div>
@if ($errors->any())
    <div class="zb-errors" role="alert">
        <strong>{{ __('bank_ui.errors_title') }}</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
