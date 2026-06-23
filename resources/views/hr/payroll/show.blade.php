@php $title = 'Payroll '.$run->payroll_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ $run->payroll_no }}</h2>
            <p class="text-sm text-slate-500">{{ $run->periodLabel() }} · {{ $run->branch?->name ?? 'All branches' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="erp-badge {{ $run->status === 'posted' ? 'erp-badge-green' : 'erp-badge-orange' }}">{{ ucfirst($run->status) }}</span>
            @if($run->status === 'draft')
                <form method="POST" action="{{ route('payroll.post', $run) }}">
                    @csrf
                    <button class="erp-btn-primary" onclick="return confirm('Post this payroll?')">Post Payroll</button>
                </form>
            @endif
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Employee</th><th>Department</th><th>Present</th><th>Absent</th>
                <th class="text-right">Basic</th><th class="text-right">Allowances</th><th class="text-right">Deductions</th><th class="text-right">Net Pay</th>
            </tr></thead>
            <tbody>
                @foreach($run->items as $item)
                    <tr>
                        <td class="font-medium">{{ $item->employee?->name }}</td>
                        <td>{{ $item->employee?->department?->name ?? '—' }}</td>
                        <td>{{ $item->days_present }}</td>
                        <td>{{ $item->days_absent }}</td>
                        <td class="text-right">{{ number_format($item->basic_salary, 2) }}</td>
                        <td class="text-right">{{ number_format($item->housing_allowance + $item->transport_allowance, 2) }}</td>
                        <td class="text-right">{{ number_format($item->deductions, 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($item->net_pay, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50/50">
                <tr>
                    <td colspan="7" class="text-right font-semibold">Total</td>
                    <td class="text-right text-lg font-bold">{{ number_format($run->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <a href="{{ route('payroll.index') }}" class="erp-btn-secondary">Back to Payroll</a>
</div>
</x-erp-layout>
