<div class="modal fade" id="modalCreateSemester" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('semesters.store') }}" method="POST">@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Semester</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Kode Semester</label><input class="form-control" name="kode" required></div><div class="mb-3"><label class="form-label">Nama Semester</label><input class="form-control" name="nama" required></div><div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" rows="3" name="deskripsi"></textarea></div></div>
<div class="modal-footer"><button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button></div>
</form></div></div></div>
@php
    $semesterUpdateRouteTemplate = route('semesters.update', ['semester' => '__SEMESTER__']);
    $semesterDestroyRouteTemplate = route('semesters.destroy', ['semester' => '__SEMESTER__']);
@endphp
<div class="modal fade" id="modalEditSemester" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form id="formEditSemester" action="#" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title" id="modalEditSemesterTitle">Edit Semester</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Kode Semester</label><input class="form-control" name="kode" id="editSemesterKode" required></div><div class="mb-3"><label class="form-label">Nama Semester</label><input class="form-control" name="nama" id="editSemesterNama" required></div><div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" rows="3" name="deskripsi" id="editSemesterDeskripsi"></textarea></div></div>
<div class="modal-footer">
    <button id="editSemesterDeleteBtn" class="btn btn-outline-danger btn-sm me-auto d-none" type="button"><i class="bi bi-trash"></i> Hapus</button>
    <span id="editSemesterDeleteBlockedBadge" class="badge bg-secondary me-auto d-none">Data digunakan, tidak dapat dihapus</span>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
<form id="formDeleteSemester" action="#" method="POST" class="d-none">@csrf @method('DELETE')</form>
@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const semesterModal = document.getElementById('modalEditSemester');
                if (!semesterModal) {
                    return;
                }

                const nonDeletableSemesterIds = new Set(@json(array_keys($nonDeletableSemesterIds ?? [])));
                const updateRouteTemplate = @json($semesterUpdateRouteTemplate);
                const destroyRouteTemplate = @json($semesterDestroyRouteTemplate);
                const routeFor = (template, id) => template.replace('__SEMESTER__', encodeURIComponent(String(id)));

                semesterModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }

                    const id = String(trigger.getAttribute('data-semester-id') || '');
                    const kode = trigger.getAttribute('data-semester-kode') || '';
                    const nama = trigger.getAttribute('data-semester-nama') || '';
                    const deskripsi = trigger.getAttribute('data-semester-deskripsi') || '';

                    document.getElementById('modalEditSemesterTitle').textContent = `Edit Semester - ${kode}`;
                    document.getElementById('editSemesterKode').value = kode;
                    document.getElementById('editSemesterNama').value = nama;
                    document.getElementById('editSemesterDeskripsi').value = deskripsi;

                    const editForm = document.getElementById('formEditSemester');
                    const deleteForm = document.getElementById('formDeleteSemester');
                    editForm.action = routeFor(updateRouteTemplate, id);
                    deleteForm.action = routeFor(destroyRouteTemplate, id);

                    const canDelete = !nonDeletableSemesterIds.has(id);
                    const deleteBtn = document.getElementById('editSemesterDeleteBtn');
                    const blockedBadge = document.getElementById('editSemesterDeleteBlockedBadge');

                    deleteBtn.classList.toggle('d-none', !canDelete);
                    blockedBadge.classList.toggle('d-none', canDelete);

                    deleteBtn.onclick = function () {
                        if (confirm(`Yakin akan menghapus semester ${kode} - ${nama}?`)) {
                            deleteForm.submit();
                        }
                    };
                });
            });
        </script>
    @endpush
@endonce
