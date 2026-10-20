@extends('layouts.admin')

@section('title', 'Email Templates')

@section('content')
<div class="adminHeading d-flex align-items-center justify-content-between">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">Email Templates</h1>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/email-campaigns') }}" class="secondaryBtn theme-text-dark">Campaigns</a>
        <a href="{{ url('admin/email-templates/create') }}" class="themeBtn text-white">New Template</a>
    </div>
</div>

<p class="text-muted mab-20">
    A <strong>template</strong> is the email itself - its subject and content. Create one here, then go to
    <a href="{{ url('admin/email-campaigns/create') }}">Campaigns</a> to actually send it to students. One
    template can be reused by several campaigns (e.g. the same "Welcome" template sent to different schools
    on different dates).
</p>

<div class="row">
    <div class="col-12">
        <div class="themeCard card card-custom card-stretch gutter-b">
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>School</th>
                            <th>Linked Event</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($templates as $template)
                            <tr>
                                <td>{{ $template->name }}</td>
                                <td>{{ $template->subject }}</td>
                                <td>{{ optional($template->school)->school_name ?? 'All schools' }}</td>
                                <td>{{ $template->trigger_event ?? '—' }}</td>
                                <td>
                                    <a href="{{ url('admin/email-templates/' . $template->id . '/edit') }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No templates yet - create your first one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
