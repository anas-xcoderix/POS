@props(['label', 'value', 'icon' => 'box', 'color' => 'indigo', 'href' => null])

@php
$colors = [
    'indigo' => 'from-indigo-500 to-indigo-600 shadow-indigo-500/25',
    'emerald' => 'from-emerald-500 to-emerald-600 shadow-emerald-500/25',
    'amber' => 'from-amber-500 to-amber-600 shadow-amber-500/25',
    'sky' => 'from-sky-500 to-sky-600 shadow-sky-500/25',
];
@endphp

@if($href)
<a href="{{ $href }}" class="erp-card group block p-5 transition hover:-translate-y-0.5 hover:shadow-md">
@else
<div class="erp-card p-5">
@endif
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
        </div>
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br {{ $colors[$color] ?? $colors['indigo'] }} text-white shadow-lg">
            <x-ui.icon :name="$icon" class="h-5 w-5" />
        </div>
    </div>
@if($href)
</a>
@else
</div>
@endif
