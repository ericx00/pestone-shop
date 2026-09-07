@extends('layouts.storefront')
@section('title', 'Checkout')

@section('content')
<div class="container-x py-8" x-data="{ zone: '{{ $zone }}', method: '{{ $gateways->first()?->key() }}' }">
    <h1 class="text-2xl font-bold text-navy">Checkout</h1>

    @if ($errors->any())
        <div class="mt-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('checkout.place') }}" method="post" class="mt-6 grid gap-8 lg:grid-cols-[1fr_360px]">
        @csrf
        <div class="space-y-6">
            <section class="card p-5">
                <h2 class="text-sm font-semibold text-slate-800">Contact details</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><label class="label">Full name</label><input name="customer_name" value="{{ old('customer_name', $user?->name) }}" class="input" required></div>
                    <div><label class="label">Phone (M-Pesa)</label><input name="customer_phone" value="{{ old('customer_phone', $user?->phone) }}" class="input" placeholder="07XX XXX XXX" required></div>
                    <div><label class="label">Email</label><input type="email" name="customer_email" value="{{ old('customer_email', $user?->email) }}" class="input"></div>
                    <div><label class="label">Company (optional)</label><input name="customer_company" value="{{ old('customer_company', $user?->company_name) }}" class="input"></div>
                </div>
            </section>

            <section class="card p-5">
                <h2 class="text-sm font-semibold text-slate-800">Delivery</h2>
                <div class="mt-4 space-y-2 text-sm">
                    @foreach ([
                        'nairobi_cbd' => ['Nairobi CBD / Westlands', setting('delivery_fees')['nairobi_cbd'] ?? 300],
                        'nairobi_metro' => ['Greater Nairobi', setting('delivery_fees')['nairobi_metro'] ?? 500],
                        'countrywide' => ['Countrywide courier', setting('delivery_fees')['countrywide'] ?? 1000],
                        'pickup' => ['Pick up from our office', 0],
                    ] as $key => [$label, $fee])
                        <label class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="delivery_zone" value="{{ $key }}" x-model="zone" @checked($zone === $key) class="text-brand-600 focus:ring-brand-500">
                                {{ $label }}
                            </span>
                            <span class="text-slate-500">{{ $fee ? kes($fee) : 'Free' }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2" x-show="zone !== 'pickup'">
                    <div class="sm:col-span-2"><label class="label">Address / building</label><input name="line1" value="{{ old('line1') }}" class="input"></div>
                    <div><label class="label">Town</label><input name="town" value="{{ old('town') }}" class="input"></div>
                    <div><label class="label">County</label><input name="county" value="{{ old('county') }}" class="input"></div>
                </div>
                <input type="hidden" name="line1" value="Office pickup" x-show="zone === 'pickup'">
                <input type="hidden" name="town" value="Nairobi" x-show="zone === 'pickup'">
            </section>

            <section class="card p-5">
                <h2 class="text-sm font-semibold text-slate-800">Payment</h2>
                @if ($gateways->isEmpty())
                    <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        Online payment is not configured yet. Place the order and our team will send M-Pesa / bank details.
                    </p>
                    <input type="hidden" name="payment_method" value="manual">
                @else
                    <div class="mt-4 space-y-2 text-sm">
                        @foreach ($gateways as $g)
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                                <input type="radio" name="payment_method" value="{{ $g->key() }}" x-model="method" @checked($loop->first) class="text-brand-600 focus:ring-brand-500">
                                {{ $g->label() }}
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-3" x-show="method === 'mpesa'">
                        <label class="label">M-Pesa phone for the payment prompt</label>
                        <input name="mpesa_phone" value="{{ old('mpesa_phone', $user?->phone) }}" class="input" placeholder="07XX XXX XXX">
                    </div>
                @endif
            </section>

            <section class="card p-5">
                <label class="label">Order notes (optional)</label>
                <textarea name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
            </section>
        </div>

        <aside class="card h-fit p-5">
            <h2 class="text-sm font-semibold text-slate-800">Your order</h2>
            <div class="mt-3 max-h-64 space-y-2 overflow-y-auto text-sm">
                @foreach ($summary['lines'] as $line)
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-600">{{ $line['qty'] }} × {{ \Illuminate\Support\Str::limit($line['product']->name, 34) }}</span>
                        <span class="whitespace-nowrap">{{ kes($line['line_gross']) }}</span>
                    </div>
                @endforeach
            </div>
            <dl class="mt-4 space-y-1.5 border-t border-slate-200 pt-4 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>{{ kes($summary['subtotal']) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">VAT (16%)</dt><dd>{{ kes($summary['vat']) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Delivery</dt><dd>{{ $summary['shipping'] ? kes($summary['shipping']) : 'Free' }}</dd></div>
                <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold text-navy"><dt>Total</dt><dd>{{ kes($summary['total']) }}</dd></div>
            </dl>
            <button class="btn-primary mt-4 w-full">Place order</button>
            <p class="mt-2 text-center text-xs text-slate-400">You'll confirm payment on the next screen.</p>
        </aside>
    </form>
</div>
@endsection
