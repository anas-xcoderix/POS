@props(['label', 'name', 'type' => 'text', 'required' => false, 'hint' => null, 'value' => null])

<div {{ $attributes->merge(['class' => '']) }}>
    <label for="{{ $name }}" class="erp-label">
        {{ $label }}
        @if($required)<span class="text-red-500">*</span>@endif
    </label>
    @if($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" @if($required) required @endif
                  class="erp-input" rows="3">{{ $value ?? $slot }}</textarea>
    @elseif($type === 'select')
        <select id="{{ $name }}" name="{{ $name }}" @if($required) required @endif class="erp-input">
            {{ $slot }}
        </select>
    @elseif($type === 'checkbox')
        <label class="mt-2 inline-flex cursor-pointer items-center gap-2.5">
            <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1"
                   @checked($value === '1' || $value === 1 || $value === true)
                   class="rounded border-slate-300 text-orange-500 focus:ring-orange-400">
            <span class="text-sm text-slate-600">{{ $slot }}</span>
        </label>
    @else
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
               value="{{ $value ?? '' }}"
               @if($required) required @endif
               class="erp-input"
               {{ $attributes->except(['label','name','type','required','hint','value','class']) }}>
    @endif
    @if($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
