@props(['route', 'icon', 'label'])

@php
$active = request()->routeIs($route) || (str_contains($route, '.index') && request()->routeIs(str_replace('.index', '.*', $route)));
@endphp

<a href="{{ route($route) }}" @click="$dispatch('close-sidebar')"
   class="{{ $active ? 'erp-sidebar-link-active' : 'erp-sidebar-link-inactive' }}">
    <x-ui.icon :name="$icon" class="h-5 w-5 shrink-0 {{ $active ? 'text-orange-500' : 'text-slate-400' }}" />
    <span class="truncate">{{ $label }}</span>
</a>
