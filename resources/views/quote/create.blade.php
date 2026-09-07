@extends('layouts.storefront')
@section('title', 'Request a quote')

@section('content')
<div class="container-x max-w-xl py-10">
    <h1 class="text-2xl font-bold text-navy">Request a quote</h1>
    <p class="mt-2 text-sm text-slate-500">
        Bulk orders, project pricing, LPO purchases or something not listed — tell us what you need and our team will
        respond with a formal quotation.
    </p>

    <div class="card mt-6 p-6">
        <form action="{{ route('quote.store') }}" method="post" class="space-y-4">
            @csrf
            @if ($product)
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="rounded-lg bg-brand-50 px-3 py-2 text-sm text-brand-800">
                    Quoting: <strong>{{ $product->name }}</strong>
                </div>
                <div class="w-32"><label class="label">Quantity</label><input type="number" name="qty" value="{{ old('qty', 1) }}" min="1" class="input"></div>
            @endif
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Name</label><input name="name" value="{{ old('name', auth()->user()?->name) }}" class="input" required></div>
                <div><label class="label">Phone</label><input name="phone" value="{{ old('phone', auth()->user()?->phone) }}" class="input" required></div>
                <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" class="input"></div>
                <div><label class="label">Company</label><input name="company" value="{{ old('company', auth()->user()?->company_name) }}" class="input"></div>
            </div>
            <div><label class="label">Subject</label><input name="subject" value="{{ old('subject') }}" class="input" placeholder="e.g. 20 laptops for new branch"></div>
            <div><label class="label">Details</label><textarea name="message" rows="5" class="input" required>{{ old('message') }}</textarea></div>
            <button class="btn-primary">Send request</button>
        </form>
    </div>
</div>
@endsection
