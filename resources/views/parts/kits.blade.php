@php $title = __('modules.kit_alternatives').' — '.$part->part_number; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $part->part_number }}</h2>
                <p class="text-sm text-slate-500">{{ $part->brand?->name }} · {{ $part->description_en }}</p>
            </div>
            <a href="{{ route('parts.index') }}" class="erp-btn-secondary shrink-0">{{ __('pages.actions.back_to_parts') }}</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Kit Components --}}
        <div class="erp-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Kit Components</h3>
                <p class="mt-1 text-xs text-slate-500">Parts included when this item is sold or issued as a kit.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table min-w-full">
                    <thead class="bg-slate-50/80"><tr>
                        <th>Component</th><th class="text-right">Qty</th><th class="text-right">{{ __('pages.table.action') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($kits as $kit)
                            <tr>
                                <td>
                                    <span class="font-semibold">{{ $kit->componentPart?->part_number }}</span>
                                    <div class="text-xs text-slate-500">{{ Str::limit($kit->componentPart?->description_en, 36) }}</div>
                                </td>
                                <td class="text-right font-medium">{{ number_format($kit->quantity, 2) }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('parts.kits.destroy', [$part, $kit]) }}" class="inline" onsubmit="return confirm('Remove this component?')">
                                        @csrf @method('DELETE')
                                        <button class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3">
                                <x-ui.empty-state title="{{ __('pages.empty.kit_components') }}" description="{{ __('pages.empty.kit_components_hint') }}" />
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 p-5">
                <form method="POST" action="{{ route('parts.kits.store', $part) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    <x-ui.form-field label="Component Part" name="component_part_id" type="select" required class="flex-1">
                        @foreach($parts as $p)
                            <option value="{{ $p->id }}">{{ $p->part_number }} — {{ Str::limit($p->description_en, 28) }}</option>
                        @endforeach
                    </x-ui.form-field>
                    <x-ui.form-field label="Quantity" name="quantity" type="number" :value="1" required class="sm:w-28" />
                    <button type="submit" class="erp-btn-primary shrink-0">Add Component</button>
                </form>
            </div>
        </div>

        {{-- Alternative Parts --}}
        <div class="erp-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Alternative Parts</h3>
                <p class="mt-1 text-xs text-slate-500">Substitute parts that can replace this item.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table min-w-full">
                    <thead class="bg-slate-50/80"><tr>
                        <th>Alternative</th><th>Notes</th><th class="text-right">{{ __('pages.table.action') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($alternatives as $alt)
                            <tr>
                                <td>
                                    <span class="font-semibold">{{ $alt->alternativePart?->part_number }}</span>
                                    <div class="text-xs text-slate-500">{{ Str::limit($alt->alternativePart?->description_en, 36) }}</div>
                                </td>
                                <td class="text-sm text-slate-500">{{ $alt->notes ?? '—' }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('parts.alternatives.destroy', [$part, $alt]) }}" class="inline" onsubmit="return confirm('Remove this alternative?')">
                                        @csrf @method('DELETE')
                                        <button class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3">
                                <x-ui.empty-state title="{{ __('pages.empty.alternatives') }}" description="{{ __('pages.empty.alternatives_hint') }}" />
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 p-5">
                <form method="POST" action="{{ route('parts.alternatives.store', $part) }}" class="flex flex-col gap-3">
                    @csrf
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <x-ui.form-field label="Alternative Part" name="alternative_part_id" type="select" required class="flex-1">
                            @foreach($parts as $p)
                                <option value="{{ $p->id }}">{{ $p->part_number }} — {{ Str::limit($p->description_en, 28) }}</option>
                            @endforeach
                        </x-ui.form-field>
                        <x-ui.form-field label="Notes" name="notes" class="flex-1" />
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="erp-btn-primary">Add Alternative</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-erp-layout>
