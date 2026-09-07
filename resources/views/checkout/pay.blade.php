@extends('layouts.storefront')
@section('title', 'Pay for order '.$order->number)

@section('content')
<div class="container-x max-w-lg py-10" x-data="{ method: '{{ $gateways->first()?->key() }}' }">
    <div class="card p-6">
        <h1 class="text-xl font-bold text-navy">Complete your payment</h1>
        <p class="mt-1 text-sm text-slate-500">Order <span class="font-semibold">{{ $order->number }}</span> · Total due
            <span class="font-semibold text-navy">{{ kes($order->grand_total) }}</span></p>

        @if ($gateways->isEmpty())
            <div class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Online payment isn't enabled yet. Our team will contact you on {{ $order->customer_phone }} with payment
                instructions. You can also call {{ company('phone') }}.
            </div>
        @else
            <form action="{{ route('checkout.pay', $order) }}" method="post" class="mt-5 space-y-4">
                @csrf
                <div class="space-y-2 text-sm">
                    @foreach ($gateways as $g)
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                            <input type="radio" name="payment_method" value="{{ $g->key() }}" x-model="method" @checked($loop->first) class="text-brand-600 focus:ring-brand-500">
                            {{ $g->label() }}
                        </label>
                    @endforeach
                </div>
                <div x-show="method === 'mpesa'">
                    <label class="label">M-Pesa phone number</label>
                    <input name="mpesa_phone" value="{{ $order->customer_phone }}" class="input" placeholder="07XX XXX XXX">
                    <p class="mt-1 text-xs text-slate-400">You'll get a prompt on this phone to enter your M-Pesa PIN.</p>
                </div>
                <button class="btn-primary w-full">Pay {{ kes($order->grand_total) }}</button>
            </form>
        @endif

        <a href="{{ route('checkout.return', $order) }}" class="mt-4 block text-center text-xs text-slate-500 hover:text-brand-700">View order details</a>
    </div>
</div>
@endsection
