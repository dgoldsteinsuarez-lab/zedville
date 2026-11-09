{{-- The level an activity screen works on (the menu by level, App\Support\AdminLevel). --}}
@php $workingLevel = \App\Support\AdminLevel::current(); @endphp
<span class="badge {{ $workingLevel ? 'bg-primary' : 'bg-secondary' }}" style="font-size:13px" title="{{ $workingLevel ? 'Only this level\'s activities and classes are shown. Pick another level in the menu.' : 'Every level\'s activities and classes are shown.' }}">
    {{ $workingLevel ? 'Level: ' . $workingLevel->name : 'All levels' }}
</span>
