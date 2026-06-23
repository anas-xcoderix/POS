@props(['title' => '', 'createLabel' => 'Add New', 'columns' => [], 'records' => null, 'formFields' => '', 'resource' => '', 'search' => null, 'emptyTitle' => 'No records found'])

@php $title = $title ?? 'Records'; @endphp
<x-erp-layout>
<div x-data="{ createOpen: false, editOpen: false }">
    <div class="erp-card overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" class="flex flex-1 gap-2 sm:max-w-md">
                <div class="relative flex-1">
                    <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search..." class="erp-input !mt-0 pl-10">
                </div>
                <button type="submit" class="erp-btn-secondary shrink-0">Search</button>
            </form>
            <button type="button" @click="createOpen = true" class="erp-btn-primary shrink-0">
                <x-ui.icon name="plus" class="h-4 w-4" /> {{ $createLabel }}
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    @foreach($columns as $col)<th>{{ $col['label'] }}</th>@endforeach
                    <th class="text-right">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            @foreach($columns as $col)
                                <td>
                                    @php $val = isset($col['relation']) ? data_get($record, $col['relation'].'.'.$col['field']) : data_get($record, $col['field']); @endphp
                                    @if(($col['type'] ?? '') === 'boolean')
                                        <span class="erp-badge {{ $val ? 'erp-badge-green' : 'erp-badge-slate' }}">{{ $val ? 'Active' : 'Inactive' }}</span>
                                    @else
                                        {{ $val ?: '—' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" onclick="openEdit(@json($record))" class="erp-btn-ghost !px-2.5 !py-2" title="Edit">
                                        <x-ui.icon name="pencil" class="h-4 w-4" />
                                    </button>
                                    <form method="POST" action="{{ url($resource.'/'.$record->id) }}" class="inline" onsubmit="return confirm('Delete this record?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="erp-btn-danger !px-2.5 !py-2" title="Delete">
                                            <x-ui.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($columns) + 1 }}">
                            <x-ui.empty-state :title="$emptyTitle" description="Click the button above to add your first record." />
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records instanceof \Illuminate\Pagination\LengthAwarePaginator && $records->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
        @endif
    </div>

    {{-- Create modal --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="createOpen = false">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="createOpen = false"></div>
        <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">{{ $createLabel }}</h3>
                <button type="button" @click="createOpen = false" class="text-slate-400 hover:text-slate-600"><x-ui.icon name="x" class="h-5 w-5" /></button>
            </div>
            <form method="POST" action="{{ url($resource) }}">
                @csrf
                <div class="space-y-4">{!! $formFields !!}</div>
                <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" @click="createOpen = false" class="erp-btn-secondary">Cancel</button>
                    <button type="submit" class="erp-btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit modal --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="editOpen = false">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="editOpen = false"></div>
        <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Edit Record</h3>
                <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-600"><x-ui.icon name="x" class="h-5 w-5" /></button>
            </div>
            <form method="POST" id="editForm">
                @csrf @method('PUT')
                <div id="editFields" class="space-y-4">{!! $formFields !!}</div>
                <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" @click="editOpen = false" class="erp-btn-secondary">Cancel</button>
                    <button type="submit" class="erp-btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEdit(record) {
    const root = document.getElementById('editForm').closest('[x-data]');
    document.getElementById('editForm').action = '{{ url($resource) }}/' + record.id;
    document.querySelectorAll('#editFields [name]').forEach(el => {
        if (record[el.name] !== undefined) {
            if (el.type === 'checkbox') el.checked = !!record[el.name];
            else el.value = record[el.name] ?? '';
        }
    });
    Alpine.$data(root).editOpen = true;
}
</script>
</x-erp-layout>
