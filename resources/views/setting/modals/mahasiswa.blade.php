<div class="modal fade" id="modalCreateMahasiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <form action="{{ route('mahasiswas.store') }}" method="POST">@csrf
            <div class="modal-header"><h5 class="modal-title">Tambah Mahasiswa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama Lengkap</label><input class="form-control" name="nama" required></div>
                <div class="mb-3"><label class="form-label">NIM</label><input class="form-control" name="nim" required></div>
                <div class="mb-3"><label class="form-label">Angkatan</label><input class="form-control" name="angkatan" value="{{ date('Y') }}"></div>
                <div class="mb-3"><label class="form-label">Program Studi</label><select name="prodi_id" class="form-select">@foreach (($prodis ?? collect()) as $prodi)<option value="{{ $prodi->id }}">{{ $prodi->jenjang }} - {{ $prodi->nama }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Alamat Email</label><input class="form-control" type="email" name="email"></div>
                <div class="mb-3"><label class="form-label">No. WA aktif</label><input class="form-control" name="phone"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button></div>
        </form>
    </div></div>
</div>
@php
    $mahasiswaUpdateRouteTemplate = route('mahasiswas.update', ['mahasiswa' => '__MAHASISWA__']);
    $mahasiswaDestroyRouteTemplate = route('mahasiswas.destroy', ['mahasiswa' => '__MAHASISWA__']);
@endphp
<div class="modal fade" id="modalEditMahasiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <form id="formEditMahasiswa" action="#" method="POST">@csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title" id="modalEditMahasiswaTitle">Edit Mahasiswa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama Lengkap</label><input class="form-control" name="nama" id="editMahasiswaNama" required></div>
                <div class="mb-3"><label class="form-label">NIM</label><input class="form-control" name="nim" id="editMahasiswaNim" required></div>
                <div class="mb-3"><label class="form-label">Angkatan</label><input class="form-control" name="angkatan" id="editMahasiswaAngkatan"></div>
                <div class="mb-3"><label class="form-label">Program Studi</label><select name="prodi_id" class="form-select" id="editMahasiswaProdiId">@foreach (($prodis ?? collect()) as $prodi)<option value="{{ $prodi->id }}">{{ $prodi->jenjang }} - {{ $prodi->nama }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Alamat Email</label><input class="form-control" type="email" name="email" id="editMahasiswaEmail"></div>
                <div class="mb-3"><label class="form-label">No. WA aktif</label><input class="form-control" name="phone" id="editMahasiswaPhone"></div>
            </div>
            <div class="modal-footer">
                <button id="editMahasiswaDeleteBtn" class="btn btn-outline-danger btn-sm me-auto d-none" type="button"><i class="bi bi-trash"></i> Hapus</button>
                <span id="editMahasiswaDeleteBlockedBadge" class="badge bg-secondary me-auto d-none">Data digunakan, tidak dapat dihapus</span>
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
            </div>
        </form>
        <form id="formDeleteMahasiswa" action="#" method="POST" class="d-none">@csrf @method('DELETE')</form>
    </div></div>
</div>
@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mahasiswaModal = document.getElementById('modalEditMahasiswa');
                if (!mahasiswaModal) {
                    return;
                }

                const nonDeletableMahasiswaIds = new Set(@json(array_keys($nonDeletableMahasiswaIds ?? [])));
                const updateRouteTemplate = @json($mahasiswaUpdateRouteTemplate);
                const destroyRouteTemplate = @json($mahasiswaDestroyRouteTemplate);
                const routeFor = (template, id) => template.replace('__MAHASISWA__', encodeURIComponent(String(id)));

                mahasiswaModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }

                    const id = String(trigger.getAttribute('data-mahasiswa-id') || '');
                    const nama = trigger.getAttribute('data-mahasiswa-nama') || '';
                    const nim = trigger.getAttribute('data-mahasiswa-nim') || '';
                    const angkatan = trigger.getAttribute('data-mahasiswa-angkatan') || '';
                    const prodiId = trigger.getAttribute('data-mahasiswa-prodi-id') || '';
                    const email = trigger.getAttribute('data-mahasiswa-email') || '';
                    const phone = trigger.getAttribute('data-mahasiswa-phone') || '';

                    document.getElementById('modalEditMahasiswaTitle').textContent = `Edit Mahasiswa - ${nim}`;
                    document.getElementById('editMahasiswaNama').value = nama;
                    document.getElementById('editMahasiswaNim').value = nim;
                    document.getElementById('editMahasiswaAngkatan').value = angkatan;
                    document.getElementById('editMahasiswaProdiId').value = prodiId;
                    document.getElementById('editMahasiswaEmail').value = email;
                    document.getElementById('editMahasiswaPhone').value = phone;

                    const editForm = document.getElementById('formEditMahasiswa');
                    const deleteForm = document.getElementById('formDeleteMahasiswa');
                    editForm.action = routeFor(updateRouteTemplate, id);
                    deleteForm.action = routeFor(destroyRouteTemplate, id);

                    const canDelete = !nonDeletableMahasiswaIds.has(id);
                    const deleteBtn = document.getElementById('editMahasiswaDeleteBtn');
                    const blockedBadge = document.getElementById('editMahasiswaDeleteBlockedBadge');

                    deleteBtn.classList.toggle('d-none', !canDelete);
                    blockedBadge.classList.toggle('d-none', canDelete);

                    deleteBtn.onclick = function () {
                        if (confirm(`Yakin akan menghapus ${nim}: ${nama}?`)) {
                            deleteForm.submit();
                        }
                    };
                });
            });
        </script>
    @endpush
@endonce
