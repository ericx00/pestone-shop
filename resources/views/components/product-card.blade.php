@props(['product'])

@php
    $price = $product->pricing(auth()->user());
@endphp

<div class="card group flex flex-col overflow-hidden transition hover:shadow-md">
    <a href="{{ route('product', $product) }}" class="block p-3">
        <x-product-thumb :product="$product" />
    </a>
    <div class="flex flex-1 flex-col px-4 pb-4">
        @if ($product->brand)
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $product->brand->name }}</span>
        @endif
        <a href="{{ route('product', $product) }}" class="mt-1 line-clamp-2 text-sm font-medium text-slate-800 hover:text-brand-700">
            {{ $product->name }}
        </a>

        <div class="mt-auto pt-3">
            @if ($price->hasDiscount())
                <div class="flex items-center gap-2">
                    <span class="text-base font-bold text-navy">{{ kes($price->gross) }}</span>
                    <span class="text-xs text-slate-400 line-through">{{ kes($price->listPrice) }}</span>
                </div>
                <span class="mt-1 inline-block rounded px-1.5 py-0.5 text-[11px] font-bold text-white" style="background-color: {{ $price->badgeColor() }}">
                    {{ $price->badgeText() }}
                </span>
            @else
                <span class="text-base font-bold text-navy">{{ kes($price->gross) }}</span>
            @endif
            <span class="ml-1 text-[11px] text-slate-400">{{ $price->isB2B ? 'ex. VAT' : 'incl. VAT' }}</span>
        </div>

        <div class="mt-3 flex items-center justify-between gap-2">
            @if ($product->isInStock())
                <span class="text-[11px] font-medium text-emerald-600">● In stock</span>
            @else
                <span class="text-[11px] font-medium text-amber-600">On request</span>
            @endif
            <form action="{{ route('cart.add') }}" method="post">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button class="btn-primary px-3 py-1.5 text-xs">Add to cart</button>
            </form>
        </div>
    </div>
</div>
