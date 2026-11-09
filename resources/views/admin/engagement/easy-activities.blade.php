@extends('layouts.admin')

@php
    $isLanguage = $section === 'language';
    // the Money Factory's section: exchange activities (made here) and its built-in activities (assigned here)
    $isExchange = $section === 'money_factory';
    $type = array_key_first($types);
    $currencies = array_keys(\App\Services\CurrencyExchangeService::ECB_RATES);
@endphp

@section('title', $isLanguage ? 'Language Activities' : ($isExchange ? 'Money Factory' : 'Transfer money to a friend'))

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <h2 class="mb-0">{{ $isLanguage ? 'Hybrid Classes: Language Activities' : ($isExchange ? 'Money Factory activities' : 'Engagement: Transfer money to a friend') }}</h2>
        <div class="d-flex gap-2 align-items-center">
            @include('admin.partials.level-badge')
            @include('components.school-badge')
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>{{ $isLanguage ? 'Hybrid classes and their activities' : 'What the students do' }}</strong></div>
        <div class="card-body">
            @if ($isLanguage)
                <p class="mb-2">
                    A <strong>hybrid class</strong> is for students who speak little of the class language (Spanish, French or German):
                    the whole city is shown in that language mixed with English, text by text. To make a class hybrid, open
                    <strong>Grades &rarr; the grade &rarr; Edit class</strong> and choose <strong>Language level: Hybrid</strong>
                    (and the class language).
                </p>
                <ul class="mb-3" style="list-style:disc; padding-left:22px">
                    <li><strong>Words and names</strong> (products, buttons, short labels): only in the language they learn - e.g. "Pollo", "Enviar".</li>
                    <li><strong>Short texts</strong>: in both - the language they learn first and bigger, English smaller next to it.</li>
                    <li><strong>Longer explanations</strong>: in English, so nobody gets lost.</li>
                </ul>
                <p class="mb-1"><strong>Match the words</strong> - the activities of this page</p>
                <ul class="mb-0" style="list-style:disc; padding-left:22px">
                    <li>You write word pairs: a word in the language the class learns and its English meaning (or start from the bank's own words).</li>
                    <li>The student sees the words big and the English meanings small, taps a word and then its meaning, until all are matched.</li>
                    <li>When all words are matched, the activity is done and counts for the <strong>Engagement</strong> badge (required or optional, like any activity on the class calendar).</li>
                    <li>You can assign it to any class; it fits hybrid and beginner classes best.</li>
                </ul>
            @elseif ($isExchange)
                <p class="mb-2">
                    Short tasks from real life that students do in the city. They count for the <strong>Engagement</strong> badge
                    like the other activities on the class calendar: a <strong>required</strong> one must be done for any badge,
                    an <strong>optional</strong> one raises the badge level. The app sees by itself when a student has done it.
                </p>
                <p class="mb-1">Students see them on the <strong>Education Finance Department</strong> board and do them at the
                    <strong>Money Factory</strong> (City Map &rarr; Financial Square).</p>
                <p class="mb-1"><strong>Exchange money</strong> (exchange Zeds into another currency) - you can make more below</p>
                <ul class="mb-2" style="list-style:disc; padding-left:22px">
                    <li>The student reads why they need the money (a school trip to New York, a weekend in Paris...) and how many Zeds to exchange into which currency. No maths: the Money Factory works out the rest.</li>
                    <li><strong>Do it</strong> fills in the exchange; they see the rate, the {{ \App\Services\CurrencyExchangeService::COMMISSION * 100 }}% commission and what they get, and confirm.</li>
                    <li>It's done when they exchange at least those Zeds into that currency. The Zeds and the commission leave their current account (two lines on the statement); they get the foreign money in cash.</li>
                    <li>The six ready-made activities are shown in Spanish, French and German too; a title or reason you write yourself is shown in English. Hybrid classes see only the task, not the reason.</li>
                </ul>
                <p class="mb-1"><strong>The built-in activities</strong> (where a banknote has been, real or fake, the story of money, the Big Mac index, central bank for a day...) - assign them below</p>
                <ul class="mb-0" style="list-style:disc; padding-left:22px">
                    <li>Each one has its own page at the Money Factory, opened from the Education Finance Department board; its card below says what the student does.</li>
                    <li>It's done when all the answers are right. Every student gets their own numbers, banknotes or order, so they can't copy the answers.</li>
                    <li>Shown in Spanish, French and German too. Hybrid classes see the task and the questions, without the explanations.</li>
                    <li>Each is for some levels (Junior to Intermediate, or Advance and Advance Economist); you can change that in "Change this activity".</li>
                </ul>
            @else
                <p class="mb-2">
                    Short tasks from real life that students do in the city. They count for the <strong>Engagement</strong> badge
                    like the other activities on the class calendar: a <strong>required</strong> one must be done for any badge,
                    an <strong>optional</strong> one raises the badge level. The app sees by itself when a student has done it.
                </p>
                <p class="mb-1"><strong>Transfer money to a friend</strong> (Bank: transfer money to another student)</p>
                <ul class="mb-0" style="list-style:disc; padding-left:22px">
                    <li>The student asks a classmate for their <strong>account number or IBAN</strong> and their <strong>name as on the account</strong> (the classmate finds them in Bank &rarr; Card &amp; details).</li>
                    <li>In Bank &rarr; Pay &amp; transfer &rarr; Send to a friend, they fill these in with the amount and a <strong>reference</strong> (what the money is for), check the summary and confirm, like in online banking.</li>
                    <li>It's done when the money reaches a <strong>classmate's</strong> account (students can only send money within their class) with a reference written, and at least the minimum amount if you set one. Transfers between their own accounts don't count.</li>
                    <li>Students only see it while it runs for their class: in the bank, on the calendar, on their home page and on the Education department board.</li>
                </ul>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>{{ $isLanguage ? 'Create a word match' : ($isExchange ? 'Create an exchange activity' : 'Create a transfer activity') }}</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.engagement.easy-activities.store') }}">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label" for="ea-title">Title</label>
                        <input id="ea-title" name="title" class="form-control" maxlength="120" required
                               value="{{ old('title', $activities->contains('title', $types[$type]['title']) ? '' : $types[$type]['title']) }}">
                        <div class="form-text">
                            @if ($isLanguage)
                                Shown to students as you write it (e.g. "Bank words", "At the supermarket").
                            @else
                                "{{ $types[$type]['title'] }}" is shown to students in Spanish, French and German. A title you write yourself is shown in English.
                            @endif
                        </div>
                    </div>
                    @if ($isLanguage)
                        <div class="col-md-3">
                            <label class="form-label" for="ea-language">Language of the words</label>
                            <select id="ea-language" name="language" class="form-control" required>
                                @foreach (\App\Models\EasyActivity::LANGUAGES as $code => $name)
                                    <option value="{{ $code }}" @selected(old('language') === $code)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ea-pairs">Word pairs - one per line: <code>word in the language = English meaning</code> (3 to 20)</label>
                            <textarea id="ea-pairs" name="pairs" class="form-control" rows="8" placeholder="el ahorro = savings&#10;el alquiler = rent&#10;la nómina = salary">{{ old('pairs') }}</textarea>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="var w = {{ \Illuminate\Support\Js::from($bankWords) }}; document.getElementById('ea-pairs').value = w[document.getElementById('ea-language').value] || '';">
                                Fill with the bank's words in this language
                            </button>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ea-instructions">Extra instructions for the students (optional, in English)</label>
                            <input id="ea-instructions" name="instructions" class="form-control" maxlength="500" value="{{ old('instructions') }}" placeholder="e.g. These are words you will see in the bank.">
                        </div>
                    @elseif ($isExchange)
                        <div class="col-md-3">
                            <label class="form-label" for="ea-currency">Currency</label>
                            <select id="ea-currency" name="currency" class="form-control" required>
                                @foreach ($currencies as $code)
                                    <option value="{{ $code }}" @selected(old('currency') === $code)>{{ $code }} - {{ __('money.currencies.' . $code) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="ea-zeds">Zeds to exchange</label>
                            <input id="ea-zeds" type="number" step="0.01" min="{{ \App\Services\CurrencyExchangeService::MIN }}" max="{{ \App\Services\CurrencyExchangeService::MAX }}" name="min_amount" class="form-control" value="{{ old('min_amount') }}" required placeholder="e.g. 100">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ea-reason">Why the student needs this money (in English)</label>
                            <input id="ea-reason" name="reason" class="form-control" maxlength="300" value="{{ old('reason') }}" placeholder="e.g. Your class is going on a school trip to New York. You need US dollars for the subway and a snack.">
                        </div>
                    @else
                        <div class="col-md-3">
                            <label class="form-label" for="ea-min">Minimum amount (Ƶ, optional)</label>
                            <input id="ea-min" type="number" step="0.01" min="0.01" name="min_amount" class="form-control" value="{{ old('min_amount') }}" placeholder="any amount">
                        </div>
                    @endif
                    @include('admin.partials.levels-field', ['levels' => null, 'isNew' => true, 'fieldId' => 'ea-levels-new'])
                </div>
                <button type="submit" class="btn btn-primary mt-3">Create</button>
            </form>
        </div>
    </div>

    @forelse ($activities as $activity)
        @php $rows = $assignments->get($activity->id, collect()); $editable = $mayEdit($activity); @endphp
        <div class="card mb-4" id="activity-{{ $activity->id }}">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <strong>{{ $activity->title }}</strong>
                <span class="text-muted small">· {{ $activity->typeLabel() }}</span>
                @if ($isLanguage)
                    <span class="text-muted small">· {{ \App\Models\EasyActivity::LANGUAGES[$activity->language] ?? $activity->language }} · {{ count($activity->pairs()) }} words</span>
                @elseif ($activity->type === 'currency_exchange')
                    <span class="text-muted small">· Ƶ {{ number_format($activity->min_amount, 2) }} into {{ $activity->currency() }}</span>
                @elseif (!$isExchange)
                    <span class="text-muted small">· {{ $activity->min_amount ? 'at least Ƶ ' . number_format($activity->min_amount, 2) : 'any amount' }}</span>
                @endif
                <span class="badge bg-light text-dark border">{{ $activity->sid ? 'This school only' : 'All schools' }}</span>
                <span class="badge bg-info text-dark">{{ \App\Support\AdminLevel::names($activity->levels) }}</span>
            </div>
            <div class="card-body">
                @if ($isLanguage)
                    <p class="small mb-2">
                        @foreach ($activity->pairs() as [$word, $meaning])
                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $word }} = {{ $meaning }}</span>
                        @endforeach
                    </p>
                @endif
                @if ($isExchange && !empty($activity->content['reason']))
                    <p class="small mb-2">{{ $activity->content['reason'] }}</p>
                @endif
                {{-- a built-in activity: what the student does there --}}
                @if (!empty(\App\Models\EasyActivity::TYPES[$activity->type]['about']))
                    <p class="small mb-2">{{ \App\Models\EasyActivity::TYPES[$activity->type]['about'] }}
                        <a href="{{ $activity->studentUrl() }}" target="_blank" rel="noopener">See the student's page &rarr;</a></p>
                @endif

                @if ($editable)
                    <details class="mb-3">
                        <summary class="small">Change this activity, or delete it</summary>
                        <form method="POST" action="{{ route('admin.engagement.easy-activities.update', $activity->id) }}" class="row g-2 mt-1">
                            @csrf
                            @method('PUT')
                            <div class="col-md-5">
                                <label class="form-label small mb-1">Title</label>
                                @if (in_array($activity->type, \App\Models\EasyActivity::BUILT_IN, true))
                                    {{-- built in: its name is translated for the students, so it stays --}}
                                    <input class="form-control form-control-sm" value="{{ $activity->title }}" readonly>
                                    <div class="form-text">Built in: shown to students in their language. You can change its levels.</div>
                                @else
                                    <input name="title" class="form-control form-control-sm" maxlength="120" required value="{{ $activity->title }}">
                                @endif
                            </div>
                            @if ($isLanguage)
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Language of the words</label>
                                    <select name="language" class="form-control form-control-sm">
                                        @foreach (\App\Models\EasyActivity::LANGUAGES as $code => $name)
                                            <option value="{{ $code }}" @selected($activity->language === $code)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-1">Word pairs (word = English meaning)</label>
                                    <textarea name="pairs" class="form-control form-control-sm" rows="6">{{ collect($activity->pairs())->map(fn ($p) => $p[0] . ' = ' . $p[1])->implode("\n") }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-1">Extra instructions (optional, in English)</label>
                                    <input name="instructions" class="form-control form-control-sm" maxlength="500" value="{{ $activity->content['instructions'] ?? '' }}">
                                </div>
                            @elseif ($activity->type === 'currency_exchange')
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Currency</label>
                                    <select name="currency" class="form-control form-control-sm">
                                        @foreach ($currencies as $code)
                                            <option value="{{ $code }}" @selected($activity->currency() === $code)>{{ $code }} - {{ __('money.currencies.' . $code) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Zeds to exchange</label>
                                    <input type="number" step="0.01" min="{{ \App\Services\CurrencyExchangeService::MIN }}" max="{{ \App\Services\CurrencyExchangeService::MAX }}" name="min_amount" class="form-control form-control-sm" value="{{ $activity->min_amount }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small mb-1">Why the student needs this money (in English)</label>
                                    <input name="reason" class="form-control form-control-sm" maxlength="300" value="{{ $activity->content['reason'] ?? '' }}">
                                </div>
                            @elseif (!in_array($activity->type, \App\Models\EasyActivity::BUILT_IN, true))
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Minimum amount (Ƶ)</label>
                                    <input type="number" step="0.01" min="0.01" name="min_amount" class="form-control form-control-sm" value="{{ $activity->min_amount }}" placeholder="any amount">
                                </div>
                            @endif
                            @include('admin.partials.levels-field', ['levels' => $activity->levels, 'fieldId' => 'ea-levels-' . $activity->id])
                            <div class="col-12"><button class="btn btn-sm btn-outline-primary">Save</button></div>
                        </form>
                        @unless (in_array($activity->type, \App\Models\EasyActivity::BUILT_IN, true))
                            <form method="POST" action="{{ route('admin.engagement.easy-activities.delete', $activity->id) }}" class="mt-2" onsubmit="return confirm(@js('Delete "' . $activity->title . '"? It is removed from every class and its calendar.'))">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete activity</button>
                            </form>
                        @endunless
                    </details>
                @endif

                <form method="POST" action="{{ route('admin.engagement.easy-activities.assign', $activity->id) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Class</label>
                            <select name="cid" class="form-control" required>
                                <option value="">Select class</option>
                                @include('admin.partials.assignment-scope-options')
                                @foreach ($classes as $class)
                                    @if (!$activity->sid || (int) $class->sid === (int) $activity->sid)
                                        <option value="{{ $class->id }}">{{ $class->label }}{{ $isLanguage && ($class->language_level ?? 'standard') === 'hybrid' ? ' (hybrid ' . strtoupper($class->default_language ?? '') . ')' : '' }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Position</label>
                            <select name="position" class="form-control" required>
                                <option value="required">Required</option>
                                <option value="optional">Optional</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From</label>
                            <input type="date" name="start_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Until</label>
                            <input type="date" name="end_date" class="form-control" value="{{ now()->endOfMonth()->toDateString() }}" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Assign</button>
                    <span class="text-muted small ms-2">Assigning a class again updates its dates.</span>
                </form>

                <table class="table mb-0 mt-3 align-middle">
                    <thead><tr><th>Class</th><th>Position</th><th>From</th><th>Until</th><th>Done</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($rows as $a)
                        <tr>
                            <td>
                                {{ $a->label }}
                                @if ($isLanguage && $activity->language && $a->class_language && $a->class_language !== $activity->language)
                                    <span class="badge bg-warning text-dark" title="The class language is {{ strtoupper($a->class_language) }}">class language: {{ strtoupper($a->class_language) }}</span>
                                @endif
                            </td>
                            <td>{{ $a->required ? 'Required' : 'Optional' }}</td>
                            <td>{{ $a->start_date }}</td>
                            <td>{{ $a->end_date }}</td>
                            <td>{{ $a->done }} of {{ $a->students }} students</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.engagement.easy-activities.unassign', $a->id) }}" onsubmit="return confirm(@js('Remove "' . $activity->title . '" from ' . $a->label . '?'))">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">Not assigned to any class yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <p class="text-muted">No activities here yet. Create the first one above.</p>
    @endforelse
</div>
@endsection
