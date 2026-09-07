@extends('layouts.storefront')
@section('title', 'Create an account')

@section('content')
<div class="container-x max-w-lg py-12" x-data="{ type: '{{ old('account_type', 'b2c') }}' }">
    <div class="card p-6">
        <h1 class="text-xl font-bold text-navy">Create your account</h1>

        @if ($errors->any())
            <div class="mt-4 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">
                <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="post" class="mt-5 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-2 text-sm">
                <label class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                    <input type="radio" name="account_type" value="b2c" x-model="type" class="text-brand-600"> Personal
                </label>
                <label class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                    <input type="radio" name="account_type" value="b2b" x-model="type" class="text-brand-600"> Business
                </label>
            </div>

            <div><label class="label">Full name</label><input name="name" value="{{ old('name') }}" class="input" required></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="input" required></div>
                <div><label class="label">Phone</label><input name="phone" value="{{ old('phone') }}" class="input" required></div>
            </div>

            <div x-show="type === 'b2b'" class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Company name</label><input name="company_name" value="{{ old('company_name') }}" class="input"></div>
                <div><label class="label">KRA PIN</label><input name="kra_pin" value="{{ old('kra_pin') }}" class="input"></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="label">Password</label><input type="password" name="password" class="input" required></div>
                <div><label class="label">Confirm password</label><input type="password" name="password_confirmation" class="input" required></div>
            </div>

            <p x-show="type === 'b2b'" class="rounded-lg bg-brand-50 px-3 py-2 text-xs text-brand-800">
                Business accounts are reviewed within one business day. You can shop at standard prices immediately;
                trade pricing unlocks once approved.
            </p>

            <button class="btn-primary w-full">Create account</button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-500">
            Already have an account? <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">Sign in</a>
        </p>
    </div>
</div>
@endsection
