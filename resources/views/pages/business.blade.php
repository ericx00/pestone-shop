@extends('layouts.storefront')
@section('title', 'For Business / B2B')

@section('content')
<x-page-shell title="For Business — B2B accounts, SLAs &amp; procurement"
    lead="Trade pricing, quotations against LPOs, structured SLA support and a single point of contact for your ICT needs.">

    <h2>Open a business account</h2>
    <p>Register a business account and, once approved, you'll see <strong>trade pricing (excl. VAT)</strong> across the
        shop, place orders against LPOs, and request formal quotations. Approval usually takes one business day.</p>
    <p>
        @auth
            <a href="{{ route('account.business') }}" class="!text-white btn-primary">Apply for a business account</a>
        @else
            <a href="{{ route('register') }}" class="!text-white btn-primary">Create a business account</a>
        @endauth
        &nbsp; <a href="{{ route('quote.create') }}">or request a quote →</a>
    </p>

    <h2>Service Level Agreements</h2>
    <p>Every SLA is tailored to your infrastructure criticality, with a single point of contact managing your support
        from ticket to resolution.</p>
    <div class="not-prose overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-slate-300 text-left text-navy">
                <th class="py-2 pr-4">Tier</th><th class="py-2 pr-4">Response</th><th class="py-2 pr-4">On-site</th><th class="py-2">Coverage</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-200">
                <tr><td class="py-2 pr-4 font-medium">Platinum (Mission Critical)</td><td class="py-2 pr-4">15 minutes</td><td class="py-2 pr-4">2 hours</td><td class="py-2">24 × 7 × 365</td></tr>
                <tr><td class="py-2 pr-4 font-medium">Gold (Business Critical)</td><td class="py-2 pr-4">30 minutes</td><td class="py-2 pr-4">4 hours</td><td class="py-2">24 × 7 × 365</td></tr>
                <tr><td class="py-2 pr-4 font-medium">Silver (Standard)</td><td class="py-2 pr-4">2 hours</td><td class="py-2 pr-4">Next business day</td><td class="py-2">Business hours</td></tr>
                <tr><td class="py-2 pr-4 font-medium">Bronze (Best Effort)</td><td class="py-2 pr-4">4 hours</td><td class="py-2 pr-4">Scheduled</td><td class="py-2">Business hours</td></tr>
            </tbody>
        </table>
    </div>
    <p>All SLA plans include 24×7 helpdesk access, a dedicated account manager, monthly reporting, and root cause analysis
        for critical incidents.</p>

    <h2>ICT services</h2>
    <ul>
        <li>Consulting &amp; technology advisory</li>
        <li>Project management &amp; deployment</li>
        <li>Proactive monitoring &amp; support</li>
        <li>On-site outsourced ICT staff</li>
    </ul>

    <h2>Talk to us</h2>
    <p>{{ company('email') }} · {{ company('phone') }} · {{ company('po_box') }}</p>
</x-page-shell>
@endsection
