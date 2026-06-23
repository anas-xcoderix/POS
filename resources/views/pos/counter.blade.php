@php $title = __('pos.counter'); @endphp
<x-pos-layout :title="$title">
<div class="flex min-h-screen flex-col">
    {{-- Top bar --}}
    <header class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3 shadow-sm">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('pos.index') }}" class="shrink-0 text-sm font-medium text-slate-500 hover:text-orange-600">{{ __('pos.back_terminals') }}</a>
            <div class="min-w-0">
                <h1 class="truncate text-lg font-bold text-slate-900">{{ $session->posTerminal?->name }}</h1>
                <p class="truncate text-xs text-slate-500">{{ $session->session_no }} · {{ $session->user?->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 text-sm">
            <span class="hidden sm:inline text-slate-500">{{ __('pos.total_sales') }}:</span>
            <span class="font-bold text-emerald-600">{{ number_format($stats['total_sales'], 2) }}</span>
            <a href="{{ route('pos.session-report', $session) }}" target="_blank" class="erp-btn-secondary !py-1.5 !text-xs">{{ __('pos.session_report') }}</a>
        </div>
    </header>

    <div class="flex flex-1 flex-col gap-4 p-4 lg:flex-row lg:overflow-hidden">
        {{-- Search & results --}}
        <div class="flex flex-col lg:w-80 lg:shrink-0">
            <div class="erp-card p-4">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('pos.search') }}</label>
                <div class="flex gap-2">
                    <input type="text" id="partSearch" autofocus
                           placeholder="{{ __('pos.search_placeholder') }}"
                           class="erp-input !mt-0 flex-1"
                           autocomplete="off">
                    <button type="button" id="searchBtn" class="erp-btn-primary shrink-0">{{ __('pos.search') }}</button>
                </div>
                <div id="searchResults" class="mt-3 max-h-64 overflow-y-auto divide-y divide-slate-100"></div>
            </div>
            {{-- Recent sales --}}
            <div class="erp-card mt-4 flex-1 overflow-hidden">
                <div class="border-b border-slate-100 px-4 py-2 text-xs font-bold uppercase text-slate-500">{{ __('pos.recent_sales') }}</div>
                <div class="max-h-48 overflow-y-auto divide-y divide-slate-50 text-sm">
                    @forelse($stats['recent'] as $sale)
                        <div class="flex items-center justify-between gap-2 px-4 py-2">
                            <span class="font-medium">{{ $sale->invoice_no }}</span>
                            <span>{{ number_format($sale->total_amount, 2) }}</span>
                            <a href="{{ route('documents.sales-invoice.pdf', $sale) }}" target="_blank" class="text-xs text-orange-600 hover:underline">{{ __('ui.pdf') }}</a>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-slate-400 text-sm">{{ __('pos.empty_cart') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Cart --}}
        <div class="flex min-h-0 flex-1 flex-col erp-card overflow-hidden">
            <div class="border-b border-slate-100 px-4 py-2 flex items-center justify-between">
                <span class="font-bold text-slate-900">{{ __('pos.cart') }}</span>
                <button type="button" id="clearCartBtn" class="text-xs text-red-600 hover:underline">{{ __('pos.clear_cart') }}</button>
            </div>
            <div class="flex-1 overflow-auto">
                <table class="erp-table min-w-full text-sm">
                    <thead class="bg-slate-50/80 sticky top-0">
                        <tr>
                            <th>{{ __('pos.part') }}</th>
                            <th class="w-20">{{ __('pos.qty') }}</th>
                            <th class="w-24">{{ __('pos.price') }}</th>
                            <th class="w-16">{{ __('pos.disc_pct') }}</th>
                            <th class="w-24 text-right">{{ __('pos.line_total') }}</th>
                            @if($canDeleteLine)<th class="w-10"></th>@endif
                        </tr>
                    </thead>
                    <tbody id="cartBody"></tbody>
                </table>
                <p id="emptyCartMsg" class="py-12 text-center text-slate-400">{{ __('pos.empty_cart') }}</p>
            </div>
        </div>

        {{-- Payment panel --}}
        <div class="lg:w-72 lg:shrink-0 flex flex-col gap-4">
            <form method="POST" action="{{ route('pos.quick-sale', $session) }}" id="saleForm" class="erp-card p-4 space-y-3">
                @csrf
                <input type="hidden" name="invoice_type" id="invoiceType" value="cash">

                <x-ui.form-field :label="__('pos.customer')" name="customer_id" type="select" id="customerSelect">
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected($c->id == $defaultCustomerId)>{{ localized($c) }}</option>
                    @endforeach
                </x-ui.form-field>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('pos.payment_type') }}</label>
                    <div class="flex gap-2">
                        <button type="button" class="pay-type-btn erp-btn-primary flex-1 !py-2" data-type="cash">{{ __('pos.cash') }}</button>
                        <button type="button" class="pay-type-btn erp-btn-secondary flex-1 !py-2" data-type="credit">{{ __('pos.credit') }}</button>
                    </div>
                </div>

                <div id="cashFields">
                    <x-ui.form-field :label="__('pos.paid_amount')" name="paid_amount" type="number" step="0.01" id="paidAmount" value="0" />
                    <p class="text-sm text-slate-600">{{ __('pos.change') }}: <strong id="changeDisplay">0.00</strong></p>
                </div>

                <dl class="space-y-1 border-t border-slate-100 pt-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('pos.subtotal') }}</dt><dd id="subtotalDisplay">0.00</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('pos.vat') }}</dt><dd id="vatDisplay">0.00</dd></div>
                    <div class="flex justify-between text-base font-bold"><dt>{{ __('pos.total') }}</dt><dd id="totalDisplay">0.00</dd></div>
                </dl>

                <button type="submit" id="completeBtn" disabled class="erp-btn-primary w-full !py-3 text-base disabled:opacity-50">
                    {{ __('pos.complete_sale') }}
                </button>
            </form>

            @if(session('last_invoice_id'))
                <a href="{{ route('documents.sales-invoice.pdf', session('last_invoice_id')) }}" target="_blank"
                   class="erp-btn-secondary w-full text-center">{{ __('pos.print_receipt') }}</a>
            @endif

            <div class="erp-card p-4 text-sm space-y-2">
                <div class="flex justify-between"><span class="text-slate-500">{{ __('pos.opening_float') }}</span><span>{{ number_format($session->opening_float, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('pos.invoice_count') }}</span><span>{{ $stats['invoice_count'] }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('pos.expected_cash') }}</span><span class="font-bold">{{ number_format($stats['expected_cash'], 2) }}</span></div>
                <form method="POST" action="{{ route('pos.close-session', $session) }}" class="border-t border-slate-100 pt-3 space-y-2">
                    @csrf
                    <x-ui.form-field :label="__('pos.closing_float')" name="closing_float" type="number" step="0.01" required />
                    <button class="erp-btn-danger w-full" onclick="return confirm('{{ __('pos.close_session') }}?')">{{ __('pos.close_session') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const vatRate = {{ $vatRate }};
    const pricingUrl = @json($pricingUrl);
    const searchUrl = @json($searchUrl);
    const canDeleteLine = @json($canDeleteLine);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    let cart = [];

    const els = {
        search: document.getElementById('partSearch'),
        searchBtn: document.getElementById('searchBtn'),
        results: document.getElementById('searchResults'),
        cartBody: document.getElementById('cartBody'),
        emptyMsg: document.getElementById('emptyCartMsg'),
        saleForm: document.getElementById('saleForm'),
        customer: document.getElementById('customerSelect'),
        paid: document.getElementById('paidAmount'),
        change: document.getElementById('changeDisplay'),
        subtotal: document.getElementById('subtotalDisplay'),
        vat: document.getElementById('vatDisplay'),
        total: document.getElementById('totalDisplay'),
        complete: document.getElementById('completeBtn'),
        invoiceType: document.getElementById('invoiceType'),
        cashFields: document.getElementById('cashFields'),
        clearCart: document.getElementById('clearCartBtn'),
    };

    function lineCalc(qty, price, disc) {
        const net = qty * price * (1 - disc / 100);
        const vat = net * (vatRate / 100);
        return { net, vat, total: net + vat };
    }

    function renderCart() {
        els.cartBody.innerHTML = '';
        els.emptyMsg.style.display = cart.length ? 'none' : 'block';
        els.complete.disabled = cart.length === 0;

        let sub = 0, vat = 0;
        cart.forEach((row, i) => {
            const c = lineCalc(row.qty, row.price, row.disc);
            sub += c.net;
            vat += c.vat;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><div class="font-medium">${row.part_number}</div><div class="text-xs text-slate-500 truncate max-w-[120px]">${row.description || ''}</div></td>
                <td><input type="number" step="0.01" min="0.01" value="${row.qty}" data-i="${i}" class="cart-qty erp-input !mt-0 !py-1 w-full"></td>
                <td><input type="number" step="0.01" min="0" value="${row.price}" data-i="${i}" class="cart-price erp-input !mt-0 !py-1 w-full"></td>
                <td><input type="number" step="0.01" min="0" max="100" value="${row.disc}" data-i="${i}" class="cart-disc erp-input !mt-0 !py-1 w-full"></td>
                <td class="text-right font-medium">${c.total.toFixed(2)}</td>
                ${canDeleteLine ? `<td><button type="button" data-i="${i}" class="cart-remove text-red-500 hover:text-red-700">×</button></td>` : ''}
            `;
            els.cartBody.appendChild(tr);
        });

        const total = sub + vat;
        els.subtotal.textContent = sub.toFixed(2);
        els.vat.textContent = vat.toFixed(2);
        els.total.textContent = total.toFixed(2);
        updateChange();
        bindCartEvents();
        rebuildFormInputs();
    }

    function updateChange() {
        const total = parseFloat(els.total.textContent) || 0;
        const paid = parseFloat(els.paid.value) || 0;
        els.change.textContent = Math.max(0, paid - total).toFixed(2);
    }

    function bindCartEvents() {
        document.querySelectorAll('.cart-qty').forEach(el => el.onchange = () => { cart[el.dataset.i].qty = parseFloat(el.value) || 1; renderCart(); });
        document.querySelectorAll('.cart-price').forEach(el => el.onchange = () => { cart[el.dataset.i].price = parseFloat(el.value) || 0; renderCart(); });
        document.querySelectorAll('.cart-disc').forEach(el => el.onchange = () => { cart[el.dataset.i].disc = parseFloat(el.value) || 0; renderCart(); });
        document.querySelectorAll('.cart-remove').forEach(el => el.onclick = () => { cart.splice(el.dataset.i, 1); renderCart(); });
    }

    function rebuildFormInputs() {
        els.saleForm.querySelectorAll('[data-cart-field]').forEach(n => n.remove());
        cart.forEach((row, i) => {
            const fields = {
                part_id: row.part_id,
                quantity: row.qty,
                unit_price: row.price,
                discount_percent: row.disc,
                vat_percent: vatRate,
            };
            Object.entries(fields).forEach(([name, value]) => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = `items[${i}][${name}]`;
                inp.value = value;
                inp.dataset.cartField = '1';
                els.saleForm.appendChild(inp);
            });
        });
    }

    async function addPart(partId) {
        const customerId = els.customer.value;
        const res = await fetch(`${pricingUrl}?part_id=${partId}&customer_id=${customerId}&quantity=1`);
        const data = await res.json();
        const existing = cart.find(r => r.part_id == partId);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                part_id: partId,
                part_number: data.part_number,
                description: data.description,
                qty: 1,
                price: parseFloat(data.unit_price) || 0,
                disc: parseFloat(data.discount_percent) || 0,
            });
        }
        renderCart();
        els.search.value = '';
        els.results.innerHTML = '';
        els.search.focus();
    }

    async function doSearch() {
        const q = els.search.value.trim();
        if (!q) return;
        const res = await fetch(`${searchUrl}?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        els.results.innerHTML = '';
        if (!data.results.length) {
            els.results.innerHTML = `<p class="py-4 text-center text-sm text-slate-400">{{ __('pos.no_results') }}</p>`;
            return;
        }
        data.results.forEach(p => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full px-2 py-2 text-left hover:bg-orange-50 rounded-lg flex justify-between gap-2';
            btn.innerHTML = `<span><strong>${p.part_number}</strong><br><span class="text-xs text-slate-500">${p.description || ''}</span></span><span class="text-sm shrink-0">${parseFloat(p.list_price).toFixed(2)}${p.stock !== null ? `<br><span class="text-xs text-slate-400">{{ __('pos.stock') }}: ${p.stock}</span>` : ''}</span>`;
            btn.onclick = () => addPart(p.id);
            els.results.appendChild(btn);
        });
        if (data.results.length === 1) addPart(data.results[0].id);
    }

    els.searchBtn.onclick = doSearch;
    els.search.onkeydown = e => { if (e.key === 'Enter') { e.preventDefault(); doSearch(); } };
    els.paid.oninput = updateChange;
    els.clearCart.onclick = () => { cart = []; renderCart(); };

    document.querySelectorAll('.pay-type-btn').forEach(btn => {
        btn.onclick = () => {
            document.querySelectorAll('.pay-type-btn').forEach(b => {
                b.classList.toggle('erp-btn-primary', b.dataset.type === btn.dataset.type);
                b.classList.toggle('erp-btn-secondary', b.dataset.type !== btn.dataset.type);
            });
            els.invoiceType.value = btn.dataset.type;
            els.cashFields.style.display = btn.dataset.type === 'cash' ? 'block' : 'none';
        };
    });

    els.saleForm.onsubmit = e => {
        if (!cart.length) { e.preventDefault(); return; }
        if (els.invoiceType.value === 'cash') {
            const total = parseFloat(els.total.textContent) || 0;
            if (parseFloat(els.paid.value) < total) els.paid.value = total.toFixed(2);
        }
    };

    renderCart();
})();
</script>
@endpush
</x-pos-layout>
