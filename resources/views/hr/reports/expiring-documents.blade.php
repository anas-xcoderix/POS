@php $title = 'Expiring Documents'; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-4">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <x-ui.form-field label="Within days" name="days" type="number" :value="$days" class="w-32" />
            <button class="erp-btn-primary shrink-0">Refresh</button>
        </form>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50 px-5 py-3 font-bold">Employee Aqama & License</div>
        <table class="erp-table min-w-full">
            <thead><tr>
                <th>Employee</th><th>Branch</th><th>Aqama No</th><th>Aqama Expiry</th><th>License No</th><th>License Expiry</th>
            </tr></thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td class="font-medium">{{ $emp->name }}</td>
                        <td>{{ $emp->branch?->name }}</td>
                        <td>{{ $emp->aqama_no ?? '—' }}</td>
                        <td class="{{ $emp->aqama_expiry && $emp->aqama_expiry->isPast() ? 'text-red-600 font-semibold' : '' }}">{{ $emp->aqama_expiry?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $emp->license_no ?? '—' }}</td>
                        <td class="{{ $emp->license_expiry && $emp->license_expiry->isPast() ? 'text-red-600 font-semibold' : '' }}">{{ $emp->license_expiry?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-6 text-center text-sm text-slate-500">No expiring employee documents in this window.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50 px-5 py-3 font-bold">Vehicle Istimara</div>
        <table class="erp-table min-w-full">
            <thead><tr>
                <th>Plate</th><th>Customer</th><th>Make / Model</th><th>Istimara Expiry</th>
            </tr></thead>
            <tbody>
                @forelse($vehicles as $veh)
                    <tr>
                        <td class="font-medium">{{ $veh->plate_no }}</td>
                        <td>{{ $veh->customer?->name ?? '—' }}</td>
                        <td>{{ $veh->make }} {{ $veh->model }}</td>
                        <td class="{{ $veh->istimara_expiry && $veh->istimara_expiry->isPast() ? 'text-red-600 font-semibold' : '' }}">{{ $veh->istimara_expiry?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-sm text-slate-500">No expiring vehicle registrations in this window.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-erp-layout>
