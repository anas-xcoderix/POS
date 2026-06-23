@props(['route', 'icon', 'label'])

@php
$active = request()->routeIs($route) || (str_contains($route, '.index') && request()->routeIs(str_replace('.index', '.*', $route)));
@endphp

<a href="{{ route($route) }}" title="{{ $label }}"
   class="group relative flex flex-col items-center gap-1 py-1">
    @if($active)
        <span class="absolute -left-3 top-1/2 h-8 w-1 -translate-y-1/2 rounded-r-full bg-cyan-400"></span>
    @endif
    <span class="{{ $active ? 'erp-sidebar-icon-active' : 'erp-sidebar-icon-inactive' }}">
        <x-ui.icon :name="$icon" class="h-5 w-5" />
    </span>
    <span class="hidden text-[10px] font-medium text-slate-500 group-hover:text-slate-300 xl:block {{ $active ? '!text-cyan-400' : '' }}">
        {{ Str::limit($label, 8) }}
    </span>
</a>
