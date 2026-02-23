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

@foreach (($evaluasis ?? collect()) as $evaluasi)
<div class="modal fade" id="modalEditEvaluasi-{{ $evaluasi->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('evaluasis.update', $evaluasi->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Evaluasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Kode Evaluasi</label><input class="form-control" name="kode" value="{{ $evaluasi->kode }}" required></div>
                    <div class="mb-3"><label class="form-label">Nama Evaluasi</label><input class="form-control" name="nama" value="{{ $evaluasi->nama }}" required></div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Pengetahuan/Kognitif" @selected($evaluasi->kategori == 'Pengetahuan/Kognitif')>Pengetahuan/Kognitif</option>
                            <option value="Hasil Proyek" @selected($evaluasi->kategori == 'Hasil Proyek')>Hasil Proyek</option>
                            <option value="Aktivitas Partisipatif" @selected($evaluasi->kategori == 'Aktivitas Partisipatif')>Aktivitas Partisipatif</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" rows="3" name="deskripsi">{{ $evaluasi->deskripsi }}</textarea></div>
                </div>
                <div class="modal-footer">
                    @php
                        $canDeleteEvaluasi = !$evaluasi->penugasans()->exists();
                    @endphp
                    @if ($canDeleteEvaluasi)
                        <button class="btn btn-outline-danger btn-sm me-auto" type="button" onclick="if(confirm('Yakin akan menghapus {{ $evaluasi->kode }}: {{ $evaluasi->nama }}?')){ document.getElementById('delete-evaluasi-{{ $evaluasi->id }}').submit(); }">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    @else
                        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                    @endif
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            @if ($canDeleteEvaluasi)
                <form id="delete-evaluasi-{{ $evaluasi->id }}" action="{{ route('evaluasis.destroy', $evaluasi->id) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
</div>
@endforeach
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

@foreach (($kontrakmks ?? collect()) as $kontrakmk)
<div class="modal fade" id="modalEditKontrakmk-{{ $kontrakmk->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('kontrakmks.update', $kontrakmk->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kontrak MK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Program Studi</label>
                            <select name="prodi_id" class="form-select" required>
                                <option value="">-Pilih Program Studi-</option>
                                @foreach (($prodis ?? collect()) as $prodi)
                                    <option value="{{ $prodi->id }}" @selected(($kontrakmk->mahasiswa->prodi_id ?? null) == $prodi->id)>{{ $prodi->jenjang }} - {{ $prodi->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mahasiswa</label>
                            <select name="mahasiswa_id" class="form-select" required>
                                <option value="">-Pilih Mahasiswa-</option>
                                @foreach (($mahasiswas ?? collect()) as $mahasiswa)
                                    <option value="{{ $mahasiswa->id }}" @selected($kontrakmk->mahasiswa_id == $mahasiswa->id)>{{ $mahasiswa->nim }} - {{ $mahasiswa->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mata Kuliah</label>
                            <select name="mk_id" class="form-select" required>
                                <option value="">-Pilih Mata Kuliah-</option>
                                @foreach (($mks ?? collect()) as $mk)
                                    <option value="{{ $mk->id }}" @selected($kontrakmk->mk_id == $mk->id)>{{ $mk->kode }} - {{ $mk->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dosen Pengampu</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-Pilih Dosen-</option>
                                @foreach (($dosens ?? collect()) as $dosen)
                                    <option value="{{ $dosen->id }}" @selected($kontrakmk->user_id == $dosen->id)>{{ $dosen->nidn }} - {{ $dosen->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" class="form-select">
                                <option value="">-Pilih Semester-</option>
                                @foreach (($semesters ?? collect()) as $semester)
                                    <option value="{{ $semester->id }}" @selected($kontrakmk->semester_id == $semester->id)>{{ $semester->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Kelas</label><input class="form-control" name="kelas" value="{{ $kontrakmk->kelas }}"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    @php
                        $canDeleteKontrakmk = true;
                    @endphp
                    @if ($canDeleteKontrakmk)
                        <button class="btn btn-outline-danger btn-sm me-auto" type="button" onclick="if(confirm('Yakin akan menghapus kontrak mata kuliah ini?')){ document.getElementById('delete-kontrakmk-{{ $kontrakmk->id }}').submit(); }">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    @else
                        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                    @endif
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            @if ($canDeleteKontrakmk)
                <form id="delete-kontrakmk-{{ $kontrakmk->id }}" action="{{ route('kontrakmks.destroy', $kontrakmk->id) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
</div>
@endforeach
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
@foreach (($mahasiswas ?? collect()) as $mahasiswa)
<div class="modal fade" id="modalEditMahasiswa-{{ $mahasiswa->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <form action="{{ route('mahasiswas.update', $mahasiswa->id) }}" method="POST">@csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Edit Mahasiswa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama Lengkap</label><input class="form-control" name="nama" value="{{ $mahasiswa->nama }}" required></div>
                <div class="mb-3"><label class="form-label">NIM</label><input class="form-control" name="nim" value="{{ $mahasiswa->nim }}" required></div>
                <div class="mb-3"><label class="form-label">Angkatan</label><input class="form-control" name="angkatan" value="{{ $mahasiswa->angkatan }}"></div>
                <div class="mb-3"><label class="form-label">Program Studi</label><select name="prodi_id" class="form-select">@foreach (($prodis ?? collect()) as $prodi)<option value="{{ $prodi->id }}" @selected($mahasiswa->prodi_id == $prodi->id)>{{ $prodi->jenjang }} - {{ $prodi->nama }}</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Alamat Email</label><input class="form-control" type="email" name="email" value="{{ $mahasiswa->email }}"></div>
                <div class="mb-3"><label class="form-label">No. WA aktif</label><input class="form-control" name="phone" value="{{ $mahasiswa->phone }}"></div>
            </div>
            <div class="modal-footer">
                @php
                    $canDeleteMahasiswa = !\App\Models\KontrakMk::where('mahasiswa_id', $mahasiswa->id)->exists()
                        && !\App\Models\Nilai::where('mahasiswa_id', $mahasiswa->id)->exists();
                @endphp
                @if ($canDeleteMahasiswa)
                    <button class="btn btn-outline-danger btn-sm me-auto" type="button" onclick="if(confirm('Yakin akan menghapus {{ $mahasiswa->nim }}: {{ $mahasiswa->nama }}?')){ document.getElementById('delete-mahasiswa-{{ $mahasiswa->id }}').submit(); }">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                @else
                    <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                @endif
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
            </div>
        </form>
        @if ($canDeleteMahasiswa)
            <form id="delete-mahasiswa-{{ $mahasiswa->id }}" action="{{ route('mahasiswas.destroy', $mahasiswa->id) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div></div>
</div>
@endforeach
@endif

@if (($title ?? '') === 'Permission')
<div class="modal fade" id="modalCreatePermission" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('permissions.store') }}" method="POST">@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Permission</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div><div class="mb-3"><label class="form-label">Guard</label><input class="form-control" name="guard_name" value="web" required></div></div>
<div class="modal-footer"><button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button></div>
</form></div></div></div>
@foreach (($permissions ?? collect()) as $permission)
<div class="modal fade" id="modalEditPermission-{{ $permission->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('permissions.update', $permission->id) }}" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title">Edit Permission</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $permission->name }}" required></div><div class="mb-3"><label class="form-label">Guard</label><input class="form-control" name="guard_name" value="{{ $permission->guard_name }}" required></div></div>
<div class="modal-footer">
    @php
        $canDeletePermission = !\Illuminate\Support\Facades\DB::table('role_has_permissions')->where('permission_id', $permission->id)->exists()
            && !\Illuminate\Support\Facades\DB::table('model_has_permissions')->where('permission_id', $permission->id)->exists();
    @endphp
    @if ($canDeletePermission)
        <button class="btn btn-outline-danger btn-sm me-auto" type="button" onclick="if(confirm('Yakin akan menghapus permission {{ $permission->name }}?')){ document.getElementById('delete-permission-{{ $permission->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
    @else
        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
    @endif
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
@if ($canDeletePermission)
<form id="delete-permission-{{ $permission->id }}" action="{{ route('permissions.destroy', $permission->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
@endforeach
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
@foreach (($prodis ?? collect()) as $prodi)
<div class="modal fade" id="modalEditProdi-{{ $prodi->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><form action="{{ route('prodis.update', $prodi->id) }}" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title">Edit Prodi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Kode Prodi (lokal)</label><input class="form-control" name="kode_prodi" value="{{ $prodi->kode_prodi }}" required></div>
        <div class="col-md-6"><label class="form-label">Nama Prodi</label><input class="form-control" name="nama" value="{{ $prodi->nama }}" required></div>
        <div class="col-md-4"><label class="form-label">Jenjang</label><input class="form-control" name="jenjang" value="{{ $prodi->jenjang }}"></div>
        <div class="col-md-4"><label class="form-label">Kode PDDIKTI</label><input class="form-control" name="kode_pddikti" value="{{ $prodi->kode_pddikti }}"></div>
        <div class="col-md-4"><label class="form-label">Singkatan</label><input class="form-control" name="singkat" value="{{ $prodi->singkat }}"></div>
        <div class="col-md-6"><label class="form-label">Perguruan Tinggi</label><input class="form-control" name="pt" value="{{ $prodi->pt }}"></div>
        <div class="col-md-6"><label class="form-label">Fakultas</label><input class="form-control" name="fakultas" value="{{ $prodi->fakultas }}"></div>
        <div class="col-12"><label class="form-label">Visi Keilmuan</label><textarea class="form-control" rows="4" name="visi_misi">{{ $prodi->visi_misi }}</textarea></div>
    </div>
</div>
<div class="modal-footer">
    @php
        $canDeleteProdi = !$prodi->kurikulums()->exists()
            && !$prodi->mahasiswas()->exists()
            && !\App\Models\JoinProdiUser::where('prodi_id', $prodi->id)->exists();
    @endphp
    @if ($canDeleteProdi)
        <button class="btn btn-outline-danger btn-sm me-auto" type="button" onclick="if(confirm('Yakin akan menghapus prodi {{ $prodi->nama }}?')){ document.getElementById('delete-prodi-{{ $prodi->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
    @else
        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
    @endif
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
@if ($canDeleteProdi)
<form id="delete-prodi-{{ $prodi->id }}" action="{{ route('prodis.destroy', $prodi->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
@endforeach
@endif

@if (($title ?? '') === 'Role')
<div class="modal fade" id="modalCreateRole" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('roles.store') }}" method="POST">@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div><div class="mb-3"><label class="form-label">Guard</label><input class="form-control" name="guard_name" value="web" required></div></div>
<div class="modal-footer">
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
@foreach (($roles ?? collect()) as $role)
<div class="modal fade" id="modalEditRole-{{ $role->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('roles.update', $role->id) }}" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title">Edit Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $role->name }}" required></div><div class="mb-3"><label class="form-label">Guard</label><input class="form-control" name="guard_name" value="{{ $role->guard_name }}" required></div></div>
<div class="modal-footer">
    @php
        $canDeleteRole = !\Illuminate\Support\Facades\DB::table('model_has_roles')->where('role_id', $role->id)->exists()
            && !\Illuminate\Support\Facades\DB::table('role_has_permissions')->where('role_id', $role->id)->exists();
    @endphp
    @if ($canDeleteRole)
        <button class="btn btn-outline-danger btn-sm me-auto" type="button" onclick="if(confirm('Yakin akan menghapus role {{ $role->name }}?')){ document.getElementById('delete-role-{{ $role->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
    @else
        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
    @endif
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
@if ($canDeleteRole)
<form id="delete-role-{{ $role->id }}" action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif

<div class="modal fade" id="modalSetRolePermission-{{ $role->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <form action="{{ route('rolepermissions.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title mb-1">SET Permission - {{ $role->name }}</h5>
                    <div class="text-muted small">Atur permission khusus untuk role ini.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                @php
                    $selectedPermissions = $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
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

                <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-3">
                    <span class="small text-muted">Permission terpilih saat ini</span>
                    <span class="badge text-bg-primary">{{ count($selectedPermissions) }}</span>
                </div>

                <div class="border rounded-3 p-3 mb-3 bg-light">
                    <div class="fw-semibold mb-2">(1) Permission Access</div>
                    @if ($accessPermissions->isNotEmpty())
                        <div class="row g-2">
                            @foreach ($accessPermissions as $permission)
                                <div class="col-md-6">
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" name="permission[]" id="role{{ $role->id }}perm{{ $permission->id }}" value="{{ $permission->id }}" @checked(in_array((string) $permission->id, $selectedPermissions, true))>
                                        <label class="form-check-label" for="role{{ $role->id }}perm{{ $permission->id }}">{{ $permission->name }}</label>
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
                                                        <input class="form-check-input" type="checkbox" name="permission[]" id="role{{ $role->id }}perm{{ $row[$action]->id }}" value="{{ $row[$action]->id }}" @checked(in_array((string) $row[$action]->id, $selectedPermissions, true))>
                                                        <label class="form-check-label" for="role{{ $role->id }}perm{{ $row[$action]->id }}"></label>
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
@endforeach
@endif

@if (($title ?? '') === 'Semester')
<div class="modal fade" id="modalCreateSemester" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('semesters.store') }}" method="POST">@csrf
<div class="modal-header"><h5 class="modal-title">Tambah Semester</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Kode Semester</label><input class="form-control" name="kode" required></div><div class="mb-3"><label class="form-label">Nama Semester</label><input class="form-control" name="nama" required></div><div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" rows="3" name="deskripsi"></textarea></div></div>
<div class="modal-footer"><button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button></div>
</form></div></div></div>
@foreach (($semesters ?? collect()) as $semester)
<div class="modal fade" id="modalEditSemester-{{ $semester->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><form action="{{ route('semesters.update', $semester->id) }}" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title">Edit Semester</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="mb-3"><label class="form-label">Kode Semester</label><input class="form-control" name="kode" value="{{ $semester->kode }}" required></div><div class="mb-3"><label class="form-label">Nama Semester</label><input class="form-control" name="nama" value="{{ $semester->nama }}" required></div><div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" rows="3" name="deskripsi">{{ $semester->deskripsi }}</textarea></div></div>
<div class="modal-footer">
    @php
        $canDeleteSemester = !$semester->kontrakmks()->exists()
            && !\App\Models\Subcpmk::where('semester_id', $semester->id)->exists()
            && !\App\Models\Penugasan::where('semester_id', $semester->id)->exists()
            && !\App\Models\JoinSubcpmkPenugasan::where('semester_id', $semester->id)->exists()
            && !\App\Models\Nilai::where('semester_id', $semester->id)->exists();
    @endphp
    @if ($canDeleteSemester)
        <button class="btn btn-outline-danger btn-sm me-auto" type="button" onclick="if(confirm('Yakin akan menghapus semester {{ $semester->kode }} - {{ $semester->nama }}?')){ document.getElementById('delete-semester-{{ $semester->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
    @else
        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
    @endif
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
@if ($canDeleteSemester)
<form id="delete-semester-{{ $semester->id }}" action="{{ route('semesters.destroy', $semester->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
@endforeach
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

@foreach (($users ?? collect()) as $user)
<div class="modal fade" id="modalEditUser-{{ $user->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><form action="{{ route('users.update', $user->id) }}" method="POST">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title">Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
    <div class="mb-3"><label class="form-label">Nama Lengkap</label><input class="form-control" name="name" value="{{ $user->name }}" required></div>
    <div class="mb-3"><label class="form-label">Username</label><input class="form-control" name="username" value="{{ $user->username }}" required></div>
    <div class="mb-3"><label class="form-label">Alamat Email</label><input class="form-control" type="email" name="email" value="{{ $user->email }}" required></div>
    <input type="hidden" name="password" value="{{ $user->password }}">
    <div class="mb-3"><label class="form-label">no. WA aktif</label><input class="form-control" name="phone" value="{{ $user->phone }}"></div>
    <div class="mb-3"><label class="form-label">NIDN</label><input class="form-control" name="nidn" value="{{ $user->nidn }}"></div>
</div>
<div class="modal-footer">
    @php
        $canDeleteUser = !$user->joinProdiUsers()->exists()
            && !$user->joinMkUsers()->exists()
            && !\App\Models\KontrakMk::where('user_id', $user->id)->exists();
    @endphp
    @if ($canDeleteUser)
        <button class="btn btn-outline-danger btn-sm me-auto" type="button" onclick="if(confirm('Yakin akan menghapus user {{ $user->name }}?')){ document.getElementById('delete-user-{{ $user->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
    @else
        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
    @endif
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button>
</div>
</form></div></div></div>
@if ($canDeleteUser)
<form id="delete-user-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif

<div class="modal fade" id="modalSetUserRole-{{ $user->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><form action="{{ route('userroles.update', $user->id) }}" method="POST">@csrf @method('PUT')
<div class="modal-header border-0 pb-0">
    <div>
        <h5 class="modal-title mb-1">SET Role - {{ $user->name }}</h5>
        <div class="text-muted small">Atur role yang dimiliki oleh user ini.</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
    @php
        $selectedUserRoles = $userRolesMap[$user->id] ?? [];
    @endphp

    <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-3">
        <span class="small text-muted">Role terpilih saat ini</span>
        <span class="badge text-bg-primary">{{ count($selectedUserRoles) }}</span>
    </div>

    <div class="border rounded-3 p-3 bg-light">
        <div class="fw-semibold mb-2">Daftar Role</div>
        <div class="row g-2">
            @foreach (($roleModels ?? collect()) as $roleModel)
                <div class="col-md-6">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="roles[]" id="user{{ $user->id }}role{{ $roleModel->id }}" value="{{ $roleModel->id }}" @checked(in_array((string) $roleModel->id, $selectedUserRoles, true))>
                        <label class="form-check-label" for="user{{ $user->id }}role{{ $roleModel->id }}">{{ $roleModel->name }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<div class="modal-footer"><button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button><button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Save</button></div>
</form></div></div></div>

<div class="modal fade" id="modalSetUserPermission-{{ $user->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><form action="{{ route('userpermissions.update', $user->id) }}" method="POST">@csrf @method('PUT')
<div class="modal-header border-0 pb-0">
    <div>
        <h5 class="modal-title mb-1">SET Permission - {{ $user->name }}</h5>
        <div class="text-muted small">Atur direct permission user tanpa mengubah permission turunan role.</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
    @php
        $directUserPermissions = $user->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
        $roleDerivedPermissions = $user->roles
            ->flatMap(fn ($role) => $role->permissions->pluck('id'))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

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
    @endphp

    <div class="alert alert-light border d-flex flex-column gap-1 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <span class="small text-muted">Direct permission user</span>
            <span class="badge text-bg-primary">{{ count($directUserPermissions) }}</span>
        </div>
        <div class="small text-muted">Permission turunan role ditandai otomatis dan tidak dapat diubah dari menu ini.</div>
    </div>

    <div class="border rounded-3 p-3 mb-3 bg-light">
        <div class="fw-semibold mb-2">(1) Permission Access</div>
        @if ($accessPermissions->isNotEmpty())
            <div class="row g-2">
                @foreach ($accessPermissions as $permission)
                    @php
                        $permissionId = (string) $permission->id;
                        $isDirect = in_array($permissionId, $directUserPermissions, true);
                        $isFromRole = in_array($permissionId, $roleDerivedPermissions, true);
                    @endphp
                    <div class="col-md-6">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" @if(!$isFromRole) name="permission[]" @endif id="user{{ $user->id }}perm{{ $permission->id }}" value="{{ $permission->id }}" @checked($isDirect || $isFromRole) @disabled($isFromRole)>
                            <label class="form-check-label" for="user{{ $user->id }}perm{{ $permission->id }}">{{ $permission->name }} @if($isFromRole)<span class="badge text-bg-secondary ms-1">dari role</span>@endif</label>
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
                                        @php
                                            $permissionId = (string) $row[$action]->id;
                                            $isDirect = in_array($permissionId, $directUserPermissions, true);
                                            $isFromRole = in_array($permissionId, $roleDerivedPermissions, true);
                                        @endphp
                                        <div class="form-check d-inline-block mb-0">
                                            <input class="form-check-input" type="checkbox" @if(!$isFromRole) name="permission[]" @endif id="user{{ $user->id }}perm{{ $row[$action]->id }}" value="{{ $row[$action]->id }}" @checked($isDirect || $isFromRole) @disabled($isFromRole)>
                                            <label class="form-check-label" for="user{{ $user->id }}perm{{ $row[$action]->id }}"></label>
                                        </div>
                                        @if($isFromRole)
                                            <div class="mt-1"><span class="badge text-bg-secondary">dari role</span></div>
                                        @endif
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
@endforeach
@endif
