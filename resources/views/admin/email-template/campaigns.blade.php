@extends('layouts.admin')

@section('title', 'Email Campaigns')

@section('content')
<div class="adminHeading d-flex align-items-center justify-content-between">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Email Campaigns</h1>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/email-templates') }}" class="secondaryBtn theme-text-dark">Templates</a>
        <a href="{{ url('admin/email-campaigns/create') }}" class="themeBtn text-white">New Campaign</a>
    </div>
</div>

<p class="text-muted mab-20">
    A <strong>campaign</strong> sends a <a href="{{ url('admin/email-templates') }}">template</a> to a group of
    students - who receives it (all students, one school, one grade, or hand-picked students) and when (right
    away, on a fixed date, N days into a school's start month, or every month on a set day). Scheduled campaigns
    send themselves automatically once due - nothing to remember or click on the day.
</p>

<div class="row">
    <div class="col-12">
        <div class="themeCard card card-custom card-stretch gutter-b">
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Template</th>
                            <th>Recipients</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Last run</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($campaigns as $campaign)
                            <tr>
                                <td>{{ $campaign->name }}</td>
                                <td>{{ optional($campaign->template)->name ?? '—' }}</td>
                                <td>
                                    @switch($campaign->scope_type)
                                        @case('all') All students @break
                                        @case('school') One school @break
                                        @case('grade') One grade @break
                                        @case('selected') {{ count($campaign->selected_user_ids ?? []) }} selected @break
                                    @endswitch
                                </td>
                                <td>
                                    @switch($campaign->trigger_type)
                                        @case('immediate') Sent immediately @break
                                        @case('fixed_date') {{ optional($campaign->fixed_date)->format('M j, Y') }} @break
                                        @case('relative_to_school_start') {{ $campaign->relative_days_offset }} day(s) into school start @break
                                        @case('recurring_monthly_day') Monthly on day {{ $campaign->recurring_day_of_month }} @break
                                    @endswitch
                                </td>
                                <td>
                                    <span class="badge bg-{{ $campaign->status === 'sent' ? 'success' : ($campaign->status === 'cancelled' ? 'secondary' : 'info') }}">
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </td>
                                <td>{{ optional($campaign->last_run_at)->format('M j, Y H:i') ?? '—' }}</td>
                                <td>
                                    @if ($campaign->status === 'scheduled')
                                        <form action="{{ url('admin/email-campaigns/' . $campaign->id . '/cancel') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No campaigns yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
