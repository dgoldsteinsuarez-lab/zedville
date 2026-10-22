@extends('layouts.admin')

@section('title', $student->nickname . ' — Student Snapshot')

@section('content')
<div class="container-fluid">
    <a href="{{ url('admin/my-students') }}" class="d-inline-block mb-3">&larr; Back to My Students</a>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h2 class="mb-1">{{ $student->nickname }}</h2>
            <p class="text-muted mb-4">{{ $student->name }} &middot; {{ $student->citizenId }}</p>
        </div>
        <a href="{{ url('admin/salary-bonuses?student_id=' . $student->id) }}" class="btn btn-sm btn-outline-primary">Grant bonus</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">Bank account &amp; budget</div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="text-muted small mb-1">Current balance</div>
                    <div class="fs-4 fw-bold">{{ number_format($balance, 2) }} zeds</div>
                </div>
                @if ($budget)
                    <div class="col-6 col-md-3 mb-3 mb-md-0">
                        <div class="text-muted small mb-1">Needs</div>
                        <div class="fs-5 fw-bold">{{ number_format($budget['totalNeeds'] ?? 0, 2) }} ({{ $budget['needsPercentage'] ?? 0 }}%)</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small mb-1">Wants</div>
                        <div class="fs-5 fw-bold">{{ number_format($budget['totalWants'] ?? 0, 2) }} ({{ $budget['wantsPercentage'] ?? 0 }}%)</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small mb-1">Savings</div>
                        <div class="fs-5 fw-bold">{{ number_format($budget['totalSavings'] ?? 0, 2) }} ({{ $budget['savingsPercentage'] ?? 0 }}%)</div>
                    </div>
                @else
                    <div class="col-md-9 text-muted">No monthly statement generated yet.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Activities this month</div>
                    <div class="fs-3 fw-bold">{{ $activitiesThisMonth }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            @php $current = $engagementHistory->last(); $meta = \App\Models\StudentBadgeRecord::BADGE_META[$current->effective_monthly_badge ?? 'NONE']; @endphp
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Engagement badge</div>
                    <div class="fs-4 fw-bold" style="color: {{ $meta['text'] }};">{{ $meta['emoji'] }} {{ $meta['label'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            @php $currentFh = $finheroHistory->last(); $fhMeta = \App\Models\FinheroBadgeRecord::BADGE_META[$currentFh->effective_monthly_badge ?? 'NONE']; @endphp
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">FinHero (knowledge)</div>
                    <div class="fs-4 fw-bold" style="color: {{ $fhMeta['text'] }};">{{ $fhMeta['emoji'] }} {{ $fhMeta['label_en'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Latest mood</div>
                    <div class="fs-4 fw-bold">{{ $moodHistory->last()?->mood ?? '—' }}</div>
                    <div class="text-muted small">{{ $moodHistory->last()?->created_at?->format('d M') ?? 'No entries yet' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Engagement badge history</div>
                <div class="card-body p-0">
                    @if ($engagementHistory->isEmpty())
                        <p class="text-muted p-3 mb-0">No records yet this academic year.</p>
                    @else
                        <table class="table mb-0">
                            <thead><tr><th>Month</th><th>Required</th><th>Optional</th><th>Participation</th><th>Badge</th></tr></thead>
                            <tbody>
                                @foreach ($engagementHistory as $r)
                                    @php $m = \App\Models\StudentBadgeRecord::BADGE_META[$r->effective_monthly_badge]; @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::create($r->year, $r->month, 1)->format('M Y') }}</td>
                                        <td>{{ $r->required_completed }}/{{ $r->required_assigned }}</td>
                                        <td>{{ $r->optional_completed }}</td>
                                        <td>{{ $r->participation_points }}</td>
                                        <td><span style="color:{{ $m['text'] }}">{{ $m['emoji'] }} {{ $m['label'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">FinHero badge history</div>
                <div class="card-body p-0">
                    @if ($finheroHistory->isEmpty())
                        <p class="text-muted p-3 mb-0">No records yet this academic year.</p>
                    @else
                        <table class="table mb-0">
                            <thead><tr><th>Month</th><th>Quiz</th><th>Library</th><th>Activity</th><th>Badge</th></tr></thead>
                            <tbody>
                                @foreach ($finheroHistory as $r)
                                    @php $m = \App\Models\FinheroBadgeRecord::BADGE_META[$r->effective_monthly_badge]; @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::create($r->year, $r->month, 1)->format('M Y') }}</td>
                                        <td>{{ $r->quiz_pts }}</td>
                                        <td>{{ $r->library_pts }}</td>
                                        <td>{{ $r->activity_pts }}</td>
                                        <td><span style="color:{{ $m['text'] }}">{{ $m['emoji'] }} {{ $m['label_en'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Exam evidence</div>
        <div class="card-body p-0">
            @if ($realExamAttempts->isEmpty())
                <p class="text-muted p-3 mb-0">No real exams completed yet.</p>
            @else
                <table class="table mb-0">
                    <thead><tr><th>Exam</th><th>Score</th><th>Completed</th></tr></thead>
                    <tbody>
                        @foreach ($realExamAttempts as $attempt)
                            <tr>
                                <td>{{ $attempt->item->title ?? '(exam removed)' }}</td>
                                <td>{{ $attempt->score }}/{{ $attempt->total_questions }}</td>
                                <td>{{ $attempt->completed_at?->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Recent mood (last {{ $moodHistory->count() }} entries)</div>
        <div class="card-body">
            @if ($moodHistory->isEmpty())
                <p class="text-muted mb-0">No mood entries yet.</p>
            @else
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($moodHistory as $log)
                        <span class="badge bg-light text-dark border" title="{{ $log->created_at?->format('d M Y') }}">
                            {{ $log->created_at?->format('d/m') }}: {{ $log->mood }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
