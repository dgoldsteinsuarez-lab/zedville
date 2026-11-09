{{-- Easy activities done in the bank, while they run for the student's class
     (Admin > Engagement > Easy Activities): what to do, step by step, or that
     it's done. $link: show the button to Pay & transfer. --}}
@foreach ($easyActivities as $activity)
    <section class="zb-card zb-task{{ $activity->done ? ' is-done' : '' }}" aria-labelledby="zb-task-{{ $activity->id }}">
        <p class="zb-task-tag">
            {{ __('engagement.easy_tag') }} · {{ __('engagement.easy_' . $activity->position) }} · {{ __('engagement.easy_badge', ['badge' => __('badges.engagement')]) }}
        </p>
        <h2 id="zb-task-{{ $activity->id }}">{{ $activity->studentTitle() }}</h2>
        @if ($activity->done)
            <p class="zb-task-done">✓ {{ __('engagement.easy_done') }}</p>
        @else
            <ol class="zb-task-steps">
                @foreach ($activity->steps() as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ol>
            <div class="zb-task-foot">
                @if ($activity->until)
                    <span class="zb-small">{{ __('engagement.easy_until', ['date' => \Carbon\Carbon::parse($activity->until)->locale(app()->getLocale())->isoFormat('LL')]) }}</span>
                @endif
                @if ($link ?? false)
                    <a class="themeBtn" href="{{ $activity->studentUrl() }}">{{ __('engagement.easy_start') }} →</a>
                @endif
            </div>
        @endif
    </section>
@endforeach
