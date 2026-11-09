{{-- A Spending Activity's shopping list on a shop page (Daniel, 2026-10-31:
     "buscar unos pocos artículos específicos" instead of clicking until a
     minimum amount). Expects $shoppingList (App\Services\ShoppingList::
     forShop). The page calls zvListTry() before adding a product - it says
     why one isn't on the list - and zvListRender() to draw the list and to
     know whether the basket can be paid. OrderController checks the same. --}}
<style>
    .zv-list { margin-bottom: 14px; padding: 14px 16px; border: 1px solid #b6e8d8; border-radius: 12px; background: #f3fbf8; }
    .zv-list-head { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 6px; margin-bottom: 8px; font-weight: 700; color: #016950; }
    .zv-list-budget { font-size: 12px; font-weight: 600; color: #4B5B55; }
    .zv-list-budget.is-over { color: #c0392b; }
    .zv-list-range { margin: -4px 0 8px; font-size: 12px; color: #4B5B55; }
    .zv-list ul { margin: 0; padding: 0; list-style: none; }
    .zv-list li { display: flex; align-items: flex-start; gap: 10px; padding: 5px 0; font-size: 13px; color: #1a1a2e; border-top: 1px dashed #d5ece4; }
    .zv-list li:first-child { border-top: 0; }
    .zv-list li.is-done { color: #0f7a5c; }
    .zv-list li.is-elsewhere { color: #8a9a95; }
    .zv-list li em { font-style: normal; font-size: 12px; }
    .zv-list-tick { flex: none; min-width: 34px; padding: 1px 6px; border-radius: 999px; background: #fff; border: 1px solid #b6e8d8;
        font-size: 12px; font-weight: 700; text-align: center; }
    .zv-list li.is-done .zv-list-tick { background: #00A47D; border-color: #00A47D; color: #fff; }
</style>
<script>
    window.ZV_LIST = @json($shoppingList);
    (function () {
        const L = window.ZV_LIST;
        const T = @json(__('spending.list'));
        const fill = (s, p) => Object.keys(p).sort((a, b) => b.length - a.length).reduce((x, k) => x.split(':' + k).join(p[k]), s);
        const money = (n) => new Intl.NumberFormat(document.documentElement.lang || 'en', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);

        // the basket's products on this shop's lines, after what was bought before -
        // each one counts, the same product more than once too (2026-11-02)
        function counts(cart) {
            const c = L.lines.map((l) => l.done);
            cart.forEach((item) => {
                for (let n = 0; n < (item.quantity || 1); n++) {
                    const i = L.lines.findIndex((l, j) => l.here && c[j] < l.qty && l.ids.includes(item.id));
                    if (i >= 0) c[i]++;
                }
            });
            return c;
        }

        window.zvListTry = function (item, cart) {
            if (L.complete) return { ok: false, message: T.already_complete };
            const c = counts(cart);
            if (L.lines.some((l, i) => l.here && c[i] < l.qty && l.ids.includes(item.id))) return { ok: true };
            const full = L.lines.find((l) => l.here && l.ids.includes(item.id));
            if (full) return { ok: false, message: fill(T.line_full, { line: full.label }) };
            const group = L.groups ? L.groups[item.id] : null;
            return { ok: false, message: group ? fill(T.group_not_on_list, { item: item.name, group }) : fill(T.not_on_list, { item: item.name }) };
        };

        window.zvListRender = function (cart, total) {
            const c = counts(cart);
            const spent = L.spent + total;
            const left = L.budget - spent;
            document.getElementById('zvList').innerHTML =
                `<div class="zv-list-head"><span>📝 ${T.your_list}</span>`
                + `<span class="zv-list-budget ${left < -0.001 ? 'is-over' : ''}">${T.budget}: Ƶ ${money(L.budget)} · ${fill(T.budget_left, { left: money(Math.max(0, left)) })}</span></div>`
                + `<p class="zv-list-range">${fill(T.range, { min: money(L.range.min), max: money(L.range.max) })}</p>`
                + '<ul>' + L.lines.map((l, i) => {
                    const got = Math.min(c[i], l.qty);
                    const done = got >= l.qty;
                    return `<li class="${done ? 'is-done' : ''} ${l.here ? '' : 'is-elsewhere'}">`
                        + `<span class="zv-list-tick">${done ? '✓' : got + '/' + l.qty}</span>`
                        + `<span>${l.label}${l.here ? '' : ' <em>(' + fill(T.buy_at, { shop: l.shop }) + ')</em>'}</span></li>`;
                }).join('') + '</ul>';

            if (L.complete) return { canPay: false, message: T.already_complete, type: 'success' };
            if (left < -0.001) return { canPay: false, message: fill(T.over_budget, { spent: money(spent), budget: money(L.budget) }), type: 'error' };
            const missing = L.lines.map((l, i) => (l.here && c[i] < l.qty) ? (l.labels[l.qty - c[i]] || l.label) : null).filter(Boolean);
            if (missing.length) return { canPay: false, message: fill(T.missing_here, { items: missing.join('; ') }), type: cart.length ? 'warning' : 'info' };
            return { canPay: cart.length > 0, message: T.ready_here, type: 'success' };
        };
    })();
</script>
