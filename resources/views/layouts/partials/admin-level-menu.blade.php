{{-- The Admin menu by level (Daniel, 2026-11-02: "hay que dividir ya las actividades que son
     para cada nivel rookie, intermedio, etc. tendrás que reorganizar el Admin" - he chose a
     menu by level): one group per level, each with every activity screen, opened on that
     level (?level=<id>, App\Support\AdminLevel). Each screen shows that level's activities
     and assigns to that level's classes. Expects $navOrg and $navSuper from the layout. --}}
@php
    $levelPages = ['admin/education/spending-activity*', 'admin/spending/*', 'admin/education/monthly-budget-activity*',
        'admin/education/emergency-fund-account*', 'admin/education/donation*', 'admin/intermediate/*', 'admin/emergencies*','admin/finhero/activities*', 'admin/finhero/task-activities*', 'admin/login-question',
        'admin/engagement/*', 'admin/money-factory*', 'admin/hybrid/*'];
    $onLevelPage = request()->is(...$levelPages);
    $workingLevel = $onLevelPage ? (int) session(\App\Support\AdminLevel::SESSION) : 0;
    $levelColours = ['#14B8A6', '#22C55E', '#3B82F6', '#8B5CF6', '#334155'];
@endphp
@if($navOrg)
    @foreach (\App\Support\AdminLevel::all()->values() as $i => $level)
        @php
            $open = $workingLevel === (int) $level->id || request()->is('admin/curriculum/levels/' . $level->id . '/*');
            $at = fn (string $url) => $url . (str_contains($url, '?') ? '&' : '?') . 'level=' . $level->id;
            $here = fn (string ...$patterns) => $open && request()->is(...$patterns);
            $initials = collect(explode(' ', $level->name))->map(fn ($word) => mb_substr($word, 0, 1))->implode('');
        @endphp
        <li class="nav-item {{ $open ? 'active' : '' }}" data-bs-toggle="tooltip" data-bs-custom-class="sidebarTooltip" data-bs-placement="right" data-bs-title="{{ $level->name }}">
            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#levelNav{{ $level->id }}" aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="sideNavbar">
                <span class="navIcon">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" width="24px" viewBox="0 0 24 24"><rect width="24" height="24" rx="5" fill="{{ $levelColours[$i] ?? '#334155' }}"/><text x="12" y="16.5" text-anchor="middle" font-family="Arial, sans-serif" font-size="{{ mb_strlen($initials) > 1 ? 10 : 13 }}" font-weight="700" fill="#fff">{{ $initials }}</text></svg>
                </span>
                <span class="navTxt">{{ $level->name }}</span>
                <span class="nav-arrow">
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.07129 1.62756L6.00033 6.55661L10.9294 1.62756" stroke="currentColor" stroke-width="1.4" stroke-linecap="square"/></svg>
                </span>
            </a>
            <div id="levelNav{{ $level->id }}" class="collapse {{ $open ? 'show' : '' }} collapseNavItems" data-bs-parent="#toggleNavsubnav">
                <ul class="subnav">
                    <li class="nav-item {{ $here('admin/education/spending-activity*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(url('admin/education/spending-activity')) }}"><span class="navTxt">Spending activities</span></a></li>
                    @if($navSuper)
                        <li class="nav-item {{ $here('admin/spending/shopping-lists*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(route('admin.spending.lists')) }}"><span class="navTxt">Shopping lists</span></a></li>
                        <li class="nav-item {{ $here('admin/spending/plans*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(route('admin.spending.plans')) }}"><span class="navTxt">Spending 4</span></a></li>
                    @endif
                    <li class="nav-item {{ $here('admin/education/emergency-fund-account*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(url('admin/education/emergency-fund-account')) }}"><span class="navTxt">Emergency Fund</span></a></li>
                    <li class="nav-item {{ $here('admin/education/donation*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(route('admin.education.donation')) }}"><span class="navTxt">Monthly Donation</span></a></li>
                    @if ((int) $level->sort_order === \App\Support\Level::INTERMEDIATE)
                        <li class="nav-item {{ $here('admin/intermediate/*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(route('admin.intermediate.setup')) }}"><span class="navTxt">Intermediate setup</span></a></li>
                    @endif
                    @if ((int) $level->sort_order >= \App\Support\Level::INTERMEDIATE)
                        {{-- the unexpected expenses (App\Services\EmergencyService) --}}
                        <li class="nav-item {{ $here('admin/emergencies*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(route('admin.emergencies.index')) }}"><span class="navTxt">Emergencies</span></a></li>
                    @endif
                    {{-- Needs, Wants & Savings Match is a tab of FinHero tasks, not an entry of its own (Daniel, 2026-10-31 and 2026-11-03) --}}
                    <li class="nav-item {{ $here('admin/finhero/activities*', 'admin/education/monthly-budget-activity*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(url('admin/finhero/activities')) }}"><span class="navTxt">FinHero tasks</span></a></li>
                    <li class="nav-item {{ $here('admin/finhero/task-activities*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(url('admin/finhero/task-activities')) }}"><span class="navTxt">Library</span></a></li>
                    <li class="nav-item {{ $here('admin/login-question') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(url('admin/login-question')) }}"><span class="navTxt">Daily questions</span></a></li>
                    <li class="nav-item {{ $here('admin/engagement/substitute-goods*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(route('admin.engagement.substitute-goods')) }}"><span class="navTxt">Substitute goods</span></a></li>
                    <li class="nav-item {{ $here('admin/engagement/easy-activities*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(route('admin.engagement.easy-activities')) }}"><span class="navTxt">Transfer money to a friend</span></a></li>
                    <li class="nav-item {{ $here('admin/money-factory*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(route('admin.money-factory')) }}"><span class="navTxt">Money Factory</span></a></li>
                    <li class="nav-item {{ $here('admin/hybrid/*') ? 'active' : '' }}"><a class="nav-link" href="{{ $at(route('admin.hybrid.activities')) }}"><span class="navTxt">Language activities (hybrid)</span></a></li>
                    @if($navSuper)
                        <li class="nav-item {{ request()->is('admin/curriculum/levels/' . $level->id . '/*') ? 'active' : '' }}"><a class="nav-link" href="{{ url('admin/curriculum/levels/' . $level->id . '/categories') }}"><span class="navTxt">Academy (curriculum)</span></a></li>
                    @endif
                </ul>
            </div>
        </li>
    @endforeach
@endif
