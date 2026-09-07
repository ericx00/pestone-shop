<footer class="mt-16 border-t border-slate-200 bg-navy text-slate-200">
    <div class="container-x grid gap-8 py-12 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <img src="{{ asset('brand/logo-white.svg') }}" alt="Pestone Technologies" class="h-10 w-auto">
            <p class="mt-3 text-sm text-slate-300">{{ company('tagline') }}. ICT solutions, maintenance support and legacy technology services across Kenya and East Africa.</p>
        </div>
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Shop</h3>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('shop') }}" class="hover:text-white">All products</a></li>
                @foreach (\App\Models\Category::topLevel()->where('is_active', true)->orderBy('name')->limit(6)->get() as $cat)
                    <li><a href="{{ route('category', $cat) }}" class="hover:text-white">{{ $cat->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Company</h3>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('page', 'about') }}" class="hover:text-white">About us</a></li>
                <li><a href="{{ route('page', 'business') }}" class="hover:text-white">For Business / B2B</a></li>
                <li><a href="{{ route('page', 'services') }}" class="hover:text-white">ICT & support services</a></li>
                <li><a href="{{ route('page', 'legacy-support') }}" class="hover:text-white">Legacy technology support</a></li>
                <li><a href="{{ route('quote.create') }}" class="hover:text-white">Request a quote</a></li>
            </ul>
        </div>
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Contact</h3>
            <ul class="mt-3 space-y-2 text-sm">
                <li>{{ company('po_box') }}</li>
                <li><a href="tel:{{ preg_replace('/\s+/', '', company('phone', '')) }}" class="hover:text-white">{{ company('phone') }}</a></li>
                <li><a href="mailto:{{ company('email') }}" class="hover:text-white">{{ company('email') }}</a></li>
                <li><a href="{{ route('page', 'delivery') }}" class="hover:text-white">Delivery & returns</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10 py-4">
        <div class="container-x flex flex-col items-center justify-between gap-2 text-xs text-slate-400 sm:flex-row">
            <span>&copy; {{ date('Y') }} {{ company('name') }}. All rights reserved.</span>
            <span class="flex gap-4">
                <a href="{{ route('page', 'terms') }}" class="hover:text-white">Terms</a>
                <a href="{{ route('page', 'privacy') }}" class="hover:text-white">Privacy</a>
                <span>Pay with M-Pesa · Visa · Mastercard</span>
            </span>
        </div>
    </div>
</footer>
