@extends('layouts.storefront')
@section('title', 'Privacy Policy')

@section('content')
<x-page-shell title="Privacy Policy">
    <p>This policy explains how {{ company('name') }} handles your personal data, in line with the Data Protection Act, 2019.</p>
    <h2>What we collect</h2>
    <ul>
        <li>Contact and delivery details you provide at registration, checkout or when requesting a quote.</li>
        <li>Order history and communications with our team.</li>
        <li>For business accounts: company name and KRA PIN for invoicing and verification.</li>
    </ul>
    <h2>How we use it</h2>
    <ul>
        <li>To process orders, payments, delivery and warranty claims.</li>
        <li>To provide quotations and account management.</li>
        <li>To meet tax and legal obligations.</li>
    </ul>
    <h2>Sharing</h2>
    <p>We share data only with payment processors (Safaricom, Pesapal) and delivery partners as needed to fulfil your
        order. We do not sell your data.</p>
    <h2>Your rights</h2>
    <p>You may request access to, correction of, or deletion of your personal data by emailing {{ company('email') }}.</p>
</x-page-shell>
@endsection
