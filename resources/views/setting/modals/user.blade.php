<div class="modal fade" id="modalCreateUser" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><form action="{{ route('users.store') }}" method="POST">@csrf
<div class="modal-header"><h5 class="modal-title">Tambah User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
    <div class="mb-3"><label class="form-label">Nama Lengkap</label><input class="form-control" name="name" required></div>
    <div class="mb-3"><label class="form-label">Username</label><input class="form-control" name="username" required></div>
    <div class="mb-3"><label class="form-label">Alamat Email</label><input class="form-control" type="email" name="email" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
    <div class="mb-3"><label class="form-label">no. WA aktif</label><input class="form-control" name="phone"></div>
    <div class="mb-3"><label class="form-label">NIDN</label><input class="form-control" name="nidn"></div>
    <div class="mb-3"><label class="form-label">Role</label><select class="form-select" name="role" required><option value="">Pilih Role</option>@foreach (($roles ?? collect()) as $roleName)<option value="{{ $roleName }}">{{ $roleName }}</option>@endforeach</select></div>
</div>
<div class="modal-footer">
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>

@php
    $allPermissions = collect($permissions ?? collect());

    $accessPermissions = $allPermissions->filter(function ($permission) {
        $tokens = preg_split('/[.\-\s_]+/', strtolower($permission->name));
        return in_array('access', $tokens, true);
    })->values();

    $nonAccessPermissions = $allPermissions->reject(function ($permission) {
        $tokens = preg_split('/[.\-\s_]+/', strtolower($permission->name));
        return in_array('access', $tokens, true);
    })->values();

    $crudAliasMap = [
        'create' => ['create', 'add', 'store'],
        'read' => ['read', 'view', 'show', 'list', 'index'],
        'update' => ['update', 'edit'],
        'delete' => ['delete', 'destroy', 'remove'],
    ];

    $allCrudAliases = collect($crudAliasMap)->flatten()->unique()->values()->all();
    $crudRows = [];

    foreach ($nonAccessPermissions as $permission) {
        $tokens = collect(preg_split('/[.\-\s_]+/', strtolower($permission->name)))
            ->filter(fn ($token) => $token !== '')
            ->values();

        $action = null;
        foreach ($crudAliasMap as $crudAction => $aliases) {
            if ($tokens->contains(fn ($token) => in_array($token, $aliases, true))) {
                $action = $crudAction;
                break;
            }
        }

        if ($action === null) {
            continue;
        }

        $contextTokens = $tokens->reject(fn ($token) => in_array($token, $allCrudAliases, true))->values();
        $contextKey = $contextTokens->isNotEmpty() ? $contextTokens->implode('_') : 'general';
        $contextLabel = $contextTokens->isNotEmpty() ? ucwords($contextTokens->implode(' ')) : 'General';

        if (!isset($crudRows[$contextKey])) {
            $crudRows[$contextKey] = [
                'label' => $contextLabel,
                'create' => null,
                'read' => null,
                'update' => null,
                'delete' => null,
            ];
        }

        $crudRows[$contextKey][$action] = $permission;
    }

    uasort($crudRows, fn ($a, $b) => strcmp($a['label'], $b['label']));

    $userUpdateRouteTemplate = route('users.update', ['user' => '__USER__']);
    $userDestroyRouteTemplate = route('users.destroy', ['user' => '__USER__']);
    $userRolesUpdateRouteTemplate = route('userroles.update', ['userrole' => '__USER__']);
    $userPermissionsUpdateRouteTemplate = route('userpermissions.update', ['userpermission' => '__USER__']);
@endphp

<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><form id="formEditUser" action="#" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title" id="modalEditUserTitle">Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
    <div class="mb-3"><label class="form-label">Nama Lengkap</label><input class="form-control" name="name" id="editUserName" required></div>
    <div class="mb-3"><label class="form-label">Username</label><input class="form-control" name="username" id="editUsername" required></div>
    <div class="mb-3"><label class="form-label">Alamat Email</label><input class="form-control" type="email" name="email" id="editUserEmail" required></div>
    <div class="mb-3"><label class="form-label">no. WA aktif</label><input class="form-control" name="phone" id="editUserPhone"></div>
    <div class="mb-3"><label class="form-label">NIDN</label><input class="form-control" name="nidn" id="editUserNidn"></div>
</div>
<div class="modal-footer">
    <button id="editUserDeleteBtn" class="btn btn-outline-danger btn-sm me-auto d-none" type="button"><i class="bi bi-trash"></i> Hapus</button>
    <span id="editUserDeleteBlockedBadge" class="badge bg-secondary me-auto d-none">Data digunakan, tidak dapat dihapus</span>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
<form id="formDeleteUser" action="#" method="POST" class="d-none">@csrf @method('DELETE')</form>

<div class="modal fade" id="modalSetUserRole" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><form id="formSetUserRole" action="#" method="POST">@csrf @method('PUT')
<div class="modal-header border-0 pb-0">
    <div>
        <h5 class="modal-title mb-1" id="modalSetUserRoleTitle">SET Role</h5>
        <div class="text-muted small">Atur role yang dimiliki oleh user ini.</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
    <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-3">
        <span class="small text-muted">Role terpilih saat ini</span>
        <span class="badge text-bg-primary" id="selectedUserRolesCount">0</span>
    </div>

    <div class="border rounded-3 p-3 bg-light">
        <div class="fw-semibold mb-2">Daftar Role</div>
        <div class="row g-2">
            @foreach (($roleModels ?? collect()) as $roleModel)
                <div class="col-md-6">
                    <div class="form-check mb-1">
                        <input class="form-check-input js-user-role-checkbox" type="checkbox" name="roles[]" id="sharedUserRole{{ $roleModel->id }}" value="{{ $roleModel->id }}">
                        <label class="form-check-label" for="sharedUserRole{{ $roleModel->id }}">{{ $roleModel->name }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<div class="modal-footer"><button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button></div>
</form></div></div></div>

<div class="modal fade" id="modalSetUserPermission" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><form id="formSetUserPermission" action="#" method="POST">@csrf @method('PUT')
<div class="modal-header border-0 pb-0">
    <div>
        <h5 class="modal-title mb-1" id="modalSetUserPermissionTitle">SET Permission</h5>
        <div class="text-muted small">Atur direct permission user tanpa mengubah permission turunan role.</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
    <div class="alert alert-light border d-flex flex-column gap-1 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <span class="small text-muted">Direct permission user</span>
            <span class="badge text-bg-primary" id="directUserPermissionsCount">0</span>
        </div>
        <div class="small text-muted">Permission turunan role ditandai otomatis dan tidak dapat diubah dari menu ini.</div>
    </div>

    <div class="border rounded-3 p-3 mb-3 bg-light">
        <div class="fw-semibold mb-2">(1) Permission Access</div>
        @if ($accessPermissions->isNotEmpty())
            <div class="row g-2">
                @foreach ($accessPermissions as $permission)
                    <div class="col-md-6">
                        <div class="form-check mb-1">
                            <input class="form-check-input js-user-permission-checkbox" type="checkbox" id="sharedAccessPerm{{ $permission->id }}" data-permission-id="{{ $permission->id }}" value="{{ $permission->id }}" name="permission[]">
                            <label class="form-check-label" for="sharedAccessPerm{{ $permission->id }}">{{ $permission->name }} <span class="badge text-bg-secondary ms-1 d-none js-from-role-badge" data-permission-id="{{ $permission->id }}">dari role</span></label>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted small">Tidak ada permission access.</div>
        @endif
    </div>

    <div class="border rounded-3 p-3">
        <div class="fw-semibold mb-2">(2) Permission CRUD</div>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-sm table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 180px;">Konteks</th>
                        <th class="text-center">Create</th>
                        <th class="text-center">Read</th>
                        <th class="text-center">Update</th>
                        <th class="text-center">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($crudRows as $row)
                        <tr>
                            <td class="fw-medium">{{ $row['label'] }}</td>
                            @foreach (['create', 'read', 'update', 'delete'] as $action)
                                <td class="text-center">
                                    @if ($row[$action])
                                        <div class="form-check d-inline-block mb-0">
                                            <input class="form-check-input js-user-permission-checkbox" type="checkbox" id="sharedCrudPerm{{ $row[$action]->id }}" data-permission-id="{{ $row[$action]->id }}" value="{{ $row[$action]->id }}" name="permission[]">
                                            <label class="form-check-label" for="sharedCrudPerm{{ $row[$action]->id }}"></label>
                                        </div>
                                        <div class="mt-1"><span class="badge text-bg-secondary d-none js-from-role-badge" data-permission-id="{{ $row[$action]->id }}">dari role</span></div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada permission CRUD.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-footer"><button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button></div>
</form></div></div></div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const userRolesMap = @json($userRolesMap ?? []);
                const userPermissionsMap = @json($userPermissionsMap ?? []);
                const userRoleDerivedPermissionsMap = @json($userRoleDerivedPermissionsMap ?? []);
                const nonDeletableUserIds = new Set(@json(array_keys($nonDeletableUserIds ?? [])));

                const updateRouteTemplate = @json($userUpdateRouteTemplate);
                const destroyRouteTemplate = @json($userDestroyRouteTemplate);
                const roleUpdateRouteTemplate = @json($userRolesUpdateRouteTemplate);
                const permissionUpdateRouteTemplate = @json($userPermissionsUpdateRouteTemplate);

                const routeFor = (template, id) => template.replace('__USER__', encodeURIComponent(String(id)));

                const editModal = document.getElementById('modalEditUser');
                const roleModal = document.getElementById('modalSetUserRole');
                const permissionModal = document.getElementById('modalSetUserPermission');

                if (editModal) {
                    editModal.addEventListener('show.bs.modal', function (event) {
                        const trigger = event.relatedTarget;
                        if (!trigger) {
                            return;
                        }

                        const userId = String(trigger.getAttribute('data-user-id') || '');
                        const name = trigger.getAttribute('data-user-name') || '';
                        const username = trigger.getAttribute('data-user-username') || '';
                        const email = trigger.getAttribute('data-user-email') || '';
                        const phone = trigger.getAttribute('data-user-phone') || '';
                        const nidn = trigger.getAttribute('data-user-nidn') || '';

                        document.getElementById('modalEditUserTitle').textContent = `Edit User - ${name}`;
                        document.getElementById('editUserName').value = name;
                        document.getElementById('editUsername').value = username;
                        document.getElementById('editUserEmail').value = email;
                        document.getElementById('editUserPhone').value = phone;
                        document.getElementById('editUserNidn').value = nidn;

                        const editForm = document.getElementById('formEditUser');
                        const deleteForm = document.getElementById('formDeleteUser');
                        editForm.action = routeFor(updateRouteTemplate, userId);
                        deleteForm.action = routeFor(destroyRouteTemplate, userId);

                        const canDelete = !nonDeletableUserIds.has(userId);
                        const deleteBtn = document.getElementById('editUserDeleteBtn');
                        const blockedBadge = document.getElementById('editUserDeleteBlockedBadge');

                        deleteBtn.classList.toggle('d-none', !canDelete);
                        blockedBadge.classList.toggle('d-none', canDelete);

                        deleteBtn.onclick = function () {
                            if (confirm(`Yakin akan menghapus user ${name}?`)) {
                                deleteForm.submit();
                            }
                        };
                    });
                }

                if (roleModal) {
                    roleModal.addEventListener('show.bs.modal', function (event) {
                        const trigger = event.relatedTarget;
                        if (!trigger) {
                            return;
                        }

                        const userId = String(trigger.getAttribute('data-user-id') || '');
                        const name = trigger.getAttribute('data-user-name') || '';

                        document.getElementById('modalSetUserRoleTitle').textContent = `SET Role - ${name}`;
                        document.getElementById('formSetUserRole').action = routeFor(roleUpdateRouteTemplate, userId);

                        const selectedRoles = new Set((userRolesMap[userId] || userRolesMap[Number(userId)] || []).map(String));
                        document.querySelectorAll('.js-user-role-checkbox').forEach((checkbox) => {
                            checkbox.checked = selectedRoles.has(String(checkbox.value));
                        });

                        document.getElementById('selectedUserRolesCount').textContent = String(selectedRoles.size);
                    });
                }

                if (permissionModal) {
                    permissionModal.addEventListener('show.bs.modal', function (event) {
                        const trigger = event.relatedTarget;
                        if (!trigger) {
                            return;
                        }

                        const userId = String(trigger.getAttribute('data-user-id') || '');
                        const name = trigger.getAttribute('data-user-name') || '';

                        document.getElementById('modalSetUserPermissionTitle').textContent = `SET Permission - ${name}`;
                        document.getElementById('formSetUserPermission').action = routeFor(permissionUpdateRouteTemplate, userId);

                        const directSet = new Set((userPermissionsMap[userId] || userPermissionsMap[Number(userId)] || []).map(String));
                        const roleDerivedSet = new Set((userRoleDerivedPermissionsMap[userId] || userRoleDerivedPermissionsMap[Number(userId)] || []).map(String));

                        document.querySelectorAll('.js-user-permission-checkbox').forEach((checkbox) => {
                            const permissionId = String(checkbox.getAttribute('data-permission-id') || checkbox.value);
                            const isFromRole = roleDerivedSet.has(permissionId);
                            const isDirect = directSet.has(permissionId);

                            checkbox.checked = isFromRole || isDirect;
                            checkbox.disabled = isFromRole;
                            if (isFromRole) {
                                checkbox.removeAttribute('name');
                            } else {
                                checkbox.setAttribute('name', 'permission[]');
                            }
                        });

                        document.querySelectorAll('.js-from-role-badge').forEach((badge) => {
                            const permissionId = String(badge.getAttribute('data-permission-id') || '');
                            const show = roleDerivedSet.has(permissionId);
                            badge.classList.toggle('d-none', !show);
                        });

                        document.getElementById('directUserPermissionsCount').textContent = String(directSet.size);
                    });
                }
            });
        </script>
    @endpush
@endonce
