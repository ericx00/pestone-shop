@extends('layouts.storefront')
@section('title', $category?->name ?? ($brand?->name ? $brand->name.' products' : 'Shop'))

@section('content')
<div class="container-x py-8">
    <nav class="text-xs text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-brand-700">Home</a> /
        <a href="{{ route('shop') }}" class="hover:text-brand-700">Shop</a>
        @if ($category) / <span class="text-slate-700">{{ $category->name }}</span> @endif
        @if ($brand) / <span class="text-slate-700">{{ $brand->name }}</span> @endif
    </nav>

    <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
        <h1 class="text-2xl font-bold text-navy">
            {{ $category?->name ?? ($brand?->name ? $brand->name : (request('q') ? 'Results for “'.request('q').'”' : 'All products')) }}
        </h1>
        <span class="text-sm text-slate-500">{{ $products->total() }} products</span>
    </div>

    <div class="mt-6 grid gap-8 lg:grid-cols-[240px_1fr]">
        {{-- Filters --}}
        <aside class="space-y-6">
            <form method="get" class="card p-4">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <h2 class="text-sm font-semibold text-slate-800">Filter</h2>

                @if ($childCategories->isNotEmpty())
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Categories</p>
                        <ul class="mt-2 space-y-1 text-sm">
                            @foreach ($childCategories as $child)
                                <li><a href="{{ route('category', $child) }}" class="text-slate-600 hover:text-brand-700">{{ $child->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($facetBrands->isNotEmpty())
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Brand</p>
                        <div class="mt-2 max-h-52 space-y-1 overflow-y-auto text-sm">
                            @foreach ($facetBrands as $b)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="brands[]" value="{{ $b->id }}"
                                           @checked(in_array($b->id, (array) request('brands')))
                                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span>{{ $b->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Price (KES)</p>
                    <div class="mt-2 flex gap-2">
                        <input type="number" name="min" value="{{ request('min') }}" placeholder="Min" class="input text-xs">
                        <input type="number" name="max" value="{{ request('max') }}" placeholder="Max" class="input text-xs">
                    </div>
                </div>

                <label class="mt-4 flex items-center gap-2 text-sm">
                    <input type="checkbox" name="in_stock" value="1" @checked(request('in_stock'))
                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span>In stock only</span>
                </label>

                <button class="btn-primary mt-4 w-full">Apply filters</button>
                @if (request()->hasAny(['brands', 'min', 'max', 'in_stock', 'q']))
                    <a href="{{ $category ? route('category', $category) : ($brand ? route('brand', $brand) : route('shop')) }}"
                       class="mt-2 block text-center text-xs text-slate-500 hover:text-brand-700">Clear all</a>
                @endif
            </form>
        </aside>

        {{-- Results --}}
        <div>
            <form method="get" class="mb-4 flex justify-end">
                @foreach (request()->except('sort', 'page') as $k => $v)
                    @if (is_array($v)) @foreach ($v as $vv)<input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">@endforeach
                    @else <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
                @endforeach
                <select name="sort" onchange="this.form.submit()" class="input max-w-52 text-sm">
                    <option value="">Sort: Featured</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
                    <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
                </select>
            </form>

            @if ($products->isEmpty())
                <div class="card p-10 text-center text-slate-500">No products match your filters.</div>
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
