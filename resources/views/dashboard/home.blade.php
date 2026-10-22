@extends('layouts.profile')

@section('title', __('dashboard.title'))

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
                <h1 class="text-xl font-bold whitespace-nowrap">{{ __('dashboard.welcome', ['name' => $studentName]) }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('dashboard.citizen_of_zedville') }} · {{ $className }}</p>
            </div>
        </div>

        {{-- Enter Zedville banner + building quick links --}}
        <section aria-label="Enter the city">
            <div
                class="rounded-xl border border-[#B7E3D6] bg-[#EEF9F5] px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-[#014A38]">{{ __('dashboard.enter_zedville') }}</h2>
                    <p class="text-sm text-[#016950] mt-1">{{ __('dashboard.enter_zedville_sub') }}</p>
                </div>
                <a href="{{ url('education/city-hall') }}" class="themeBtn inline-flex items-center justify-center text-sm">
                    {{ __('dashboard.go_to_the_city') }}
                </a>
            </div>

            <nav aria-label="Building quick links" class="flex flex-wrap gap-2 mt-3">
                <a href="{{ url('education/city-mall') }}" class="whiteBtn zv-quicklink text-sm">🏬 {{ __('dashboard.city_mall') }}</a>
                <a href="{{ url('supermarket') }}" class="whiteBtn zv-quicklink text-sm">🛒 {{ __('dashboard.supermarket') }}</a>
                <a href="{{ route('bank.index') }}" class="whiteBtn zv-quicklink text-sm">🏦 {{ __('dashboard.bank') }}</a>
                <a href="{{ route('education.educational_finance_department') }}" class="whiteBtn zv-quicklink text-sm">🎓
                    {{ __('dashboard.education_finance_department') }}</a>
            </nav>
        </section>

        {{-- Row 1: Activities | My bank --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-4" aria-label="Tasks and money">

            {{-- Activities to do --}}
            <div class="zv-card px-5 py-4">
                <h2 class="zv-section-label mb-3">{{ __('dashboard.activities_to_do') }}</h2>
                @if(count($activities))
                    <ul class="divide-y divide-gray-100">
                        @foreach($activities as $a)
                            @php
                                $due = null;
                                if (!empty($a['date'])) {
                                    $d = \Carbon\Carbon::parse($a['date'])->startOfDay();
                                    $diff = now()->startOfDay()->diffInDays($d, false);
                                    if ($diff < 0) {
                                        $due = [__('dashboard.overdue'), 'text-red-600'];
                                    } elseif ($diff <= 3) {
                                        $due = [__('dashboard.due', ['day' => $d->format('D')]), 'text-red-600'];
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
                        <p class="text-sm font-medium text-gray-700">{{ __('dashboard.no_activities_right_now') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('dashboard.no_activities_hint') }}</p>
                    </div>
                @endif
            </div>

            {{-- My bank --}}
            <div class="zv-card px-5 py-4">
                <h2 class="zv-section-label mb-3">{{ __('dashboard.my_bank') }}</h2>

                @if(!empty($bank['has_account']))
                    <div class="flex gap-8">
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">{{ __('dashboard.balance') }}</p>
                            <p class="text-2xl font-bold">{{ number_format($bank['balance']) }} {{ __('dashboard.zeds') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">{{ __('dashboard.savings') }}</p>
                            <p class="text-2xl font-bold text-[#016950]">{{ number_format($bank['savings']) }} {{ __('dashboard.zeds') }}</p>
                        </div>
                    </div>

                    @if(!empty($bank['savings_goal']))
                        @php $pct = min(100, round(($bank['savings'] / $bank['savings_goal']) * 100)); @endphp
                        <div class="mt-3">
                            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-2 rounded-full bg-[#00A47D]" style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1.5">{{ __('dashboard.savings_goal_pct', ['pct' => $pct]) }}</p>
                        </div>
                    @endif

                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2">
                        <label for="zv-statement-month" class="sr-only">{{ __('dashboard.choose_a_month') }}</label>
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
                            {{ __('dashboard.view_statement') }}
                        </a>
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm font-medium text-gray-700">{{ __('dashboard.no_bank_account_yet') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('dashboard.no_bank_account_hint') }}</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Row 2: Badges | Mood --}}
        <section class="grid grid-cols-1 md:grid-cols-2 gap-4" aria-label="Badges and mood">

            {{-- My badges --}}
            <div class="zv-card px-5 py-4">
                <h2 class="zv-section-label mb-3">{{ __('dashboard.my_badges') }}</h2>
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
                        <p class="text-sm font-medium text-gray-700">{{ __('dashboard.no_badges_yet') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('dashboard.no_badges_hint') }}</p>
                    </div>
                @endif
            </div>

            {{-- Mood this month --}}
            <div class="zv-card px-5 py-4">
                <h2 class="zv-section-label mb-3">{{ __('dashboard.mood_this_month') }}</h2>
                @if($mood)
                    @php
                        $getMoodIcon = function($moodId, $size = 44) {
                            $id = strtolower(str_replace([' ', '-'], '_', trim($moodId ?? '')));
                            
                            $moodQuadrantMap = [
                                'furious' => 'HU', 'angry' => 'HU', 'nervous' => 'HU', 'worried' => 'HU',
                                'excited' => 'HC', 'ecstatic' => 'HC', 'happy' => 'HC', 'joyful' => 'HC',
                                'lonely' => 'LU', 'sad' => 'LU', 'hopeless' => 'LU', 'disappointed' => 'LU',
                                'calm' => 'LC', 'serene' => 'LC', 'at_ease' => 'LC', 'content' => 'LC',
                            ];
                            $q = $moodQuadrantMap[$id] ?? 'LC';
                            
                            $qsMap = [
                                'HU' => '#dc2626',
                                'HC' => '#d97706',
                                'LU' => '#2563eb',
                                'LC' => '#1aaa8e',
                            ];
                            $color = $qsMap[$q] ?? '#1aaa8e';
                            $s = 'stroke="' . $color . '"';
                            
                            if (in_array($id, ['furious', 'angry'])) {
                                $eyes = '<line x1="20" y1="21" x2="26" y2="24" ' . $s . ' stroke-width="2" stroke-linecap="round"/><line x1="34" y1="24" x2="40" y2="21" ' . $s . ' stroke-width="2" stroke-linecap="round"/><circle cx="23" cy="25" r="2" fill="' . $color . '"/><circle cx="37" cy="25" r="2" fill="' . $color . '"/>';
                            } elseif (in_array($id, ['ecstatic', 'excited'])) {
                                $eyes = '<path d="M21 26 Q23 23 25 26" ' . $s . ' stroke-width="2" fill="none" stroke-linecap="round"/><path d="M35 26 Q37 23 39 26" ' . $s . ' stroke-width="2" fill="none" stroke-linecap="round"/>';
                            } else {
                                $eyes = '<circle cx="23" cy="26" r="2" fill="' . $color . '"/><circle cx="37" cy="26" r="2" fill="' . $color . '"/>';
                            }

                            if (in_array($id, ['happy', 'ecstatic', 'excited', 'joyful', 'calm', 'serene'])) {
                                $mouth = '<path d="M21 35 Q30 43 39 35" ' . $s . ' stroke-width="2.2" fill="none" stroke-linecap="round"/>';
                            } elseif (in_array($id, ['furious', 'angry', 'hopeless', 'lonely'])) {
                                $mouth = '<path d="M21 39 Q30 33 39 39" ' . $s . ' stroke-width="2.2" fill="none" stroke-linecap="round"/>';
                            } elseif ($id === 'worried') {
                                $mouth = '<ellipse cx="30" cy="38" rx="5" ry="4" fill="none" ' . $s . ' stroke-width="2"/>';
                            } else {
                                $mouth = '<line x1="22" y1="38" x2="38" y2="38" ' . $s . ' stroke-width="2.2" stroke-linecap="round"/>';
                            }

                            return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 60 60"><circle cx="30" cy="30" r="22" fill="none" ' . $s . ' stroke-width="2.2"/>' . $eyes . $mouth . '</svg>';
                        };
                    @endphp
                    <div class="flex items-center">
                        <div class="flex-1 flex items-center gap-3">
                            {!! $getMoodIcon($mood['my_mood']['mood'] ?? $mood['my_mood']['label'], 44) !!}
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">{{ __('dashboard.my_mood') }}</p>
                                <p class="text-xl font-bold">{{ $mood['my_mood']['label'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $mood['my_mood']['detail'] }}</p>
                            </div>
                        </div>
                        <div class="flex-1 border-l border-gray-100 pl-6 flex items-center gap-3">
                            {!! $getMoodIcon($mood['city_mood']['mood'] ?? $mood['city_mood']['label'], 44) !!}
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">{{ __('dashboard.city_mood') }}</p>
                                <p class="text-xl font-bold">{{ $mood['city_mood']['label'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $mood['city_mood']['detail'] }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm font-medium text-gray-700">{{ __('dashboard.no_mood_logged') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('dashboard.no_mood_hint') }}</p>
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
                        {{ trans_choice('dashboard.mailbox_new_messages', $mailbox['unread'], ['count' => $mailbox['unread']]) }}@if(!empty($mailbox['latest'])) —
                        {{ $mailbox['latest'] }}@endif
                    @else
                        {{ __('dashboard.mailbox_no_new') }}
                    @endif
                </p>
                <a href="{{ route('profile.mailbox') }}" class="secondaryBtn text-sm">{{ __('dashboard.open') }}</a>
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