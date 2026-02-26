<div class="modal fade" id="modalCreateRole" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('roles.store') }}" method="POST">@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div><div class="mb-3"><label class="form-label">Guard</label><input class="form-control" name="guard_name" value="web" required></div></div>
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
        $contextLabel = $contextTokens->isNotEmpty()
            ? ucwords($contextTokens->implode(' '))
            : 'General';

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
@endphp
@php
    $roleUpdateRouteTemplate = route('roles.update', ['role' => '__ROLE__']);
    $roleDestroyRouteTemplate = route('roles.destroy', ['role' => '__ROLE__']);
    $rolePermissionsUpdateRouteTemplate = route('rolepermissions.update', ['rolepermission' => '__ROLE__']);
    $rolePermissionsMap = collect($roles ?? collect())
        ->mapWithKeys(fn ($role) => [(string) $role->id => $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->values()->all()])
        ->all();
@endphp
<div class="modal fade" id="modalEditRole" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form id="formEditRole" action="#" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title" id="modalEditRoleTitle">Edit Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" id="editRoleName" required></div><div class="mb-3"><label class="form-label">Guard</label><input class="form-control" name="guard_name" id="editRoleGuardName" required></div></div>
<div class="modal-footer">
    <button id="editRoleDeleteBtn" class="btn btn-outline-danger btn-sm me-auto d-none" type="button"><i class="bi bi-trash"></i> Hapus</button>
    <span id="editRoleDeleteBlockedBadge" class="badge bg-secondary me-auto d-none">Data digunakan, tidak dapat dihapus</span>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
<form id="formDeleteRole" action="#" method="POST" class="d-none">@csrf @method('DELETE')</form>

<div class="modal fade" id="modalSetRolePermission" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <form id="formSetRolePermission" action="#" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title mb-1" id="modalSetRolePermissionTitle">SET Permission</h5>
                    <div class="text-muted small">Atur permission khusus untuk role ini.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-3">
                    <span class="small text-muted">Permission terpilih saat ini</span>
                    <span class="badge text-bg-primary" id="selectedRolePermissionsCount">0</span>
                </div>

                <div class="border rounded-3 p-3 mb-3 bg-light">
                    <div class="fw-semibold mb-2">(1) Permission Access</div>
                    @if ($accessPermissions->isNotEmpty())
                        <div class="row g-2">
                            @foreach ($accessPermissions as $permission)
                                <div class="col-md-6">
                                    <div class="form-check mb-1">
                                        <input class="form-check-input js-role-permission-checkbox" type="checkbox" name="permission[]" id="sharedRoleAccessPerm{{ $permission->id }}" data-permission-id="{{ $permission->id }}" value="{{ $permission->id }}">
                                        <label class="form-check-label" for="sharedRoleAccessPerm{{ $permission->id }}">{{ $permission->name }}</label>
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
                                                        <input class="form-check-input js-role-permission-checkbox" type="checkbox" name="permission[]" id="sharedRoleCrudPerm{{ $row[$action]->id }}" data-permission-id="{{ $row[$action]->id }}" value="{{ $row[$action]->id }}">
                                                        <label class="form-check-label" for="sharedRoleCrudPerm{{ $row[$action]->id }}"></label>
                                                    </div>
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
        </form>
    </div></div>
</div>
@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const roleEditModal = document.getElementById('modalEditRole');
                const rolePermissionModal = document.getElementById('modalSetRolePermission');
                if (!roleEditModal && !rolePermissionModal) {
                    return;
                }

                const nonDeletableRoleIds = new Set(@json(array_keys($nonDeletableRoleIds ?? [])));
                const rolePermissionsMap = @json($rolePermissionsMap);
                const updateRouteTemplate = @json($roleUpdateRouteTemplate);
                const destroyRouteTemplate = @json($roleDestroyRouteTemplate);
                const rolePermissionUpdateRouteTemplate = @json($rolePermissionsUpdateRouteTemplate);
                const routeFor = (template, id) => template.replace('__ROLE__', encodeURIComponent(String(id)));

                if (roleEditModal) {
                    roleEditModal.addEventListener('show.bs.modal', function (event) {
                        const trigger = event.relatedTarget;
                        if (!trigger) {
                            return;
                        }

                        const id = String(trigger.getAttribute('data-role-id') || '');
                        const name = trigger.getAttribute('data-role-name') || '';
                        const guardName = trigger.getAttribute('data-role-guard') || '';

                        document.getElementById('modalEditRoleTitle').textContent = `Edit Role - ${name}`;
                        document.getElementById('editRoleName').value = name;
                        document.getElementById('editRoleGuardName').value = guardName;

                        const editForm = document.getElementById('formEditRole');
                        const deleteForm = document.getElementById('formDeleteRole');
                        editForm.action = routeFor(updateRouteTemplate, id);
                        deleteForm.action = routeFor(destroyRouteTemplate, id);

                        const canDelete = !nonDeletableRoleIds.has(id);
                        const deleteBtn = document.getElementById('editRoleDeleteBtn');
                        const blockedBadge = document.getElementById('editRoleDeleteBlockedBadge');

                        deleteBtn.classList.toggle('d-none', !canDelete);
                        blockedBadge.classList.toggle('d-none', canDelete);

                        deleteBtn.onclick = function () {
                            if (confirm(`Yakin akan menghapus role ${name}?`)) {
                                deleteForm.submit();
                            }
                        };
                    });
                }

                if (rolePermissionModal) {
                    rolePermissionModal.addEventListener('show.bs.modal', function (event) {
                        const trigger = event.relatedTarget;
                        if (!trigger) {
                            return;
                        }

                        const id = String(trigger.getAttribute('data-role-id') || '');
                        const name = trigger.getAttribute('data-role-name') || '';
                        document.getElementById('modalSetRolePermissionTitle').textContent = `SET Permission - ${name}`;
                        document.getElementById('formSetRolePermission').action = routeFor(rolePermissionUpdateRouteTemplate, id);

                        const selectedPermissions = new Set((rolePermissionsMap[id] || rolePermissionsMap[Number(id)] || []).map(String));
                        document.querySelectorAll('.js-role-permission-checkbox').forEach((checkbox) => {
                            const permissionId = String(checkbox.getAttribute('data-permission-id') || checkbox.value);
                            checkbox.checked = selectedPermissions.has(permissionId);
                        });

                        document.getElementById('selectedRolePermissionsCount').textContent = String(selectedPermissions.size);
                    });
                }
            });
        </script>
    @endpush
@endonce
