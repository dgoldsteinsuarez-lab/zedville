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

                    {{-- One version per language: each student receives the one in their
                         language, or the English one when theirs is left empty. --}}
                    <ul class="nav nav-tabs mab-20" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#version-en" type="button" role="tab">English (main)</button>
                        </li>
                        @foreach ($languages as $locale => $language)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#version-{{ $locale }}" type="button" role="tab">
                                    {{ $language }}
                                    @if (filled(old("translations.$locale.subject", $template->translations[$locale]['subject'] ?? null)))
                                        <span class="badge bg-success ms-1">✓</span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="version-en" role="tabpanel">
                            <p class="text-muted small">Students whose language has no version of its own receive this one.</p>
                            <div class="form-group mab-20">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" name="subject" value="{{ old('subject', $template->subject) }}">
                            </div>
                            <div class="form-group mab-20">
                                <label class="form-label">Content</label>
                                <textarea class="form-control longheight js-editor" name="content" id="emilnote">{!! old('content', $template->content) !!}</textarea>
                            </div>
                        </div>
                        @foreach ($languages as $locale => $language)
                            <div class="tab-pane fade" id="version-{{ $locale }}" role="tabpanel">
                                <p class="text-muted small">Optional. Students who use Zedville in {{ $language }} receive this version. Leave it empty to send them the English one. The same @{{name}} / @{{citizenId}} placeholders work here.</p>
                                <div class="form-group mab-20">
                                    <label class="form-label">Subject ({{ $language }})</label>
                                    <input type="text" class="form-control" name="translations[{{ $locale }}][subject]" value="{{ old("translations.$locale.subject", $template->translations[$locale]['subject'] ?? '') }}">
                                </div>
                                <div class="form-group mab-20">
                                    <label class="form-label">Content ({{ $language }})</label>
                                    <textarea class="form-control longheight js-editor" name="translations[{{ $locale }}][content]">{!! old("translations.$locale.content", $template->translations[$locale]['content'] ?? '') !!}</textarea>
                                </div>
                            </div>
                        @endforeach
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
        document.querySelectorAll('.js-editor').forEach(function (textarea) {
            ClassicEditor
                .create(textarea, {
                    toolbar: [
                        'bold', 'italic',
                        '|', 'bulletedList', 'numberedList',
                        '|', 'link', 'undo', 'redo'
                    ]
                })
                .catch(error => console.error(error));
        });
    });
</script>
@endpush
