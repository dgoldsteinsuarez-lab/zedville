@extends('layouts.admin')

@section('title', $template->exists ? 'Edit Template' : 'New Template')

@section('content')
<div class="adminHeading">
    <h1 class="d-flex align-items-center theme-text-dark fw-500 fs-16 mab-20">
        {{ $template->exists ? 'Edit Template' : 'New Template' }}
    </h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="themeCard card card-custom card-stretch gutter-b">
            <div class="card-body pall-30">
                <form
                    action="{{ $template->exists ? url('admin/email-templates/' . $template->id) : url('admin/email-templates') }}"
                    method="POST" class="themeForm">
                    @csrf
                    @if ($template->exists)
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-20">
                                <label class="form-label">Template name</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $template->name) }}" required>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group mab-20">
                                <label class="form-label">School</label>
                                <select class="form-select" name="sid">
                                    <option value="">All schools</option>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('sid', $template->sid) == $school->id ? 'selected' : '' }}>
                                            {{ $school->school_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mab-20">
                        <label class="form-label">
                            Linked event
                            <small class="text-muted">(optional - makes this template drive an automatic system email instead of only being used in campaigns)</small>
                        </label>
                        <select class="form-select" name="trigger_event">
                            <option value="">None - campaign use only</option>
                            <option value="welcome" {{ old('trigger_event', $template->trigger_event) == 'welcome' ? 'selected' : '' }}>Welcome (on registration)</option>
                            <option value="bank-account-ready" {{ old('trigger_event', $template->trigger_event) == 'bank-account-ready' ? 'selected' : '' }}>Bank Account Ready (on bank account opened)</option>
                            <option value="salary-deposite" {{ old('trigger_event', $template->trigger_event) == 'salary-deposite' ? 'selected' : '' }}>First Salary Notification (on bank account opened)</option>
                            <option value="direct-deposit" {{ old('trigger_event', $template->trigger_event) == 'direct-deposit' ? 'selected' : '' }}>Direct Deposit Form (on bank account opened)</option>
                            <option value="auto-debit" {{ old('trigger_event', $template->trigger_event) == 'auto-debit' ? 'selected' : '' }}>Auto-Debit Intro (on bank account opened)</option>
                        </select>
                    </div>

                    <div class="form-group mab-20">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" value="{{ old('subject', $template->subject) }}" required>
                    </div>

                    <div class="form-group mab-20">
                        <label class="form-label">Content</label>
                        <textarea class="form-control longheight" name="content" id="emilnote">{!! old('content', $template->content) !!}</textarea>
                    </div>

                    <button type="submit" class="themeBtn text-white mt-2">{{ $template->exists ? 'Update' : 'Create' }} Template</button>
                    <a href="{{ url('admin/email-templates') }}" class="secondaryBtn theme-text-dark mt-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        ClassicEditor
            .create(document.querySelector('#emilnote'), {
                toolbar: [
                    'bold', 'italic',
                    '|', 'bulletedList', 'numberedList',
                    '|', 'link', 'undo', 'redo'
                ]
            })
            .catch(error => console.error(error));
    });
</script>
@endpush
