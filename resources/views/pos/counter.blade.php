@php $title = 'POS — '.$session->session_no; @endphp
<x-erp-layout>
<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('pos.quick-sale', $session) }}" class="erp-card p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-ui.form-field label="Invoice No" name="invoice_no" :value="'POS-'.now()->format('YmdHis')" required />
                <x-ui.form-field label="Customer" name="customer_id" type="select" required>
                    @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </x-ui.form-field>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table min-w-full">
                    <thead><tr><th>Part</th><th>Qty</th><th>Price</th><th></th></tr></thead>
                    <tbody id="posItems"></tbody>
                </table>
            </div>
            <button type="button" onclick="addPosRow()" class="erp-btn-ghost text-sm">Add Item</button>
            <button class="erp-btn-primary">Complete Sale</button>
        </form>
    </div>
    <div class="erp-card p-6">
        <h3 class="font-bold">Session Info</h3>
        <dl class="mt-4 space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Terminal</dt><dd>{{ $session->posTerminal?->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Opened</dt><dd>{{ $session->opened_at?->format('H:i') }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Total Sales</dt><dd class="font-bold">{{ number_format($session->total_sales, 2) }}</dd></div>
        </dl>
        <form method="POST" action="{{ route('pos.close-session', $session) }}" class="mt-6 space-y-3 border-t border-slate-100 pt-4">
            @csrf
            <x-ui.form-field label="Closing Float" name="closing_float" type="number" step="0.01" required />
            <button class="erp-btn-danger w-full">Close Session</button>
        </form>
    </div>
</div>
<template id="posRow">
    <tr>
        <td><select name="items[__I__][part_id]" required class="erp-input !mt-0" onchange="fillPosPrice(this)">@foreach($parts as $p)<option value="{{ $p->id }}" data-price="{{ $p->list_price }}">{{ $p->part_number }}</option>@endforeach</select></td>
        <td><input type="number" step="0.01" name="items[__I__][quantity]" value="1" required class="erp-input !mt-0 w-20"></td>
        <td><input type="number" step="0.01" name="items[__I__][unit_price]" value="0" required class="erp-input !mt-0 w-24 pos-price"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2">×</button></td>
    </tr>
</template>
<script>
let pi=0;
function addPosRow(){document.getElementById('posItems').insertAdjacentHTML('beforeend',document.getElementById('posRow').innerHTML.replaceAll('__I__',pi++));}
function fillPosPrice(s){s.closest('tr').querySelector('.pos-price').value=s.selectedOptions[0]?.dataset.price||0;}
addPosRow();
</script>
</x-erp-layout>
