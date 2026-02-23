@if (($title ?? '') === 'Evaluasi')
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
@endif

@if (($title ?? '') === 'KontrakMk')
<div class="modal fade" id="modalCreateKontrakmk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('kontrakmks.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kontrak MK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Program Studi</label>
                            <select name="prodi_id" class="form-select" required>
                                <option value="">-Pilih Program Studi-</option>
                                @foreach (($prodis ?? collect()) as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->jenjang }} - {{ $prodi->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mahasiswa</label>
                            <select name="mahasiswa_id" class="form-select" required>
                                <option value="">-Pilih Mahasiswa-</option>
                                @foreach (($mahasiswas ?? collect()) as $mahasiswa)
                                    <option value="{{ $mahasiswa->id }}">{{ $mahasiswa->nim }} - {{ $mahasiswa->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mata Kuliah</label>
                            <select name="mk_id" class="form-select" required>
                                <option value="">-Pilih Mata Kuliah-</option>
                                @foreach (($mks ?? collect()) as $mk)
                                    <option value="{{ $mk->id }}">{{ $mk->kode }} - {{ $mk->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dosen Pengampu</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-Pilih Dosen-</option>
                                @foreach (($dosens ?? collect()) as $dosen)
                                    <option value="{{ $dosen->id }}">{{ $dosen->nidn }} - {{ $dosen->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" class="form-select">
                                <option value="">-Pilih Semester-</option>
                                @foreach (($semesters ?? collect()) as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Kelas</label><input class="form-control" name="kelas" placeholder="Contoh: A"></div>
                    </div>
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
    $kontrakmkUpdateRouteTemplate = route('kontrakmks.update', ['kontrakmk' => '__KONTRAKMK__']);
    $kontrakmkDestroyRouteTemplate = route('kontrakmks.destroy', ['kontrakmk' => '__KONTRAKMK__']);
@endphp
<div class="modal fade" id="modalEditKontrakmk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form id="formEditKontrakmk" action="#" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditKontrakmkTitle">Edit Kontrak MK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Program Studi</label>
                            <select name="prodi_id" class="form-select" id="editKontrakmkProdiId" required>
                                <option value="">-Pilih Program Studi-</option>
                                @foreach (($prodis ?? collect()) as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->jenjang }} - {{ $prodi->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mahasiswa</label>
                            <select name="mahasiswa_id" class="form-select" id="editKontrakmkMahasiswaId" required>
                                <option value="">-Pilih Mahasiswa-</option>
                                @foreach (($mahasiswas ?? collect()) as $mahasiswa)
                                    <option value="{{ $mahasiswa->id }}">{{ $mahasiswa->nim }} - {{ $mahasiswa->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mata Kuliah</label>
                            <select name="mk_id" class="form-select" id="editKontrakmkMkId" required>
                                <option value="">-Pilih Mata Kuliah-</option>
                                @foreach (($mks ?? collect()) as $mk)
                                    <option value="{{ $mk->id }}">{{ $mk->kode }} - {{ $mk->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dosen Pengampu</label>
                            <select name="user_id" class="form-select" id="editKontrakmkUserId" required>
                                <option value="">-Pilih Dosen-</option>
                                @foreach (($dosens ?? collect()) as $dosen)
                                    <option value="{{ $dosen->id }}">{{ $dosen->nidn }} - {{ $dosen->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" class="form-select" id="editKontrakmkSemesterId">
                                <option value="">-Pilih Semester-</option>
                                @foreach (($semesters ?? collect()) as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Kelas</label><input class="form-control" name="kelas" id="editKontrakmkKelas"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="editKontrakmkDeleteBtn" class="btn btn-outline-danger btn-sm me-auto" type="button"><i class="bi bi-trash"></i> Hapus</button>
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            <form id="formDeleteKontrakmk" action="#" method="POST" class="d-none">
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
                const kontrakmkModal = document.getElementById('modalEditKontrakmk');
                if (!kontrakmkModal) {
                    return;
                }

                const updateRouteTemplate = @json($kontrakmkUpdateRouteTemplate);
                const destroyRouteTemplate = @json($kontrakmkDestroyRouteTemplate);
                const routeFor = (template, id) => template.replace('__KONTRAKMK__', encodeURIComponent(String(id)));

                kontrakmkModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }

                    const id = String(trigger.getAttribute('data-kontrakmk-id') || '');
                    const prodiId = trigger.getAttribute('data-kontrakmk-prodi-id') || '';
                    const mahasiswaId = trigger.getAttribute('data-kontrakmk-mahasiswa-id') || '';
                    const mkId = trigger.getAttribute('data-kontrakmk-mk-id') || '';
                    const userId = trigger.getAttribute('data-kontrakmk-user-id') || '';
                    const semesterId = trigger.getAttribute('data-kontrakmk-semester-id') || '';
                    const kelas = trigger.getAttribute('data-kontrakmk-kelas') || '';

                    document.getElementById('modalEditKontrakmkTitle').textContent = `Edit Kontrak MK - #${id}`;
                    document.getElementById('editKontrakmkProdiId').value = prodiId;
                    document.getElementById('editKontrakmkMahasiswaId').value = mahasiswaId;
                    document.getElementById('editKontrakmkMkId').value = mkId;
                    document.getElementById('editKontrakmkUserId').value = userId;
                    document.getElementById('editKontrakmkSemesterId').value = semesterId;
                    document.getElementById('editKontrakmkKelas').value = kelas;

                    const editForm = document.getElementById('formEditKontrakmk');
                    const deleteForm = document.getElementById('formDeleteKontrakmk');
                    editForm.action = routeFor(updateRouteTemplate, id);
                    deleteForm.action = routeFor(destroyRouteTemplate, id);

                    document.getElementById('editKontrakmkDeleteBtn').onclick = function () {
                        if (confirm('Yakin akan menghapus kontrak mata kuliah ini?')) {
                            deleteForm.submit();
                        }
                    };
                });
            });
        </script>
    @endpush
@endonce
@endif

@if (($title ?? '') === 'Mahasiswa')
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
@endif

@if (($title ?? '') === 'Permission')
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
@endif

@if (($title ?? '') === 'Prodi')
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
@endif

@if (($title ?? '') === 'Role')
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
@endif

@if (($title ?? '') === 'Semester')
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
@endif

@if (($title ?? '') === 'User')
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
@endif
