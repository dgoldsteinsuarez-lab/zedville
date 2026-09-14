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
<div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
  <h1 class="text-xl font-bold whitespace-nowrap ">My Closet — Zedville</h1>
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
            Durable goods
          </span>
          <p class="text-sm font-medium text-gray-900 mb-1">Used over time</p>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Physical items that last 3 or more years and are not consumed in a single use.
            They lose value gradually through depreciation.
          </p>
          <div class="flex flex-wrap gap-1">
            @forelse($durableProducts as $product)
              <span class="text-xs font-medium px-3 py-1 rounded-full"
                style="background:var(--cat-transport-bg);color:var(--cat-transport-text);border:0.5px solid var(--cat-transport-border)">
                {{ $product }}
              </span>
            @empty
              <span class="text-xs text-gray-400">
                No durable goods purchased
              </span>
            @endforelse
          </div>
        </div>

        <!-- Non-durable -->
        <div class="p-5 bg-white">
          <span class="inline-block text-xs font-medium px-3 py-1 rounded-full mb-3"
            style="background:var(--cat-tools-bg);color:var(--cat-tools-text);border:0.5px solid var(--cat-tools-border)">
            Non-durable goods
          </span>
          <p class="text-sm font-medium text-gray-900 mb-1">Consumed quickly</p>
          <p class="text-xs text-gray-500 leading-relaxed mb-3">
            Items used up in less than 3 years — often in a single use.
            Once spent, they are gone and must be replaced.
          </p>
          <div class="flex flex-wrap gap-1">
            @forelse($nonDurableProducts as $product)
              <span class="text-xs font-medium px-3 py-1 rounded-full"
                style="background:var(--cat-tools-bg);color:var(--cat-tools-text);border:0.5px solid var(--cat-tools-border)">
                {{ $product }}
              </span>
            @empty
              <span class="text-xs text-gray-400">
                No non-durable goods purchased
              </span>
            @endforelse
          </div>
        </div>

        <!-- Footer note -->
        <div class="col-span-2 bg-gray-50 px-5 py-2 text-xs text-gray-400 tracking-wide border-t border-gray-200">
          Only durable goods appear in your closet
        </div>
      </div>

      <!-- ── SECTION HEADER ──────────────────────────────────────── -->
      <div class="flex items-center gap-3 mb-4">
        <h2 class="text-lg font-medium text-gray-900" id="section-title">My Closet</h2>
        <span id="item-count-badge" class="text-xs font-medium px-3 py-1 rounded-full"
          style="background:var(--cat-transport-bg);color:var(--cat-transport-text);border:0.5px solid var(--cat-transport-border)">
          0 items
        </span>
      </div>

      <!-- ── SUMMARY CARDS ───────────────────────────────────────── -->
      <div id="summary-cards" class="hidden grid grid-cols-3 gap-2 mb-5">
        <div class="bg-gray-50 rounded-lg p-3">
          <div class="text-xs text-gray-500 mb-1">Items owned</div>
          <div class="text-lg font-medium text-gray-900" id="summary-count">—</div>
        </div>
        <div class="bg-gray-50 rounded-lg p-3">
          <div class="text-xs text-gray-500 mb-1">Total value</div>
          <div class="text-lg font-medium text-gray-900" id="summary-value">—</div>
        </div>
        <div class="bg-gray-50 rounded-lg p-3">
          <div class="text-xs text-gray-500 mb-1">Oldest item</div>
          <div class="text-lg font-medium text-gray-900" id="summary-oldest">—</div>
        </div>
      </div>

      <!-- ── FILTERS ─────────────────────────────────────────────── -->
      <div id="filter-bar" class="hidden flex flex-wrap gap-2 mb-5"></div>

      <!-- ── STATES ──────────────────────────────────────────────── -->
      <div id="state-loading" class="text-sm text-gray-400 text-center py-10">Loading closet…</div>
      <div id="state-error" class="hidden text-sm text-red-500 text-center py-6"></div>
      <div id="state-empty" class="hidden text-sm text-gray-400 text-center bg-gray-50 rounded-xl py-10 px-6">
        No durable goods yet. Start spending to fill your closet!
      </div>
      <div id="state-cat-empty" class="hidden text-sm text-gray-400 text-center bg-gray-50 rounded-xl py-8 px-6">
        No items in this category yet.
      </div>

      <!-- ── ITEM GRID ───────────────────────────────────────────── -->
      <div id="item-grid" class="hidden grid gap-2"
        style="grid-template-columns: repeat(auto-fill, minmax(175px, 1fr))"></div>
    </div>
  </div>
</div>
<script>
  (() => {
    // ── Category config ─────────────────────────────────────
    const CATS = {
      tech: { label: 'Tech', bg: 'var(--cat-tech-bg)', text: 'var(--cat-tech-text)', border: 'var(--cat-tech-border)', bar: 'var(--cat-tech-bar)' },
      transport: { label: 'Transport', bg: 'var(--cat-transport-bg)', text: 'var(--cat-transport-text)', border: 'var(--cat-transport-border)', bar: 'var(--cat-transport-bar)' },
      home: { label: 'Home', bg: 'var(--cat-home-bg)', text: 'var(--cat-home-text)', border: 'var(--cat-home-border)', bar: 'var(--cat-home-bar)' },
      clothing: { label: 'Clothing', bg: 'var(--cat-clothing-bg)', text: 'var(--cat-clothing-text)', border: 'var(--cat-clothing-border)', bar: 'var(--cat-clothing-bar)' },
      tools: { label: 'Tools', bg: 'var(--cat-tools-bg)', text: 'var(--cat-tools-text)', border: 'var(--cat-tools-border)', bar: 'var(--cat-tools-bar)' },
      other: { label: 'Other', bg: 'var(--cat-other-bg)', text: 'var(--cat-other-text)', border: 'var(--cat-other-border)', bar: 'var(--cat-other-bar)' },
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
    if (studentId) $title.textContent = 'Student Closet';

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
        $error.textContent = 'Could not load closet. Please try again.';
        $error.classList.remove('hidden');
      }
    }

    // ── Render ──────────────────────────────────────────────
    function render(totalValue) {
      $loading.classList.add('hidden');

      // Badge
      $badge.textContent = `${allItems.length} ${allItems.length === 1 ? 'item' : 'items'}`;

      if (allItems.length === 0) {
        $empty.classList.remove('hidden');
        return;
      }

      // Summary
      const oldest = allItems.reduce((a, b) => a.ageYears > b.ageYears ? a : b, allItems[0]);
      $sumCount.textContent = allItems.length;
      $sumValue.textContent = `${totalValue.toLocaleString()} zeds`;
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
        btn.textContent = key === 'all' ? 'All' : getCat(key).label;
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
            <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0" style="${iconStyle}">
              <img
                  src="/public/uploads/products/${item.icon}"
                  alt="${item.name}"
                  class="w-full h-full object-cover">
             </div>
            <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="${badgeStyle}">
              ${cat.label}
            </span>
          </div>

          <!-- Name & price -->
          <div class="font-medium text-gray-900 mb-0.5">${item.name}</div>
          <div class="text-xs text-gray-500 mb-2">${item.pricePaidZeds.toLocaleString()} zeds</div>

          <!-- Life bar -->
          <div class="h-1 bg-gray-100 rounded-full overflow-hidden mb-1">
            <div class="life-bar-fill h-full rounded-full" style="width:${item.lifeUsedPct}%;background:${barColor}"></div>
          </div>

          <!-- Age / lifespan -->
          <div class="flex justify-between text-xs text-gray-400 mb-2">
            <span>Age: ${item.ageYears}yr</span>
            <span>Life: ${item.lifespanYears}yr</span>
          </div>

          <!-- Cost per year -->
          <div class="pt-2 border-t border-gray-100 text-xs text-gray-500">
            ~${item.costPerYear.toLocaleString()} zeds/year
          </div>
        </div>`;
    }

    // ── Boot ────────────────────────────────────────────────
    load();
  })();
</script>