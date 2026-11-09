{{-- The Spending Activity's task: mission, budget, items, shop, delivery and rules
     (Daniel, 2026-10-28: a "Task" button on the shop page "to be able to review the
     task I need to do"). One card for the activity list and the shops, opened with
     zvOpenTask(). Expects $activity (an activities row); $shopLinks (shop name =>
     url) adds the "Start shopping" buttons, on the activity list only.
     An activity with a shopping list (App\Services\ShoppingList, Daniel
     2026-10-31) shows the list and its budget instead of a spending range. --}}
@php
    $taskList = !empty($activity->shopping_list) ? ($shoppingList ?? \App\Services\ShoppingList::state($activity, auth()->user())) : null;
    $salary = (float) config('zedville.monthly_salary', 3952.40);
    $taskMin = round($salary * 0.10);
    $taskMax = round($salary * 0.20);
    $taskItems = $activity->max_items
        ? __('spending.items_range', ['min' => $activity->min_items, 'max' => $activity->max_items])
        : __('spending.items_min', ['min' => $activity->min_items]);
    $taskSpecial = $activity->special && !$taskList ? ct('activities', $activity->special) : null;
    $taskDestination = method_exists($activity, 'text') ? $activity->text('destination') : ct('activities', $activity->destination);
    $taskShops = array_map('trim', explode(',', (string) $activity->shop));
@endphp
@php $taskId = $taskId ?? 'zvTask'; @endphp
<div class="zv-task-overlay" id="{{ $taskId }}" aria-hidden="true">
    <div class="zv-task" role="dialog" aria-modal="true" aria-labelledby="{{ $taskId }}Title">
        <div class="zv-task-head">
            <button type="button" class="zv-task-close" onclick="zvCloseTask('{{ $taskId }}')" aria-label="{{ __('shop.close') }}">✕</button>
            <div class="zv-task-label">{{ __('spending.kinds.' . ($activity->type ?? 'high')) }} · {{ __('spending.activity_n_of', ['n' => $activity->number, 'total' => \App\Models\Activity::where('type', $activity->type ?? 'high')->count()]) }}</div>
            <div class="zv-task-title" id="{{ $taskId }}Title">{{ method_exists($activity, 'text') ? $activity->text('title') : ct('activities', $activity->title) }}</div>
        </div>
        <div class="zv-task-body">
            <div class="zv-task-mission">
                <div class="zv-task-caption">{{ __('spending.your_mission') }}</div>
                <p>{{ method_exists($activity, 'text') ? $activity->text('mission') : ct('activities', $activity->mission) }}</p>
            </div>
            @if ($taskList)
                <div class="zv-task-list">
                    <div class="zv-task-caption">📝 {{ __('spending.list.your_list') }}</div>
                    <ul>
                        @foreach ($taskList['lines'] as $line)
                            @php $done = $line['done'] >= $line['qty']; @endphp
                            <li @class(['is-done' => $done])>
                                <span class="zv-task-tick">{{ $done ? '✓' : $line['done'] . '/' . $line['qty'] }}</span>
                                <span>{{ $line['label'] }} <em>({{ __('spending.list.buy_at', ['shop' => $line['shop']]) }})</em></span>
                            </li>
                        @endforeach
                    </ul>
                    <p>{{ $taskList['complete'] ? __('spending.list.already_complete') : __('spending.list.intro') }}</p>
                    @unless ($taskList['complete'])
                        <p>{{ __('spending.list.range', ['min' => \App\Support\Money::number($taskList['range']['min']), 'max' => \App\Support\Money::number($taskList['range']['max'])]) }}</p>
                    @endunless
                </div>
            @endif
            @if ($taskSpecial)
                <div class="zv-task-special">
                    <div class="zv-task-caption">{{ __('spending.special_requirements') }}</div>
                    <p>{{ $taskSpecial }}</p>
                </div>
            @endif
            <div class="zv-task-grid">
                @if ($taskList)
                    <div><div class="zv-task-caption">{{ __('spending.list.budget') }}</div><div class="zv-task-value is-orange">{{ zeds($taskList['budget']) }}</div></div>
                    <div><div class="zv-task-caption">&nbsp;</div><div class="zv-task-value is-teal">{{ __('spending.list.budget_left', ['left' => \App\Support\Money::number($taskList['left'])]) }}</div></div>
                @else
                <div><div class="zv-task-caption">{{ __('spending.budget_range') }}</div><div class="zv-task-value is-orange">Ƶ {{ $taskMin }}–{{ $taskMax }} (10–20%)</div></div>
                <div><div class="zv-task-caption">{{ __('spending.items_required') }}</div><div class="zv-task-value is-teal">{{ $taskItems }}</div></div>
                @endif
                <div><div class="zv-task-caption">{{ __('spending.shop_at') }}</div><div class="zv-task-value">{{ $activity->shop }}</div></div>
                <div><div class="zv-task-caption">{{ __('spending.delivery_to') }}</div><div class="zv-task-value is-blue">{{ $taskDestination }}</div></div>
            </div>
            @unless ($taskList)
            <div class="zv-task-caption">{{ __('spending.shopping_rules') }}</div>
            <ul class="zv-task-rules">
                <li>{!! __('spending.rule_spend', ['min' => $taskMin, 'max' => $taskMax]) !!}</li>
                <li>{!! __('spending.rule_buy', ['items' => e($taskItems)]) !!}</li>
                @if ($taskSpecial)
                    <li>{!! __('spending.rule_special', ['special' => e($taskSpecial)]) !!}</li>
                @endif
                <li>{!! __('spending.rule_shop', ['shop' => e($activity->shop)]) !!}</li>
                <li>{!! __('spending.rule_delivery', ['destination' => e($taskDestination)]) !!}</li>
                <li>{{ __('spending.rule_limits') }}</li>
            </ul>
            @endunless
            @foreach ($shopLinks ?? [] as $shop => $url)
                @if (in_array($shop, $taskShops, true))
                    <a href="{{ $url }}" class="zv-task-go">{{ __('spending.start_shopping', ['shop' => $shop]) }}</a>
                @endif
            @endforeach
        </div>
    </div>
</div>
@once
<style>
    .zv-task-overlay { position: fixed; inset: 0; z-index: 1000; display: none; align-items: center; justify-content: center;
        padding: 24px; background: rgba(10, 25, 20, .45); backdrop-filter: blur(4px); }
    .zv-task-overlay.is-open { display: flex; }
    .zv-task { width: 100%; max-width: 580px; max-height: 88vh; overflow-y: auto; background: #fff; border: 1px solid #e4eae7;
        border-radius: 20px; box-shadow: 0 24px 64px rgba(0, 0, 0, .13); color: #1a1a2e; font-size: 14px; line-height: 1.5; }
    .zv-task-head { position: relative; padding: 24px 24px 20px; border-bottom: 1px solid #e4eae7; }
    .zv-task-close { position: absolute; top: 18px; right: 18px; width: 32px; height: 32px; border-radius: 50%; border: 1.5px solid #e4eae7;
        background: #fff; color: #666; font-size: 13px; line-height: 1; cursor: pointer; transition: background .2s, color .2s, border-color .2s; }
    .zv-task-close:hover { background: #e03e3e; border-color: #e03e3e; color: #fff; }
    .zv-task-label { display: inline-block; margin-bottom: 10px; padding: 3px 10px; border-radius: 50px; background: #e8f7f3; color: #1aaa8e;
        font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .zv-task-title { padding-right: 40px; font-size: 20px; font-weight: 700; line-height: 1.25; }
    .zv-task-body { padding: 20px 24px 24px; }
    .zv-task-caption { margin-bottom: 6px; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #999; }
    .zv-task-mission, .zv-task-special { margin-bottom: 14px; padding: 14px 16px; border-radius: 10px; }
    .zv-task-mission { background: #e8f7f3; border: 1px solid #b6e8d8; }
    .zv-task-mission .zv-task-caption { color: #1aaa8e; }
    .zv-task-special { background: #eff4ff; border: 1px solid #c5d7f5; }
    .zv-task-special .zv-task-caption { color: #2a5aa0; }
    .zv-task-mission p, .zv-task-special p { margin: 0; font-size: 13px; line-height: 1.65; }
    .zv-task-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }
    .zv-task-grid > div { padding: 12px 14px; border-radius: 10px; background: #eef9f5; border: 1px solid #e4eae7; }
    .zv-task-value { font-size: 13px; font-weight: 600; }
    .zv-task-value.is-teal { color: #1aaa8e; }
    .zv-task-value.is-orange { color: #d97706; }
    .zv-task-value.is-blue { color: #2a5aa0; }
    .zv-task-rules { margin: 0 0 20px; padding: 0; list-style: none; }
    .zv-task-rules li { position: relative; padding: 9px 0 9px 30px; border-bottom: 1px solid #e4eae7; font-size: 12px; color: #666; line-height: 1.55; }
    .zv-task-rules li:last-child { border-bottom: none; }
    .zv-task-rules li::before { content: "✓"; position: absolute; left: 0; top: 9px; width: 20px; height: 20px; border-radius: 50%;
        background: #e8f7f3; color: #1aaa8e; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .zv-task-rules strong { color: #1a1a2e; font-weight: 600; }
    .zv-task-list { margin-bottom: 14px; padding: 14px 16px; border-radius: 10px; background: #fffaf0; border: 1px solid #f5d9a0; }
    .zv-task-list .zv-task-caption { color: #8a5a00; }
    .zv-task-list ul { margin: 0 0 8px; padding: 0; list-style: none; }
    .zv-task-list li { display: flex; gap: 10px; align-items: flex-start; padding: 5px 0; font-size: 13px; border-top: 1px dashed #f0e2c2; }
    .zv-task-list li:first-child { border-top: 0; }
    .zv-task-list li em { font-style: normal; color: #8a7a5a; font-size: 12px; }
    .zv-task-list li.is-done { color: #0f7a5c; }
    .zv-task-list p { margin: 0; font-size: 12px; color: #8a5a00; }
    .zv-task-tick { flex: none; min-width: 34px; padding: 1px 6px; border-radius: 999px; background: #fff; border: 1px solid #f5d9a0;
        font-size: 12px; font-weight: 700; text-align: center; }
    .zv-task-list li.is-done .zv-task-tick { background: #00A47D; border-color: #00A47D; color: #fff; }
    .zv-task-go { display: block; margin-bottom: 8px; padding: 11px 22px; border-radius: 50px; background: #1aaa8e; color: #fff;
        text-align: center; font-size: 13px; font-weight: 600; text-decoration: none; transition: background .2s; }
    .zv-task-go:hover { background: #158870; color: #fff; }
    @media (max-width: 768px) { .zv-task-grid { grid-template-columns: 1fr; } }
</style>
<script>
    function zvOpenTask(id) {
        document.getElementById(id || 'zvTask').classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function zvCloseTask(id) {
        document.querySelectorAll(id ? '#' + id : '.zv-task-overlay').forEach(function (el) { el.classList.remove('is-open'); });
        document.body.style.overflow = '';
    }
    document.addEventListener('click', function (e) { if (e.target.classList && e.target.classList.contains('zv-task-overlay')) zvCloseTask(e.target.id); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') zvCloseTask(); });
</script>
@endonce
