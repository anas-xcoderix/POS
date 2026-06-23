@php $title = 'Users & Roles'; @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4">
        <p class="text-sm text-slate-600">Manage user roles, branch access, and maximum discount authority.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>User</th><th>Role</th><th>Branch</th><th>Max Discount</th><th>All Branches</th><th>Active</th><th class="text-right">Actions</th>
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
                        <td>{{ $user->can_access_all_branches ? 'Yes' : 'No' }}</td>
                        <td>{{ $user->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-right">
                            <button type="button" onclick="openUserEdit(@json($user))" class="erp-btn-ghost !px-2.5 !py-2">
                                <x-ui.icon name="pencil" class="h-4 w-4" />
                            </button>
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
        <h3 class="mb-4 text-lg font-bold">Edit User</h3>
        <form method="POST" id="userEditForm">
            @csrf @method('PUT')
            <div class="space-y-4">
                <x-ui.form-field label="Role" name="role" type="select" id="edit_role">
                    @foreach($roles as $role)<option value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach
                </x-ui.form-field>
                <x-ui.form-field label="Branch" name="branch_id" type="select" id="edit_branch">
                    <option value="">— None —</option>
                    @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                </x-ui.form-field>
                <x-ui.form-field label="Max Discount %" name="max_discount_percent" type="number" step="0.01" id="edit_max_discount" />
                <x-ui.form-field label="Access All Branches" name="can_access_all_branches" type="checkbox" id="edit_all_branches">Can access all branches</x-ui.form-field>
                <x-ui.form-field label="Active" name="is_active" type="checkbox" id="edit_active">Account active</x-ui.form-field>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeUserEdit()" class="erp-btn-secondary">Cancel</button>
                <button class="erp-btn-primary">Save</button>
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
</script>
</x-erp-layout>
