@extends('layouts.storefront')
@section('title', 'Order '.$order->number)

@section('content')
<div class="container-x max-w-2xl py-10">
    <div class="card p-6">
        @if ($order->isPaid())
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-xl text-emerald-600">✓</span>
                <div>
                    <h1 class="text-xl font-bold text-navy">Thank you! Payment received</h1>
                    <p class="text-sm text-slate-500">Order {{ $order->number }} · {{ $order->placed_at?->format('d M Y, H:i') }}</p>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-xl text-amber-600">⏳</span>
                <div>
                    <h1 class="text-xl font-bold text-navy">Order received — awaiting payment</h1>
                    <p class="text-sm text-slate-500">Order {{ $order->number }}</p>
                </div>
            </div>
            <a href="{{ route('checkout.pay.form', $order) }}" class="btn-primary mt-4">Complete payment</a>
        @endif

        <table class="mt-6 w-full text-sm">
            <tbody class="divide-y divide-slate-200">
                @foreach ($order->items as $item)
                    <tr>
                        <td class="py-2">{{ $item->qty }} × {{ $item->name }}</td>
                        <td class="py-2 text-right whitespace-nowrap">{{ kes($item->line_total) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="text-sm">
                <tr><td class="pt-3 text-slate-500">Subtotal (excl. VAT)</td><td class="pt-3 text-right">{{ kes($order->subtotal) }}</td></tr>
                <tr><td class="text-slate-500">VAT (16%)</td><td class="text-right">{{ kes($order->vat_total) }}</td></tr>
                <tr><td class="text-slate-500">Delivery</td><td class="text-right">{{ $order->shipping_total ? kes($order->shipping_total) : 'Free' }}</td></tr>
                <tr class="font-bold text-navy"><td class="pt-2">Total</td><td class="pt-2 text-right">{{ kes($order->grand_total) }}</td></tr>
            </tfoot>
        </table>

        @if ($order->successfulPayment())
            <p class="mt-4 text-xs text-slate-400">Paid via {{ ucfirst($order->payment_method) }} · Ref {{ $order->successfulPayment()->gateway_ref }}</p>
        @endif

        <div class="mt-6 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
            <p class="font-medium text-slate-800">Delivery to</p>
            <p>{{ $order->customer_name }} · {{ $order->customer_phone }}</p>
            @if ($order->shipping_address)
                <p>{{ $order->shipping_address['line1'] ?? '' }}, {{ $order->shipping_address['town'] ?? '' }}</p>
            @endif
            <p class="mt-2 text-xs text-slate-400">Questions? {{ company('email') }} · {{ company('phone') }}</p>
        </div>

        <a href="{{ route('shop') }}" class="mt-6 inline-block text-sm font-semibold text-brand-700 hover:underline">Continue shopping →</a>
    </div>
</div>
@endsection
