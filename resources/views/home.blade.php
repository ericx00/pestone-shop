@extends('layouts.storefront')
@section('title', 'Technology for business & home')

@section('content')
    {{-- Hero --}}
    <section class="bg-navy text-white">
        <div class="container-x grid gap-8 py-14 lg:grid-cols-2 lg:py-20">
            <div class="flex flex-col justify-center">
                <span class="text-sm font-semibold uppercase tracking-widest text-brand-300">{{ company('tagline') }}</span>
                <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                    Laptops, servers, networking &amp; ICT gear — delivered across Kenya
                </h1>
                <p class="mt-4 max-w-xl text-slate-300">
                    From a single laptop to a full office rollout. Genuine products from HP, Lenovo, Dell, Epson, TP-Link,
                    Logitech and more — with warranty, installation and support from Pestone Technologies.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('shop') }}" class="btn-accent">Shop all products</a>
                    <a href="{{ route('page', 'business') }}" class="btn-ghost border-white/30 bg-transparent text-white hover:border-white hover:text-white">Open a business account</a>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 self-center">
                @foreach ($categories->take(4) as $cat)
                    <a href="{{ route('category', $cat) }}" class="rounded-xl bg-white/5 p-4 ring-1 ring-white/10 transition hover:bg-white/10">
                        <span class="text-sm font-semibold text-white">{{ $cat->name }}</span>
                        <span class="mt-1 block text-xs text-slate-400">{{ $cat->products_count }} products</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Trust bar --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="container-x grid gap-4 py-6 text-sm sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Genuine & warranted', 'Authorised products with manufacturer warranty'],
                ['Pay by M-Pesa or card', 'Secure checkout — M-Pesa STK push & Pesapal'],
                ['Nationwide delivery', 'Nairobi same/next day; countrywide courier'],
                ['Business terms', 'B2B pricing, quotes, LPOs & SLA support'],
            ] as [$h, $p])
                <div class="flex gap-3">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700">✓</span>
                    <div><p class="font-semibold text-slate-800">{{ $h }}</p><p class="text-slate-500">{{ $p }}</p></div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Categories --}}
    <section class="container-x py-12">
        <h2 class="text-xl font-bold text-navy">Shop by category</h2>
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($categories as $cat)
                <a href="{{ route('category', $cat) }}" class="card flex flex-col items-center gap-2 p-4 text-center transition hover:border-brand-300 hover:shadow-md">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-600 text-lg font-bold">{{ \Illuminate\Support\Str::substr($cat->name, 0, 1) }}</span>
                    <span class="text-sm font-medium text-slate-700">{{ $cat->name }}</span>
                    <span class="text-xs text-slate-400">{{ $cat->products_count }}</span>
                </a>
            @endforeach
        </div>
    </section>

    @if ($deals->isNotEmpty())
        <section class="bg-white py-12">
            <div class="container-x">
                <div class="flex items-end justify-between">
                    <h2 class="text-xl font-bold text-navy">Hot deals</h2>
                    <a href="{{ route('shop', ['sort' => 'price_asc']) }}" class="text-sm font-semibold text-brand-700 hover:underline">View all</a>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($deals as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="container-x py-12">
        <div class="flex items-end justify-between">
            <h2 class="text-xl font-bold text-navy">Featured products</h2>
            <a href="{{ route('shop') }}" class="text-sm font-semibold text-brand-700 hover:underline">Browse the shop</a>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($featured as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

    {{-- B2B band --}}
    <section class="bg-navy py-14 text-white">
        <div class="container-x grid gap-8 lg:grid-cols-[1.2fr_1fr]">
            <div>
                <h2 class="text-2xl font-bold">Buying for an organisation?</h2>
                <p class="mt-3 max-w-xl text-slate-300">
                    Pestone Technologies is one of Kenya's fastest-growing ICT solutions companies — computing, connectivity,
                    security, backup, power and printing, plus maintenance and legacy support. Open a business account for
                    trade pricing, quotations against LPOs, and structured SLA support.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('page', 'business') }}" class="btn-accent">Business & B2B</a>
                    <a href="{{ route('quote.create') }}" class="btn-ghost border-white/30 bg-transparent text-white hover:text-white">Request a quote</a>
                </div>
            </div>
            <div class="rounded-xl bg-white/5 p-5 ring-1 ring-white/10">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-brand-300">SLA response tiers</h3>
                <table class="mt-3 w-full text-sm">
                    <tbody class="divide-y divide-white/10">
                        <tr><td class="py-2 font-medium">Platinum</td><td class="py-2 text-slate-300">15 min · 2 hr on-site · 24×7</td></tr>
                        <tr><td class="py-2 font-medium">Gold</td><td class="py-2 text-slate-300">30 min · 4 hr on-site · 24×7</td></tr>
                        <tr><td class="py-2 font-medium">Silver</td><td class="py-2 text-slate-300">2 hr · next business day</td></tr>
                        <tr><td class="py-2 font-medium">Bronze</td><td class="py-2 text-slate-300">4 hr · scheduled</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Brand strip --}}
    <section class="container-x py-10">
        <p class="text-center text-xs font-semibold uppercase tracking-widest text-slate-400">Brands we carry</p>
        <div class="mt-4 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-slate-500">
            @foreach ($brands as $b)
                <a href="{{ route('brand', $b) }}" class="text-sm font-semibold hover:text-brand-700">{{ $b->name }}</a>
            @endforeach
        </div>
    </section>
@endsection
