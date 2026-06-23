@php $title = __('modules.parts_master'); @endphp
<x-erp-layout>
<div x-data="{ createOpen: false, editOpen: false }">
    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1 sm:max-w-md">
                    <x-ui.icon name="search" class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.parts_search_placeholder') }}"
                           class="erp-input !mt-0 ps-10">
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="erp-btn-secondary">{{ __('ui.search') }}</button>
                    <a href="{{ route('documents.masters.parts.pdf') }}" target="_blank" class="erp-btn-secondary">
                        <x-ui.icon name="document" class="h-4 w-4" /> {{ __('ui.print_master') }}
                    </a>
                    <a href="{{ route('parts.import') }}" class="erp-btn-secondary">{{ __('ui.import_csv') }}</a>
                    <button type="button" @click="createOpen = true" class="erp-btn-primary">
                        <x-ui.icon name="plus" class="h-4 w-4" /> {{ __('modules.add_part') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>{{ __('pdf.part_no') }}</th><th>{{ __('pdf.brand') }}</th><th>{{ __('pdf.description') }}</th><th>{{ __('pdf.list_price') }}</th><th>{{ __('pdf.cost') }}</th><th class="text-end">{{ __('ui.actions') }}</th>
                </tr></thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td><span class="font-semibold text-slate-900">{{ $record->part_number }}</span>
                                @if($record->oem_no)<div class="text-xs text-slate-500">{{ __('pdf.oem') }}: {{ $record->oem_no }}</div>@endif
                            </td>
                            <td><span class="erp-badge erp-badge-slate">{{ $record->brand?->name }}</span></td>
                            <td class="max-w-xs truncate">{{ localized($record, 'description_en', 'description_ar') }}</td>
                            <td>{{ number_format($record->list_price, 2) }}</td>
                            <td>{{ number_format($record->cost_price, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('parts.kits', $record) }}" class="erp-btn-ghost !px-2.5 !py-2" title="{{ __('modules.kit_alternatives') }}"><x-ui.icon name="box" class="h-4 w-4" /></a>
                                <a href="{{ route('documents.part.label', $record) }}" target="_blank" class="erp-btn-ghost !px-2.5 !py-2" title="{{ __('ui.print') }}"><x-ui.icon name="document" class="h-4 w-4" /></a>
                                <button type="button" onclick="openPartEdit(@json($record))" class="erp-btn-ghost !px-2.5 !py-2"><x-ui.icon name="pencil" class="h-4 w-4" /></button>
                                <form method="POST" action="{{ route('parts.destroy', $record) }}" class="inline" onsubmit="return confirm(@json(__('ui.delete_confirm')))">
                                    @csrf @method('DELETE')
                                    <button class="erp-btn-danger !px-2.5 !py-2"><x-ui.icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-ui.empty-state :title="__('ui.no_parts')" :description="__('ui.no_parts_hint')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
    </div>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="createOpen = false"></div>
        <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="mb-5 text-lg font-bold">{{ __('modules.add_part') }}</h3>
            <form method="POST" action="{{ route('parts.store') }}">@csrf
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">@include('parts._form')</div>
                <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                    <button type="button" @click="createOpen = false" class="erp-btn-secondary">{{ __('ui.cancel') }}</button>
                    <button class="erp-btn-primary">{{ __('ui.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" id="partEditModal">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="editOpen = false"></div>
        <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="mb-5 text-lg font-bold">{{ __('ui.edit_part') }}</h3>
            <form method="POST" id="partEditForm">@csrf @method('PUT')
                <div id="partEditFields" class="grid grid-cols-1 gap-4 md:grid-cols-2">@include('parts._form')</div>
                <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                    <button type="button" @click="editOpen = false" class="erp-btn-secondary">{{ __('ui.cancel') }}</button>
                    <button class="erp-btn-primary">{{ __('ui.update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openPartEdit(record) {
    const root = document.getElementById('partEditModal').closest('[x-data]');
    document.getElementById('partEditForm').action = '/parts/' + record.id;
    document.querySelectorAll('#partEditFields [name]').forEach(el => {
        if (record[el.name] !== undefined) el.value = record[el.name] ?? '';
    });
    Alpine.$data(root).editOpen = true;
}
</script>
</x-erp-layout>
