<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pestone Technologies') — {{ company('name', 'Pestone Technologies Ltd') }}</title>
    <meta name="description" content="@yield('meta_description', 'Buy laptops, servers, networking, printers, UPS and ICT accessories in Kenya. Business & personal. Proven Technology Solutions from Pestone Technologies.')">
    <link rel="icon" href="{{ asset('brand/favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-full flex-col">
    @include('partials.header')

    <main class="flex-1">
        @if (session('status'))
            <div class="container-x mt-4">
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">{{ session('status') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="container-x mt-4">
                <div class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-200">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')
    @stack('scripts')
</body>
</html>
