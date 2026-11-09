{{--
    ZEDVILLE — FinHero Badge
    View: Student Badge Section (Badges tab in Settings)
    Place in: resources/views/finhero/index.blade.php
    
    NOTE: This renders inside the existing Badges tab 
    in the student's Account Settings page.
    IT should embed this as a section within that tab,
    alongside the Engagement Badge section.
--}}

<div class="space-y-8" id="finhero-section">

  {{-- Loading --}}
  <div id="fh-loading" class="text-center py-12 text-gray-400">
    <p>{{ __('finhero.badge.loading') }}</p>
  </div>

  <div id="fh-content" class="hidden space-y-6">

    {{-- Section header --}}
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-sm">FH</div>
      <h2 class="text-lg font-bold text-gray-800">{{ __('finhero.badge.title') }}</h2>
      <span class="text-xs text-gray-400 italic">{{ __('finhero.badge.subtitle') }}</span>
    </div>

    {{-- Top row: Monthly + Accumulated --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

      {{-- Monthly Badge --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">{{ __('finhero.badge.this_month') }}</div>
        <div class="flex items-center gap-4 mb-5">
          <div id="fh-monthly-emoji" class="text-5xl"></div>
          <div>
            <div id="fh-monthly-label" class="text-xl font-bold text-gray-800"></div>
            <div id="fh-monthly-pct" class="text-sm text-gray-400 mt-0.5"></div>
          </div>
        </div>

        {{-- Points breakdown --}}
        <div class="space-y-2 mb-4">
          <div class="flex justify-between text-xs text-gray-500">
            <span>{{ __('finhero.badge.daily_quiz') }}</span><span id="fh-quiz-pts"></span>
          </div>
          <div class="flex justify-between text-xs text-gray-500">
            <span>{{ __('finhero.badge.library_module') }}</span><span id="fh-library-pts"></span>
          </div>
          <div class="flex justify-between text-xs text-gray-500">
            <span>{{ __('finhero.badge.activities') }}</span><span id="fh-activity-pts"></span>
          </div>
          <div class="border-t border-gray-100 pt-2 flex justify-between text-xs font-semibold text-gray-700">
            <span>{{ __('finhero.badge.total') }}</span><span id="fh-total-pts"></span>
          </div>
        </div>

        {{-- Progress bar --}}
        <div class="w-full bg-gray-100 rounded-full h-2">
          <div id="fh-monthly-bar" class="bg-green-500 h-2 rounded-full transition-all duration-500" style="width:0%"></div>
        </div>
        <div class="text-xs text-gray-400 text-right mt-1" id="fh-monthly-bar-label"></div>
      </div>

      {{-- Accumulated Badge --}}
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">{{ __('finhero.badge.this_year') }}</div>
        <div class="flex items-center gap-4 mb-5">
          <div id="fh-acc-emoji" class="text-5xl"></div>
          <div>
            <div id="fh-acc-label" class="text-xl font-bold text-gray-800"></div>
            <div id="fh-acc-year" class="text-sm text-gray-400 mt-0.5"></div>
          </div>
        </div>

        {{-- Year progress bar --}}
        <div class="w-full bg-gray-100 rounded-full h-3 mb-1">
          <div id="fh-acc-bar" class="bg-indigo-500 h-3 rounded-full transition-all duration-700" style="width:0%"></div>
        </div>
        <div id="fh-acc-pts-label" class="text-xs text-gray-400 text-right"></div>

        {{-- Year milestone markers --}}
        <div class="flex justify-between text-xs text-gray-300 mt-2">
          <span>0</span>
          <span>{{ __('finhero.badge.milestone_rookie') }}</span>
          <span>{{ __('finhero.badge.milestone_finhero') }}</span>
          <span>{{ __('finhero.badge.milestone_champion') }}</span>
          <span>{{ __('finhero.badge.milestone_legend') }}</span>
          <span>40</span>
        </div>
      </div>
    </div>

    {{-- Month-by-month history --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
      <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">{{ __('finhero.badge.history') }}</div>
      <div id="fh-history" class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-10 gap-2">
        {{-- Filled by JS --}}
      </div>
    </div>

  </div>{{-- end fh-content --}}
</div>

<script>
(function() {
  // Texts in the student's language (lang/<language>/finhero.php).
  const T = {{ \Illuminate\Support\Js::from(__('finhero.badge')) }};
  const LANG = document.documentElement.lang || 'en';
  const fill = (text, params) => Object.keys(params).reduce((s, k) => s.split(':' + k).join(params[k]), text);
  const monthName = (m) => new Date(2000, m - 1, 1).toLocaleString(LANG, { month: 'short' });
  const BADGE_META = {
    LEGEND:   { emoji: '🏆', label: T.legend,   short: T.short_legend,   color: '#FFF9E6', text: '#7A5C00' },
    CHAMPION: { emoji: '⭐', label: T.champion, short: T.short_champion, color: '#F0F8FF', text: '#1F3864' },
    FINHERO:  { emoji: '💪', label: T.finhero,  short: T.short_finhero,  color: '#E8F5EE', text: '#1B6B3A' },
    ROOKIE:   { emoji: '🌱', label: T.rookie,   short: T.short_rookie,   color: '#F5F5F5', text: '#555555' },
    NONE:     { emoji: '—',  label: T.none,     short: T.none,           color: '#FFE8E8', text: '#AA0000' },
  };

  async function loadFinhero() {
    try {
      const res  = await fetch('/finhero-badge/data');
      const data = await res.json();
      render(data);
    } catch(e) {
      document.getElementById('fh-loading').textContent = T.load_error;
    }
  }

  function render(data) {
    const current = data.current_month;
    const badge   = current?.monthly_badge || 'NONE';
    const meta    = BADGE_META[badge];

    // Monthly
    document.getElementById('fh-monthly-emoji').textContent = meta.emoji;
    document.getElementById('fh-monthly-label').textContent = meta.label;
    document.getElementById('fh-monthly-pct').textContent   = current
      ? fill(T.earned_of, { earned: current.total_earned, available: current.total_available, pct: current.monthly_pct })
      : T.no_data;

    if (current) {
      document.getElementById('fh-quiz-pts').textContent     = `${current.quiz_pts} / 10`;
      document.getElementById('fh-library-pts').textContent  = `${current.library_pts} / 10`;
      document.getElementById('fh-activity-pts').textContent = fill(T.pts, { n: current.activity_pts });
      document.getElementById('fh-total-pts').textContent    = `${current.total_earned} / ${current.total_available}`;
      const pct = current.total_available > 0 ? Math.min(100, Math.round(current.total_earned / current.total_available * 100)) : 0;
      document.getElementById('fh-monthly-bar').style.width     = `${pct}%`;
      document.getElementById('fh-monthly-bar-label').textContent = `${pct}%`;
    }

    // Accumulated
    const accMeta = BADGE_META[data.accumulated_badge || 'NONE'];
    document.getElementById('fh-acc-emoji').textContent     = accMeta.emoji;
    document.getElementById('fh-acc-label').textContent     = accMeta.label;
    document.getElementById('fh-acc-year').textContent      = data.academic_year || '';
    document.getElementById('fh-acc-pts-label').textContent = fill(T.year_pts, { n: data.accumulated_points });
    document.getElementById('fh-acc-bar').style.width       = `${Math.min(100, Math.round(data.accumulated_points / 40 * 100))}%`;

    // History
    const historyEl = document.getElementById('fh-history');
    historyEl.innerHTML = '';
    if (!data.history?.length) {
      historyEl.innerHTML = `<p class="col-span-5 text-gray-400 text-sm">${T.no_history}</p>`;
    } else {
      data.history.forEach(r => {
        const m    = BADGE_META[r.monthly_badge || 'NONE'];
        const card = document.createElement('div');
        card.className = 'rounded-xl p-2 text-center border';
        card.style.backgroundColor = m.color;
        card.innerHTML = `
          <div class="text-xl mb-0.5">${m.emoji}</div>
          <div class="text-xs font-semibold" style="color:${m.text}">${m.short}</div>
          <div class="text-xs text-gray-400">${monthName(r.month)}</div>
          <div class="text-xs text-gray-300">${r.monthly_pct}%</div>
          ${r.is_overridden ? '<div class="text-xs text-orange-400">★</div>' : ''}
        `;
        historyEl.appendChild(card);
      });
    }

    document.getElementById('fh-loading').classList.add('hidden');
    document.getElementById('fh-content').classList.remove('hidden');
  }

  loadFinhero();
})();
</script>
