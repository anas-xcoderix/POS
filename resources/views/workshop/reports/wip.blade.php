@php $title = __('modules.wip'); @endphp
<x-erp-layout>
<div class="space-y-4">
    <div class="erp-card p-4">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <x-ui.form-field label="Branch" name="branch_id" type="select" class="sm:w-64">
                <option value="">All branches</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                @endforeach
            </x-ui.form-field>
            <button class="erp-btn-primary shrink-0">Filter</button>
        </form>
    </div>

    <div class="erp-card p-4 text-sm text-slate-600">
        Open jobs: <strong>{{ $records->count() }}</strong> · Total WIP value: <strong>{{ number_format($totalWip, 2) }}</strong>
    </div>

    <div class="erp-card overflow-hidden">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Job No</th><th>Customer</th><th>Vehicle</th><th>Mechanic</th><th>Promised</th><th>Status</th><th class="text-right">Total</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->job_no }}</td>
                        <td>{{ $row->customer?->name }}</td>
                        <td>{{ $row->vehicle?->plate_no ?? '—' }}</td>
                        <td>{{ $row->mechanic?->name ?? '—' }}</td>
                        <td>{{ $row->promised_date?->format('M d, Y') ?? '—' }}</td>
                        <td><span class="erp-badge erp-badge-orange">{{ ucfirst(str_replace('_', ' ', $row->status)) }}</span></td>
                        <td class="text-right font-medium">{{ number_format($row->total_amount, 2) }}</td>
                        <td><a href="{{ route('job-cards.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-ui.empty-state title="No WIP jobs" description="All job cards are completed or invoiced." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-erp-layout>
