@extends('layouts.storefront')
@section('title', 'Delivery & Returns')

@section('content')
<x-page-shell title="Delivery &amp; Returns">
    <h2>Delivery</h2>
    <ul>
        <li><strong>Nairobi CBD / Westlands</strong> — same or next business day.</li>
        <li><strong>Greater Nairobi</strong> — 1–2 business days.</li>
        <li><strong>Countrywide</strong> — 2–4 business days via courier.</li>
        <li><strong>Office pickup</strong> — collect from {{ company('po_box') }} once you receive a "ready" notification.</li>
    </ul>
    <p>Delivery fees are shown at checkout and depend on your zone and order value. Large or project orders are quoted
        individually.</p>
    <h2>Payment</h2>
    <p>We accept M-Pesa (STK push) and card payments via Pesapal. Approved business accounts may arrange payment against
        an LPO — <a href="{{ route('quote.create') }}">contact us</a>.</p>
    <h2>Returns &amp; warranty</h2>
    <ul>
        <li>Report dead-on-arrival or defective items within 7 days of delivery.</li>
        <li>Manufacturer warranty applies as stated on each product; we facilitate warranty claims.</li>
        <li>Software, consumables and opened items are non-returnable unless faulty.</li>
    </ul>
    <p>Questions: {{ company('email') }} · {{ company('phone') }}</p>
</x-page-shell>
@endsection
