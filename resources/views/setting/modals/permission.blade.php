<div class="modal fade" id="modalCreatePermission" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('permissions.store') }}" method="POST">@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Permission</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div><div class="mb-3"><label class="form-label">Guard</label><input class="form-control" name="guard_name" value="web" required></div></div>
<div class="modal-footer"><button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button></div>
</form></div></div></div>
@php
    $permissionUpdateRouteTemplate = route('permissions.update', ['permission' => '__PERMISSION__']);
    $permissionDestroyRouteTemplate = route('permissions.destroy', ['permission' => '__PERMISSION__']);
@endphp
<div class="modal fade" id="modalEditPermission" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form id="formEditPermission" action="#" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title" id="modalEditPermissionTitle">Edit Permission</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" id="editPermissionName" required></div><div class="mb-3"><label class="form-label">Guard</label><input class="form-control" name="guard_name" id="editPermissionGuardName" required></div></div>
<div class="modal-footer">
    <button id="editPermissionDeleteBtn" class="btn btn-outline-danger btn-sm me-auto d-none" type="button"><i class="bi bi-trash"></i> Hapus</button>
    <span id="editPermissionDeleteBlockedBadge" class="badge bg-secondary me-auto d-none">Data digunakan, tidak dapat dihapus</span>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
<form id="formDeletePermission" action="#" method="POST" class="d-none">@csrf @method('DELETE')</form>
@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const permissionModal = document.getElementById('modalEditPermission');
                if (!permissionModal) {
                    return;
                }

                const nonDeletablePermissionIds = new Set(@json(array_keys($nonDeletablePermissionIds ?? [])));
                const updateRouteTemplate = @json($permissionUpdateRouteTemplate);
                const destroyRouteTemplate = @json($permissionDestroyRouteTemplate);
                const routeFor = (template, id) => template.replace('__PERMISSION__', encodeURIComponent(String(id)));

                permissionModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }

                    const id = String(trigger.getAttribute('data-permission-id') || '');
                    const name = trigger.getAttribute('data-permission-name') || '';
                    const guardName = trigger.getAttribute('data-permission-guard') || '';

                    document.getElementById('modalEditPermissionTitle').textContent = `Edit Permission - ${name}`;
                    document.getElementById('editPermissionName').value = name;
                    document.getElementById('editPermissionGuardName').value = guardName;

                    const editForm = document.getElementById('formEditPermission');
                    const deleteForm = document.getElementById('formDeletePermission');
                    editForm.action = routeFor(updateRouteTemplate, id);
                    deleteForm.action = routeFor(destroyRouteTemplate, id);

                    const canDelete = !nonDeletablePermissionIds.has(id);
                    const deleteBtn = document.getElementById('editPermissionDeleteBtn');
                    const blockedBadge = document.getElementById('editPermissionDeleteBlockedBadge');

                    deleteBtn.classList.toggle('d-none', !canDelete);
                    blockedBadge.classList.toggle('d-none', canDelete);

                    deleteBtn.onclick = function () {
                        if (confirm(`Yakin akan menghapus permission ${name}?`)) {
                            deleteForm.submit();
                        }
                    };
                });
            });
        </script>
    @endpush
@endonce
