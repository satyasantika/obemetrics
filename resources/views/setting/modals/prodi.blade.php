<div class="modal fade" id="modalCreateProdi" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><form action="{{ route('prodis.store') }}" method="POST">@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Prodi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Kode Prodi (lokal)</label><input class="form-control" name="kode_prodi" required></div>
        <div class="col-md-6"><label class="form-label">Nama Prodi</label><input class="form-control" name="nama" required></div>
        <div class="col-md-4"><label class="form-label">Jenjang</label><input class="form-control" name="jenjang"></div>
        <div class="col-md-4"><label class="form-label">Kode PDDIKTI</label><input class="form-control" name="kode_pddikti"></div>
        <div class="col-md-4"><label class="form-label">Singkatan</label><input class="form-control" name="singkat"></div>
        <div class="col-md-6"><label class="form-label">Perguruan Tinggi</label><input class="form-control" name="pt"></div>
        <div class="col-md-6"><label class="form-label">Fakultas</label><input class="form-control" name="fakultas"></div>
        <div class="col-12"><label class="form-label">Visi Keilmuan</label><textarea class="form-control" rows="4" name="visi_misi"></textarea></div>
    </div>
</div>
<div class="modal-footer"><button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button></div>
</form></div></div></div>
@php
    $prodiUpdateRouteTemplate = route('prodis.update', ['prodi' => '__PRODI__']);
    $prodiDestroyRouteTemplate = route('prodis.destroy', ['prodi' => '__PRODI__']);
@endphp
<div class="modal fade" id="modalEditProdi" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><form id="formEditProdi" action="#" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title" id="modalEditProdiTitle">Edit Prodi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Kode Prodi (lokal)</label><input class="form-control" name="kode_prodi" id="editProdiKodeProdi" required></div>
        <div class="col-md-6"><label class="form-label">Nama Prodi</label><input class="form-control" name="nama" id="editProdiNama" required></div>
        <div class="col-md-4"><label class="form-label">Jenjang</label><input class="form-control" name="jenjang" id="editProdiJenjang"></div>
        <div class="col-md-4"><label class="form-label">Kode PDDIKTI</label><input class="form-control" name="kode_pddikti" id="editProdiKodePddikti"></div>
        <div class="col-md-4"><label class="form-label">Singkatan</label><input class="form-control" name="singkat" id="editProdiSingkat"></div>
        <div class="col-md-6"><label class="form-label">Perguruan Tinggi</label><input class="form-control" name="pt" id="editProdiPt"></div>
        <div class="col-md-6"><label class="form-label">Fakultas</label><input class="form-control" name="fakultas" id="editProdiFakultas"></div>
        <div class="col-12"><label class="form-label">Visi Keilmuan</label><textarea class="form-control" rows="4" name="visi_misi" id="editProdiVisiMisi"></textarea></div>
    </div>
</div>
<div class="modal-footer">
    <button id="editProdiDeleteBtn" class="btn btn-outline-danger btn-sm me-auto d-none" type="button"><i class="bi bi-trash"></i> Hapus</button>
    <span id="editProdiDeleteBlockedBadge" class="badge bg-secondary me-auto d-none">Data digunakan, tidak dapat dihapus</span>
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
<form id="formDeleteProdi" action="#" method="POST" class="d-none">@csrf @method('DELETE')</form>
@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const prodiModal = document.getElementById('modalEditProdi');
                if (!prodiModal) {
                    return;
                }

                const nonDeletableProdiIds = new Set(@json(array_keys($nonDeletableProdiIds ?? [])));
                const updateRouteTemplate = @json($prodiUpdateRouteTemplate);
                const destroyRouteTemplate = @json($prodiDestroyRouteTemplate);
                const routeFor = (template, id) => template.replace('__PRODI__', encodeURIComponent(String(id)));

                prodiModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }

                    const id = String(trigger.getAttribute('data-prodi-id') || '');
                    const kodeProdi = trigger.getAttribute('data-prodi-kode-prodi') || '';
                    const nama = trigger.getAttribute('data-prodi-nama') || '';
                    const jenjang = trigger.getAttribute('data-prodi-jenjang') || '';
                    const kodePddikti = trigger.getAttribute('data-prodi-kode-pddikti') || '';
                    const singkat = trigger.getAttribute('data-prodi-singkat') || '';
                    const pt = trigger.getAttribute('data-prodi-pt') || '';
                    const fakultas = trigger.getAttribute('data-prodi-fakultas') || '';
                    const visiMisi = trigger.getAttribute('data-prodi-visi-misi') || '';

                    document.getElementById('modalEditProdiTitle').textContent = `Edit Prodi - ${nama}`;
                    document.getElementById('editProdiKodeProdi').value = kodeProdi;
                    document.getElementById('editProdiNama').value = nama;
                    document.getElementById('editProdiJenjang').value = jenjang;
                    document.getElementById('editProdiKodePddikti').value = kodePddikti;
                    document.getElementById('editProdiSingkat').value = singkat;
                    document.getElementById('editProdiPt').value = pt;
                    document.getElementById('editProdiFakultas').value = fakultas;
                    document.getElementById('editProdiVisiMisi').value = visiMisi;

                    const editForm = document.getElementById('formEditProdi');
                    const deleteForm = document.getElementById('formDeleteProdi');
                    editForm.action = routeFor(updateRouteTemplate, id);
                    deleteForm.action = routeFor(destroyRouteTemplate, id);

                    const canDelete = !nonDeletableProdiIds.has(id);
                    const deleteBtn = document.getElementById('editProdiDeleteBtn');
                    const blockedBadge = document.getElementById('editProdiDeleteBlockedBadge');

                    deleteBtn.classList.toggle('d-none', !canDelete);
                    blockedBadge.classList.toggle('d-none', canDelete);

                    deleteBtn.onclick = function () {
                        if (confirm(`Yakin akan menghapus prodi ${nama}?`)) {
                            deleteForm.submit();
                        }
                    };
                });
            });
        </script>
    @endpush
@endonce
