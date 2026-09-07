@php
    $navCategories = \App\Models\Category::topLevel()->where('is_active', true)
        ->orderBy('position')->orderBy('name')->with('children')->get();
    $cartCount = app(\App\Services\Cart\CartService::class)->count();
@endphp

<header x-data="{ open: false, mega: null }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="bg-navy text-white">
        <div class="container-x flex flex-wrap items-center justify-between gap-2 py-1.5 text-xs">
            <span>{{ company('tagline', 'Proven Technology Solutions') }} · Nationwide delivery across Kenya</span>
            <span class="flex items-center gap-4">
                <a href="tel:{{ preg_replace('/\s+/', '', company('phone', '')) }}" class="hover:text-brand-200">{{ company('phone') }}</a>
                <a href="mailto:{{ company('email') }}" class="hidden hover:text-brand-200 sm:inline">{{ company('email') }}</a>
            </span>
        </div>
    </div>

    <div class="container-x flex items-center gap-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('brand/logo.svg') }}" alt="Pestone Technologies" class="h-10 w-auto">
        </a>

        <form action="{{ route('shop') }}" method="get" class="relative hidden flex-1 md:block">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search laptops, printers, switches, UPS…"
                   class="input pl-10">
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3m1.8-4.4a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
        </form>

        <nav class="ml-auto flex items-center gap-1 text-sm font-medium">
            @auth
                <a href="{{ route('account') }}" class="btn-ghost hidden sm:inline-flex">My account</a>
                @if (auth()->user()->is_admin)
                    <a href="/admin" class="hidden text-xs text-slate-500 hover:text-brand-700 lg:inline">Admin</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-ghost hidden sm:inline-flex">Sign in</a>
            @endauth
            <a href="{{ route('cart.index') }}" class="btn-accent relative">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.4a.75.75 0 0 1 .73.57L6 8.25m0 0 1.6 6.4a1.5 1.5 0 0 0 1.46 1.1h7.88a1.5 1.5 0 0 0 1.46-1.14l1.5-6a.75.75 0 0 0-.73-.93H6Zm1.5 12a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm9 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z"/></svg>
                <span class="hidden sm:inline">Cart</span>
                @if ($cartCount > 0)
                    <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-navy px-1 text-[11px] font-bold">{{ $cartCount }}</span>
                @endif
            </a>
        </nav>
    </div>

    <div class="border-t border-slate-100 bg-white">
        <div class="container-x flex items-center gap-1 overflow-x-auto py-2 text-sm">
            <a href="{{ route('shop') }}" class="whitespace-nowrap rounded-md px-3 py-1.5 font-semibold text-navy hover:bg-brand-50">All products</a>
            @foreach ($navCategories as $cat)
                <a href="{{ route('category', $cat) }}" class="whitespace-nowrap rounded-md px-3 py-1.5 text-slate-600 hover:bg-brand-50 hover:text-brand-700">{{ $cat->name }}</a>
            @endforeach
            <a href="{{ route('page', 'business') }}" class="ml-auto whitespace-nowrap rounded-md bg-navy px-3 py-1.5 font-semibold text-white hover:bg-brand-800">For Business / B2B</a>
        </div>
    </div>

    <form action="{{ route('shop') }}" method="get" class="container-x pb-3 md:hidden">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products…" class="input">
    </form>
</header>
