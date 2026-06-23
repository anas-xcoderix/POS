@php $title = __('hr.employee_profile').' — '.$employee->name; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
            <div>
                <h2 class="text-xl font-bold">{{ localized($employee) }}</h2>
                <p class="text-sm text-slate-500">{{ $employee->employee_no }} · {{ localized($employee->department) ?? '—' }} · {{ localized($employee->branch) }}</p>
                @if($employee->job_title)<p class="text-sm text-slate-500">{{ $employee->job_title }}</p>@endif
            </div>
            <span class="erp-badge {{ $employee->is_active ? 'erp-badge-green' : 'erp-badge-slate' }}">{{ $employee->is_active ? __('ui.active') : __('ui.inactive') }}</span>
        </div>
        <dl class="mt-6 grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
            <div><dt class="text-slate-500">{{ __('forms.basic_salary') }}</dt><dd class="font-medium">{{ number_format($employee->basic_salary, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('hr.housing') }}</dt><dd>{{ number_format($employee->housing_allowance, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('hr.transport') }}</dt><dd>{{ number_format($employee->transport_allowance, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('hr.gosi') }}</dt><dd>{{ $employee->gosi_eligible ? __('ui.yes') : __('ui.no') }}</dd></div>
            @if($employee->bank_account)
                <div><dt class="text-slate-500">{{ __('hr.bank_account') }}</dt><dd>{{ $employee->bank_name }} — {{ $employee->bank_account }}</dd></div>
            @endif
            @if($employee->aqama_no)
                <div><dt class="text-slate-500">Aqama</dt><dd>{{ $employee->aqama_no }} · {{ $employee->aqama_expiry?->format('Y-m-d') ?? '—' }}</dd></div>
            @endif
            @if($employee->user)
                <div><dt class="text-slate-500">{{ __('hr.linked_user') }}</dt><dd>{{ $employee->user->name }}</dd></div>
            @endif
        </dl>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="erp-card p-6">
            <h3 class="mb-4 font-bold">{{ __('hr.attendance_summary') }} ({{ $month }}/{{ $year }})</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500">{{ __('hr.present') }}</dt><dd class="font-bold text-emerald-600">{{ $attendance['present'] + $attendance['half_day'] * 0.5 }}</dd></div>
                <div><dt class="text-slate-500">{{ __('hr.absent') }}</dt><dd class="font-bold text-red-600">{{ $attendance['absent'] }}</dd></div>
                <div><dt class="text-slate-500">{{ __('hr.leave_short') }}</dt><dd>{{ $attendance['leave'] }}</dd></div>
                <div><dt class="text-slate-500">{{ __('hr.half_day_short') }}</dt><dd>{{ $attendance['half_day'] }}</dd></div>
            </dl>
            <a href="{{ route('attendance.index', ['month' => $month, 'year' => $year]) }}" class="mt-4 inline-block text-sm text-orange-600 hover:underline">{{ __('hr.attendance') }}</a>
        </div>

        <div class="erp-card p-6">
            <h3 class="mb-4 font-bold">{{ __('hr.leave_balance') }} ({{ $year }})</h3>
            <ul class="space-y-2 text-sm">
                @foreach($leaveTypes as $type)
                    @php $bal = $leaveBalances[$type->id] ?? ['used' => 0, 'max' => 0, 'remaining' => 0]; @endphp
                    <li class="flex justify-between border-b border-slate-50 pb-2">
                        <span>{{ localized($type) }}</span>
                        <span>{{ $bal['used'] }} / {{ $bal['max'] }} ({{ __('hr.remaining') }}: {{ $bal['remaining'] }})</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4 font-bold">{{ __('hr.payroll_history') }}</div>
        <table class="erp-table min-w-full text-sm">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('hr.payroll_no') }}</th><th>{{ __('hr.period') }}</th><th class="text-right">{{ __('hr.net_pay') }}</th>
            </tr></thead>
            <tbody>
                @forelse($payrollHistory as $item)
                    <tr>
                        <td><a href="{{ route('payroll.show', $item->payrollRun) }}" class="text-orange-600 hover:underline">{{ $item->payrollRun?->payroll_no }}</a></td>
                        <td>{{ $item->payrollRun?->periodLabel() }}</td>
                        <td class="text-right font-medium">{{ number_format($item->net_pay, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-slate-400">{{ __('hr.no_payroll_runs') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('employees.index') }}" class="erp-btn-secondary">{{ __('ui.back') }}</a>
</div>
</x-erp-layout>
