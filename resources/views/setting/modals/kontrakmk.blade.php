<div class="modal fade" id="modalCreateKontrakmk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
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
    $isPimpinanProdi = auth()->check() && auth()->user()->hasRole('pimpinan prodi');
@endphp
<div class="modal fade" id="modalEditKontrakmk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
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
                            <select
                                @unless($isPimpinanProdi) name="prodi_id" @endunless
                                class="form-select"
                                id="editKontrakmkProdiId"
                                required
                                @if($isPimpinanProdi) disabled aria-disabled="true" @endif
                            >
                                <option value="">-Pilih Program Studi-</option>
                                @foreach (($prodis ?? collect()) as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->jenjang }} - {{ $prodi->nama }}</option>
                                @endforeach
                            </select>
                            @if($isPimpinanProdi)
                                <input type="hidden" name="prodi_id" id="editKontrakmkProdiIdHidden">
                            @endif
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
                    const prodiHiddenInput = document.getElementById('editKontrakmkProdiIdHidden');
                    if (prodiHiddenInput) {
                        prodiHiddenInput.value = prodiId;
                    }
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
