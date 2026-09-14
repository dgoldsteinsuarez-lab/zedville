@extends('layouts.profile')

@section('title', 'Home')

@section('content')
    @push('styles')
        <style>
            /* Scoped to .zvHome so nothing here leaks into the rest of the app. */
            /* Match the rest of the app: full-width, left-aligned content
               (layout <main> already provides px-8 / py-10). Overrides the
               max-w-5xl / mx-auto utilities on the wrapper without a HTML change. */
            .zvHome {
                color: #222;
                max-width: none !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .zvHome .zv-card {
                background: #fff;
                border: 1px solid #D2DDDB;
                border-radius: 12px;
            }

            .zvHome .zv-quicklink {
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .zvHome .zv-section-label {
                font-size: 12px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #5C5C5C;
                font-family: 'Manrope', sans-serif;
            }
        </style>
    @endpush

    <div class="zvHome max-w-5xl mx-auto space-y-4">

        {{-- Header --}}
        <div class="flex flex-col items-start justify-between pb-6 space-y-2 lg:flex-row lg:items-baseline lg:space-y-0">
            <div>
                <h1 class="text-xl font-bold whitespace-nowrap">Welcome, {{ $studentName }}</h1>
                <p class="text-sm text-gray-500 mt-1">Citizen of Zedville · {{ $className }}</p>
            </div>
        </div>

        {{-- Enter Zedville banner + building quick links --}}
        <section aria-label="Enter the city">
            <div
                class="rounded-xl border border-[#B7E3D6] bg-[#EEF9F5] px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-[#014A38]">Enter Zedville</h2>
                    <p class="text-sm text-[#016950] mt-1">Explore the city and visit its buildings</p>
                </div>
                <a href="{{ url('education/city-hall') }}" class="themeBtn inline-flex items-center justify-center text-sm">
                    Go to the city
                </a>
            </div>

            <nav aria-label="Building quick links" class="flex flex-wrap gap-2 mt-3">
                <a href="{{ url('education/city-mall') }}" class="whiteBtn zv-quicklink text-sm">🏬 City Mall</a>
                <a href="{{ url('supermarket') }}" class="whiteBtn zv-quicklink text-sm">🛒 Supermarket</a>
                <a href="{{ route('bank.index') }}" class="whiteBtn zv-quicklink text-sm">🏦 Bank</a>
                <a href="{{ route('education.educational_finance_department') }}" class="whiteBtn zv-quicklink text-sm">🎓
                    Education Finance Department</a>
            </nav>
        </section>

        {{-- Row 1: Activities | My bank --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-4" aria-label="Tasks and money">

            {{-- Activities to do --}}
            <div class="zv-card px-5 py-4">
                <h2 class="zv-section-label mb-3">Activities to do</h2>
                @if(count($activities))
                    <ul class="divide-y divide-gray-100">
                        @foreach($activities as $a)
                            @php
                                $due = null;
                                if (!empty($a['date'])) {
                                    $d = \Carbon\Carbon::parse($a['date'])->startOfDay();
                                    $diff = now()->startOfDay()->diffInDays($d, false);
                                    if ($diff < 0) {
                                        $due = ['Overdue', 'text-red-600'];
                                    } elseif ($diff <= 3) {
                                        $due = ['Due ' . $d->format('D'), 'text-red-600'];
                                    } else {
                                        $due = [$d->format('M j'), 'text-gray-400'];
                                    }
                                }
                            @endphp
                            <li class="flex items-center justify-between py-2 gap-3">
                                <span class="text-sm">{{ $a['title'] }}</span>
                                @if($due)
                                    <span class="text-xs font-medium whitespace-nowrap {{ $due[1] }}">{{ $due[0] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm font-medium text-gray-700">No activities right now</p>
                        <p class="text-sm text-gray-500 mt-1">When your tutor assigns a new activity, it will appear here.</p>
                    </div>
                @endif
            </div>

            {{-- My bank --}}
            <div class="zv-card px-5 py-4">
                <h2 class="zv-section-label mb-3">My bank</h2>

                @if(!empty($bank['has_account']))
                    <div class="flex gap-8">
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Balance</p>
                            <p class="text-2xl font-bold">{{ number_format($bank['balance']) }} zeds</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Savings</p>
                            <p class="text-2xl font-bold text-[#016950]">{{ number_format($bank['savings']) }} zeds</p>
                        </div>
                    </div>

                    @if(!empty($bank['savings_goal']))
                        @php $pct = min(100, round(($bank['savings'] / $bank['savings_goal']) * 100)); @endphp
                        <div class="mt-3">
                            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-2 rounded-full bg-[#00A47D]" style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5">{{ $pct }}% of monthly savings goal</p>
                        </div>
                    @endif

                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2">
                        <label for="zv-statement-month" class="sr-only">Choose a month</label>
                        <select id="zv-statement-month"
                            class="flex-1 rounded-lg border border-[#D2DDDB] text-sm px-3 py-2 bg-white">
                            @foreach($months as $m)
                                @php
                                    $fromDate = \Carbon\Carbon::parse($m['value'])->startOfMonth()->toDateString();
                                    $toDate = \Carbon\Carbon::parse($m['value'])->endOfMonth()->toDateString();
                                @endphp
                                <option value="{{ $m['value'] }}" data-from="{{ $fromDate }}" data-to="{{ $toDate }}">{{ $m['label'] }}</option>
                            @endforeach
                        </select>
                        <a id="zv-view-statement" href="{{ route('bank.bank_statement_show') }}"
                            class="themeBtn text-sm whitespace-nowrap">
                            View statement
                        </a>
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm font-medium text-gray-700">No bank account yet</p>
                        <p class="text-sm text-gray-500 mt-1">Complete your Citizen Activation to open your account.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Row 2: Badges | Mood --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-4" aria-label="Badges and mood">

            {{-- My badges --}}
            <div class="zv-card px-5 py-4">
                <h2 class="zv-section-label mb-3">My badges</h2>
                @if(count($badges))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($badges as $b)
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg {{ $b['type'] === 'finhero' ? 'bg-amber-100' : 'bg-[#EEF9F5]' }}"
                                    aria-hidden="true">{{ $b['icon'] }}</div>
                                <div>
                                    <p class="text-sm font-semibold">{{ $b['name'] }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $b['message'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm font-medium text-gray-700">No badges yet</p>
                        <p class="text-sm text-gray-500 mt-1">Your story is just beginning. Step into the city and make your mark.</p>
                    </div>
                @endif
            </div>

            {{-- Mood this month --}}
            <div class="zv-card px-5 py-4">
                <h2 class="zv-section-label mb-3">Mood this month</h2>
                @if($mood)
                    <div class="flex">
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-0.5">My mood</p>
                            <p class="text-xl font-bold">{{ $mood['my_mood']['label'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $mood['my_mood']['detail'] }}</p>
                        </div>
                        <div class="flex-1 border-l border-gray-100 pl-6">
                            <p class="text-xs text-gray-500 mb-0.5">City mood</p>
                            <p class="text-xl font-bold">{{ $mood['city_mood']['label'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $mood['city_mood']['detail'] }}</p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm font-medium text-gray-700">No mood logged this month</p>
                        <p class="text-sm text-gray-500 mt-1">Log your mood when you enter Zedville to see your monthly average.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Mailbox strip --}}
        <section aria-label="Mailbox">
            <div class="rounded-xl bg-[#FFF9E9] border border-[#FFE48D] px-4 py-3 flex items-center gap-3">
                <span aria-hidden="true">✉️</span>
                <p class="flex-1 text-sm text-[#7A5B12]">
                    @if(!empty($mailbox['unread']))
                        Mailbox · {{ $mailbox['unread'] }} new
                        message{{ $mailbox['unread'] > 1 ? 's' : '' }}@if(!empty($mailbox['latest'])) —
                        {{ $mailbox['latest'] }}@endif
                    @else
                        Mailbox · no new messages
                    @endif
                </p>
                <a href="{{ route('profile.mailbox') }}" class="secondaryBtn text-sm">Open</a>
            </div>
        </section>

    </div>

    <script>
        (function () {
            var sel = document.getElementById('zv-statement-month');
            var btn = document.getElementById('zv-view-statement');
            if (!sel || !btn) return;
            var base = btn.getAttribute('href');
            function sync() {
                var opt = sel.options[sel.selectedIndex];
                var fromDate = opt ? opt.getAttribute('data-from') : '';
                var toDate = opt ? opt.getAttribute('data-to') : '';

                if (!fromDate || !toDate) {
                    var parts = sel.value.split('-');
                    if (parts.length === 2) {
                        var y = parseInt(parts[0], 10);
                        var m = parseInt(parts[1], 10);
                        var lastDay = new Date(y, m, 0).getDate();
                        var mm = m < 10 ? '0' + m : m;
                        var dd = lastDay < 10 ? '0' + lastDay : lastDay;
                        fromDate = y + '-' + mm + '-01';
                        toDate = y + '-' + mm + '-' + dd;
                    }
                }

                var glue = base.indexOf('?') === -1 ? '?' : '&';
                btn.setAttribute('href', base + glue + 'from_date=' + encodeURIComponent(fromDate) + '&to_date=' + encodeURIComponent(toDate));
            }
            sync();
            sel.addEventListener('change', sync);
        })();
    </script>
@endsection