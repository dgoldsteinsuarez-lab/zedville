@extends('layouts.profile')

@section('title', __('bank_ui.tab_pay'))

{{-- Pay & transfer (Daniel, 2026-10-29: students transfer money to a friend
     "like in real life, adding the bank account number and so on"): three
     choices - to a friend (their account number or IBAN and their name),
     between the student's own accounts, a bill, and since 2026-11-08 the
     monthly donation to a charity (bank.partials.donation) - then a check step that says in
     words what will happen, then Send - no card PIN, like online banking (Daniel,
     2026-10-29: "better no pin for transfer"). Money goes to classmates only.
     Later dates and standing orders for the older students ($advanced,
     App\Support\BankLevel). While a "transfer money to a friend" activity runs,
     the reference is required: the activity needs one. --}}
@section('content')
@php
    $billerData = $billers->mapWithKeys(fn ($b) => [$b->id => ['name' => $b->name, 'label' => txn_text($b->name), 'number' => $b->account_number, 'amount' => (float) $b->amount]]);
    $fromDefault = old('from_account', $accounts['current']['from']);
    $memoNeeded = $easyActivities->contains(fn ($activity) => !$activity->done);
    $date = fn ($d) => $d->copy()->locale(app()->getLocale())->isoFormat('LL');
    $texts = [
        'send' => __('bank_ui.summary_send', ['amount' => '{amount}', 'name' => '{name}']),
        'move' => __('bank_ui.summary_move', ['amount' => '{amount}', 'account' => '{name}']),
        'pay' => __('bank_ui.summary_pay', ['amount' => '{amount}', 'name' => '{name}']),
        'left' => __('bank_ui.summary_left', ['amount' => '{amount}']),
        'later' => __('bank_ui.summary_later', ['date' => '{date}']),
        'reference' => __('bank_ui.summary_reference', ['memo' => '{memo}']),
    ];
@endphp
<div class="zb-page" x-data="zbPay()">
    @include('bank.partials.nav')

    <h2 class="zb-h2">{{ __('bank_ui.pay_title') }}</h2>
    <div class="zb-choices" role="list">
        @foreach (['friend' => 'choice_friend', 'own' => 'choice_own', 'bill' => 'choice_bill', 'donation' => 'choice_donation'] as $key => $label)
            <a role="listitem" href="{{ route('bank.transfer', ['to' => $key]) }}" class="zb-choice{{ $to === $key ? ' is-on' : '' }}" @if ($to === $key) aria-current="true" @endif>
                <b>{{ __('bank_ui.' . $label) }}</b>
                <span>{{ __('bank_ui.' . $label . '_hint') }}</span>
            </a>
        @endforeach
    </div>

    @include('bank.partials.easy-activities')

    @if ($to === 'donation')
        @include('bank.partials.donation')
    @else
    <div class="zb-grid" style="grid-template-columns: minmax(0, 1fr) {{ $to === 'friend' ? 'minmax(260px, 340px)' : '' }}; align-items:start">
        <form x-ref="form" method="POST" class="zb-card"
              action="{{ $to === 'bill' ? route('bank.store_pay_bill') : route('bank.transfer_store') }}" @submit="busy = true">
            @csrf
            <input type="hidden" name="to" value="{{ $to }}">

            <div x-show="step === 'form'">
                @if ($to !== 'bill')
                    <div class="zb-field">
                        <label for="zb-from">{{ __('bank_ui.from') }}</label>
                        <select id="zb-from" name="from_account" class="form-control" x-model="from" required>
                            @foreach ($accounts as $key => $account)
                                <option value="{{ $account['from'] }}">{{ $account['name'] }} — {{ zeds($account['balance']) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($to === 'friend')
                    <div class="zb-row2">
                        <div class="zb-field">
                            <label for="zb-name">{{ __('bank_ui.friend_name') }}</label>
                            <input id="zb-name" name="beneficiary_name" class="form-control" x-model="name" required maxlength="120" autocomplete="off">
                        </div>
                        <div class="zb-field">
                            <label for="zb-number">{{ __('bank_ui.friend_number') }}</label>
                            <input id="zb-number" name="account_number" class="form-control" x-model="number" required maxlength="40" autocomplete="off" inputmode="text">
                        </div>
                    </div>
                    <p class="zb-help" style="margin:-8px 0 14px">{{ __('bank_ui.friend_where') }} {{ __('bank_ui.friend_class_only') }}</p>
                @elseif ($to === 'own')
                    <div class="zb-field">
                        <label for="zb-own">{{ __('bank_ui.to') }}</label>
                        <select id="zb-own" name="own_to" class="form-control" x-model="ownTo" required>
                            <option value="">{{ __('bank_ui.choose') }}</option>
                            @foreach ($accounts as $key => $account)
                                <option value="{{ $key }}" x-bind:disabled="fromKey() === '{{ $key }}'">{{ $account['name'] }}</option>
                            @endforeach
                        </select>
                        @if (count($accounts) < 2)
                            <p class="zb-warn">{{ __('flash.no_active_account') }}</p>
                        @endif
                        @if ($mmLock)
                            <p class="zb-warn" x-show="ownTo === 'money_market'" x-cloak>{{ __('flash.' . $mmLock) }}</p>
                        @endif
                    </div>
                @else
                    <div class="zb-field">
                        <label for="zb-biller">{{ __('bank_ui.biller') }}</label>
                        <select id="zb-biller" class="form-control" x-model="biller" @change="pickBiller()" required>
                            <option value="">{{ __('bank_ui.choose') }}</option>
                            @foreach ($billerData as $id => $biller)
                                <option value="{{ $id }}">{{ $biller['label'] }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="beneficiary_name" x-model="name">
                        <input type="hidden" name="account_number" x-model="number">
                        <p class="zb-help" x-show="number" x-cloak>{{ __('bank_ui.account_number') }}: <span class="zb-num" x-text="number"></span></p>
                    </div>
                @endif

                <div class="zb-row2">
                    <div class="zb-field">
                        <label for="zb-amount">{{ __('bank_ui.amount') }} (Ƶ)</label>
                        <input id="zb-amount" name="amount" type="number" step="0.01" min="0.01" class="form-control" x-model="amount" required>
                        <p class="zb-help" x-text="availableText()"></p>
                    </div>
                    @if ($to === 'friend')
                        <div class="zb-field">
                            <label for="zb-memo">{{ $memoNeeded ? __('bank_ui.reference_needed') : __('bank_ui.reference') }}</label>
                            <input id="zb-memo" name="memo" class="form-control" maxlength="140" x-model="memo" placeholder="{{ __('bank_ui.reference_ph') }}" @required($memoNeeded)>
                            @if ($memoNeeded)
                                <p class="zb-help">{{ __('bank_ui.reference_needed_help') }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($advanced)
                    <div class="zb-field">
                        <span class="zb-label">{{ __('bank_ui.when') }}</span>
                        <div class="zb-seg">
                            <label><input type="radio" name="type" value="now" x-model="type"> {{ __('bank_ui.when_now') }}</label>
                            <label><input type="radio" name="type" value="later" x-model="type"> {{ __('bank_ui.when_later') }}</label>
                            <label><input type="radio" name="type" value="recurring" x-model="type"> {{ __('bank_ui.when_recurring') }}</label>
                        </div>
                    </div>
                    <div class="zb-row2" x-show="type === 'later'" x-cloak>
                        <div class="zb-field">
                            <label for="zb-paydate">{{ __('bank_ui.date') }}</label>
                            <input id="zb-paydate" type="date" name="paydate" class="form-control" x-model="paydate" min="{{ now()->toDateString() }}" x-bind:required="type === 'later'">
                        </div>
                    </div>
                    <div class="zb-row2" x-show="type === 'recurring'" x-cloak>
                        <div class="zb-field">
                            <label for="zb-frequency">{{ __('bank_ui.how_often') }}</label>
                            <select id="zb-frequency" name="frequency" class="form-control" x-model="frequency">
                                <option value="weekly">{{ __('bank_ui.freq_weekly') }}</option>
                                <option value="monthly">{{ __('bank_ui.freq_monthly') }}</option>
                                <option value="yearly">{{ __('bank_ui.freq_yearly') }}</option>
                            </select>
                        </div>
                        <div class="zb-field">
                            <label for="zb-start">{{ __('bank_ui.first_date') }}</label>
                            <input id="zb-start" type="date" name="start_date" class="form-control" x-model="start" min="{{ now()->toDateString() }}" x-bind:required="type === 'recurring'">
                        </div>
                        <div class="zb-field">
                            <label for="zb-end">{{ __('bank_ui.last_date') }}</label>
                            <input id="zb-end" type="date" name="end_date" class="form-control" x-model="end" x-bind:min="start || '{{ now()->toDateString() }}'" x-bind:required="type === 'recurring'">
                        </div>
                    </div>
                @else
                    <input type="hidden" name="type" value="now">
                @endif

                @if ($to === 'friend')
                    <label class="zb-field" style="display:flex;gap:8px;align-items:center;font-weight:600">
                        <input type="checkbox" name="saveAsBeneficiary" value="1" style="accent-color:#00A47D;width:18px;height:18px" @checked(old('saveAsBeneficiary'))>
                        {{ __('bank_ui.save_friend') }}
                    </label>
                @endif

                <button type="button" class="themeBtn" @click="check()">{{ __('bank_ui.review') }} →</button>
            </div>

            <div x-show="step === 'check'" x-cloak>
                <div class="zb-check">
                    <p x-text="summary()"></p>
                    <p class="zb-small" x-text="leftText()"></p>
                    <p class="zb-small" x-show="memo.trim()" x-text="texts.reference.replace('{memo}', memo.trim())"></p>
                    <p class="zb-small" x-show="type !== 'now'" x-text="laterText()"></p>
                    <p class="zb-warn" x-show="type === 'now' && left() < 0">{{ __('bank_ui.too_much') }}</p>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <button type="button" class="whiteBtn" @click="step = 'form'">← {{ __('bank_ui.change') }}</button>
                    <button type="submit" class="themeBtn" x-ref="confirm" x-bind:disabled="busy || (type === 'now' && left() < 0)">
                        {{ $to === 'bill' ? __('bank_ui.pay') : __('bank_ui.send') }}
                    </button>
                </div>
            </div>
        </form>

        @if ($to === 'friend')
            <aside class="zb-card" aria-labelledby="zb-saved">
                <h2 id="zb-saved">{{ __('bank_ui.saved') }}</h2>
                <div class="zb-list">
                    @forelse ($beneficiaries as $beneficiary)
                        <div class="zb-row">
                            <div class="zb-row-main">
                                <div class="zb-row-title">{{ $beneficiary->name }}</div>
                                <div class="zb-num">{{ $beneficiary->account_number }}</div>
                            </div>
                            <button type="button" class="zb-copy" @click="useFriend(@js($beneficiary->name), @js($beneficiary->account_number))">{{ __('bank_ui.use') }}</button>
                            <form method="POST" action="{{ route('bank.beneficiary_delete', $beneficiary) }}">
                                @csrf
                                <button type="submit" class="zb-copy" style="border-color:#F3B9BC;color:#8E1F25" aria-label="{{ __('bank_ui.remove') }} {{ $beneficiary->name }}">✕</button>
                            </form>
                        </div>
                    @empty
                        <p class="zb-muted">{{ __('bank_ui.saved_empty') }}</p>
                    @endforelse
                </div>
            </aside>
        @endif
    </div>
    @endif

    @if ($orders->isNotEmpty())
        <section class="zb-card" style="margin-top:16px" aria-labelledby="zb-orders">
            <h2 id="zb-orders">{{ __('bank_ui.orders_title') }}</h2>
            <div class="zb-list">
                @foreach ($orders as $order)
                    <div class="zb-row">
                        <span class="zb-tick is-due" aria-hidden="true">{{ $order->type === 'recurring' ? '↻' : '⏱' }}</span>
                        <div class="zb-row-main">
                            <div class="zb-row-title">{{ $order->targetLabel($bankAccount) }}</div>
                            <div class="zb-row-sub">
                                @if ($order->type === 'recurring')
                                    {{ __('bank_ui.freq_' . $order->frequency) }} ·
                                @endif
                                {{ __('bank_ui.next_run', ['date' => $date($order->next_run_at)]) }}
                                @if ($order->failure === 'insufficient_balance')
                                    · {{ __('bank_ui.last_failed') }}
                                @endif
                            </div>
                        </div>
                        <span class="zb-row-amount">{{ zeds(-$order->amount) }}</span>
                        @if ($order->kind === \App\Services\DonationService::KIND)
                            {{-- the monthly donation can't be cancelled, only moved to another charity --}}
                            <a href="{{ route('bank.transfer', ['to' => 'donation']) }}" class="zb-copy" title="{{ __('bank_ui.donation_lock') }}"><span aria-hidden="true">🔒</span> {{ __('bank_ui.donation_change') }}</a>
                        @else
                            <form method="POST" action="{{ route('bank.transfer_cancel', $order) }}">
                                @csrf
                                <button type="submit" class="zb-copy">{{ __('bank_ui.cancel_order') }}</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

<script>
    function zbPay() {
        const accounts = @js($accounts);
        const billers = @js($billerData);
        const money = @js(\App\Support\Money::style());
        const texts = @js($texts);
        const locale = @js(app()->getLocale());
        const ownNames = @js(collect($accounts)->map(fn ($a) => $a['name']));
        return {
            step: 'form', busy: false,
            to: @js($to),
            from: @js($fromDefault),
            ownTo: @js(old('own_to', '')),
            name: @js(old('beneficiary_name', '')),
            number: @js(old('account_number', '')),
            amount: @js(old('amount', '')),
            memo: @js(old('memo', '')),
            type: @js(old('type', 'now')),
            paydate: @js(old('paydate', '')),
            start: @js(old('start_date', '')),
            end: @js(old('end_date', '')),
            frequency: @js(old('frequency', 'monthly')),
            biller: '',
            texts,
            fmt(n) {
                const v = Math.round((Number(n) || 0) * 100) / 100;
                const [whole, cents] = Math.abs(v).toFixed(2).split('.');
                const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, money.thou);
                const s = grouped + money.dec + cents;
                return (v < 0 ? '−' : '') + (money.before ? 'Ƶ ' + s : s + ' Ƶ');
            },
            fromKey() {
                if (this.to === 'bill') return 'current';
                return Object.keys(accounts).find((k) => accounts[k].from === this.from) || 'current';
            },
            available() { return accounts[this.fromKey()] ? accounts[this.fromKey()].balance : 0; },
            left() { return this.available() - (Number(this.amount) || 0); },
            availableText() { return @js(__('bank_ui.available', ['amount' => '{amount}'])).replace('{amount}', this.fmt(this.available())); },
            leftText() { return texts.left.replace('{amount}', this.fmt(this.left())); },
            laterText() {
                const d = this.type === 'later' ? this.paydate : this.start;
                if (!d) return '';
                const date = new Date(d + 'T12:00:00').toLocaleDateString(locale, { day: 'numeric', month: 'long', year: 'numeric' });
                return texts.later.replace('{date}', date);
            },
            summary() {
                const amount = this.fmt(this.amount);
                if (this.to === 'own') return texts.move.replace('{amount}', amount).replace('{name}', ownNames[this.ownTo] || '');
                if (this.to === 'bill') return texts.pay.replace('{amount}', amount).replace('{name}', (billers[this.biller] || {}).label || this.name);
                return texts.send.replace('{amount}', amount).replace('{name}', this.name.trim());
            },
            pickBiller() {
                const b = billers[this.biller];
                if (!b) { this.name = ''; this.number = ''; return; }
                this.name = b.name; this.number = b.number; this.amount = b.amount;
            },
            useFriend(name, number) {
                this.name = name; this.number = number; this.step = 'form';
                this.$nextTick(() => document.getElementById('zb-amount').focus());
            },
            check() {
                if (!this.$refs.form.reportValidity()) return;
                this.step = 'check';
                this.$nextTick(() => this.$refs.confirm.focus());
            },
        };
    }
</script>
@endsection
