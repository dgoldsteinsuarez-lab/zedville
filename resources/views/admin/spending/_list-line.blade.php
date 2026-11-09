{{-- One line of a shopping list in the editor (admin/spending/lists). --}}
@php $kind = isset($line['group']) ? 'group' : (isset($line['store']) ? 'store' : 'products'); @endphp
<div class="zv-line row g-2 align-items-start mb-2 border rounded p-2">
    <div class="col-md-1">
        <input name="lines[{{ $i }}][qty]" type="number" min="1" max="10" class="form-control form-control-sm" value="{{ $line['qty'] ?? 1 }}" aria-label="How many">
    </div>
    <div class="col-md-3">
        <select name="lines[{{ $i }}][kind]" class="form-select form-select-sm zv-kind">
            <option value="products" @selected($kind === 'products')>different products from these</option>
            <option value="group" @selected($kind === 'group')>different products from a food group (Supermarket)</option>
            <option value="store" @selected($kind === 'store')>anything from a shop</option>
        </select>
    </div>
    <div class="col-md-7">
        <select name="lines[{{ $i }}][group]" class="form-select form-select-sm" data-for="group">
            @foreach ($groupNames as $value => $label)
                <option value="{{ $value }}" @selected(($line['group'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="lines[{{ $i }}][store]" class="form-select form-select-sm" data-for="store">
            @foreach ($shops as $shop)
                <option value="{{ $shop }}" @selected(($line['store'] ?? '') === $shop)>{{ $shop }}</option>
            @endforeach
        </select>
        <select name="lines[{{ $i }}][products][]" class="form-select form-select-sm" multiple size="5" data-for="products">
            @foreach ($byShop as $shop => $items)
                <optgroup label="{{ $shop }}">
                    @foreach ($items as $product)
                        <option value="{{ $product->product_name }}" @selected(in_array($product->product_name, $line['products'] ?? [], true))>{{ $product->product_name }} - Ƶ {{ number_format($product->price, 2) }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <div class="form-text" data-for="products">Ctrl/Cmd + click to choose several, all from one shop.</div>
    </div>
    <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger zv-remove-line" title="Remove the line">✕</button></div>
</div>
