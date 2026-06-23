@php $title = __('modules.showroom_vehicles'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-wrap gap-2">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="{{ __('forms.chassis_or_stock') }}" class="erp-input !mt-0 sm:max-w-xs">
            <select name="status" class="erp-input !mt-0 w-auto">
                <option value="">{{ __('ui.all') }}</option>
                <option value="in_stock" @selected(($status ?? '') === 'in_stock')>{{ __('forms.status_in_stock') }}</option>
                <option value="in_transit" @selected(($status ?? '') === 'in_transit')>{{ __('forms.status_in_transit') }}</option>
                <option value="sold" @selected(($status ?? '') === 'sold')>{{ __('forms.status_sold') }}</option>
            </select>
            <button type="submit" class="erp-btn-secondary">{{ __('ui.search') }}</button>
        </form>
        <a href="{{ route('showroom-vehicles.create') }}" class="erp-btn-primary shrink-0">
            <x-ui.icon name="plus" class="h-4 w-4" /> {{ __('modules.add_showroom_vehicle') }}
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('forms.stock_no') }}</th>
                <th>{{ __('forms.chassis_no') }}</th>
                <th>{{ __('forms.model') }}</th>
                <th>{{ __('forms.color') }}</th>
                <th>{{ __('ui.branch') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('forms.list_price') }}</th>
                <th class="text-end">{{ __('ui.actions') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $v)
                    <tr>
                        <td class="font-semibold">{{ $v->stock_no }}</td>
                        <td>{{ $v->chassis_no }}</td>
                        <td>{{ localized($v->model) }}</td>
                        <td>{{ localized($v->color) }}</td>
                        <td>{{ localized($v->branch) }}</td>
                        <td><span class="erp-badge erp-badge-slate">{{ __('forms.status_'.$v->status) }}</span></td>
                        <td>{{ number_format($v->list_price, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('showroom-vehicles.show', $v) }}" class="erp-btn-ghost !px-2.5 !py-2">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-ui.empty-state :title="__('messages.showroom.no_vehicles')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
