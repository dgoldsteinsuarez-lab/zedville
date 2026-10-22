{{--
    Shared by education.academy.category and education.pisa.level - keeps
    the item-card markup (image, title, real-exam badge, PISA level badge,
    best score) in one place instead of duplicated per listing page.
--}}
<a href="{{ route('academy.item', $item) }}"
    class="block bg-white border border-color-[#D2DDDB] rounded p-5 hover:shadow transition">
    @if ($item->image)
        <img src="{{ asset('uploads/curriculum/' . $item->image) }}" alt="" class="w-full rounded mb-3" style="height:120px;object-fit:cover;">
    @endif
    <h3 class="text-lg font-semibold mb-1">
        {{ $item->title }}
        @if ($item->is_real_exam)
            <span class="inline-block text-xs font-semibold bg-red-100 text-red-700 rounded px-2 py-1 ms-1">{{ __('academy.real_exam') }}</span>
        @endif
    </h3>
    @if ($item->description)
        <p class="text-sm text-gray-600 mb-2">{{ $item->description }}</p>
    @endif
    @if ($item->pisaLevel)
        <span class="inline-block text-xs font-semibold bg-gray-100 rounded px-2 py-1 mb-2">{{ $item->pisaLevel->name }}</span>
    @endif
    @if ($item->best_score)
        <p class="text-sm text-green-700 mb-0">{{ __('academy.best_score') }}: {{ $item->best_score->score }}/{{ $item->best_score->total_questions }}</p>
    @elseif ($item->type === 'quiz')
        <p class="text-sm text-gray-500 mb-0">{{ __('academy.not_attempted_yet') }}</p>
    @endif
</a>
