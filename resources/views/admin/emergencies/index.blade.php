@extends('layouts.admin')

@section('title', 'Emergencies')

{{-- Admin > level > Emergencies (EmergencyAdminController; App\Services\EmergencyService): on or off for the level,
     the students' months of each scenario, the list of emergencies, and what each student got and how they paid. --}}
@section('content')
    <div class="adminHeading">
        <h1 class="d-flex align-items-center gap-2 theme-text-dark fw-500 fs-16 mab-20">Emergencies @include('admin.partials.level-badge')</h1>
    </div>
    <p class="text-muted mab-15">
        Three unexpected expenses a school year, one a term. Each comes the first time the student logs in that month from the
        7th (after the salary and the bills), or on the month's last day if they don't, with a letter and "Sort out your
        unexpected expense" on the department's board: 30 days to pay it with their Emergency Fund, part fund and part current
        account, the current account or a loan, seeing what each costs in all; then two short questions. A health one costs
        what their insurance leaves them. Not paid in 30 days, it is taken from the current account, into overdraft if need be,
        with no fine. The months count from the student's start in the level (Intermediate setup): scenario A brings a fund
        from the Rookie, B starts one, so theirs come later.
    </p>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="themeCard card card-custom mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.emergencies.update') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="enabled" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="enabled" name="enabled" value="1" @checked(old('enabled', $settings['enabled']))>
                        <label class="form-check-label" for="enabled">Students of {{ $level->name }} get unexpected expenses</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="months_a">Months, scenario A (with a fund from the Rookie)</label>
                    <input class="form-control" id="months_a" name="months_a" value="{{ old('months_a', implode(', ', $settings['A'])) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="months_b">Months, scenario B (no fund yet)</label>
                    <input class="form-control" id="months_b" name="months_b" value="{{ old('months_b', implode(', ', $settings['B'])) }}">
                </div>
                {{-- the frauds (App\Services\FraudService): a call on screen and a fake email, in the student's months of the level --}}
                <div class="col-12 mt-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="frauds_enabled" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="frauds_enabled" name="frauds_enabled" value="1" @checked(old('frauds_enabled', $settings['frauds']))>
                        <label class="form-check-label" for="frauds_enabled">Students of {{ $level->name }} get the two frauds: a call on screen and a fake email from the Education Finance Department</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="call_month">Month of the call</label>
                    <input class="form-control" type="number" min="1" max="12" id="call_month" name="call_month" value="{{ old('call_month', $settings['call']) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="email_month">Month of the fake email</label>
                    <input class="form-control" type="number" min="1" max="12" id="email_month" name="email_month" value="{{ old('email_month', $settings['email']) }}">
                </div>
                <div class="col-md-4"><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>

    <div class="themeCard card card-custom mb-3">
        <div class="card-body table-responsive">
            <h2 class="fs-16 fw-500 mb-2">The list</h2>
            <p class="text-muted small mb-2">Each student gets a different one each time, always the same for the same student, between 5 % and 25 % of the salary.</p>
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Unexpected expense</th><th class="text-end">Cost</th><th>Health insurance</th></tr></thead>
                <tbody>
                    @foreach (\App\Services\EmergencyService::KINDS as $kind => $k)
                        @continue($kind === \App\Services\EmergencyService::FRAUD)
                        <tr>
                            <td>{{ $k['icon'] }} {{ __('emergency.kinds.' . $kind . '.title', [], 'en') }}</td>
                            <td class="text-end">{{ number_format($k['cost'], 2) }} Ƶ</td>
                            <td>{{ $k['health'] ? ($k['dentist'] ? 'Yes (dentist: 3 months\' waiting period on Basic)' : 'Yes') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="themeCard card card-custom">
        <div class="card-body table-responsive">
            <h2 class="fs-16 fw-500 mb-2">What the students got</h2>
            @if ($emergencies->isEmpty())
                <p class="text-muted mb-0">No unexpected expenses yet.</p>
            @else
                <table class="table table-sm align-middle">
                    <thead><tr><th>Student</th><th>Class</th><th>Came</th><th>Unexpected expense</th><th class="text-end">They pay</th><th>Paid with</th><th class="text-end">Real cost</th><th>Questions</th></tr></thead>
                    <tbody>
                        @foreach ($emergencies as $e)
                            @php $student = $students[$e->user_id] ?? null; @endphp
                            <tr>
                                <td>{{ $student ? ($student->nickname ?: $student->name) : '—' }}</td>
                                <td>{{ $student && isset($classes[$student->grade]) ? \App\Support\ClassLabel::make($classes[$student->grade]->grade->name ?? null, $classes[$student->grade]->name) : '—' }}</td>
                                <td>{{ $e->arrived_at->format('d M Y') }}</td>
                                <td>{{ \App\Services\EmergencyService::KINDS[$e->kind]['icon'] }} {{ __('emergency.kinds.' . $e->kind . '.title', [], 'en') }}</td>
                                <td class="text-end">{{ number_format((float) $e->amount, 2) }} Ƶ</td>
                                <td>
                                    @if ($e->status === 'pending')
                                        <span class="badge bg-warning text-dark">Not yet · until {{ $e->due_on->format('d M') }}</span>
                                    @elseif ($e->status === 'auto')
                                        <span class="badge bg-danger">Taken after 30 days{{ $e->method === 'overdraft' ? ' · overdraft' : '' }}</span>
                                    @else
                                        {{ __('emergency.methods.' . $e->method, [], 'en') }}
                                    @endif
                                </td>
                                <td class="text-end">{{ $e->status === 'pending' ? '—' : number_format($e->realCost(), 2) . ' Ƶ' }}</td>
                                <td>{{ $e->completed_at ? __('emergency.changes.' . $e->change, [], 'en') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="themeCard card card-custom mt-3">
        <div class="card-body table-responsive">
            <h2 class="fs-16 fw-500 mb-2">Frauds</h2>
            <p class="text-muted small mb-2">
                The call rings on screen after the mood meter and the question of the day; the fake email arrives in the
                mailbox, its address with a 1 for an l. Hanging up or reporting it earns a participation point; falling for
                it costs 5 % to 25 % of the salary, which the student covers with their Emergency Fund or a loan in
                "Sort out your unexpected expense", with a letter from the bank.
            </p>
            @if ($frauds->isEmpty())
                <p class="text-muted mb-0">No frauds yet.</p>
            @else
                <table class="table table-sm align-middle">
                    <thead><tr><th>Student</th><th>Came</th><th>Fraud</th><th>What they did</th><th class="text-end">Lost</th></tr></thead>
                    <tbody>
                        @foreach ($frauds as $f)
                            @php
                                $student = $students[$f->user_id] ?? null;
                                $lost = $f->emergency_id ? optional($emergencies->firstWhere('id', $f->emergency_id))->amount : null;
                            @endphp
                            <tr>
                                <td>{{ $student ? ($student->nickname ?: $student->name) : '—' }}</td>
                                <td>{{ $f->arrived_at->format('d M Y') }}</td>
                                <td>{{ $f->channel === 'call' ? 'Call' : 'Email' }}: {{ __('fraud.names.' . $f->script, [], 'en') }} ({{ $f->script }})</td>
                                <td>
                                    @if ($f->status === 'safe')<span class="badge bg-success">Spotted it</span>
                                    @elseif ($f->status === 'fell')<span class="badge bg-danger">Fell for it</span>
                                    @else<span class="badge bg-secondary">Not answered yet</span>@endif
                                </td>
                                <td class="text-end">{{ $lost !== null ? number_format((float) $lost, 2) . ' Ƶ' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
