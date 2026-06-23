@php $title = __('nav.users_roles'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4">
        <p class="text-sm text-slate-600">{{ __('pages.users.subtitle') }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('pages.table.user') }}</th><th>{{ __('pages.table.role') }}</th><th>{{ __('ui.branch') }}</th><th>{{ __('pages.table.max_discount') }}</th><th>{{ __('pages.table.all_branches') }}</th><th>{{ __('ui.active') }}</th><th class="text-right">{{ __('ui.actions') }}</th>
            </tr></thead>
            <tbody>
                @foreach($records as $user)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $user->email }}</div>
                        </td>
                        <td><span class="erp-badge erp-badge-slate">{{ $user->role }}</span></td>
                        <td>{{ $user->branch?->name ?? '—' }}</td>
                        <td>{{ number_format($user->max_discount_percent, 0) }}%</td>
                        <td>{{ $user->can_access_all_branches ? __('ui.yes') : __('ui.no') }}</td>
                        <td>{{ $user->is_active ? __('ui.yes') : __('ui.no') }}</td>
                        <td class="text-right space-x-1">
                            <button type="button" onclick="openUserEdit(@json($user))" class="erp-btn-ghost !px-2.5 !py-2" title="{{ __('pages.actions.edit_user') }}">
                                <x-ui.icon name="pencil" class="h-4 w-4" />
                            </button>
                            <button type="button" onclick="openPermissions({{ $user->id }}, @json($userPermissions[$user->id] ?? []))" class="erp-btn-ghost !px-2.5 !py-2 text-xs">{{ __('pages.actions.rights') }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>

<div id="userEditModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50" onclick="closeUserEdit()"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <h3 class="mb-4 text-lg font-bold">{{ __('pages.users.edit_user') }}</h3>
        <form method="POST" id="userEditForm">
            @csrf @method('PUT')
            <div class="space-y-4">
                <x-ui.form-field :label="__('pages.table.role')" name="role" type="select" id="edit_role">
                    @foreach($roles as $role)<option value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach
                </x-ui.form-field>
                <x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select" id="edit_branch">
                    <option value="">{{ __('pages.users.none') }}</option>
                    @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                </x-ui.form-field>
                <x-ui.form-field :label="__('pages.users.max_discount_pct')" name="max_discount_percent" type="number" step="0.01" id="edit_max_discount" />
                <x-ui.form-field :label="__('pages.users.access_all_branches')" name="can_access_all_branches" type="checkbox" id="edit_all_branches">{{ __('pages.users.can_access_all_branches') }}</x-ui.form-field>
                <x-ui.form-field :label="__('ui.active')" name="is_active" type="checkbox" id="edit_active">{{ __('pages.users.account_active') }}</x-ui.form-field>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeUserEdit()" class="erp-btn-secondary">{{ __('ui.cancel') }}</button>
                <button class="erp-btn-primary">{{ __('ui.save') }}</button>
            </div>
        </form>
    </div>
</div>

<div id="permModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50" onclick="closePermissions()"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <h3 class="mb-4 text-lg font-bold">{{ __('pages.users.granular_permissions') }}</h3>
        <form method="POST" id="permForm">
            @csrf @method('PUT')
            <div class="max-h-80 space-y-3 overflow-y-auto">
                @foreach($granularPermissions as $key => $label)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="permissions[{{ $key }}]" value="1" class="perm-check rounded border-slate-300" data-key="{{ $key }}">
                        <span>{{ $label }}</span>
                        <code class="text-xs text-slate-400">({{ $key }})</code>
                    </label>
                @endforeach
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closePermissions()" class="erp-btn-secondary">{{ __('ui.cancel') }}</button>
                <button class="erp-btn-primary">{{ __('pages.actions.save_permissions') }}</button>
            </div>
        </form>
    </div>
</div>
<script>
function openUserEdit(user) {
    document.getElementById('userEditForm').action = '/users/' + user.id;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_branch').value = user.branch_id || '';
    document.getElementById('edit_max_discount').value = user.max_discount_percent;
    document.getElementById('edit_all_branches').checked = !!user.can_access_all_branches;
    document.getElementById('edit_active').checked = user.is_active !== false;
    document.getElementById('userEditModal').classList.remove('hidden');
    document.getElementById('userEditModal').classList.add('flex');
}
function closeUserEdit() {
    document.getElementById('userEditModal').classList.add('hidden');
    document.getElementById('userEditModal').classList.remove('flex');
}
function openPermissions(userId, granted) {
    document.getElementById('permForm').action = '/users/' + userId + '/permissions';
    document.querySelectorAll('.perm-check').forEach(cb => {
        cb.checked = granted.includes(cb.dataset.key);
    });
    document.getElementById('permModal').classList.remove('hidden');
    document.getElementById('permModal').classList.add('flex');
}
function closePermissions() {
    document.getElementById('permModal').classList.add('hidden');
    document.getElementById('permModal').classList.remove('flex');
}
</script>
</x-erp-layout>
