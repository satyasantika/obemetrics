@php
    $prodiUserUpdateRouteTemplate = route('prodis.prodiusers.update', ['prodi' => $prodi->id, 'prodiuser' => '__PRODIUSER__']);
    $prodiUserDestroyRouteTemplate = route('prodis.prodiusers.destroy', ['prodi' => $prodi->id, 'prodiuser' => '__PRODIUSER__']);
@endphp

<div class="modal fade" id="modalEditProdiUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditProdiUser" action="#" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditProdiUserTitle">Edit User Prodi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">User Dosen</label>
                            <input type="text" class="form-control" id="editProdiUserName" readonly>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-1">
                                <input type="hidden" name="status_pimpinan" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="editJoinProdiStatusPimpinan" name="status_pimpinan" value="1">
                                <label class="form-check-label" for="editJoinProdiStatusPimpinan">Status pimpinan prodi</label>
                            </div>
                            <small class="text-muted d-block mt-1">Hanya user dengan status pimpinan yang dapat mengakses alur kurikulum prodi.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="editJoinProdiDeleteBtn" class="btn btn-outline-danger btn-sm me-auto d-none" type="button"><i class="bi bi-trash"></i> Hapus</button>
                    <span id="editJoinProdiDeleteBlockedBadge" class="badge bg-secondary me-auto d-none">Data digunakan, tidak dapat dihapus</span>
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="formDeleteProdiUser" action="#" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('modalEditProdiUser');
                if (!modal) {
                    return;
                }

                const updateRouteTemplate = @json($prodiUserUpdateRouteTemplate);
                const destroyRouteTemplate = @json($prodiUserDestroyRouteTemplate);
                const routeFor = function (template, id) {
                    return template.replace('__PRODIUSER__', encodeURIComponent(String(id)));
                };

                modal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }

                    const id = String(trigger.getAttribute('data-prodiuser-id') || '');
                    const userName = trigger.getAttribute('data-prodiuser-username') || '-';
                    const isPimpinan = String(trigger.getAttribute('data-prodiuser-status-pimpinan') || '0') === '1';
                    const canDelete = String(trigger.getAttribute('data-prodiuser-can-delete') || '0') === '1';

                    const editForm = document.getElementById('formEditProdiUser');
                    const deleteForm = document.getElementById('formDeleteProdiUser');
                    const title = document.getElementById('modalEditProdiUserTitle');
                    const inputName = document.getElementById('editProdiUserName');
                    const inputPimpinan = document.getElementById('editJoinProdiStatusPimpinan');
                    const deleteBtn = document.getElementById('editJoinProdiDeleteBtn');
                    const blockedBadge = document.getElementById('editJoinProdiDeleteBlockedBadge');

                    editForm.action = routeFor(updateRouteTemplate, id);
                    deleteForm.action = routeFor(destroyRouteTemplate, id);
                    title.textContent = `Edit User Prodi - ${userName}`;
                    inputName.value = userName;
                    inputPimpinan.checked = isPimpinan;

                    deleteBtn.classList.toggle('d-none', !canDelete);
                    blockedBadge.classList.toggle('d-none', canDelete);

                    deleteBtn.onclick = function () {
                        if (confirm(`Yakin akan menghapus ${userName} dari prodi ini?`)) {
                            deleteForm.submit();
                        }
                    };
                });
            });
        </script>
    @endpush
@endonce
