@extends('layouts.admin')

@section('title', 'Intermediate setup')

@section('content')
    <div class="adminHeading">
        <h1 class="d-flex align-items-center gap-2 theme-text-dark fw-500 fs-16 mab-20">Intermediate setup @include('admin.partials.level-badge')</h1>
    </div>
    {{-- Daniel, 2026-11-08: the scenario per student, on its own ("debería hacerse por estudiante, esto puede ser
         mucho trabajo"); a new account gets B (App\Services\IntermediateClassService). --}}
    <p class="text-muted mab-15">
        Each student's scenario is set on its own from their bank. <strong>A</strong>: they bring an Emergency Fund with money
        from the Rookie. <strong>B</strong>: they start without one (a new account, or nothing saved); they open it in their
        first month and their emergencies come later, counted from when they arrived. The level's months (the price rise in
        month 2, the emergencies…) count from the month the class started it: the start of the school year unless you set
        another month here.
    </p>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="themeCard card card-custom">
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Started the Intermediate in</th>
                        <th>Month now</th>
                        <th>A: with a fund from the Rookie</th>
                        <th>B: no fund yet</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $class)
                        <tr>
                            <td>{{ \App\Support\ClassLabel::make($class->grade->name ?? null, $class->name) }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.intermediate.setup.update', $class) }}" class="d-flex gap-2 align-items-center">
                                    @csrf
                                    <input type="month" name="intermediate_started_on" class="form-control form-control-sm" style="width:auto"
                                           value="{{ $class->level_start->format('Y-m') }}" required>
                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                </form>
                                @unless ($class->intermediate_started_on)
                                    <div class="small text-muted">The start of the school year</div>
                                @endunless
                            </td>
                            <td>{{ $class->level_month ? 'Month ' . $class->level_month : '—' }}</td>
                            @foreach (['A', 'B'] as $scenario)
                                <td>
                                    @php $students = $class->scenarios->get($scenario, collect()); @endphp
                                    <strong>{{ $students->count() }}</strong>
                                    @if ($students->isNotEmpty())
                                        <div class="small text-muted">{{ $students->pluck('display_name')->implode(', ') }}</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No Intermediate classes. A class is Intermediate when its grade's level is (Grades).</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
