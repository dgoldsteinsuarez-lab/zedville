{{-- Extra choices for a Class (or Grade) select on the activity screens:
     assign to every class of the school being managed, or of every school
     (super admins). See App\Support\AssignmentScope. $noun: 'classes' or 'grades'.
     On a screen of one level (the menu by level), "every class" is every class
     of that level (App\Support\AdminLevel). --}}
@php
    $noun = $noun ?? 'classes';
    $scopeLevel = \App\Support\AdminLevel::current();
    $scopeNoun = $scopeLevel ? $scopeLevel->name . ' ' . $noun : $noun;
@endphp
@if (session('selected_school') || !auth()->user()->isSuperAdmin())
    <option value="{{ \App\Support\AssignmentScope::SCHOOL }}">★ All {{ $scopeNoun }} in this school</option>
@endif
@if (\App\Support\AssignmentScope::canUseAll())
    <option value="{{ \App\Support\AssignmentScope::ALL }}">★ All {{ $scopeNoun }} in every school</option>
@endif
