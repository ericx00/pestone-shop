@extends('layouts.storefront')
@section('title', 'Your cart')

@section('content')
<div class="container-x py-8">
    <h1 class="text-2xl font-bold text-navy">Your cart</h1>

    @if (empty($summary['lines']))
        <div class="card mt-6 p-10 text-center">
            <p class="text-slate-500">Your cart is empty.</p>
            <a href="{{ route('shop') }}" class="btn-primary mt-4">Start shopping</a>
        </div>
    @else
        <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_340px]">
            <div class="card divide-y divide-slate-200">
                @foreach ($summary['lines'] as $line)
                    @php $p = $line['product']; @endphp
                    <div class="flex gap-4 p-4">
                        <a href="{{ route('product', $p) }}"><x-product-thumb :product="$p" class="h-24 w-24" /></a>
                        <div class="flex-1">
                            <a href="{{ route('product', $p) }}" class="text-sm font-medium text-slate-800 hover:text-brand-700">{{ $p->name }}</a>
                            <p class="mt-1 text-xs text-slate-400">{{ $p->brand?->name }} {{ $p->sku ? '· '.$p->sku : '' }}</p>
                            <div class="mt-2 flex items-center gap-3">
                                <form action="{{ route('cart.update') }}" method="post" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $p->id }}">
                                    <input type="number" name="qty" value="{{ $line['qty'] }}" min="0" max="999"
                                           onchange="this.form.submit()" class="input w-20 text-sm">
                                </form>
                                <form action="{{ route('cart.remove') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $p->id }}">
                                    <button class="text-xs text-slate-400 hover:text-rose-600">Remove</button>
                                </form>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-navy">{{ kes($line['line_gross']) }}</p>
                            <p class="text-xs text-slate-400">{{ kes($line['unit_gross']) }} each</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card h-fit p-5">
                <h2 class="text-sm font-semibold text-slate-800">Order summary</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Subtotal (excl. VAT)</dt><dd>{{ kes($summary['subtotal']) }}</dd></div>
                    @if ($summary['discount'] > 0)
                        <div class="flex justify-between text-emerald-600"><dt>Discounts</dt><dd>− {{ kes($summary['discount']) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-slate-500">VAT (16%)</dt><dd>{{ kes($summary['vat']) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold text-navy">
                        <dt>Total</dt><dd>{{ kes($summary['subtotal'] + $summary['vat']) }}</dd>
                    </div>
                </dl>
                <p class="mt-2 text-xs text-slate-400">Delivery calculated at checkout.</p>
                <a href="{{ route('checkout.index') }}" class="btn-primary mt-4 w-full">Proceed to checkout</a>
                <a href="{{ route('shop') }}" class="mt-2 block text-center text-xs text-slate-500 hover:text-brand-700">Continue shopping</a>
            </div>
        </div>
    @endif
</div>
@endsection
