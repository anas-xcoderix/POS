@php $title = 'Job Cards'; @endphp
<x-erp-layout>
<div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Job no..." class="erp-input !mt-0 w-48">
            <select name="status" class="erp-input !mt-0 w-40">
                <option value="">All statuses</option>
                @foreach(['open','in_progress','completed','invoiced','cancelled'] as $s)
                    <option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            <button class="erp-btn-secondary">Filter</button>
        </form>
        <a href="{{ route('job-cards.create') }}" class="erp-btn-primary">
            <x-ui.icon name="plus" class="h-4 w-4" /> New Job Card
        </a>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Job No</th><th>Date</th><th>Customer</th><th>Vehicle</th><th>Mechanic</th><th>Status</th><th class="text-right">Total</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td class="font-semibold">{{ $row->job_no }}</td>
                            <td>{{ $row->job_date?->format('M d, Y') }}</td>
                            <td>{{ $row->customer?->name }}</td>
                            <td>{{ $row->vehicle?->plate_no ?? '—' }}</td>
                            <td>{{ $row->mechanic?->name ?? '—' }}</td>
                            <td><span class="erp-badge erp-badge-orange">{{ ucfirst(str_replace('_', ' ', $row->status)) }}</span></td>
                            <td class="text-right font-medium">{{ number_format($row->total_amount, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('job-cards.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-ui.empty-state title="No job cards" description="Create a job card to track workshop work." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())<div class="border-t px-5 py-4">{{ $records->links() }}</div>@endif
    </div>
</div>
</x-erp-layout>
