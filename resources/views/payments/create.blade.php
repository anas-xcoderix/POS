@php $title = $partyType === 'vendor' ? __('modules.new_vendor_payment') : __('modules.new_customer_receipt'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('payments.store') }}" class="space-y-6" id="paymentForm">
    @csrf

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-sm text-emerald-700">1</span>
            Payment Details
        </h3>

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('payments.create', ['party_type' => 'customer']) }}"
               class="erp-btn-ghost text-sm {{ $partyType === 'customer' ? '!bg-emerald-50 !text-emerald-700 ring-1 ring-emerald-200' : '' }}">
                Customer Receipt
            </a>
            <a href="{{ route('payments.create', ['party_type' => 'vendor']) }}"
               class="erp-btn-ghost text-sm {{ $partyType === 'vendor' ? '!bg-emerald-50 !text-emerald-700 ring-1 ring-emerald-200' : '' }}">
                Vendor Payment
            </a>
        </div>

        <input type="hidden" name="party_type" value="{{ $partyType }}">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Receipt No" name="receipt_no" :value="$receiptNo" required />
            <x-ui.form-field label="Date" name="receipt_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="Branch" name="branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>

            @if($partyType === 'customer')
                <x-ui.form-field label="Customer" name="customer_id" type="select" required id="partySelect">
                    @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </x-ui.form-field>
                <x-ui.form-field label="Apply to Invoice" name="sales_invoice_id" type="select" hint="Optional — links payment to invoice" id="invoiceSelect">
                    <option value="">— None —</option>
                    @foreach($salesInvoices as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->invoice_no }} — {{ number_format($inv->total_amount - $inv->paid_amount, 2) }} due</option>
                    @endforeach
                </x-ui.form-field>
            @else
                <x-ui.form-field label="Vendor" name="vendor_id" type="select" required id="partySelect">
                    @foreach($vendors as $v)<option value="{{ $v->id }}">{{ $v->name }}</option>@endforeach
                </x-ui.form-field>
                <x-ui.form-field label="Apply to Invoice" name="purchase_invoice_id" type="select" hint="Optional — links payment to invoice" id="invoiceSelect">
                    <option value="">— None —</option>
                    @foreach($purchaseInvoices as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->invoice_no }} — {{ number_format($inv->total_amount - $inv->paid_amount, 2) }} due</option>
                    @endforeach
                </x-ui.form-field>
            @endif

            <x-ui.form-field label="Amount" name="amount" type="number" :value="old('amount')" required />
            <x-ui.form-field label="Payment Method" name="payment_method" type="select" required>
                <option value="cash">Cash</option>
                <option value="bank">Bank Transfer</option>
                <option value="cheque">Cheque</option>
            </x-ui.form-field>
            <x-ui.form-field label="Reference No" name="reference_no" hint="Cheque no, transfer ref, etc." />
            <x-ui.form-field label="Remarks" name="remarks" class="md:col-span-2 lg:col-span-3" />
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('payments.index') }}" class="erp-btn-secondary text-center">Cancel</a>
        <button type="submit" class="erp-btn-primary">Record Payment</button>
    </div>
</form>
</x-erp-layout>
