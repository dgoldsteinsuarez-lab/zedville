{{-- One month (or the final review) on the Monthly Questions page. $href is null while it's still locked. --}}
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    class="block border border-color-[#D2DDDB] rounded p-5 {{ $href ? 'bg-white hover:shadow transition' : 'bg-gray-50 opacity-75' }}"
    @unless ($href) aria-disabled="true" @endunless>
    <div class="flex justify-between items-start gap-3 mb-1">
        <h3 class="text-lg font-semibold mb-0">{{ $title }}</h3>
        <span class="text-xs font-semibold px-2 py-1 rounded whitespace-nowrap {{ $href ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">{{ $status }}</span>
    </div>
    <p class="text-sm text-gray-500 mb-0">{{ $line }}</p>
</{{ $tag }}>
