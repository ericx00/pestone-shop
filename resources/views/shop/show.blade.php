@extends('layouts.storefront')
@section('title', $product->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->short_description ?? $product->description), 155))

@section('content')
@php $price = $product->pricing(auth()->user()); @endphp
<div class="container-x py-8">
    <nav class="text-xs text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-brand-700">Home</a> /
        <a href="{{ route('shop') }}" class="hover:text-brand-700">Shop</a>
        @if ($product->category) /
            <a href="{{ route('category', $product->category) }}" class="hover:text-brand-700">{{ $product->category->name }}</a>
        @endif
    </nav>

    <div class="mt-4 grid gap-10 lg:grid-cols-2">
        <div class="card p-6">
            <x-product-thumb :product="$product" class="aspect-[4/3]" />
        </div>

        <div>
            @if ($product->brand)
                <a href="{{ route('brand', $product->brand) }}" class="text-xs font-semibold uppercase tracking-wide text-slate-400 hover:text-brand-700">{{ $product->brand->name }}</a>
            @endif
            <h1 class="mt-1 text-2xl font-bold text-navy">{{ $product->name }}</h1>
            @if ($product->sku)
                <p class="mt-1 text-xs text-slate-400">SKU: {{ $product->sku }}</p>
            @endif

            <div class="mt-5">
                @if ($price->hasDiscount())
                    <div class="flex items-center gap-3">
                        <span class="text-3xl font-bold text-navy">{{ kes($price->gross) }}</span>
                        <span class="text-lg text-slate-400 line-through">{{ kes($price->listPrice) }}</span>
                        <span class="rounded px-2 py-0.5 text-xs font-bold text-white" style="background-color: {{ $price->badgeColor() }}">{{ $price->badgeText() }}</span>
                    </div>
                @else
                    <span class="text-3xl font-bold text-navy">{{ kes($price->gross) }}</span>
                @endif
                <p class="mt-1 text-xs text-slate-500">
                    {{ $price->isB2B ? 'Business price, excludes 16% VAT' : 'Includes 16% VAT' }}
                    · Net {{ kes($price->net) }} + VAT {{ kes($price->vat) }}
                </p>
            </div>

            <div class="mt-4">
                @if ($product->isInStock())
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-600">● In stock ({{ $product->stock_qty }} available)</span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-600">● Available on request — {{ $product->availability_label }}</span>
                @endif
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <form action="{{ route('cart.add') }}" method="post" class="flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="number" name="qty" value="1" min="1" max="999" class="input w-20">
                    <button class="btn-primary">Add to cart</button>
                </form>
                <a href="{{ route('quote.create', ['product' => $product->slug]) }}" class="btn-ghost">Request a bulk / B2B quote</a>
            </div>

            @unless (auth()->check() && auth()->user()->isApprovedB2B())
                <p class="mt-4 rounded-lg bg-brand-50 px-3 py-2 text-xs text-brand-800">
                    Buying for a business? <a href="{{ route('page', 'business') }}" class="font-semibold underline">Open a B2B account</a> for trade pricing and quotations.
                </p>
            @endunless

            <div class="prose prose-sm mt-8 max-w-none text-slate-700">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Description</h2>
                <p class="whitespace-pre-line">{{ $product->description }}</p>
            </div>

            @if ($product->specs)
                <table class="mt-6 w-full text-sm">
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($product->specs as $k => $v)
                            <tr><td class="py-2 pr-4 font-medium text-slate-600">{{ $k }}</td><td class="py-2 text-slate-800">{{ $v }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-14">
            <h2 class="text-lg font-bold text-navy">Related products</h2>
            <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($related as $r)
                    <x-product-card :product="$r" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
