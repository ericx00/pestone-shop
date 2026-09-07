@extends('layouts.storefront')
@section('title', 'Legacy Technology Support')

@section('content')
<x-page-shell title="Legacy Technology Support"
    lead="Extend the life of your existing IT investment — third-party maintenance, spares and migration planning.">
    <p>Many organisations continue to rely on older servers, network equipment and storage systems that are no longer
        covered by original manufacturer warranties. Pestone provides comprehensive legacy technology support — helping
        you maximise return on existing infrastructure, avoid unplanned expenditure, and plan migrations on your own
        timeline.</p>
    <h2>What we offer</h2>
    <ul>
        <li><strong>Third-Party Maintenance (TPM)</strong> — vendor-agnostic support for servers, storage and network equipment beyond OEM end-of-life, at significantly lower cost than OEM contracts.</li>
        <li><strong>Break &amp; Fix support</strong> — on-demand repair for legacy hardware incidents, by contract or one-off call-out.</li>
        <li><strong>Spare parts supply &amp; management</strong> — inventory for legacy IBM, HP, Dell, Lenovo and Fujitsu servers, networking and storage.</li>
        <li><strong>Hardware health assessment</strong> — full audit of your legacy estate with a prioritised remediation and migration roadmap.</li>
        <li><strong>Preventive maintenance</strong> — scheduled quarterly/annual programmes: cleaning, firmware updates, UPS servicing, cable management.</li>
        <li><strong>Platform migration services</strong> — structured migration from end-of-life platforms with parallel-running support.</li>
    </ul>
    <p><a href="{{ route('quote.create') }}">Request a legacy support assessment →</a></p>
</x-page-shell>
@endsection
