@php $title = __('modules.record_cheque'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('cheques.store') }}" class="space-y-6" id="chequeForm">
    @csrf

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-sm text-emerald-700">1</span>
            Cheque Details
        </h3>

        <div class="mb-4 flex flex-wrap gap-2">
            <button type="button" onclick="setChequeType('received')"
                    class="cheque-type-btn erp-btn-ghost text-sm {{ old('cheque_type', 'received') === 'received' ? '!bg-emerald-50 !text-emerald-700 ring-1 ring-emerald-200' : '' }}"
                    data-type="received">
                Received (from customer)
            </button>
            <button type="button" onclick="setChequeType('issued')"
                    class="cheque-type-btn erp-btn-ghost text-sm {{ old('cheque_type') === 'issued' ? '!bg-emerald-50 !text-emerald-700 ring-1 ring-emerald-200' : '' }}"
                    data-type="issued">
                Issued (to vendor)
            </button>
        </div>

        <input type="hidden" name="cheque_type" id="chequeType" value="{{ old('cheque_type', 'received') }}">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Cheque No" name="cheque_no" :value="old('cheque_no')" required />
            <x-ui.form-field label="Cheque Date" name="cheque_date" type="date" :value="old('cheque_date', date('Y-m-d'))" required />
            <x-ui.form-field label="Due Date" name="due_date" type="date" :value="old('due_date')" />
            <x-ui.form-field label="Branch" name="branch_id" type="select" required>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                @endforeach
            </x-ui.form-field>

            <div id="customerField" class="{{ old('cheque_type', 'received') === 'issued' ? 'hidden' : '' }}">
                <x-ui.form-field label="Customer" name="customer_id" type="select" id="customerSelect">
                    <option value="">— Select —</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </x-ui.form-field>
            </div>

            <div id="vendorField" class="{{ old('cheque_type', 'received') !== 'issued' ? 'hidden' : '' }}">
                <x-ui.form-field label="Vendor" name="vendor_id" type="select" id="vendorSelect">
                    <option value="">— Select —</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" @selected(old('vendor_id') == $v->id)>{{ $v->name }}</option>
                    @endforeach
                </x-ui.form-field>
            </div>

            <x-ui.form-field label="Amount" name="amount" type="number" :value="old('amount')" required />
            <x-ui.form-field label="Bank Account" name="bank_account_id" type="select" hint="Optional GL bank account">
                <option value="">— None —</option>
                @foreach($bankAccounts as $acc)
                    <option value="{{ $acc->id }}" @selected(old('bank_account_id') == $acc->id)>{{ $acc->account_code }} — {{ $acc->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Bank Name" name="bank_name" :value="old('bank_name')" hint="Issuing bank on the cheque" />
            <x-ui.form-field label="Status" name="status" type="select" required>
                @foreach(['pending', 'cleared', 'bounced', 'cancelled'] as $s)
                    <option value="{{ $s }}" @selected(old('status', 'pending') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Remarks" name="remarks" class="md:col-span-2 lg:col-span-3" :value="old('remarks')" />
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('cheques.index') }}" class="erp-btn-secondary text-center">Cancel</a>
        <button type="submit" class="erp-btn-primary">Save Cheque</button>
    </div>
</form>

<script>
function setChequeType(type) {
    document.getElementById('chequeType').value = type;
    document.getElementById('customerField').classList.toggle('hidden', type !== 'received');
    document.getElementById('vendorField').classList.toggle('hidden', type !== 'issued');
    document.querySelectorAll('.cheque-type-btn').forEach(btn => {
        const active = btn.dataset.type === type;
        btn.classList.toggle('!bg-emerald-50', active);
        btn.classList.toggle('!text-emerald-700', active);
        btn.classList.toggle('ring-1', active);
        btn.classList.toggle('ring-emerald-200', active);
    });
    if (type === 'received') {
        document.getElementById('vendorSelect').value = '';
    } else {
        document.getElementById('customerSelect').value = '';
    }
}
</script>
</x-erp-layout>
