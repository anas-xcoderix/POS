@php $title = __('hr.payroll').' '.$run->payroll_no; @endphp
<x-erp-layout>
<div class="space-y-6" x-data="{ editId: null }">
    <div class="erp-card p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ $run->payroll_no }}</h2>
            <p class="text-sm text-slate-500">{{ $run->periodLabel() }} · {{ $run->branch ? localized($run->branch) : __('hr.all_branches') }}</p>
            <p class="text-xs text-slate-400">{{ __('hr.total_employees') }}: {{ $run->items->count() }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="erp-badge {{ $run->status === 'posted' ? 'erp-badge-green' : 'erp-badge-orange' }}">{{ __('ui.'.$run->status) }}</span>
            <span class="erp-badge {{ $run->payment_status === 'paid' ? 'erp-badge-green' : 'erp-badge-slate' }}">{{ __('hr.'.$run->payment_status) }}</span>
            @if($run->isEditable())
                <form method="POST" action="{{ route('payroll.regenerate', $run) }}" class="inline">
                    @csrf
                    <button class="erp-btn-secondary text-sm" onclick="return confirm('{{ __('hr.regenerate_confirm') }}')">{{ __('hr.regenerate') }}</button>
                </form>
                <form method="POST" action="{{ route('payroll.post', $run) }}" class="inline">
                    @csrf
                    <button class="erp-btn-primary text-sm" onclick="return confirm('{{ __('hr.post_payroll_confirm') }}')">{{ __('hr.post_payroll') }}</button>
                </form>
            @elseif($run->status === 'posted' && $run->payment_status === 'unpaid')
                <button type="button" @click="$refs.payModal.showModal()" class="erp-btn-primary text-sm">{{ __('hr.pay_payroll') }}</button>
            @endif
        </div>
    </div>

    <div class="erp-card overflow-x-auto">
        <table class="erp-table min-w-full text-sm">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('hr.employee') }}</th>
                <th class="text-right">{{ __('hr.basic') }}</th>
                <th class="text-right">{{ __('hr.allowances') }}</th>
                <th class="text-right">{{ __('hr.overtime') }}</th>
                <th class="text-right">{{ __('hr.bonus') }}</th>
                <th class="text-right">{{ __('hr.deductions') }}</th>
                <th class="text-right">{{ __('hr.gosi') }}</th>
                <th class="text-right">{{ __('hr.net_pay') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr></thead>
            <tbody>
                @foreach($run->items as $item)
                    <tr>
                        <td>
                            <a href="{{ route('employees.show', $item->employee) }}" class="font-medium text-orange-600 hover:underline">{{ localized($item->employee) }}</a>
                            <div class="text-xs text-slate-500">{{ $item->days_present }}P / {{ $item->days_absent }}A</div>
                        </td>
                        <td class="text-right">{{ number_format($item->basic_salary, 2) }}</td>
                        <td class="text-right">{{ number_format($item->housing_allowance + $item->transport_allowance, 2) }}</td>
                        <td class="text-right">{{ number_format($item->overtime_amount, 2) }}</td>
                        <td class="text-right">{{ number_format($item->bonus_amount, 2) }}</td>
                        <td class="text-right">{{ number_format($item->deductions + $item->loan_deduction + $item->other_deductions, 2) }}</td>
                        <td class="text-right">{{ number_format($item->gosi_deduction, 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($item->net_pay, 2) }}</td>
                        <td>
                            <div class="flex gap-2">
                                @if($run->isEditable())
                                    <button type="button" @click="editId = {{ $item->id }}" class="text-xs text-orange-600 hover:underline">{{ __('hr.edit_line') }}</button>
                                @endif
                                <a href="{{ route('documents.payslip.pdf', [$run, $item]) }}" target="_blank" class="text-xs text-slate-600 hover:underline">{{ __('hr.print_payslip') }}</a>
                            </div>
                        </td>
                    </tr>
                    @if($run->isEditable())
                    <tr x-show="editId === {{ $item->id }}" x-cloak>
                        <td colspan="9" class="bg-orange-50/50 p-4">
                            <form method="POST" action="{{ route('payroll.update-item', [$run, $item]) }}" class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-6">
                                @csrf @method('PATCH')
                                <x-ui.form-field :label="__('hr.basic')" name="basic_salary" type="number" step="0.01" :value="$item->basic_salary" />
                                <x-ui.form-field :label="__('hr.housing')" name="housing_allowance" type="number" step="0.01" :value="$item->housing_allowance" />
                                <x-ui.form-field :label="__('hr.transport')" name="transport_allowance" type="number" step="0.01" :value="$item->transport_allowance" />
                                <x-ui.form-field :label="__('hr.overtime')" name="overtime_amount" type="number" step="0.01" :value="$item->overtime_amount" />
                                <x-ui.form-field :label="__('hr.bonus')" name="bonus_amount" type="number" step="0.01" :value="$item->bonus_amount" />
                                <x-ui.form-field :label="__('hr.absent_deduction')" name="deductions" type="number" step="0.01" :value="$item->deductions" />
                                <x-ui.form-field :label="__('hr.gosi')" name="gosi_deduction" type="number" step="0.01" :value="$item->gosi_deduction" />
                                <x-ui.form-field :label="__('hr.loan')" name="loan_deduction" type="number" step="0.01" :value="$item->loan_deduction" />
                                <x-ui.form-field :label="__('hr.other_deductions')" name="other_deductions" type="number" step="0.01" :value="$item->other_deductions" />
                                <x-ui.form-field :label="__('hr.notes')" name="notes" :value="$item->notes" />
                                <div class="flex items-end gap-2">
                                    <button class="erp-btn-primary text-sm">{{ __('hr.save_line') }}</button>
                                    <button type="button" @click="editId = null" class="erp-btn-secondary text-sm">{{ __('ui.cancel') }}</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50/50">
                <tr>
                    <td colspan="7" class="text-right font-semibold">{{ __('ui.total') }}</td>
                    <td class="text-right text-lg font-bold">{{ number_format($run->total_amount, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <dialog x-ref="payModal" class="rounded-xl p-0 shadow-xl backdrop:bg-slate-900/40">
        <form method="POST" action="{{ route('payroll.pay', $run) }}" class="erp-card w-full max-w-md p-6 space-y-4">
            @csrf
            <h3 class="text-lg font-bold">{{ __('hr.pay_payroll') }}</h3>
            <p class="text-sm text-slate-500">{{ __('ui.total') }}: {{ number_format($run->total_amount, 2) }}</p>
            <x-ui.form-field :label="__('hr.payment_method')" name="payment_method" type="select">
                <option value="cash">{{ __('hr.cash') }}</option>
                <option value="bank_transfer">{{ __('hr.bank_transfer') }}</option>
            </x-ui.form-field>
            <x-ui.form-field :label="__('hr.payment_reference')" name="payment_reference" />
            <div class="flex gap-3">
                <button type="submit" class="erp-btn-primary" onclick="return confirm('{{ __('hr.pay_payroll_confirm') }}')">{{ __('hr.pay_payroll') }}</button>
                <button type="button" onclick="this.closest('dialog').close()" class="erp-btn-secondary">{{ __('ui.cancel') }}</button>
            </div>
        </form>
    </dialog>

    <a href="{{ route('payroll.index') }}" class="erp-btn-secondary">{{ __('hr.back_to_payroll') }}</a>
</div>
</x-erp-layout>
