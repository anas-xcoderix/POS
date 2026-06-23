@php $title = __('modules.journal_entries'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative max-w-sm flex-1">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.search_placeholder') }}" class="erp-input !mt-0 pl-10">
        </form>
        <a href="{{ route('journal-entries.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> {{ __('modules.manual_journal') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead><tr>
                <th>{{ __('ui.reference') }}</th><th>{{ __('ui.date') }}</th><th>{{ __('ui.branch') }}</th><th>{{ __('ui.description') }}</th><th>{{ __('ui.status') }}</th><th class="text-right">{{ __('pages.table.action') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->entry_no }}</td>
                        <td>{{ $row->entry_date?->format('M d, Y') }}</td>
                        <td>{{ $row->branch?->code }}</td>
                        <td class="max-w-xs truncate">{{ $row->description }}</td>
                        <td><span class="erp-badge erp-badge-green">{{ $row->status === 'posted' ? __('ui.posted') : ucfirst($row->status) }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('journal-entries.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state :title="__('pages.finance.no_journals')" :description="__('pages.finance.no_journals_hint')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
