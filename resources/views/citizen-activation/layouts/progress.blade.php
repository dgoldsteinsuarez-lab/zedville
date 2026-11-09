@php
    $step = $step ?? 1;
    $total = 6;
    $percent = round(($step / $total) * 100);
@endphp

<div class="progress-header">

    <div class="progress-label">
        <span>{{ __('citizen.progress', ['step' => $step, 'total' => $total]) }}</span>
        <span>{{ $percent }}%</span>
    </div>

    <div class="progress-track">

        @for($i = 1; $i <= $total; $i++)

            <div class="progress-seg
                @if($i < $step) done @endif
                @if($i == $step) active @endif">
            </div>

        @endfor

    </div>

</div>