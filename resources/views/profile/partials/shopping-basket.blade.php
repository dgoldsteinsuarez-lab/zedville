{{--
    Substitute Goods feature (2026-10-19). A clear, always-available way
    to swap one grocery item for a substitute, without redoing the full
    Consumer Profile survey. Also where FinHero Task 6 plays out: an
    item an admin/tutor has marked "temporarily unavailable" shows here
    and must be swapped to complete the task.
--}}
<div class="tailCard w-full rounded-lg p-6" id="{{ $anchorId ?? 'myShoppingBasket' }}"
     x-data="shoppingBasket()" x-init="load()">

    <h3 class="text-lg font-bold mb-2">My Shopping Basket</h3>
    <p class="text-gray-600 mb-4">
        In economics, two products are <strong>substitute goods</strong> when one can replace the other for a similar
        purpose &mdash; like chicken and tofu, or butter and margarine. Swapping an item here changes what you'll
        actually buy next month, so think about which substitute gives you similar value for a similar (or better)
        price.
    </p>

    <template x-if="!loaded">
        <p class="text-gray-400">Loading your basket&hellip;</p>
    </template>

    <template x-if="loaded && items.length === 0">
        <p class="text-gray-400">Complete your monthly Consumer Profile survey to build a shopping basket.</p>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3" x-show="loaded && items.length > 0">
        <template x-for="item in items" :key="item.id">
            <div class="flex items-center justify-between rounded-md border px-4 py-3"
                 :class="item.blocked ? 'border-red-300 bg-red-50' : 'border-[#D2DDDB]'">
                <div>
                    <div class="font-semibold" x-text="item.name"></div>
                    <template x-if="item.blocked">
                        <div class="text-xs text-red-600 font-semibold">Currently unavailable &mdash; choose a substitute</div>
                    </template>
                    <template x-if="!item.blocked">
                        <div class="text-xs text-gray-500" x-text="item.total_price.toFixed(2) + ' Zed'"></div>
                    </template>
                </div>
                <button type="button" class="btn btn-sm"
                        :class="item.blocked ? 'btn-danger' : 'btn-outline-secondary'"
                        @click="openSwap(item)">
                    Swap for a substitute
                </button>
            </div>
        </template>
    </div>

    <div class="mt-3 font-semibold" x-show="loaded && items.length > 0">
        Basket total: <span x-text="basketCost.toFixed(2) + ' Zed'"></span>
    </div>

    {{-- Swap modal --}}
    <div x-show="swapping" x-cloak x-transition.opacity
         class="fixed w-full h-full z-[100] overflow-y-auto top-0 left-0 themeModal">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="swapping = false"></div>
        <div class="modalDilog max-w-[480px] mx-auto mt-24 relative">
            <div class="modalContent bg-white py-8 px-10 rounded-lg border border-[#D2DDDB]">
                <h4 class="text-lg font-semibold text-black mb-1">
                    Replace <span x-text="swapTarget?.name"></span>
                </h4>
                <p class="text-gray-500 text-sm mb-4">Pick a substitute from your diet's other items.</p>

                <template x-if="options.length === 0">
                    <p class="text-gray-400">No other items available to swap into right now.</p>
                </template>

                <select class="form-control mb-4" x-model="chosenOptionId">
                    <option value="">Select a substitute&hellip;</option>
                    <template x-for="option in options" :key="option.id">
                        <option :value="option.id" x-text="option.product_name + ' (' + parseFloat(option.price).toFixed(2) + ' Zed)'"></option>
                    </template>
                </select>

                <div x-show="swapError" class="text-red-600 text-sm mb-3" x-text="swapError"></div>

                <div class="flex gap-2">
                    <button type="button" class="themeBtn flex-1" :disabled="!chosenOptionId" @click="confirmSwap()">Confirm Swap</button>
                    <button type="button" class="btn btn-outline-secondary flex-1" @click="swapping = false">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function shoppingBasket() {
    return {
        loaded: false,
        items: [],
        basketCost: 0,
        swapping: false,
        swapTarget: null,
        options: [],
        chosenOptionId: '',
        swapError: '',

        load() {
            fetch('{{ route('basket.state') }}')
                .then(r => r.json())
                .then(data => {
                    this.items = data.items || [];
                    this.basketCost = data.basket_cost || 0;
                    this.loaded = true;
                });
        },

        openSwap(item) {
            this.swapTarget = item;
            this.chosenOptionId = '';
            this.swapError = '';
            this.options = [];
            this.swapping = true;

            fetch('{{ route('basket.options') }}')
                .then(r => r.json())
                .then(data => { this.options = data.items || []; });
        },

        confirmSwap() {
            if (!this.chosenOptionId) return;

            fetch('{{ route('basket.swap') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    old_item_id: this.swapTarget.id,
                    new_item_id: this.chosenOptionId
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (!data.status) {
                        this.swapError = data.message || 'Could not swap that item.';
                        return;
                    }
                    this.swapping = false;
                    if (data.task6_completed) {
                        alert('Task 6 complete! You replaced every unavailable item with a valid substitute.');
                    }
                    this.load();
                })
                .catch(() => { this.swapError = 'Something went wrong. Please try again.'; });
        }
    };
}
</script>
