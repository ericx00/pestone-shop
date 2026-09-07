@extends('layouts.storefront')
@section('title', 'About us')

@section('content')
<x-page-shell title="About Pestone Technologies" lead="{{ company('tagline') }} — end-to-end ICT solutions across Kenya and East Africa.">
    <p>Pestone Technologies Ltd (PTL) is one of Kenya's fastest-growing ICT solutions companies, headquartered in Nairobi
        and serving organisations across the country and the wider East African region. We provide end-to-end technology
        solutions spanning computing infrastructure, connectivity, software, security, and managed support services.</p>
    <p>We help businesses of all sizes — from SMEs to mid-size enterprises — conceptualise, deploy, and sustain technology
        that drives operational efficiency and growth. Our engineers combine global technology expertise with an
        understanding of the local business environment to deliver solutions on time and within budget.</p>

    <h2>Our solution pillars</h2>
    <ul>
        <li><strong>Computing Infrastructure</strong> — servers, workstations, desktops and laptops from HP, Dell, Lenovo, Fujitsu and Acer.</li>
        <li><strong>Network &amp; Connectivity</strong> — structured cabling, switching and routing from Cisco, Juniper, D-Link, HP and Nexans.</li>
        <li><strong>Security Solutions</strong> — UTM, next-gen firewalls, endpoint and email security from Fortinet, Cisco, Kaspersky and Bitdefender.</li>
        <li><strong>Backup &amp; Storage</strong> — enterprise backup, storage and disaster recovery from Dell EMC, HP, Veeam, Veritas and Seagate.</li>
        <li><strong>Power &amp; UPS</strong> — uninterruptible power supplies from APC, Eaton and MGE.</li>
        <li><strong>Printing &amp; Imaging</strong> — document and printing solutions from Canon, HP, Epson, Kyocera and Brother.</li>
    </ul>

    <h2>Contact</h2>
    <p>{{ company('po_box') }}<br>
        Phone: <a href="tel:{{ preg_replace('/\s+/', '', company('phone')) }}">{{ company('phone') }}</a><br>
        Email: <a href="mailto:{{ company('email') }}">{{ company('email') }}</a></p>
</x-page-shell>
@endsection
