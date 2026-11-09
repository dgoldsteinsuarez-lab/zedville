@extends('layouts.admin')

@section('title', 'Spending 4')

@section('content')
@php $frequencies = [1 => 'Once', 2 => 'Every other Saturday (2×)', 4 => 'Every Saturday (4×)']; @endphp
<div class="container-fluid">
    <h2 class="mb-1 d-flex align-items-center gap-2">Spending 4: the plan of the month @include('admin.partials.level-badge')</h2>
    <p class="text-muted">
        Once a month, after the question of the day, every student chooses one of four plans - one from each price level, the same four
        for the whole class, the next four every month. A class has <strong>Low Spending 4</strong>, next to its Low Spending activity:
        together at least {{ round(\App\Services\WantsPlanService::LOW_TOTAL * 100) }}% of the salary (Ƶ {{ number_format(\App\Services\WantsPlanService::LOW_TOTAL * $salary, 2) }}), so only the
        plans that reach it with that activity's budget are offered; or <strong>High Spending 4</strong>, on its own: every plan more than {{ round(\App\Services\WantsPlanService::HIGH_MIN * 100) }}%
        (Ƶ {{ number_format(\App\Services\WantsPlanService::HIGH_MIN * $salary, 2) }}). Classes from the <strong>Intermediate</strong> on have their own, cheaper plans:
        with the Low Spending activity about {{ round(\App\Services\WantsPlanService::MID_TOTAL * 100) }}% (Ƶ {{ number_format(\App\Services\WantsPlanService::MID_TOTAL * $salary, 2) }}),
        because they save as much as they can and give 4-6% to a charity.
        A plan is a Want, paid from the current account; a payment there's no money for waits and is paid as soon as there is -
        at the latest right after the next salary (the student is told when).
    </p>
    <p class="small">This month {{ $chosen->sum() }} {{ \Illuminate\Support\Str::plural('student', $chosen->sum()) }} chose a plan.</p>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    {{-- each class: Low or High Spending 4, and whether it reaches the share of the salary --}}
    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <strong>Classes</strong>
            <form method="POST" action="{{ route('admin.spending.level-kind') }}" class="d-flex gap-2 align-items-center small">
                @csrf
                Every class at level
                <select name="grade_level_id" class="form-select form-select-sm" style="width: auto">
                    @foreach ($gradeLevels as $level)<option value="{{ $level->id }}">{{ $level->name }}</option>@endforeach
                </select>
                gets
                <select name="spending4" class="form-select form-select-sm" style="width: auto"><option value="low">Low Spending 4</option><option value="high">High Spending 4</option></select>
                <button class="btn btn-sm btn-outline-primary">Apply</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Class</th><th>Level</th><th>Spending 4</th><th>Low Spending activity</th><th>The cheapest plan offered</th><th>Check</th></tr></thead>
                <tbody>
                    @forelse ($classes as $class)
                        <tr>
                            <td>{{ \App\Support\ClassLabel::make($class->grade->name ?? null, $class->name) }}</td>
                            <td>{{ $class->grade->level->name ?? '—' }}</td>
                            <td>
                                @if ($class->s4_kind === 'mid')
                                    <span class="small">Intermediate plans</span>
                                @else
                                    <form method="POST" action="{{ route('admin.spending.class-kind', $class) }}">@csrf
                                        <select name="spending4" class="form-select form-select-sm" style="width: auto" onchange="this.form.submit()">
                                            <option value="low" @selected($class->s4_kind === 'low')>Low Spending 4</option>
                                            <option value="high" @selected($class->s4_kind === 'high')>High Spending 4</option>
                                        </select>
                                    </form>
                                @endif
                            </td>
                            <td>
                                @if ($class->s4_kind === 'high')
                                    <span class="text-muted small">not needed</span>
                                @elseif ($class->s4_low)
                                    {{ $class->s4_low->title }} <span class="text-muted">(Ƶ {{ number_format($class->s4_low->budget, 2) }})</span>
                                @else
                                    <span class="text-danger small">none assigned</span>
                                @endif
                            </td>
                            <td>Ƶ {{ number_format((float) $class->s4_cheapest, 2) }} <span class="text-muted small">({{ $class->s4_options }} plans to rotate)</span></td>
                            <td>
                                @if ($class->s4_ok)
                                    <span class="text-success">✓ Ƶ {{ number_format($class->s4_total, 2) }} ({{ round($class->s4_total / $salary * 100) }}%)</span>
                                @elseif ($class->s4_kind !== 'high' && !$class->s4_low)
                                    <span class="text-danger small">⚠ give it a Low Spending activity in <a href="{{ url('admin/education/spending-activity') }}">Assign to classes</a></span>
                                @else
                                    <span class="text-danger">⚠ Ƶ {{ number_format($class->s4_total, 2) }} ({{ round($class->s4_total / $salary * 100) }}%)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted p-3">No classes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th style="width: 120px"></th><th>Kind</th><th>Plan</th><th>Cost</th><th>A full month</th><th>Price level</th><th>Chosen this month</th><th>Offered</th><th class="text-end"></th></tr></thead>
                <tbody>
                    @foreach ($plans as $plan)
                        <tr @class(['table-light text-muted' => !$plan->is_active])>
                            <td><img src="{{ $plan->imageUrl() }}" alt="" style="width: 112px; aspect-ratio: 8/5; object-fit: cover; border-radius: 8px;"></td>
                            <td><span class="badge {{ match ($plan->kind) { 'high' => 'bg-danger', 'mid' => 'bg-warning text-dark', default => 'bg-info text-dark' } }}">{{ match ($plan->kind) { 'high' => 'High', 'mid' => 'Intermediate', default => 'Low' } }}</span></td>
                            <td><strong>{{ $plan->name }}</strong><div class="small text-muted" style="max-width: 460px">{{ $plan->description }}</div>
                                <div class="small">ES: {{ $plan->translations['es']['name'] ?? '—' }} · FR: {{ $plan->translations['fr']['name'] ?? '—' }} · DE: {{ $plan->translations['de']['name'] ?? '—' }}</div></td>
                            <td>Ƶ {{ number_format($plan->cost, 2) }}<div class="small text-muted">{{ $frequencies[$plan->times_per_month] ?? $plan->times_per_month . '×' }}</div></td>
                            <td>Ƶ {{ number_format($plan->monthlyCost(), 2) }}</td>
                            <td>{{ $levels[$plan->id] ?? '—' }}</td>
                            <td>{{ $chosen[$plan->id] ?? 0 }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.spending.plans.toggle', $plan) }}">@csrf
                                    <button class="btn btn-sm {{ $plan->is_active ? 'btn-success' : 'btn-outline-secondary' }}">{{ $plan->is_active ? 'Yes' : 'No' }}</button>
                                </form>
                            </td>
                            <td class="text-end"><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#plan-{{ $plan->id }}">Edit</button></td>
                        </tr>
                        <tr class="collapse" id="plan-{{ $plan->id }}"><td colspan="9">@include('admin.spending._plan-form', ['plan' => $plan])</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>+ New plan</strong></div>
        <div class="card-body">@include('admin.spending._plan-form', ['plan' => null])</div>
    </div>
</div>
@endsection
