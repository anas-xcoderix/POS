@props(['type' => 'success'])

@php
$styles = match($type) {
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    'info' => 'bg-orange-50 border-orange-200 text-orange-800',
    default => 'bg-slate-50 border-slate-200 text-slate-800',
};
@endphp

<div x-data="{ show: true }" x-show="show" x-transition
     class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 {{ $styles }}" role="alert">
    <div class="flex-1 text-sm font-medium">{{ $slot }}</div>
    <button type="button" @click="show = false" class="opacity-60 hover:opacity-100">
        <x-ui.icon name="x" class="h-4 w-4" />
    </button>
</div>
