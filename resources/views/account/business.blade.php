@extends('layouts.storefront')
@section('title', 'Business account')

@section('content')
<div class="container-x max-w-xl py-8">
    <a href="{{ route('account') }}" class="text-sm text-slate-500 hover:text-brand-700">← Back to account</a>
    <div class="card mt-3 p-6">
        <h1 class="text-xl font-bold text-navy">Business (B2B) account</h1>

        @if ($user->b2b_status === 'approved')
            <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                Your business account is <strong>approved</strong>. Trade prices (excl. VAT) now show across the shop.
            </div>
        @elseif ($user->b2b_status === 'pending')
            <div class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Your application is <strong>under review</strong>. We'll email you within one business day.
            </div>
        @elseif ($user->b2b_status === 'rejected')
            <div class="mt-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800">
                Your previous application wasn't approved. Contact {{ company('email') }} for details or re-apply below.
            </div>
        @endif

        @if ($user->b2b_status !== 'approved')
            <form action="{{ route('account.business.apply') }}" method="post" class="mt-5 space-y-4">
                @csrf
                <div><label class="label">Registered company name</label><input name="company_name" value="{{ old('company_name', $user->company_name) }}" class="input" required></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="label">KRA PIN</label><input name="kra_pin" value="{{ old('kra_pin', $user->kra_pin) }}" class="input" required></div>
                    <div><label class="label">Contact phone</label><input name="phone" value="{{ old('phone', $user->phone) }}" class="input" required></div>
                </div>
                <button class="btn-primary">Submit application</button>
            </form>
        @endif
    </div>
</div>
@endsection
