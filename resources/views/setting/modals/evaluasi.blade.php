<div class="modal fade" id="modalCreateEvaluasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('evaluasis.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Evaluasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Kode Evaluasi</label><input class="form-control" name="kode" required></div>
                    <div class="mb-3"><label class="form-label">Nama Evaluasi</label><input class="form-control" name="nama" required></div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Pengetahuan/Kognitif">Pengetahuan/Kognitif</option>
                            <option value="Hasil Proyek">Hasil Proyek</option>
                            <option value="Aktivitas Partisipatif">Aktivitas Partisipatif</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" rows="3" name="deskripsi"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $evaluasiUpdateRouteTemplate = route('evaluasis.update', ['evaluasi' => '__EVALUASI__']);
    $evaluasiDestroyRouteTemplate = route('evaluasis.destroy', ['evaluasi' => '__EVALUASI__']);
@endphp
<div class="modal fade" id="modalEditEvaluasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="formEditEvaluasi" action="#" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditEvaluasiTitle">Edit Evaluasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Kode Evaluasi</label><input class="form-control" name="kode" id="editEvaluasiKode" required></div>
                    <div class="mb-3"><label class="form-label">Nama Evaluasi</label><input class="form-control" name="nama" id="editEvaluasiNama" required></div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" id="editEvaluasiKategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Pengetahuan/Kognitif">Pengetahuan/Kognitif</option>
                            <option value="Hasil Proyek">Hasil Proyek</option>
                            <option value="Aktivitas Partisipatif">Aktivitas Partisipatif</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" rows="3" name="deskripsi" id="editEvaluasiDeskripsi"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button id="editEvaluasiDeleteBtn" class="btn btn-outline-danger btn-sm me-auto d-none" type="button"><i class="bi bi-trash"></i> Hapus</button>
                    <span id="editEvaluasiDeleteBlockedBadge" class="badge bg-secondary me-auto d-none">Data digunakan, tidak dapat dihapus</span>
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            <form id="formDeleteEvaluasi" action="#" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const evaluasiModal = document.getElementById('modalEditEvaluasi');
                if (!evaluasiModal) {
                    return;
                }

                const nonDeletableEvaluasiIds = new Set(@json(array_keys($nonDeletableEvaluasiIds ?? [])));
                const updateRouteTemplate = @json($evaluasiUpdateRouteTemplate);
                const destroyRouteTemplate = @json($evaluasiDestroyRouteTemplate);
                const routeFor = (template, id) => template.replace('__EVALUASI__', encodeURIComponent(String(id)));

                evaluasiModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }

                    const id = String(trigger.getAttribute('data-evaluasi-id') || '');
                    const kode = trigger.getAttribute('data-evaluasi-kode') || '';
                    const nama = trigger.getAttribute('data-evaluasi-nama') || '';
                    const kategori = trigger.getAttribute('data-evaluasi-kategori') || '';
                    const deskripsi = trigger.getAttribute('data-evaluasi-deskripsi') || '';

                    document.getElementById('modalEditEvaluasiTitle').textContent = `Edit Evaluasi - ${kode}`;
                    document.getElementById('editEvaluasiKode').value = kode;
                    document.getElementById('editEvaluasiNama').value = nama;
                    document.getElementById('editEvaluasiKategori').value = kategori;
                    document.getElementById('editEvaluasiDeskripsi').value = deskripsi;

                    const editForm = document.getElementById('formEditEvaluasi');
                    const deleteForm = document.getElementById('formDeleteEvaluasi');
                    editForm.action = routeFor(updateRouteTemplate, id);
                    deleteForm.action = routeFor(destroyRouteTemplate, id);

                    const canDelete = !nonDeletableEvaluasiIds.has(id);
                    const deleteBtn = document.getElementById('editEvaluasiDeleteBtn');
                    const blockedBadge = document.getElementById('editEvaluasiDeleteBlockedBadge');

                    deleteBtn.classList.toggle('d-none', !canDelete);
                    blockedBadge.classList.toggle('d-none', canDelete);

                    deleteBtn.onclick = function () {
                        if (confirm(`Yakin akan menghapus ${kode}: ${nama}?`)) {
                            deleteForm.submit();
                        }
                    };
                });
            });
        </script>
    @endpush
@endonce
