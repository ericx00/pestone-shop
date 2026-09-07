@extends('layouts.storefront')
@section('title', 'Terms & Conditions')

@section('content')
<x-page-shell title="Terms &amp; Conditions">
    <p>These terms govern your use of the {{ company('name') }} online shop and any purchase made through it.</p>
    <h2>Orders &amp; pricing</h2>
    <p>All prices are in Kenyan Shillings and include 16% VAT unless shown as a business (ex-VAT) price. Prices and
        availability may change without notice. An order is confirmed only once payment is received or, for approved
        business accounts, an LPO is accepted.</p>
    <h2>Payment</h2>
    <p>Payments are processed by Safaricom (M-Pesa) and Pesapal. We do not store card details.</p>
    <h2>Delivery &amp; risk</h2>
    <p>Risk passes to you on delivery or collection. Delivery timelines are estimates.</p>
    <h2>Warranty &amp; liability</h2>
    <p>Products carry the manufacturer's warranty as stated. To the extent permitted by law, our liability is limited to
        the value of the goods supplied.</p>
    <h2>Contact</h2>
    <p>{{ company('name') }}, {{ company('po_box') }} · {{ company('email') }} · {{ company('phone') }}</p>
</x-page-shell>
@endsection
