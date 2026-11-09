@extends('layouts.profile')

@section('title', 'My Closet — Zedville')

@section('content')
  @push('styles')
    <style>
      /* ── Category colour tokens ─────────────────────────────── */
      :root {
        --cat-tech-bg: #E6F1FB;
        --cat-tech-text: #0C447C;
        --cat-tech-border: #B5D4F4;
        --cat-tech-bar: #378ADD;
        --cat-transport-bg: #E1F5EE;
        --cat-transport-text: #085041;
        --cat-transport-border: #9FE1CB;
        --cat-transport-bar: #1D9E75;
        --cat-home-bg: #EEEDFE;
        --cat-home-text: #3C3489;
        --cat-home-border: #CECBF6;
        --cat-home-bar: #7F77DD;
        --cat-clothing-bg: #FAECE7;
        --cat-clothing-text: #712B13;
        --cat-clothing-border: #F5C4B3;
        --cat-clothing-bar: #D85A30;
        --cat-tools-bg: #FAEEDA;
        --cat-tools-text: #633806;
        --cat-tools-border: #FAC775;
        --cat-tools-bar: #BA7517;
        --cat-other-bg: #F1EFE8;
        --cat-other-text: #444441;
        --cat-other-border: #D3D1C7;
        --cat-other-bar: #888780;
      }

      /* ── Life bar animation ──────────────────────────────────── */
      .life-bar-fill {
        transition: width 0.4s ease;
      }

      /* ── Item card hover ─────────────────────────────────────── */
      .item-card {
        transition: border-color 0.15s;
      }

      .item-card:hover {
        border-color: #9ca3af !important;
      }

      /* ── Filter button active states ─────────────────────────── */
      .filter-btn {
        transition: all 0.15s;
      }
    </style>
  @endpush
  <div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">{{ __('closet.my_closet_zedville') }}</h1>
  </div>
  <div class="grid grid-cols-1 gap-5 ">
    <div class="themeTabspills">
      <div class="w-full " style="position: relative;">
        <!-- ── EXPLAINER BANNER ────────────────────────────────────── -->
        <div id="explainer-banner" class="grid grid-cols-2 border border-gray-200 rounded-xl overflow-hidden mb-6">

          <!-- Durable -->
          <div class="p-5 bg-white border-r border-gray-200">
            <span class="inline-block text-xs font-medium px-3 py-1 rounded-full mb-3"
              style="background:var(--cat-transport-bg);color:var(--cat-transport-text);border:0.5px solid var(--cat-transport-border)">
              {{ __('closet.durable_goods') }}
            </span>
            <p class="text-sm font-medium text-gray-900 mb-1">{{ __('closet.used_over_time') }}</p>
            <p class="text-xs text-gray-500 leading-relaxed mb-3">
              {{ __('closet.physical_items_that_last_3_or') }}
            </p>
            <div class="flex flex-wrap gap-1">
              @foreach(['Laptop', 'Bicycle', 'Phone', 'Furniture'] as $tag)
                <span class="text-xs font-medium px-3 py-1 rounded-full"
                  style="background:var(--cat-transport-bg);color:var(--cat-transport-text);border:0.5px solid var(--cat-transport-border)">
                  {{ $tag }}
                </span>
              @endforeach
            </div>
          </div>

          <!-- Non-durable -->
          <div class="p-5 bg-white">
            <span class="inline-block text-xs font-medium px-3 py-1 rounded-full mb-3"
              style="background:var(--cat-tools-bg);color:var(--cat-tools-text);border:0.5px solid var(--cat-tools-border)">
              {{ __('closet.non_durable_goods') }}
            </span>
            <p class="text-sm font-medium text-gray-900 mb-1">{{ __('closet.consumed_quickly') }}</p>
            <p class="text-xs text-gray-500 leading-relaxed mb-3">
              {{ __('closet.items_used_up_in_less_than') }}
            </p>
            <div class="flex flex-wrap gap-1">
              @foreach(['Food', 'Shampoo', 'Notebook', 'Bus ticket'] as $tag)
                <span class="text-xs font-medium px-3 py-1 rounded-full"
                  style="background:var(--cat-tools-bg);color:var(--cat-tools-text);border:0.5px solid var(--cat-tools-border)">
                  {{ $tag }}
                </span>
              @endforeach
            </div>
          </div>

          <!-- Footer note -->
          <div class="col-span-2 bg-gray-50 px-5 py-2 text-xs text-gray-400 tracking-wide border-t border-gray-200">
            {{ __('closet.only_durable_goods_appear_in_your') }}
          </div>
        </div>

        <!-- ── SECTION HEADER ──────────────────────────────────────── -->
        <div class="flex items-center gap-3 mb-4">
          <h2 class="text-lg font-medium text-gray-900" id="section-title">{{ __('closet.my_closet') }}</h2>
          <span id="item-count-badge" class="text-xs font-medium px-3 py-1 rounded-full"
            style="background:var(--cat-transport-bg);color:var(--cat-transport-text);border:0.5px solid var(--cat-transport-border)">
            {{ __('closet.n0_items') }}
          </span>
        </div>

        <!-- ── SUMMARY CARDS ───────────────────────────────────────── -->
        <div id="summary-cards" class="hidden grid grid-cols-3 gap-2 mb-5">
          <div class="bg-gray-50 rounded-lg p-3">
            <div class="text-xs text-gray-500 mb-1">{{ __('closet.items_owned') }}</div>
            <div class="text-lg font-medium text-gray-900" id="summary-count">—</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-3">
            <div class="text-xs text-gray-500 mb-1">{{ __('closet.total_value') }}</div>
            <div class="text-lg font-medium text-gray-900" id="summary-value">—</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-3">
            <div class="text-xs text-gray-500 mb-1">{{ __('closet.oldest_item') }}</div>
            <div class="text-lg font-medium text-gray-900" id="summary-oldest">—</div>
          </div>
        </div>

        <!-- ── FILTERS ─────────────────────────────────────────────── -->
        <div id="filter-bar" class="hidden flex flex-wrap gap-2 mb-5"></div>

        <!-- ── STATES ──────────────────────────────────────────────── -->
        <div id="state-loading" class="text-sm text-gray-400 text-center py-10">{{ __('closet.loading_closet') }}</div>
        <div id="state-error" class="hidden text-sm text-red-500 text-center py-6"></div>
        <div id="state-empty" class="hidden text-sm text-gray-400 text-center bg-gray-50 rounded-xl py-10 px-6">
          {{ __('closet.no_durable_goods_yet_start_spending') }}
        </div>
        <div id="state-cat-empty" class="hidden text-sm text-gray-400 text-center bg-gray-50 rounded-xl py-8 px-6">
          {{ __('closet.no_items_in_this_category_yet') }}
        </div>

        <!-- ── ITEM GRID ───────────────────────────────────────────── -->
        <div id="item-grid" class="hidden grid gap-2"
          style="grid-template-columns: repeat(auto-fill, minmax(175px, 1fr))"></div>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <!-- ── JAVASCRIPT ──────────────────────────────────────────── -->
  <script>
    (() => {
    // Texts in the student's language (lang/<language>/closet.php).
    const CL_T = {{ \Illuminate\Support\Js::from(__('closet.js')) }};
      // ── Category config ─────────────────────────────────────
      const CATS = {
        tech: { label: CL_T.categories.tech, bg: 'var(--cat-tech-bg)', text: 'var(--cat-tech-text)', border: 'var(--cat-tech-border)', bar: 'var(--cat-tech-bar)' },
        transport: { label: CL_T.categories.transport, bg: 'var(--cat-transport-bg)', text: 'var(--cat-transport-text)', border: 'var(--cat-transport-border)', bar: 'var(--cat-transport-bar)' },
        home: { label: CL_T.categories.home, bg: 'var(--cat-home-bg)', text: 'var(--cat-home-text)', border: 'var(--cat-home-border)', bar: 'var(--cat-home-bar)' },
        clothing: { label: CL_T.categories.clothing, bg: 'var(--cat-clothing-bg)', text: 'var(--cat-clothing-text)', border: 'var(--cat-clothing-border)', bar: 'var(--cat-clothing-bar)' },
        tools: { label: CL_T.categories.tools, bg: 'var(--cat-tools-bg)', text: 'var(--cat-tools-text)', border: 'var(--cat-tools-border)', bar: 'var(--cat-tools-bar)' },
        other: { label: CL_T.categories.other, bg: 'var(--cat-other-bg)', text: 'var(--cat-other-text)', border: 'var(--cat-other-border)', bar: 'var(--cat-other-bar)' },
      };
      const getCat = key => CATS[key] || CATS.other;

      // ── State ───────────────────────────────────────────────
      let allItems = [];
      let activeFilter = 'all';

      // ── DOM refs ────────────────────────────────────────────
      const $loading = document.getElementById('state-loading');
      const $error = document.getElementById('state-error');
      const $empty = document.getElementById('state-empty');
      const $catEmpty = document.getElementById('state-cat-empty');
      const $grid = document.getElementById('item-grid');
      const $summary = document.getElementById('summary-cards');
      const $filterBar = document.getElementById('filter-bar');
      const $badge = document.getElementById('item-count-badge');
      const $title = document.getElementById('section-title');
      const $sumCount = document.getElementById('summary-count');
      const $sumValue = document.getElementById('summary-value');
      const $sumOldest = document.getElementById('summary-oldest');

      // ── Detect tutor view (?student=<id>) ───────────────────
      const params = new URLSearchParams(window.location.search);
      const studentId = params.get('student');
      const endpoint = studentId ? `/closet/student/${studentId}` : '/closet/mine';
      if (studentId) $title.textContent = CL_T.student_closet;

      // ── Fetch ───────────────────────────────────────────────
      async function load() {
        try {
          const res = await fetch(endpoint, { credentials: 'include' });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const data = await res.json();
          allItems = data.items || [];
          render(data.totalValue || 0);
        } catch (err) {
          $loading.classList.add('hidden');
          $error.textContent = CL_T.load_error;
          $error.classList.remove('hidden');
        }
      }

      // ── Render ──────────────────────────────────────────────
      function render(totalValue) {
        $loading.classList.add('hidden');

        // Badge
        $badge.textContent = (allItems.length === 1 ? CL_T.item : CL_T.items).replace(':count', allItems.length);

        if (allItems.length === 0) {
          $empty.classList.remove('hidden');
          return;
        }

        // Summary
        const oldest = allItems.reduce((a, b) => a.ageYears > b.ageYears ? a : b, allItems[0]);
        $sumCount.textContent = allItems.length;
        $sumValue.textContent = CL_T.zeds.replace(':amount', totalValue.toLocaleString(document.documentElement.lang));
        $sumOldest.textContent = oldest ? oldest.name : '—';
        $summary.classList.remove('hidden');
        $summary.style.display = 'grid';

        // Filters
        buildFilters();

        // Grid
        renderGrid();
      }

      function buildFilters() {
        const presentCats = [...new Set(allItems.map(i => i.category))];
        $filterBar.innerHTML = '';
        ['all', ...presentCats].forEach(key => {
          const btn = document.createElement('button');
          btn.dataset.key = key;
          btn.textContent = key === 'all' ? CL_T.all : getCat(key).label;
          btn.className = 'filter-btn text-xs font-medium px-4 py-1 rounded-full border cursor-pointer';
          applyFilterStyle(btn, key === activeFilter, key);
          btn.addEventListener('click', () => {
            activeFilter = key;
            document.querySelectorAll('.filter-btn').forEach(b => applyFilterStyle(b, b.dataset.key === key, b.dataset.key));
            renderGrid();
          });
          $filterBar.appendChild(btn);
        });
        $filterBar.classList.remove('hidden');
        $filterBar.style.display = 'flex';
      }

      function applyFilterStyle(btn, isActive, key) {
        const cat = key === 'all' ? CATS.transport : getCat(key);
        if (isActive) {
          btn.style.background = cat.bg;
          btn.style.color = cat.text;
          btn.style.borderColor = cat.border;
        } else {
          btn.style.background = '#fff';
          btn.style.color = '#6b7280';
          btn.style.borderColor = '#d1d5db';
        }
      }

      function renderGrid() {
        $catEmpty.classList.add('hidden');
        $grid.classList.add('hidden');

        const filtered = activeFilter === 'all'
          ? allItems
          : allItems.filter(i => i.category === activeFilter);

        if (filtered.length === 0) {
          $catEmpty.classList.remove('hidden');
          return;
        }

        $grid.innerHTML = filtered.map(itemCardHTML).join('');
        $grid.classList.remove('hidden');
        $grid.style.display = 'grid';
      }

      function itemCardHTML(item) {
        const cat = getCat(item.category);
        const barColor = item.lifeUsedPct >= 85 ? '#E24B4A' : cat.bar;
        const badgeStyle = `background:${cat.bg};color:${cat.text};border:0.5px solid ${cat.border}`;
        const iconStyle = `background:${cat.bg}`;

        return `
          <div class="item-card border border-gray-200 rounded-xl p-4" style="font-size:14px">
            <!-- Top row -->
            <div class="flex items-start justify-between mb-2">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl flex-shrink-0" style="${iconStyle}">
                ${item.icon}
              </div>
              <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="${badgeStyle}">
                ${cat.label}
              </span>
            </div>

            <!-- Name & price -->
            <div class="font-medium text-gray-900 mb-0.5">${item.name}</div>
            <div class="text-xs text-gray-500 mb-2">${CL_T.zeds.replace(':amount', item.pricePaidZeds.toLocaleString(document.documentElement.lang))}</div>

            <!-- Life bar -->
            <div class="h-1 bg-gray-100 rounded-full overflow-hidden mb-1">
              <div class="life-bar-fill h-full rounded-full" style="width:${item.lifeUsedPct}%;background:${barColor}"></div>
            </div>

            <!-- Age / lifespan -->
            <div class="flex justify-between text-xs text-gray-400 mb-2">
              <span>${CL_T.age.replace(':years', item.ageYears)}</span>
              <span>${CL_T.life.replace(':years', item.lifespanYears)}</span>
            </div>

            <!-- Cost per year -->
            <div class="pt-2 border-t border-gray-100 text-xs text-gray-500">
              ~${CL_T.per_year.replace(':amount', item.costPerYear.toLocaleString(document.documentElement.lang))}
            </div>
          </div>`;
      }

      // ── Boot ────────────────────────────────────────────────
      load();
    })();
  </script>
@endpush