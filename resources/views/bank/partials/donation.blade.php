{{-- The monthly donation (App\Services\DonationService; Daniel, 2026-11-08: "tienen que aprender a
     hacerlo en el banco"): first the charity is saved as a beneficiary with its name and account
     number, found in the Donation Center; then a standing order of 4-6% of the salary, every month
     from the first date to the end of the school year - checked in words before it's set. It can't
     be cancelled, only moved to another saved charity. The next school year it's set up again, last
     year's charity and amount filled in to keep or change. --}}
@php
    [$donMin, $donMax] = $donationRange;
    $charityName = fn ($b) => ct('activities', optional(\App\Services\DonationService::npo($b->account_number))->name ?? $b->name);
    $digits = fn ($n) => preg_replace('/\D/', '', (string) $n);
    $others = $donation ? $charities->reject(fn ($c) => $digits($c->account_number) === $digits($donation->account_number))->values() : collect();
    $adding = old('for') === 'donation';
    $yearEnd = $date(\App\Services\DonationService::yearEnd(now()));
    $lastCharity = $lastDonation ? $charities->first(fn ($c) => $digits($c->account_number) === $digits($lastDonation->account_number)) : null;
@endphp
<div class="zb-grid" style="grid-template-columns: minmax(0, 1fr) minmax(260px, 340px); align-items:start">
    @if ($donation)
        <section class="zb-card" aria-labelledby="zb-don">
            <h2 id="zb-don"><span aria-hidden="true">🔒</span> {{ __('bank_ui.donation_yours') }}</h2>
            <div class="zb-check">
                <p><b>{{ $donation->targetLabel($bankAccount) }}</b> · {{ zeds($donation->amount) }} · {{ __('bank_ui.freq_monthly') }}</p>
                <p class="zb-small">{{ __('bank_ui.next_run', ['date' => $date($donation->next_run_at)]) }}</p>
                @if ($donation->end_date)
                    <p class="zb-small">{{ __('bank_ui.donation_until', ['date' => $date($donation->end_date)]) }}</p>
                @endif
                @if ($donation->failure === 'insufficient_balance')
                    <p class="zb-warn">{{ __('bank_ui.last_failed') }}</p>
                @endif
            </div>
            <p class="zb-help">{{ __('bank_ui.donation_lock') }}</p>
            @if ($others->isNotEmpty())
                <form method="POST" action="{{ route('bank.donation_change') }}">
                    @csrf
                    <div class="zb-field">
                        <label for="zb-move">{{ __('bank_ui.donation_move_to') }}</label>
                        <select id="zb-move" name="beneficiary" class="form-control" required>
                            <option value="">{{ __('bank_ui.choose') }}</option>
                            @foreach ($others as $charity)
                                <option value="{{ $charity->id }}">{{ $charityName($charity) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="themeBtn">{{ __('bank_ui.donation_change') }}</button>
                </form>
            @else
                <p class="zb-muted">{{ __('bank_ui.donation_save_other') }}</p>
            @endif
        </section>
    @else
        <form x-ref="don" method="POST" class="zb-card" action="{{ route('bank.donation_store') }}" x-data="zbDonation()" @submit="busy = true">
            @csrf
            <div x-show="step === 'form'">
                @if ($lastDonation)
                    <p class="zb-check">{{ __('bank_ui.donation_last', ['name' => $lastDonation->targetLabel($bankAccount), 'amount' => zeds($lastDonation->amount)]) }}</p>
                @endif
                <div class="zb-field">
                    <label for="zb-charity">{{ __('bank_ui.donation_charity') }}</label>
                    <select id="zb-charity" name="beneficiary" class="form-control" x-model="charity" required>
                        <option value="">{{ __('bank_ui.choose') }}</option>
                        @foreach ($charities as $charity)
                            <option value="{{ $charity->id }}">{{ $charityName($charity) }} — {{ $charity->account_number }}</option>
                        @endforeach
                    </select>
                    @if ($charities->isEmpty())
                        <p class="zb-warn">{{ __('bank_ui.donation_save_first') }}</p>
                    @endif
                </div>
                <div class="zb-row2">
                    <div class="zb-field">
                        <label for="zb-don-amount">{{ __('bank_ui.amount') }} (Ƶ)</label>
                        <input id="zb-don-amount" name="amount" type="number" step="0.01" min="{{ $donMin }}" max="{{ $donMax }}" class="form-control" x-model="amount" required>
                        <p class="zb-help">{{ __('bank_ui.donation_range', ['min' => zeds($donMin), 'max' => zeds($donMax)]) }}</p>
                    </div>
                    <div class="zb-field">
                        <label for="zb-don-start">{{ __('bank_ui.first_date') }}</label>
                        <input id="zb-don-start" type="date" name="start_date" class="form-control" x-model="start" min="{{ now()->toDateString() }}" required>
                        <p class="zb-help">{{ __('bank_ui.donation_every', ['date' => $yearEnd]) }}</p>
                    </div>
                </div>
                <button type="button" class="themeBtn" @click="check()" @disabled($charities->isEmpty())>{{ __('bank_ui.review') }} →</button>
            </div>
            <div x-show="step === 'check'" x-cloak>
                <div class="zb-check">
                    <p x-text="summary()"></p>
                    <p class="zb-small" x-text="startText()"></p>
                    <p class="zb-small">{{ __('bank_ui.donation_lock') }}</p>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <button type="button" class="whiteBtn" @click="step = 'form'">← {{ __('bank_ui.change') }}</button>
                    <button type="submit" class="themeBtn" x-ref="confirm" x-bind:disabled="busy">{{ __('bank_ui.donation_set') }}</button>
                </div>
            </div>
        </form>
    @endif

    <aside class="zb-card" aria-labelledby="zb-charities">
        <h2 id="zb-charities">{{ __('bank_ui.donation_saved') }}</h2>
        <div class="zb-list">
            @forelse ($charities as $charity)
                <div class="zb-row">
                    <div class="zb-row-main">
                        <div class="zb-row-title">{{ $charityName($charity) }}</div>
                        <div class="zb-num">{{ $charity->account_number }}</div>
                    </div>
                    <form method="POST" action="{{ route('bank.beneficiary_delete', $charity) }}">
                        @csrf
                        <button type="submit" class="zb-copy" style="border-color:#F3B9BC;color:#8E1F25" aria-label="{{ __('bank_ui.remove') }} {{ $charityName($charity) }}">✕</button>
                    </form>
                </div>
            @empty
                <p class="zb-muted">{{ __('bank_ui.donation_saved_empty') }}</p>
            @endforelse
        </div>

        <h2 style="margin-top:18px">{{ __('bank_ui.donation_add') }}</h2>
        <form method="POST" action="{{ route('bank.beneficiary_store') }}">
            @csrf
            <input type="hidden" name="for" value="donation">
            <div class="zb-field">
                <label for="zb-ch-name">{{ __('bank_ui.donation_add_name') }}</label>
                <input id="zb-ch-name" name="name" class="form-control" required maxlength="120" autocomplete="off" value="{{ $adding ? old('name') : '' }}">
            </div>
            <div class="zb-field">
                <label for="zb-ch-number">{{ __('bank_ui.account_number') }}</label>
                <input id="zb-ch-number" name="account_number" class="form-control" required maxlength="40" autocomplete="off" value="{{ $adding ? old('account_number') : '' }}">
            </div>
            <p class="zb-help">{{ __('bank_ui.donation_where') }} <a href="{{ route('education.npos') }}">{{ __('bank_ui.donation_where_link') }}</a></p>
            <button type="submit" class="whiteBtn">{{ __('bank_ui.donation_add_button') }}</button>
        </form>
    </aside>
</div>

<script>
    function zbDonation() {
        const names = @js($charities->mapWithKeys(fn ($c) => [$c->id => $charityName($c)]));
        const locale = @js(app()->getLocale());
        const money = @js(\App\Support\Money::style());
        return {
            step: 'form', busy: false,
            fmt(n) {
                const v = Math.round((Number(n) || 0) * 100) / 100;
                const [whole, cents] = Math.abs(v).toFixed(2).split('.');
                const s = whole.replace(/\B(?=(\d{3})+(?!\d))/g, money.thou) + money.dec + cents;
                return money.before ? 'Ƶ ' + s : s + ' Ƶ';
            },
            // last school year's charity and amount, to keep or change
            charity: @js((string) old('beneficiary', optional($lastCharity)->id ?? '')),
            amount: @js(old('amount', $lastDonation ? (float) $lastDonation->amount : '')),
            start: @js(old('start_date', now()->toDateString())),
            summary() {
                return @js(__('bank_ui.summary_donate', ['amount' => '{amount}', 'name' => '{name}']))
                    .replace('{amount}', this.fmt(this.amount)).replace('{name}', names[this.charity] || '');
            },
            startText() {
                if (!this.start) return '';
                const date = new Date(this.start + 'T12:00:00').toLocaleDateString(locale, { day: 'numeric', month: 'long', year: 'numeric' });
                return @js(__('bank_ui.donation_first', ['date' => '{date}'])).replace('{date}', date);
            },
            check() {
                if (!this.$refs.don.reportValidity()) return;
                this.step = 'check';
                this.$nextTick(() => this.$refs.confirm.focus());
            },
        };
    }
</script>
