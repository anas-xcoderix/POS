@php $title = __('modules.settings'); @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <h3 class="mb-4 text-lg font-bold text-slate-900">Tax & Pricing Defaults</h3>
        <form method="POST" action="{{ route('settings.update') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @csrf @method('PUT')
            <x-ui.form-field label="Company Name" name="company_name" :value="$settings['company_name'] ?? ''" required />
            <x-ui.form-field label="Default VAT %" name="default_vat_rate" type="number" step="0.01" :value="$settings['default_vat_rate'] ?? '15'" required />
            <x-ui.form-field label="Enforce Credit Limits" name="enforce_credit_limit" type="checkbox" :value="($settings['enforce_credit_limit'] ?? '1') === '1' ? '1' : '0'">
                Block credit sales over limit
            </x-ui.form-field>
            <x-ui.form-field label="Retail Price Level" name="price_level_retail" type="select">
                @foreach([1=>'List Price',2=>'Price 2',3=>'Price 3'] as $v=>$l)
                    <option value="{{ $v }}" @selected(($settings['price_level_retail'] ?? '1') == $v)>{{ $l }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Wholesale Price Level" name="price_level_wholesale" type="select">
                @foreach([1=>'List Price',2=>'Price 2',3=>'Price 3'] as $v=>$l)
                    <option value="{{ $v }}" @selected(($settings['price_level_wholesale'] ?? '2') == $v)>{{ $l }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Corporate Price Level" name="price_level_corporate" type="select">
                @foreach([1=>'List Price',2=>'Price 2',3=>'Price 3'] as $v=>$l)
                    <option value="{{ $v }}" @selected(($settings['price_level_corporate'] ?? '3') == $v)>{{ $l }}</option>
                @endforeach
            </x-ui.form-field>
            <div class="md:col-span-2 lg:col-span-3">
                <button type="submit" class="erp-btn-primary">Save Settings</button>
            </div>
        </form>
    </div>

    <div class="erp-card p-6">
        <h3 class="mb-4 text-lg font-bold text-slate-900">General Ledger (Auto-Post)</h3>
        <form method="POST" action="{{ route('settings.update') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @csrf @method('PUT')
            <input type="hidden" name="form_section" value="gl">
            <x-ui.form-field label="Auto-post GL on invoice post" name="auto_post_gl" type="checkbox" :value="($settings['auto_post_gl'] ?? '1') === '1' ? '1' : '0'">
                Create journal entries when sales/purchase invoices are posted
            </x-ui.form-field>
            <x-ui.form-field label="Cash Account" name="gl_cash" :value="$settings['gl_cash'] ?? '1000'" />
            <x-ui.form-field label="Accounts Receivable" name="gl_accounts_receivable" :value="$settings['gl_accounts_receivable'] ?? '1100'" />
            <x-ui.form-field label="Inventory" name="gl_inventory" :value="$settings['gl_inventory'] ?? '1200'" />
            <x-ui.form-field label="VAT Input" name="gl_vat_input" :value="$settings['gl_vat_input'] ?? '1300'" />
            <x-ui.form-field label="Accounts Payable" name="gl_accounts_payable" :value="$settings['gl_accounts_payable'] ?? '2100'" />
            <x-ui.form-field label="VAT Payable" name="gl_vat_payable" :value="$settings['gl_vat_payable'] ?? '2200'" />
            <x-ui.form-field label="Sales Revenue" name="gl_sales_revenue" :value="$settings['gl_sales_revenue'] ?? '4000'" />
            <x-ui.form-field label="Cost of Goods Sold" name="gl_cogs" :value="$settings['gl_cogs'] ?? '5000'" />
            <div class="md:col-span-2 lg:col-span-3">
                <button type="submit" class="erp-btn-primary">Save GL Settings</button>
            </div>
        </form>
    </div>

    <div class="erp-card p-6">
        <h3 class="mb-4 text-lg font-bold text-slate-900">Discount Rules</h3>
        <form method="POST" action="{{ route('discount-rules.store') }}" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            @csrf
            <x-ui.form-field label="Rule Name" name="name" required />
            <x-ui.form-field label="Rule Type" name="rule_type" type="select" required>
                <option value="customer">{{ __('ui.customer') }}</option>
                <option value="brand">Brand</option>
                <option value="customer_type">Customer Type</option>
            </x-ui.form-field>
            <x-ui.form-field label="Discount %" name="discount_percent" type="number" step="0.01" value="0" required />
            <x-ui.form-field label="Priority" name="priority" type="number" value="0" />
            <x-ui.form-field label="Customer" name="customer_id" type="select">
                <option value="">—</option>
                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Brand" name="brand_id" type="select">
                <option value="">—</option>
                @foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Customer Type" name="customer_type" hint="e.g. wholesale" />
            <x-ui.form-field label="Price Level Override" name="price_level" type="select">
                <option value="">— None —</option>
                <option value="1">List</option><option value="2">Price 2</option><option value="3">Price 3</option>
            </x-ui.form-field>
            <div class="flex items-end">
                <button type="submit" class="erp-btn-primary">Add Rule</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Name</th><th>Type</th><th>Target</th><th>Discount</th><th>Priority</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse($discountRules as $rule)
                        <tr>
                            <td class="font-medium">{{ $rule->name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $rule->rule_type)) }}</td>
                            <td class="text-sm text-slate-600">
                                @if($rule->customer) {{ $rule->customer->name }}
                                @elseif($rule->brand) {{ $rule->brand->name }}
                                @else {{ $rule->customer_type }}
                                @endif
                            </td>
                            <td>{{ number_format($rule->discount_percent, 2) }}%</td>
                            <td>{{ $rule->priority }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('discount-rules.destroy', $rule) }}" class="inline" onsubmit="return confirm('Delete rule?')">
                                    @csrf @method('DELETE')
                                    <button class="erp-btn-danger !px-2 !py-1 text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-slate-500 py-6">No discount rules yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-erp-layout>
