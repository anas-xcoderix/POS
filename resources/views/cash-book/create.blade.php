@php $title = __('modules.new_cash_book_entry'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('cash-book.store') }}" class="erp-card max-w-2xl p-6 space-y-4">
    @csrf
    <x-ui.form-field label="{{ __('ui.branch') }}" name="branch_id" type="select" required>
        @foreach($branches as $b)<option value="{{ $b->id }}" @selected($defaultBranchId == $b->id)>{{ $b->name }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field label="{{ __('ui.date') }}" name="entry_date" type="date" :value="date('Y-m-d')" required />
    <x-ui.form-field label="{{ __('ui.type') }}" name="entry_type" type="select" required>
        <option value="receipt">Receipt</option>
        <option value="payment">Payment</option>
        <option value="in">Cash In</option>
        <option value="out">Cash Out</option>
    </x-ui.form-field>
    <x-ui.form-field label="Account" name="account_id" type="select" required>
        @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->account_code }} — {{ $a->name }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field label="{{ __('forms.currency') }}" name="currency_id" type="select">
        <option value="">{{ __('pages.filter.base_currency') }}</option>
        @foreach($currencies as $c)<option value="{{ $c->id }}">{{ $c->code }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field label="Exchange Rate" name="exchange_rate" type="number" step="0.000001" value="1" />
    <x-ui.form-field label="{{ __('ui.amount') }}" name="amount" type="number" step="0.01" required />
    <x-ui.form-field label="Reference No" name="reference_no" />
    <x-ui.form-field label="Description" name="description" type="textarea" />
    <div class="flex gap-3 justify-end">
        <a href="{{ route('cash-book.index') }}" class="erp-btn-secondary">{{ __('ui.cancel') }}</a>
        <button class="erp-btn-primary">Save Entry</button>
    </div>
</form>
</x-erp-layout>
