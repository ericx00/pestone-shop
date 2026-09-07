@extends('layouts.storefront')
@section('title', 'Processing payment')

@section('content')
<div class="container-x max-w-lg py-16 text-center"
     x-data="paymentPoll('{{ route('checkout.status', $order) }}')">
    <div class="card p-8">
        <template x-if="!done">
            <div>
                <svg class="mx-auto h-12 w-12 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
                </svg>
                <h1 class="mt-4 text-lg font-bold text-navy">Waiting for payment…</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $message ?? 'Check your phone and approve the payment prompt.' }}</p>
                <p class="mt-1 text-xs text-slate-400">Order {{ $order->number }} · <span x-text="elapsed"></span>s</p>
            </div>
        </template>
        <template x-if="done && paid">
            <div>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-2xl text-emerald-600">✓</div>
                <h1 class="mt-4 text-lg font-bold text-navy">Payment received!</h1>
                <p class="mt-2 text-sm text-slate-500">Redirecting to your receipt…</p>
            </div>
        </template>
        <template x-if="done && !paid">
            <div>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-2xl text-rose-600">!</div>
                <h1 class="mt-4 text-lg font-bold text-navy">Payment not completed</h1>
                <p class="mt-2 text-sm text-slate-500">The payment was cancelled or timed out.</p>
                <a href="{{ route('checkout.pay.form', $order) }}" class="btn-primary mt-4">Try again</a>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function paymentPoll(url) {
    return {
        done: false, paid: false, elapsed: 0, timer: null,
        init() {
            this.timer = setInterval(() => this.elapsed++, 1000);
            this.check();
            this.poll = setInterval(() => this.check(), 4000);
            setTimeout(() => { if (!this.done) { this.finish(false); } }, 120000);
        },
        async check() {
            try {
                const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                if (d.paid) { this.finish(true, d.redirect); }
                else if (d.gateway_status === 'failed') { this.finish(false); }
            } catch (e) {}
        },
        finish(paid, redirect) {
            this.done = true; this.paid = paid;
            clearInterval(this.poll); clearInterval(this.timer);
            if (paid) { setTimeout(() => window.location = redirect || '{{ route('checkout.return', $order) }}', 1200); }
        }
    }
}
</script>
@endpush
@endsection
