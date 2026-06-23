@php $title = __('modules.fiscal_periods'); @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <x-ui.icon name="cog" class="h-5 w-5 text-slate-500" />
            Close Period
        </h3>
        <form method="POST" action="{{ route('finance.periods.close') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            @csrf
            <x-ui.form-field label="Year" name="year" type="number" :value="now()->year" required class="sm:w-36" />
            <x-ui.form-field label="Month" name="month" type="select" required class="sm:w-44">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($m === (int) now()->month)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endforeach
            </x-ui.form-field>
            <button type="submit" class="erp-btn-primary" onclick="return confirm('Close this period? No postings will be allowed until reopened.')">
                Close Period
            </button>
        </form>
        <p class="mt-3 text-xs text-slate-500">Closing a period prevents new journal entries, invoices, and payments for that month.</p>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-bold text-slate-900">Period History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Period</th><th>Status</th><th>Closed At</th><th>Closed By</th><th class="text-right">Action</th>
                </tr></thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td class="font-semibold">{{ $row->label() }}</td>
                            <td>
                                <span class="erp-badge {{ $row->is_closed ? 'erp-badge-slate' : 'erp-badge-green' }}">
                                    {{ $row->is_closed ? 'Closed' : 'Open' }}
                                </span>
                            </td>
                            <td>{{ $row->closed_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td>{{ $row->closedByUser?->name ?? '—' }}</td>
                            <td class="text-right">
                                @if($row->is_closed)
                                    <form method="POST" action="{{ route('finance.periods.reopen', $row) }}" class="inline">
                                        @csrf
                                        <button class="erp-btn-ghost !py-1.5 !px-3 text-xs" onclick="return confirm('Reopen this period?')">Reopen</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">Current</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">
                            <x-ui.empty-state title="No fiscal periods" description="Periods are created automatically when you post transactions." />
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
    </div>
</div>
</x-erp-layout>
