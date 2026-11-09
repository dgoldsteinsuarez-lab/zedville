@extends('layouts.admin')

@section('title', 'Grades')

@section('content')
<div class="container-fluid">
    <h2 class="mb-2">Grades</h2>
    <p class="text-muted">
        A Grade (e.g. "Grade 8") carries one curriculum Level and a language, shared by every Class underneath it
        (e.g. "8A", "8B"). Add a Grade below, then use <strong>+ Add Class</strong> on it to add its Classes - each
        Class starts in the Grade's language and can be given another one. Each Class gets its own student roster
        and Tutor - assign a Tutor from the <a href="{{ url('admin/staff') }}">School User</a> screen.
    </p>

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
        <div class="card-header"><strong>Add a Grade</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/grades') }}" class="row g-3">
                @csrf
                <div class="col-md-2">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Grade 8">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Level</label>
                    <select name="grade_level_id" class="form-control">
                        <option value="">Unassigned</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level->id }}" @selected(old('grade_level_id') == $level->id)>{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="newGradeLanguage">Language</label>
                    <select name="default_language" id="newGradeLanguage" class="form-control">
                        @foreach (config('zedville.languages') as $code => $languageName)
                            <option value="{{ $code }}" @selected(old('default_language', 'en') === $code)>{{ $languageName }}</option>
                        @endforeach
                    </select>
                    <div class="form-check mt-1">
                        <input type="checkbox" class="form-check-input" style="appearance: auto; -webkit-appearance: checkbox" name="language_mode" value="locked" id="newGradeLocked" @checked(old('language_mode') === 'locked')>
                        <label class="form-check-label small" for="newGradeLocked">Lock it (students can't switch)</label>
                    </div>
                </div>
                {{-- Daniel, 2026-10-31: "where can I add a grade with a language for A2/B1
                     level or hybrid here?" - right when the Grade is added, like the language. --}}
                <div class="col-md-3">
                    <label class="form-label" for="newGradeLanguageLevel">Language level</label>
                    <select name="language_level" id="newGradeLanguageLevel" class="form-control">
                        <option value="standard" @selected(old('language_level', 'standard') === 'standard')>Standard</option>
                        <option value="beginner" @selected(old('language_level') === 'beginner')>Beginner (A2/B1 - simpler wording)</option>
                        <option value="hybrid" @selected(old('language_level') === 'hybrid')>Hybrid (English + this language)</option>
                    </select>
                    <div class="form-text">Hybrid: words in the language, short texts in both, longer explanations in English.</div>
                </div>
                {{-- Daniel, 2026-10-31: a Grade can start in a different month from the school. --}}
                <div class="col-md-3">
                    <label class="form-label" for="newGradeStartMonth">Starts in</label>
                    <select name="start_month" id="newGradeStartMonth" class="form-control">
                        <option value="">Same as the school{{ $schoolStart ? ' (' . date('F', mktime(0, 0, 0, $schoolStart, 1)) . ')' : '' }}</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" @selected((int) old('start_month') === $i)>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                        @endfor
                    </select>
                    <div class="form-text">Month 1 of its questions.</div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Add Grade</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>All Grades</strong></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Name &amp; Level</th><th>Classes</th><th>Language</th><th>Starts in</th><th></th></tr></thead>
                <tbody>
                @forelse ($grades as $g)
                    <tr>
                        <td>
                            <form method="POST" action="{{ url('admin/grades/' . $g->id) }}" class="d-flex gap-2 align-items-center">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $g->name }}" class="form-control form-control-sm" style="max-width:200px">
                                <select name="grade_level_id" class="form-control form-control-sm" style="max-width:160px">
                                    <option value="">Unassigned</option>
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}" @selected($g->grade_level_id == $level->id)>{{ $level->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ url('admin/grades/' . $g->id . '/general-information') }}">
                                {{ $g->classes->count() }} {{ \Illuminate\Support\Str::plural('class', $g->classes->count()) }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ url('admin/grades/' . $g->id . '/general-information') }}#grade-language" title="Change the language for this Grade">
                                {{ config('zedville.languages')[$g->default_language ?? 'en'] ?? strtoupper($g->default_language ?? 'en') }}
                            </a>
                            @if (($g->language_mode ?? 'free') === 'locked')
                                <span class="badge bg-warning text-dark">&#128274; locked</span>
                            @endif
                            @if (($g->language_level ?? 'standard') === 'beginner')
                                <span class="badge bg-info text-dark">Beginner A2/B1</span>
                            @elseif (($g->language_level ?? 'standard') === 'hybrid')
                                <span class="badge bg-success">Hybrid + English</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ url('admin/grades/' . $g->id . '/general-information') }}#start-month" title="Change when this Grade starts">
                                @if ($g->start_month)
                                    {{ date('F', mktime(0, 0, 0, $g->start_month, 1)) }}
                                @else
                                    <span class="text-muted">School's{{ $schoolStart ? ' (' . date('F', mktime(0, 0, 0, $schoolStart, 1)) . ')' : '' }}</span>
                                @endif
                            </a>
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ url('admin/grades/' . $g->id . '/general-information') }}#add-class" class="btn btn-sm btn-success">+ Add Class</a>
                            <a href="{{ url('admin/grades/' . $g->id . '/general-information') }}" class="btn btn-sm btn-primary">Manage Classes</a>
                            <form method="POST" action="{{ url('admin/grades/' . $g->id) }}" onsubmit="return confirm('Delete this Grade? Its Classes must be empty/reassigned first.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted p-3">No Grades yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (auth()->user()->isSuperAdmin())
    {{-- Managing a level writes to AdminDashboardController::addGradeLevel()/
         updateGradeLevel()/deleteGradeLevel(), all Super-Admin-only (levels
         are a shared, global set - a School Admin assigns one to their own
         Grade above, but doesn't rename/add/remove the set itself). Gated
         here to match - the old copy of this card (moved from the Classes
         screen) wasn't, and would 403 silently for a School Admin on submit. --}}
    <div class="card mb-4">
        <div class="card-header"><strong>Grade levels</strong></div>
        <div class="card-body">
            <p class="text-muted">
                A fixed set of curriculum levels (e.g. "Rookie", "Intermediate") shared by every school -
                each Grade is assigned one.
            </p>
            <form action="{{ url('admin/grade-level/add') }}" method="post" class="d-flex gap-2 mab-15">
                @csrf
                <input class="form-control w-300px" type="text" name="name" placeholder="New level, e.g. Rookie" required maxlength="100">
                <button type="submit" class="themeBtn text-white">Add level</button>
            </form>
            @forelse ($levels as $level)
                <form action="{{ url('admin/grade-level/edit', $level->id) }}" method="post" class="d-flex gap-2 mab-10 align-items-center">
                    @csrf @method('PUT')
                    <input class="form-control w-300px" type="text" name="name" value="{{ $level->name }}" required maxlength="100">
                    <button type="submit" class="themeBtnGray theme-text-dark">Rename</button>
                    <button type="submit" form="del-level-{{ $level->id }}" class="secondaryBtn theme-text-mute"
                        onclick="return confirm('Remove this level? Grades using it become unassigned.')">Remove</button>
                </form>
                <form id="del-level-{{ $level->id }}" action="{{ url('admin/grade-level/delete', $level->id) }}" method="post">@csrf @method('DELETE')</form>
            @empty
                <p class="text-muted mb-0">No levels yet.</p>
            @endforelse
        </div>
    </div>
    @endif

</div>
@endsection
