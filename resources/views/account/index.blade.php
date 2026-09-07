@extends('layouts.storefront')
@section('title', 'My account')

@section('content')
<div class="container-x py-8">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-navy">My account</h1>
        <form action="{{ route('logout') }}" method="post">@csrf<button class="text-sm text-slate-500 hover:text-rose-600">Sign out</button></form>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[280px_1fr]">
        <aside class="card h-fit p-5 text-sm">
            <p class="font-semibold text-slate-800">{{ $user->name }}</p>
            <p class="text-slate-500">{{ $user->email }}</p>
            <div class="mt-3">
                @if ($user->type === 'b2b')
                    <span class="rounded-full bg-navy px-2 py-0.5 text-xs font-semibold text-white">
                        Business · {{ ucfirst($user->b2b_status) }}
                    </span>
                @else
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">Personal account</span>
                @endif
            </div>
            <nav class="mt-4 space-y-1">
                <a href="{{ route('account.business') }}" class="block rounded-md px-2 py-1.5 text-slate-600 hover:bg-brand-50 hover:text-brand-700">Business account</a>
                <a href="{{ route('quote.create') }}" class="block rounded-md px-2 py-1.5 text-slate-600 hover:bg-brand-50 hover:text-brand-700">Request a quote</a>
            </nav>
        </aside>

        <div>
            <h2 class="text-sm font-semibold text-slate-800">Recent orders</h2>
            @if ($orders->isEmpty())
                <div class="card mt-3 p-8 text-center text-sm text-slate-500">No orders yet. <a href="{{ route('shop') }}" class="font-semibold text-brand-700">Start shopping</a></div>
            @else
                <div class="card mt-3 divide-y divide-slate-200">
                    @foreach ($orders as $order)
                        <a href="{{ route('account.order', $order) }}" class="flex items-center justify-between p-4 text-sm hover:bg-slate-50">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $order->number }}</p>
                                <p class="text-xs text-slate-400">{{ $order->placed_at?->format('d M Y') }} · {{ $order->items_count }} items</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-navy">{{ kes($order->grand_total) }}</p>
                                <span class="text-xs font-medium {{ $order->isPaid() ? 'text-emerald-600' : 'text-amber-600' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
