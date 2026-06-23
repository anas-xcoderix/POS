@php $title = __('modules.add_showroom_vehicle'); @endphp
<x-erp-layout>
<div class="mx-auto max-w-3xl erp-card p-6">
    <form method="POST" action="{{ route('showroom-vehicles.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @csrf
        @include('showroom-vehicles._form')
        <div class="md:col-span-2 flex justify-end gap-3 border-t pt-4">
            <a href="{{ route('showroom-vehicles.index') }}" class="erp-btn-secondary">{{ __('ui.cancel') }}</a>
            <button class="erp-btn-primary">{{ __('ui.save') }}</button>
        </div>
    </form>
</div>
</x-erp-layout>
