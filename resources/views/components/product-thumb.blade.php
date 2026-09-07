@props(['product', 'class' => 'aspect-square'])

@php
    $img = $product->primaryImage();
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden rounded-lg bg-slate-100 $class"]) }}>
    @if ($img)
        <img src="{{ \Illuminate\Support\Str::startsWith($img, ['http', '/']) ? $img : \Illuminate\Support\Facades\Storage::url($img) }}"
             alt="{{ $product->name }}" class="h-full w-full object-contain p-3" loading="lazy">
    @else
        <div class="flex h-full w-full flex-col items-center justify-center gap-2 p-4 text-center">
            <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.51 2.51m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $product->brand?->name ?? 'Pestone' }}</span>
        </div>
    @endif
</div>
