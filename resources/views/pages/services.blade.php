@extends('layouts.storefront')
@section('title', 'ICT & Support Services')

@section('content')
<x-page-shell title="ICT &amp; Support Services"
    lead="Consulting, deployment, monitoring and outsourced ICT staff — delivered by certified engineers.">
    <h2>Consulting services</h2>
    <p>Technology assessment and advisory to help you select, plan and justify the right ICT investments for your business
        objectives and budget.</p>
    <h2>Project management &amp; deployment</h2>
    <p>End-to-end project delivery — design, procurement, installation, testing and handover — managed by experienced
        systems engineers.</p>
    <h2>Proactive monitoring &amp; support</h2>
    <p>Performance monitoring and fault detection using industry-standard tools, so mission-critical issues are resolved
        before they cause disruption.</p>
    <h2>On-site outsourced ICT staff</h2>
    <p>Embedded technical support staff for organisations without sufficient in-house ICT resources — expert capacity
        without the cost of full-time employment.</p>
    <h2>Workshop &amp; repair</h2>
    <p>Hardware upgrades, OS installation, diagnostics, data backup/clone and on-site support for desktops, laptops and
        all-in-ones — in and out of warranty. <a href="{{ route('quote.create') }}">Request a service quote →</a></p>
</x-page-shell>
@endsection
