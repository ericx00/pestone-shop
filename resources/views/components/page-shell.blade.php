@props(['title', 'lead' => null])

<div class="bg-navy text-white">
    <div class="container-x py-12">
        <h1 class="text-3xl font-bold">{{ $title }}</h1>
        @if ($lead)<p class="mt-3 max-w-2xl text-slate-300">{{ $lead }}</p>@endif
    </div>
</div>
<div class="container-x max-w-3xl space-y-4 py-10 text-slate-700 [&_h2]:mt-8 [&_h2]:text-lg [&_h2]:font-bold [&_h2]:text-navy [&_h3]:mt-4 [&_h3]:font-semibold [&_h3]:text-slate-800 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5 [&_a]:font-medium [&_a]:text-brand-700 [&_a:hover]:underline">
    {{ $slot }}
</div>
