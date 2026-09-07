@extends('layouts.storefront')
@section('title', 'Order '.$order->number)

@section('content')
<div class="container-x max-w-2xl py-8">
    <a href="{{ route('account') }}" class="text-sm text-slate-500 hover:text-brand-700">← Back to account</a>
    <div class="card mt-3 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-navy">{{ $order->number }}</h1>
            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $order->isPaid() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
            </span>
        </div>
        <p class="mt-1 text-sm text-slate-500">Placed {{ $order->placed_at?->format('d M Y, H:i') }}</p>

        <table class="mt-5 w-full text-sm">
            <tbody class="divide-y divide-slate-200">
                @foreach ($order->items as $item)
                    <tr><td class="py-2">{{ $item->qty }} × {{ $item->name }}</td><td class="py-2 text-right">{{ kes($item->line_total) }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <dl class="mt-4 space-y-1 border-t border-slate-200 pt-4 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>{{ kes($order->subtotal) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">VAT</dt><dd>{{ kes($order->vat_total) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Delivery</dt><dd>{{ kes($order->shipping_total) }}</dd></div>
            <div class="flex justify-between font-bold text-navy"><dt>Total</dt><dd>{{ kes($order->grand_total) }}</dd></div>
        </dl>

        @unless ($order->isPaid())
            <a href="{{ route('checkout.pay.form', $order) }}" class="btn-primary mt-4">Complete payment</a>
        @endunless
    </div>
</div>
@endsection
