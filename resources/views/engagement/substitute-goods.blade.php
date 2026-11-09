@extends('layouts.profile')

@section('title', __('engagement.substitute_goods_title'))

@section('content')
<div class="flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">{{ __('engagement.shopping_basket_update') }}</h1>
</div>

<div class="grid grid-cols-1 gap-5" x-data="substituteGoods()" x-init="load()">

    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-2">{{ __('engagement.special_challenge_shopping_basket_update') }}</h2>
        <p class="text-gray-700 mb-4">
            {!! __('engagement.a_few_items_in_your_shopping') !!}
        </p>

        <p class="text-gray-700 mb-2"><strong>{{ __('engagement.your_challenge') }}</strong></p>
        <ul class="list-disc list-inside text-gray-700 mb-4 space-y-1">
            <li>{{ __('engagement.look_at_your_current_shopping_basket') }}</li>
            <li>{!! __('engagement.unavailable_rule') !!}</li>
            <li>{{ __('engagement.choose_a_replacement_from_the_other') }}</li>
            <li>{{ __('engagement.keep_your_basket_at_the_same') }}</li>
        </ul>

        <p class="text-gray-700 mb-2"><strong>{{ __('engagement.substitution_strategy') }}</strong></p>
        <ul class="list-disc list-inside text-gray-700 mb-4 space-y-1">
            <li>{{ __('engagement.try_to_replace_an_item_with') }}</li>
            <li>{{ __('engagement.consider_the_cost_difference_your_swap') }}</li>
            <li>{{ __('engagement.think_about_how_the_replacement_affects') }}</li>
            <li>{{ __('engagement.choose_the_best_alternative_available_not') }}</li>
        </ul>

        <div class="rounded-md border border-[#D2DDDB] px-4 py-3 mb-4">
            <template x-if="!loaded">
                <span class="text-gray-400">{{ __('engagement.checking_your_basket') }}</span>
            </template>
            <template x-if="loaded && !active">
                <span class="font-semibold text-gray-600">{{ __('engagement.not_assigned') }}</span>
            </template>
            <template x-if="loaded && active && blockedCount > 0">
                <span class="font-semibold text-red-600" x-text="(blockedCount === 1 ? @js(__('engagement.one_item_needs')) : @js(__('engagement.items_need'))).replace(':count', blockedCount)"></span>
            </template>
            <template x-if="loaded && active && blockedCount === 0">
                <span class="font-semibold text-green-600">{{ __('engagement.nothing_in_your_basket_needs_a') }}</span>
            </template>
        </div>

        <a :href="supermarketUrl" class="themeBtn inline-block" x-show="loaded && diet">
            {{ __('engagement.go_to_my_supermarket') }}
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
function substituteGoods() {
    return {
        loaded: false,
        diet: null,
        active: false,
        blockedCount: 0,
        get supermarketUrl() {
            return this.diet ? '/supermarket/' + this.diet : '#';
        },
        load() {
            fetch('{{ route('basket.state') }}')
                .then(r => r.json())
                .then(data => {
                    this.diet = data.diet || null;
                    this.active = !!data.active;
                    this.blockedCount = (data.items || []).filter(i => i.blocked).length;
                    this.loaded = true;
                });
        }
    };
}
</script>
@endpush
