@extends('layouts.profile')

@section('title', __('bank_ui.st_title'))

{{-- One statement page (bank review, Daniel 2026-10-29: there were three - two
     always empty, the third with "Total Savings" showing the current account
     and the heading always this month): an account and a month, the opening
     balance, every line, the closing balance, and totals that add up. --}}
@section('content')
@php
    $loc = app()->getLocale();
    $monthLabel = fn ($m) => $m->copy()->locale($loc)->isoFormat(__('transactions.month_year'));
    $s = $statement;
    $groupLabels = ['needs' => 'm_needs', 'wants' => 'm_wants', 'savings' => 'm_savings', 'transfers' => 'g_transfers', 'bills' => 'g_bills', 'other' => 'g_other'];
@endphp
<div class="zb-page">
    @include('bank.partials.nav')

    <form method="GET" action="{{ route('bank.bank_statement_show') }}" class="zb-card zb-noprint" style="display:flex;flex-wrap:wrap;gap:14px;align-items:end;margin-bottom:16px">
        <div>
            <span class="zb-label">{{ __('bank_ui.st_account') }}</span>
            <div class="zb-seg">
                @foreach ($accounts as $account)
                    <label><input type="radio" name="account" value="{{ $account }}" @checked($key === $account) onchange="this.form.submit()"> {{ __('bank_ui.' . $account) }}</label>
                @endforeach
            </div>
        </div>
        <div class="zb-field" style="margin:0">
            <label for="zb-month-pick">{{ __('bank_ui.st_month') }}</label>
            <select id="zb-month-pick" name="month" class="form-control" onchange="this.form.submit()">
                @foreach ($months as $m)
                    <option value="{{ $m->format('Y-m') }}" @selected($m->format('Y-m') === $month->format('Y-m'))>{{ $monthLabel($m) }}</option>
                @endforeach
            </select>
        </div>
        <noscript><button type="submit" class="themeBtn">{{ __('bank_ui.show') }}</button></noscript>
        <button type="button" class="whiteBtn" style="margin-left:auto" onclick="window.print()">{{ __('bank_ui.print') }}</button>
    </form>

    <section class="zb-card">
        <h2>{{ __('bank_ui.st_title') }} · {{ __('bank_ui.' . $key) }} · {{ $monthLabel($month) }}</h2>
        <p class="zb-small" style="margin:-6px 0 12px">
            {{ $user->name }} · {{ __('bank_ui.account_number') }}
            {{ \App\Services\BankTransferService::numberOf($bankAccount, $key) }} · Universal Bank
        </p>

        <dl class="zb-sum">
            <div><dt>{{ __('bank_ui.st_opening') }}</dt><dd>{{ zeds($s['opening']) }}</dd></div>
            <div><dt>{{ __('bank_ui.st_in') }}</dt><dd class="zb-in">{{ zeds($s['in'], true) }}</dd></div>
            <div><dt>{{ __('bank_ui.st_out') }}</dt><dd>{{ zeds(-$s['out']) }}</dd></div>
            <div><dt>{{ __('bank_ui.st_closing') }}</dt><dd>{{ zeds($s['closing']) }}</dd></div>
        </dl>
        <p class="zb-small" style="margin:0 0 14px">{{ __('bank_ui.st_check') }}</p>

        @if (empty($s['lines']))
            <p class="zb-muted">{{ __('bank_ui.st_empty') }}</p>
        @else
            <div style="overflow-x:auto">
                <table class="zb-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('bank_ui.st_date') }}</th>
                            <th scope="col">{{ __('bank_ui.st_text') }}</th>
                            <th scope="col" class="num">{{ __('bank_ui.st_in') }}</th>
                            <th scope="col" class="num">{{ __('bank_ui.st_out') }}</th>
                            <th scope="col" class="num">{{ __('bank_ui.st_balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td><em>{{ __('bank_ui.st_opening') }}</em></td>
                            <td></td><td></td>
                            <td class="num">{{ zeds($s['opening']) }}</td>
                        </tr>
                        @foreach ($s['lines'] as $line)
                            <tr>
                                <td style="white-space:nowrap">{{ $line['date']->copy()->locale($loc)->isoFormat('L') }}</td>
                                <td>
                                    {{ $line['text'] }}
                                    @if ($line['memo'])
                                        <div class="zb-small">“{{ $line['memo'] }}”</div>
                                    @endif
                                </td>
                                <td class="num zb-in">{{ $line['in'] !== null ? zeds($line['in']) : '' }}</td>
                                <td class="num">{{ $line['out'] !== null ? zeds($line['out']) : '' }}</td>
                                <td class="num">{{ zeds($line['balance']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td>{{ __('bank_ui.st_closing') }}</td>
                            <td class="num zb-in">{{ zeds($s['in']) }}</td>
                            <td class="num">{{ zeds($s['out']) }}</td>
                            <td class="num">{{ zeds($s['closing']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </section>

    @if ($key === 'current' && !empty($s['groups']))
        <section class="zb-card" style="margin-top:16px">
            <h2>{{ __('bank_ui.st_where') }}</h2>
            <div class="zb-bar" aria-hidden="true">
                @foreach (['needs', 'wants', 'savings', 'transfers', 'bills', 'other'] as $group)
                    @if (($s['groups'][$group] ?? 0) > 0)
                        <span class="zb-c-{{ in_array($group, ['transfers', 'bills', 'other']) ? 'other' : $group }}" style="width: {{ round($s['groups'][$group] / max(0.01, $s['out']) * 100, 2) }}%{{ in_array($group, ['transfers', 'bills']) ? ';opacity:.7' : '' }}"></span>
                    @endif
                @endforeach
            </div>
            <dl class="zb-legend">
                @foreach ($groupLabels as $group => $label)
                    @if (($s['groups'][$group] ?? 0) > 0)
                        <dt><span class="zb-sw zb-c-{{ in_array($group, ['transfers', 'bills', 'other']) ? 'other' : $group }}"></span>{{ __('bank_ui.' . $label) }}</dt>
                        <dd>{{ zeds($s['groups'][$group]) }}</dd>
                    @endif
                @endforeach
                <dt class="is-total">{{ __('bank_ui.st_out') }}</dt><dd class="is-total">{{ zeds($s['out']) }}</dd>
            </dl>
        </section>
    @endif
</div>
@endsection
