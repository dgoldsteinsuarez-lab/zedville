@extends('layouts.profile')

@section('title', 'Task 6: Shopping Basket Update')

@section('content')
<div class="flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">Task 6: Shopping Basket Update</h1>
</div>

<div class="grid grid-cols-1 gap-5" x-data="task6()" x-init="load()">

    <div class="tailCard w-full rounded-lg p-6">
        <h2 class="text-lg font-bold mb-2">🔄 Special Challenge: Shopping Basket Update!</h2>
        <p class="text-gray-700 mb-4">
            A few items in your shopping basket are <strong>temporarily unavailable</strong> at the supermarket.
            In real life, suppliers run out of stock, prices spike, or a product gets discontinued — and
            shoppers have to adapt. Economists call a product that can replace another for a similar purpose a
            <strong>substitute good</strong> (think chicken and tofu, or butter and margarine).
        </p>

        <p class="text-gray-700 mb-2"><strong>Your task:</strong></p>
        <ul class="list-disc list-inside text-gray-700 mb-4 space-y-1">
            <li>Look at your current shopping basket in the Supermarket.</li>
            <li>Any item marked <strong class="text-red-600">"Currently unavailable"</strong> needs a substitute.</li>
            <li>Choose a replacement from the other items available in your diet section.</li>
            <li>Keep your basket at the same number of items — a straight swap, not an addition or removal.</li>
        </ul>

        <p class="text-gray-700 mb-2"><strong>Substitution strategy:</strong></p>
        <ul class="list-disc list-inside text-gray-700 mb-4 space-y-1">
            <li>Try to replace an item with something of similar nutritional value.</li>
            <li>Consider the cost difference — your swap changes next month's expense total.</li>
            <li>Think about how the replacement affects your original shopping goal.</li>
            <li>Choose the best alternative available, not just the cheapest.</li>
        </ul>

        <div class="rounded-md border border-[#D2DDDB] px-4 py-3 mb-4">
            <template x-if="!loaded">
                <span class="text-gray-400">Checking your basket&hellip;</span>
            </template>
            <template x-if="loaded && blockedCount > 0">
                <span class="font-semibold text-red-600" x-text="blockedCount + (blockedCount === 1 ? ' item needs' : ' items need') + ' a substitute.'"></span>
            </template>
            <template x-if="loaded && blockedCount === 0">
                <span class="font-semibold text-green-600">✅ Nothing in your basket needs a substitute right now.</span>
            </template>
        </div>

        <a :href="supermarketUrl" class="themeBtn inline-block" x-show="loaded && diet">
            Go to My Supermarket →
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
function task6() {
    return {
        loaded: false,
        diet: null,
        blockedCount: 0,
        get supermarketUrl() {
            return this.diet ? '/supermarket/' + this.diet : '#';
        },
        load() {
            fetch('{{ route('basket.state') }}')
                .then(r => r.json())
                .then(data => {
                    this.diet = data.diet || null;
                    this.blockedCount = (data.items || []).filter(i => i.blocked).length;
                    this.loaded = true;
                });
        }
    };
}
</script>
@endpush
