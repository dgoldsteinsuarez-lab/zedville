{{--
    My Shopping Basket - swap any grocery item for a substitute good,
    without redoing the Consumer Profile survey. Also where the Substitute
    Goods activity plays out (Admin > Engagement): while it runs for the
    student's class, a few items are "out of stock" and must be swapped.
    Shown only in the Supermarket section of the student's own diet.
    Redesigned 2026-10-23 (Daniel: friendlier, starts with a question).
--}}
<div class="tailCard w-full rounded-lg p-6 zv-basket" id="{{ $anchorId ?? 'myShoppingBasket' }}"
     x-data="shoppingBasket()" x-init="load()">

    <div class="zv-basket-intro">
        <div class="zv-basket-icon" aria-hidden="true">🛒</div>
        <div>
            <h3 class="text-lg font-bold mb-1">{{ __('shop.basket.title') }}</h3>
            <p class="zv-basket-q mb-0">{{ __('shop.basket.question_1') }}</p>
            <p class="zv-basket-q mb-2">{{ __('shop.basket.question_2') }}</p>
            <p class="text-gray-600 mb-0">{!! __('shop.basket.intro') !!}</p>
        </div>
    </div>

    {{-- The Substitute Goods activity, while it runs for this class. --}}
    <div class="zv-basket-alert" x-show="loaded && blockedCount() > 0" x-cloak>
        <span x-text="blockedCount() === 1 ? @js(__('shop.basket.challenge_one')) : @js(__('shop.basket.challenge_many')).replace(':count', blockedCount())"></span>
        <a href="{{ route('engagement.substitute-goods') }}" class="zv-basket-link">{{ __('shop.basket.how_it_works') }}</a>
    </div>

    <template x-if="!loaded">
        <p class="text-gray-400">{{ __('shop.basket.loading') }}</p>
    </template>

    <template x-if="loaded && items.length === 0">
        <p class="text-gray-400">{{ __('shop.basket.empty') }}</p>
    </template>

    <div class="zv-basket-grid" x-show="loaded && items.length > 0">
        <template x-for="item in items" :key="item.id">
            <div class="zv-basket-item" :class="item.blocked ? 'is-out' : ''">
                <div class="min-w-0">
                    <div class="zv-basket-name" x-text="item.name"></div>
                    <div class="zv-basket-price" x-text="item.total_price.toFixed(2) + ' Ƶ'"></div>
                    <template x-if="item.blocked">
                        <span class="zv-basket-chip">{{ __('shop.basket.out_of_stock') }}</span>
                    </template>
                </div>
                <button type="button" class="zv-basket-swap" :class="item.blocked ? 'is-primary' : ''" @click="openSwap(item)">
                    <span aria-hidden="true">⇄</span>
                    <span x-text="item.blocked ? @js(__('shop.basket.swap_now')) : @js(__('shop.basket.swap'))"></span>
                </button>
            </div>
        </template>
    </div>

    <div class="mt-3 font-semibold" x-show="loaded && items.length > 0">
        {{ __('shop.basket.total') }} <span x-text="basketCost.toFixed(2) + ' Ƶ'"></span>
    </div>

    {{-- Swap pop-up --}}
    <div x-show="swapping" x-cloak x-transition.opacity
         class="fixed w-full h-full z-[100] overflow-y-auto top-0 left-0 themeModal">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="swapping = false"></div>
        <div class="modalDilog max-w-[480px] mx-auto mt-24 relative">
            <div class="modalContent bg-white py-8 px-10 rounded-lg border border-[#D2DDDB]">
                <h4 class="text-lg font-semibold text-black mb-1">
                    {{ __('shop.basket.replace') }} <span x-text="swapTarget?.name"></span>
                </h4>
                <p class="text-gray-500 text-sm mb-4">{{ __('shop.basket.pick_substitute') }}</p>

                <template x-if="options.length === 0">
                    <p class="text-gray-400">{{ __('shop.basket.no_options') }}</p>
                </template>

                <select class="form-control mb-4" x-model="chosenOptionId">
                    <option value="">{{ __('shop.basket.select_substitute') }}</option>
                    <template x-for="option in options" :key="option.id">
                        <option :value="option.id" x-text="(option.label || option.product_name) + ' (' + parseFloat(option.price).toFixed(2) + ' Ƶ)'"></option>
                    </template>
                </select>

                <div x-show="swapError" class="text-red-600 text-sm mb-3" x-text="swapError"></div>

                <div class="flex gap-2">
                    <button type="button" class="themeBtn flex-1" :disabled="!chosenOptionId" @click="confirmSwap()">{{ __('shop.basket.confirm') }}</button>
                    <button type="button" class="whiteBtn flex-1" @click="swapping = false">{{ __('shop.basket.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .zv-basket-intro { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px; }
    .zv-basket-icon { flex: none; width: 48px; height: 48px; border-radius: 14px; background: #E7FBF3;
        display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .zv-basket-q { font-weight: 700; color: #016950; }
    .zv-basket-alert { display: flex; flex-wrap: wrap; gap: 6px 12px; align-items: center; margin-bottom: 14px;
        padding: 10px 14px; border-radius: 10px; background: #FFF5D4; border: 1px solid #F1E2B0; color: #6b5417; font-size: 14px; font-weight: 600; }
    .zv-basket-link { color: #016950; text-decoration: underline; font-weight: 600; white-space: nowrap; }
    .zv-basket-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; }
    .zv-basket-item { display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 10px 12px; border: 1px solid #D2DDDB; border-radius: 10px; background: #fff; }
    .zv-basket-item.is-out { background: #FFFBEF; border-color: #F1E2B0; }
    .zv-basket-name { font-weight: 600; font-size: 14px; color: #0F2A22; line-height: 1.3; }
    .zv-basket-price { font-size: 12px; color: #6B7B76; }
    .zv-basket-chip { display: inline-block; margin-top: 3px; padding: 1px 8px; border-radius: 999px;
        background: #FDE7C2; color: #8a5a00; font-size: 12px; font-weight: 700; }
    .zv-basket-swap { flex: none; display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 999px;
        border: 1px solid #00A47D; background: #fff; color: #016950; font-size: 13px; font-weight: 600; transition: background .15s, color .15s; }
    .zv-basket-swap:hover, .zv-basket-swap:focus-visible { background: #E7FBF3; outline: none; }
    .zv-basket-swap.is-primary { background: #00A47D; color: #fff; }
    .zv-basket-swap.is-primary:hover { background: #016950; }
</style>

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

        blockedCount() {
            return this.items.filter(item => item.blocked).length;
        },

        load() {
            fetch('{{ route('basket.state') }}')
                .then(r => r.json())
                .then(data => {
                    this.items = data.items || [];
                    // The server sends money as text ("100.00").
                    this.basketCost = parseFloat(data.basket_cost) || 0;
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
                        this.swapError = data.message || @json(__('shop.basket.could_not_swap'));
                        return;
                    }
                    this.swapping = false;
                    if (data.activity_completed) {
                        Swal.fire({
                            icon: 'success',
                            title: @json(__('shop.basket.done_title')),
                            html: '<p style="margin:0 0 8px">' + @json(__('shop.basket.done_text')) + '</p>'
                                + (data.engagement_point_awarded ? '<p style="margin:0;font-weight:700;color:#016950">' + @json(__('shop.basket.engagement_point')) + '</p>' : ''),
                            confirmButtonText: @json(__('shop.basket.great')),
                            confirmButtonColor: '#00A47D'
                        });
                    }
                    this.load();
                })
                .catch(() => { this.swapError = @json(__('shop.basket.went_wrong')); });
        }
    };
}
</script>
