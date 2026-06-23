@php $title = __('modules.import_parts'); @endphp
<x-erp-layout>
<div class="max-w-3xl space-y-6">
    <div class="erp-card p-6">
        <h3 class="mb-2 text-lg font-bold text-slate-900">Upload CSV File</h3>
        <p class="mb-6 text-sm text-slate-600">
            Required columns: <code class="rounded bg-slate-100 px-1">part_number</code>,
            <code class="rounded bg-slate-100 px-1">description_en</code>,
            <code class="rounded bg-slate-100 px-1">brand_code</code>.
            Optional: oem_no, barcode, list_price, price_2, price_3, cost_price, description_ar.
        </p>
        <form method="POST" action="{{ route('parts.import.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-ui.form-field label="CSV File" name="file" type="file" accept=".csv,.txt" required />
            <x-ui.form-field label="Update Existing" name="update_existing" type="checkbox">
                Update parts that already exist (matched by part_number)
            </x-ui.form-field>
            <button type="submit" class="erp-btn-primary">Import Parts</button>
        </form>
    </div>

    <div class="erp-card p-6">
        <h4 class="mb-3 font-semibold text-slate-900">Sample CSV</h4>
        <pre class="overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs text-slate-100">part_number,description_en,brand_code,oem_no,list_price,price_2,price_3,cost_price
BRK-001,Brake Pad Set Front,TOY,04465-02220,150,140,130,95
FLT-002,Oil Filter,TOY,90915-YZZD2,35,32,30,18</pre>
        <a href="{{ route('parts.index') }}" class="erp-btn-secondary mt-4 inline-flex">{{ __('pages.actions.back_to_parts') }}</a>
    </div>
</div>
</x-erp-layout>
