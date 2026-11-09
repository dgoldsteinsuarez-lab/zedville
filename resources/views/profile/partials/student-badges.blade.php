{{-- The Badges tab (Daniel, 2026-11-02: "if someone gets a Badge for last month, he should be able to see it
     in the main part ... we should make the website amazing and the kids should be proud of having a badge").
     A badge is worked out when its month ends, so the big medal is the latest badge earned - normally last
     month's - with a celebration the first time it is seen; then this month so far and what the next badge
     needs, the academic year's badge and the ten months. Data: App\Services\BadgeOverview. --}}
@php
    $bdgLabel = fn (string $type, ?string $badge) => !$badge || $badge === 'NONE'
        ? __('badges.js.no_badge_yet')
        : __('badges.js.' . ($type === 'engagement' ? 'levels' : 'fh_levels') . '.' . strtolower($badge));
    $bdgSymbol = fn (string $type, ?string $badge) => '#m-' . (!$badge || $badge === 'NONE' ? ($type === 'engagement' ? 'eng-none' : 'fh-none') : strtolower($badge));
    $bdgPraise = fn (string $type, ?string $badge) => __('badges.js.' . ($type === 'engagement' ? 'praise' : 'fh_praise') . '.' . strtolower($badge ?: 'none'));
    $bdgMonth = fn ($date, string $format = 'MMMM YYYY') => $date->copy()->locale(app()->getLocale())->isoFormat($format);
    $bdgName = fn (string $type) => $type === 'engagement' ? __('badges.engagement') : 'FinHero';
    $bdgPct = fn (float $value) => \App\Support\Money::number($value, $value == floor($value) ? 0 : 1) . (app()->getLocale() === 'en' ? '%' : "\u{00A0}%");
    // last month's new badges: the celebration
    $bdgNew = collect($badges)->filter(fn ($b) => $b['latest'] && $b['latest']['last_month']);
@endphp
<style>
  .bdg { --bdg-ink: #0F2A22; --bdg-text: #33443E; --bdg-muted: #5E7069; --bdg-line: #D2DDDB; --bdg-soft: #F2F7F5; --bdg-green: #00A47D; --bdg-deep: #016950;
    max-width: 920px; margin: 0 auto; padding: 1.5rem 1rem 3rem; color: var(--bdg-text); }
  .bdg-head { text-align: center; margin-bottom: 1.25rem; }
  .bdg-head h2 { margin: 0 0 4px; font: 800 26px/1.2 "Manrope", sans-serif; color: var(--bdg-ink); text-transform: none; }
  .bdg-head p { margin: 0; font-size: 15px; color: var(--bdg-muted); }

  /* the celebration */
  .bdg-party { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; padding: 14px 18px; border-radius: 16px; color: #fff;
    background: linear-gradient(120deg, #016950, #00A47D 55%, #34D399); box-shadow: 0 10px 28px rgba(1, 105, 80, .25); }
  .bdg-party-ico { font-size: 34px; line-height: 1; }
  .bdg-party strong { display: block; font: 800 18px/1.3 "Manrope", sans-serif; }
  .bdg-party span { font-size: 15px; opacity: .95; }
  .bdg-confetti { position: fixed; inset: 0; pointer-events: none; z-index: 70; overflow: hidden; }
  .bdg-confetti i { position: absolute; top: -12px; width: 9px; height: 14px; border-radius: 2px; opacity: .9; animation: bdg-fall 2.8s cubic-bezier(.25, .6, .4, 1) forwards; }
  @keyframes bdg-fall { to { transform: translate3d(var(--dx), 105vh, 0) rotate(var(--rot)); opacity: 0; } }

  /* the latest badges, big */
  .bdg-heroes { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
  .bdg-hero { position: relative; overflow: hidden; padding: 22px 20px 18px; border-radius: 20px; text-align: center; background: #fff; border: 1px solid var(--bdg-line); }
  .bdg-hero.is-earned { border: 2px solid #F6C453; background: radial-gradient(circle at 50% 28%, #FFF8E1 0, #FFFFFF 62%); box-shadow: 0 12px 32px rgba(214, 158, 46, .18); }
  .bdg-hero--finhero.is-earned { border-color: #6EE7B7; background: radial-gradient(circle at 50% 28%, #E6FBF3 0, #FFFFFF 62%); box-shadow: 0 12px 32px rgba(0, 164, 125, .18); }
  .bdg-kicker { margin: 0 0 6px; font-size: 13px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: var(--bdg-muted); }
  .bdg-stage { position: relative; width: 170px; height: 170px; margin: 0 auto 6px; display: grid; place-items: center; }
  .bdg-rays { position: absolute; inset: -10px; border-radius: 50%;
    background: repeating-conic-gradient(from 0deg, rgba(246, 196, 83, .35) 0 10deg, transparent 10deg 30deg); animation: bdg-spin 18s linear infinite;
    -webkit-mask: radial-gradient(circle, #000 35%, transparent 70%); mask: radial-gradient(circle, #000 35%, transparent 70%); }
  .bdg-hero--finhero .bdg-rays { background: repeating-conic-gradient(from 0deg, rgba(52, 211, 153, .32) 0 10deg, transparent 10deg 30deg); }
  @keyframes bdg-spin { to { transform: rotate(360deg); } }
  .bdg-medal { position: relative; width: 128px; height: 138px; filter: drop-shadow(0 8px 14px rgba(15, 42, 34, .18)); }
  .bdg-hero.is-earned .bdg-medal { animation: bdg-pop .9s cubic-bezier(.2, 1.4, .4, 1) both; }
  @keyframes bdg-pop { from { transform: scale(.4) rotate(-12deg); opacity: 0; } to { transform: none; opacity: 1; } }
  .bdg-spark { position: absolute; width: 10px; height: 10px; background: #FDE68A; clip-path: polygon(50% 0, 62% 38%, 100% 50%, 62% 62%, 50% 100%, 38% 62%, 0 50%, 38% 38%);
    animation: bdg-twinkle 1.8s ease-in-out infinite; }
  .bdg-spark:nth-of-type(2) { top: 18%; left: 12%; animation-delay: .3s; } .bdg-spark:nth-of-type(3) { top: 10%; right: 16%; animation-delay: .9s; width: 14px; height: 14px; }
  .bdg-spark:nth-of-type(4) { bottom: 16%; right: 8%; animation-delay: 1.3s; } .bdg-spark:nth-of-type(5) { bottom: 22%; left: 6%; animation-delay: .6s; width: 8px; height: 8px; }
  @keyframes bdg-twinkle { 0%, 100% { transform: scale(.3); opacity: .2; } 50% { transform: scale(1); opacity: 1; } }
  .bdg-ribbon { display: inline-block; margin: 0 0 6px; padding: 4px 14px; border-radius: 999px; background: var(--bdg-ink); color: #fff; font-size: 13px; font-weight: 800; letter-spacing: .04em; }
  .bdg-name { margin: 0; font: 800 26px/1.2 "Manrope", sans-serif; color: var(--bdg-ink); text-transform: none; }
  .bdg-when { margin: 2px 0 8px; font-size: 14px; font-weight: 700; color: var(--bdg-deep); text-transform: capitalize; }
  .bdg-praise { margin: 0 auto; max-width: 340px; font-size: 15px; line-height: 1.5; font-style: italic; color: var(--bdg-text); }
  .bdg-year { display: flex; align-items: center; gap: 12px; margin-top: 16px; padding: 10px 12px; border-radius: 14px; background: var(--bdg-soft); text-align: left; }
  .bdg-year svg { flex: none; width: 34px; height: 37px; }
  .bdg-year strong { display: block; font-size: 14px; color: var(--bdg-ink); }
  .bdg-year span { font-size: 13px; color: var(--bdg-muted); }
  .bdg-year-bar { height: 6px; margin-top: 5px; border-radius: 99px; background: #DDE6E3; overflow: hidden; }
  .bdg-year-bar i { display: block; height: 100%; border-radius: 99px; background: var(--bdg-green); }

  /* this month */
  .bdg-month { margin-top: 18px; padding: 18px 20px; border-radius: 20px; background: #fff; border: 1px solid var(--bdg-line); }
  .bdg-month h3 { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: 0 0 12px; font: 800 18px/1.3 "Manrope", sans-serif; color: var(--bdg-ink); text-transform: none; }
  .bdg-month h3 span.bdg-date { text-transform: capitalize; }
  .bdg-pill { padding: 2px 10px; border-radius: 999px; background: #FFF4D6; color: #9A6400; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
  .bdg-month-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
  .bdg-col { padding: 14px; border-radius: 14px; background: var(--bdg-soft); }
  .bdg-col h4 { display: flex; align-items: center; gap: 8px; margin: 0 0 10px; font: 800 15px/1.3 "Manrope", sans-serif; color: var(--bdg-ink); text-transform: none; }
  .bdg-col h4 svg { width: 26px; height: 28px; }
  .bdg-checks { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 8px; }
  .bdg-checks li { display: grid; grid-template-columns: 24px 1fr auto; align-items: center; gap: 8px; font-size: 14px; }
  .bdg-checks li > span:first-child { width: 22px; height: 22px; display: grid; place-items: center; border-radius: 99px; background: #fff; border: 2px solid var(--bdg-line); font-size: 12px; font-weight: 800; color: #fff; }
  .bdg-checks li.is-done > span:first-child { background: var(--bdg-green); border-color: var(--bdg-green); }
  .bdg-checks li.is-none > span:first-child { color: var(--bdg-muted); }
  .bdg-checks li.is-none b { font-weight: 600; color: var(--bdg-muted); }
  .bdg-checks b { color: var(--bdg-ink); font-variant-numeric: tabular-nums; }
  .bdg-bar { position: relative; height: 10px; margin: 8px 0 4px; border-radius: 99px; background: #DDE6E3; }
  .bdg-bar > i { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #00A47D, #34D399); }
  .bdg-tick { position: absolute; top: -3px; width: 2px; height: 16px; background: var(--bdg-ink); opacity: .35; }
  .bdg-ticks { position: relative; height: 18px; font-size: 12px; color: var(--bdg-muted); }
  .bdg-ticks span { position: absolute; transform: translateX(-50%); white-space: nowrap; }
  .bdg-sofar { margin: 10px 0 0; padding: 8px 10px; border-radius: 10px; background: #E2F6EF; color: #014A38; font-size: 14px; font-weight: 700; }
  .bdg-next { margin: 10px 0 0; font-size: 14px; line-height: 1.45; color: var(--bdg-text); }
  .bdg-next strong { color: var(--bdg-ink); }
  .bdg-how { margin-top: 10px; font-size: 14px; }
  .bdg-how summary { cursor: pointer; font-weight: 700; color: var(--bdg-deep); }
  .bdg-how ul { margin: 6px 0 0; padding-left: 18px; list-style: disc; line-height: 1.6; }

  /* month by month */
  .bdg-history { margin-top: 18px; padding: 18px 20px; border-radius: 20px; background: #fff; border: 1px solid var(--bdg-line); }
  .bdg-history h3 { display: flex; justify-content: space-between; margin: 0 0 12px; font: 800 16px/1.3 "Manrope", sans-serif; color: var(--bdg-ink); text-transform: none; }
  .bdg-history h3 span { font-weight: 600; color: var(--bdg-muted); font-size: 14px; }
  .bdg-table { overflow-x: auto; }
  .bdg-row { display: grid; grid-template-columns: 110px repeat(10, minmax(54px, 1fr)); align-items: center; min-width: 660px; }
  .bdg-row + .bdg-row { border-top: 1px solid #EEF3F1; }
  .bdg-row > div { padding: 8px 2px; text-align: center; font-size: 12px; color: var(--bdg-muted); }
  .bdg-row > div:first-child { text-align: left; font-size: 14px; font-weight: 800; color: var(--bdg-ink); }
  .bdg-row.is-head > div { font-weight: 800; text-transform: uppercase; letter-spacing: .06em; }
  .bdg-row > div.is-current { background: #FFFBF2; border-radius: 10px; color: #9A6400; }
  .bdg-row > div.is-future { opacity: .4; }
  .bdg-cell-medal { display: block; width: 34px; height: 37px; margin: 0 auto 2px; }
  .bdg-cell-label { display: block; font-weight: 700; color: var(--bdg-ink); }
  .bdg-dot { display: inline-block; width: 8px; height: 8px; border-radius: 99px; background: #D2DDDB; }
  .bdg-row > div.is-current .bdg-dot { background: #F59E0B; box-shadow: 0 0 0 4px rgba(245, 158, 11, .2); }
  @media (prefers-reduced-motion: reduce) { .bdg-rays, .bdg-spark, .bdg-hero.is-earned .bdg-medal, .bdg-confetti i { animation: none !important; } .bdg-confetti { display: none; } }
</style>

@include('profile.partials.badge-medals')

<div class="bdg">
  <div class="bdg-head">
    <h2>{{ __('badges.my_badges') }}</h2>
    <p>{{ __('badges.tab_intro') }}</p>
  </div>

  {{-- last month's new badges: well done! (confetti the first time - and again if a badge goes up) --}}
  @if ($bdgNew->isNotEmpty())
    @php $bdgFirst = $bdgNew->first()['latest']['month']; @endphp
    <div class="bdg-party" id="bdg-party" data-key="zv-badges-{{ auth()->id() }}-{{ $bdgFirst->format('Y-m') }}-{{ strtolower($bdgNew->map(fn ($b) => $b['latest']['badge'])->implode('-')) }}">
      <span class="bdg-party-ico" aria-hidden="true">🎉</span>
      <div>
        <strong>{{ __('badges.celebrate', ['month' => $bdgMonth($bdgFirst, 'MMMM')]) }}</strong>
        <span>{{ $bdgNew->map(fn ($b) => $bdgName($b['type']) . ': ' . $bdgLabel($b['type'], $b['latest']['badge']))->implode(' · ') }}</span>
      </div>
    </div>
  @endif

  {{-- the latest badge of each kind, big --}}
  <div class="bdg-heroes">
    @foreach ($badges as $type => $b)
      @php $latest = $b['latest']; $shown = $latest['badge'] ?? 'NONE'; @endphp
      <article class="bdg-hero bdg-hero--{{ $type }} {{ $latest ? 'is-earned' : '' }}" aria-label="{{ $bdgName($type) }}">
        <p class="bdg-kicker">{{ $bdgName($type) }}</p>
        <div class="bdg-stage">
          @if ($latest)<span class="bdg-rays" aria-hidden="true"></span>@endif
          <svg class="bdg-medal" viewBox="0 0 120 130" role="img" aria-label="{{ $bdgLabel($type, $shown) }}"><use href="{{ $bdgSymbol($type, $shown) }}"/></svg>
          @if ($latest)
            @for ($s = 0; $s < 4; $s++)<span class="bdg-spark" aria-hidden="true"></span>@endfor
          @endif
        </div>
        @if ($latest)
          <p class="bdg-ribbon">{{ $latest['last_month'] ? __('badges.last_month') : __('badges.latest') }}</p>
          <h3 class="bdg-name">{{ $bdgLabel($type, $shown) }}</h3>
          <p class="bdg-when">{{ $bdgMonth($latest['month']) }}</p>
        @else
          <h3 class="bdg-name">{{ __('badges.js.no_badge_yet') }}</h3>
          <p class="bdg-when">&nbsp;</p>
        @endif
        <p class="bdg-praise">{{ $bdgPraise($type, $shown) }}</p>

        {{-- the academic year's badge --}}
        @php $year = $b['year']; @endphp
        <div class="bdg-year">
          <svg viewBox="0 0 120 130" aria-hidden="true"><use href="{{ $bdgSymbol($type, $year['badge']) }}"/></svg>
          <div style="flex:1;min-width:0">
            <strong>{{ __('badges.year_title') }}: {{ $bdgLabel($type, $year['badge']) }}</strong>
            @if ($year['next'])
              <span>{{ __('badges.year_next', ['points' => $year['points'], 'total' => $year['next']['points'], 'badge' => $bdgLabel($type, $year['next']['badge'])]) }}</span>
              <div class="bdg-year-bar"><i style="width:{{ min(100, round($year['points'] / $year['next']['points'] * 100)) }}%"></i></div>
            @else
              <span>{{ __('badges.year_top') }}</span>
            @endif
          </div>
        </div>
      </article>
    @endforeach
  </div>

  {{-- this month so far, and what the next badge needs --}}
  <section class="bdg-month" aria-labelledby="bdg-month-title">
    <h3 id="bdg-month-title">{{ __('badges.this_month') }} · <span class="bdg-date">{{ $bdgMonth(now()) }}</span> <span class="bdg-pill">{{ __('badges.in_progress') }}</span></h3>
    <div class="bdg-month-grid">
      @php $e = $badges['engagement']['current']; @endphp
      <div class="bdg-col">
        <h4><svg viewBox="0 0 120 130" aria-hidden="true"><use href="{{ $bdgSymbol('engagement', $e['badge']) }}"/></svg>{{ __('badges.engagement') }}</h4>
        @if ($e['required_assigned'] === 0)
          <p class="bdg-next" style="margin-top:0">{{ __('badges.no_required') }}</p>
        @else
          <ul class="bdg-checks">
            <li class="{{ $e['required_completed'] >= $e['required_assigned'] ? 'is-done' : '' }}"><span aria-hidden="true">✓</span>{{ __('badges.required_activities') }}
              <b>{{ __('badges.of', ['done' => $e['required_completed'], 'total' => $e['required_assigned']]) }}</b></li>
            {{-- none on the class calendar: not the student's fault - Silver needs none, and Gold takes activities done on their own --}}
            @php $eOffered = max($e['optional_assigned'], $e['optional_completed']); @endphp
            <li class="{{ $eOffered === 0 ? 'is-none' : ($e['optional_completed'] > 0 ? 'is-done' : '') }}"><span aria-hidden="true">{{ $eOffered === 0 ? '–' : '✓' }}</span>{{ __('badges.optional_activities') }}
              <b>{{ $eOffered === 0 ? __('badges.none_this_month') : __('badges.of', ['done' => $e['optional_completed'], 'total' => $eOffered]) }}</b></li>
            @if ($e['optional_assigned'] === 0)
              <li class="{{ $e['own_completed'] > 0 ? 'is-done' : '' }}"><span aria-hidden="true">✓</span>{{ __('badges.own_activities') }}
                <b>{{ $e['own_completed'] }}</b></li>
            @endif
            <li class="{{ $e['participation_points'] >= \App\Services\BadgeCalculatorService::PARTICIPATION_THRESHOLDS['BRONZE'] ? 'is-done' : '' }}"><span aria-hidden="true">✓</span>{{ __('badges.points_title') }}
              <b>{{ __('badges.of', ['done' => $e['participation_points'], 'total' => $e['points_max']]) }}</b></li>
          </ul>
          <div class="bdg-bar" aria-hidden="true"><i style="width:{{ min(100, round($e['participation_points'] / $e['points_max'] * 100)) }}%"></i></div>
          @if ($e['badge'] !== 'NONE')
            <p class="bdg-sofar">🎉 {{ __('badges.so_far', ['badge' => $bdgLabel('engagement', $e['badge'])]) }}</p>
          @endif
          @if ($e['next'])
            <p class="bdg-next"><strong>{{ __('badges.next', ['badge' => $bdgLabel('engagement', $e['next']['badge'])]) }}</strong><br>
              {{ __('badges.still_to_do') }}
              {{ collect([
                  $e['next']['required'] ? trans_choice('badges.missing_required', $e['next']['required'], ['count' => $e['next']['required']]) : null,
                  $e['next']['optional'] ? trans_choice('badges.missing_optional', $e['next']['optional'], ['count' => $e['next']['optional']]) : null,
                  $e['next']['own'] ? trans_choice('badges.missing_own', $e['next']['own'], ['count' => $e['next']['own']]) : null,
                  $e['next']['points'] ? trans_choice('badges.missing_points', $e['next']['points'], ['count' => $e['next']['points']]) : null,
              ])->filter()->implode(' · ') }}</p>
          @elseif ($e['badge'] === 'PLATINUM')
            <p class="bdg-sofar">🏆 {{ __('badges.top_month') }}</p>
          @else
            <p class="bdg-sofar">🏆 {{ trans_choice('badges.top_possible', $e['optional_assigned'], ['count' => $e['optional_assigned']]) }}</p>
          @endif
        @endif
        <details class="bdg-how">
          <summary>{{ __('badges.how_title') }}</summary>
          <ul>@foreach (['bronze', 'silver', 'gold', 'platinum', 'no_optional', 'own'] as $level)<li>{{ __('badges.how_eng.' . $level) }}</li>@endforeach</ul>
        </details>
      </div>

      @php $f = $badges['finhero']['current']; @endphp
      <div class="bdg-col">
        <h4><svg viewBox="0 0 120 130" aria-hidden="true"><use href="{{ $bdgSymbol('finhero', $f['badge']) }}"/></svg>FinHero</h4>
        <p class="bdg-next" style="margin-top:0"><strong>{{ __('badges.fh_pct', ['pct' => $bdgPct($f['pct'])]) }}</strong>
          ({{ __('badges.of', ['done' => $f['earned'], 'total' => $f['available']]) }})</p>
        <div class="bdg-bar" aria-hidden="true">
          <i style="width:{{ min(100, $f['pct']) }}%"></i>
          @foreach ($f['thresholds'] as $level => $needed)<span class="bdg-tick" style="left:{{ $needed }}%"></span>@endforeach
        </div>
        <div class="bdg-ticks" aria-hidden="true">
          @foreach ($f['thresholds'] as $level => $needed)<span style="left:{{ $needed }}%">{{ $bdgPct($needed) }}</span>@endforeach
        </div>
        @if ($f['badge'] !== 'NONE')
          <p class="bdg-sofar">🎉 {{ __('badges.so_far', ['badge' => $bdgLabel('finhero', $f['badge'])]) }}</p>
        @endif
        @if ($f['next'])
          <p class="bdg-next"><strong>{{ __('badges.fh_next', ['badge' => $bdgLabel('finhero', $f['next']['badge']), 'pct' => $bdgPct($f['next']['pct'])]) }}</strong></p>
        @else
          <p class="bdg-sofar">🏆 {{ __('badges.top_month') }}</p>
        @endif
        <details class="bdg-how">
          <summary>{{ __('badges.how_title') }}</summary>
          <p style="margin:6px 0 0">{{ __('badges.how_fh') }}</p>
          <ul>@foreach ($f['thresholds'] as $level => $needed)<li>{{ __('badges.how_fh_level', ['badge' => $bdgLabel('finhero', $level), 'pct' => $bdgPct($needed)]) }}</li>@endforeach</ul>
        </details>
      </div>
    </div>
  </section>

  {{-- the ten months --}}
  <section class="bdg-history" aria-labelledby="bdg-history-title">
    <h3 id="bdg-history-title">{{ __('badges.month_by_month') }} <span>{{ $badges['engagement']['months'][0]['month']->year }}–{{ $badges['engagement']['months'][0]['month']->year + 1 }}</span></h3>
    <div class="bdg-table">
      <div class="bdg-row is-head">
        <div></div>
        @foreach ($badges['engagement']['months'] as $m)
          <div class="{{ $m['state'] === 'current' ? 'is-current' : ($m['state'] === 'future' ? 'is-future' : '') }}">{{ $bdgMonth($m['month'], 'MMM') }}</div>
        @endforeach
      </div>
      @foreach ($badges as $type => $b)
        <div class="bdg-row">
          <div>{{ $bdgName($type) }}</div>
          @foreach ($b['months'] as $m)
            <div class="{{ $m['state'] === 'current' ? 'is-current' : ($m['state'] === 'future' ? 'is-future' : '') }}">
              @if ($m['state'] !== 'future' && $m['badge'] && $m['badge'] !== 'NONE')
                <svg class="bdg-cell-medal" viewBox="0 0 120 130" aria-hidden="true"><use href="{{ $bdgSymbol($type, $m['badge']) }}"/></svg>
                <span class="bdg-cell-label">{{ $bdgLabel($type, $m['badge']) }}</span>
              @else
                <span class="bdg-dot" title="{{ $m['state'] === 'current' ? __('badges.in_progress') : __('badges.js.no_badge_yet') }}"></span>
              @endif
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  </section>
</div>

<script>
  // Confetti, once, the first time a new badge from last month is seen (prefers-reduced-motion: none).
  (function () {
    const party = document.getElementById('bdg-party');
    if (!party || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const seen = () => { try { return !!localStorage.getItem(party.dataset.key); } catch (e) { return false; } };
    const start = () => {
      if (seen()) return;
      try { localStorage.setItem(party.dataset.key, '1'); } catch (e) {}
      const box = document.createElement('div');
      box.className = 'bdg-confetti';
      const colours = ['#F6C453', '#00A47D', '#2a78d6', '#eb6834', '#34D399', '#e34948', '#6250d6'];
      for (let i = 0; i < 90; i++) {
        const bit = document.createElement('i');
        bit.style.left = Math.random() * 100 + 'vw';
        bit.style.background = colours[i % colours.length];
        bit.style.animationDelay = Math.random() * 0.8 + 's';
        bit.style.setProperty('--dx', (Math.random() * 160 - 80) + 'px');
        bit.style.setProperty('--rot', (Math.random() * 720 - 360) + 'deg');
        box.appendChild(bit);
      }
      document.body.appendChild(box);
      setTimeout(() => box.remove(), 4200);
    };
    // the Badges tab may be hidden until it is opened
    if (party.offsetParent) start();
    else document.querySelectorAll('[data-tab]').forEach((tab) => tab.addEventListener('click', () => setTimeout(() => { if (party.offsetParent) start(); }, 80)));
  })();
</script>
