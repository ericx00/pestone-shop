@extends('layouts.storefront')
@section('title', 'Sign in')

@section('content')
<div class="container-x max-w-md py-12">
    <div class="card p-6">
        <h1 class="text-xl font-bold text-navy">Sign in</h1>
        <p class="mt-1 text-sm text-slate-500">Access your orders, business pricing and quotes.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login.store') }}" method="post" class="mt-5 space-y-4">
            @csrf
            <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="input" required autofocus></div>
            <div><label class="label">Password</label><input type="password" name="password" class="input" required></div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Remember me
            </label>
            <button class="btn-primary w-full">Sign in</button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-500">
            New here? <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:underline">Create an account</a>
        </p>
    </div>
</div>
@endsection
