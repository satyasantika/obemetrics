@push('title')
    Dashboard ADMIN
@endpush
<div class="row g-3 mt-1">
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 border-bottom">
                <div class="fw-semibold">Navigasi Admin</div>
                <small class="text-muted">Akses modul utama</small>
            </div>
            <div class="card-body d-grid gap-2">
                @can('read users')
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary text-start"><i class="bi bi-person-gear me-2"></i>Manajemen User</a>
                @endcan
                @can('read roles')
                    <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-primary text-start"><i class="bi bi-people me-2"></i>Role</a>
                @endcan
                @can('read permissions')
                    <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-outline-primary text-start"><i class="bi bi-shield-lock me-2"></i>Permission</a>
                @endcan
                @can('read prodis')
                    <a href="{{ route('prodis.index') }}" class="btn btn-sm btn-outline-secondary text-start"><i class="bi bi-journal-bookmark me-2"></i>Prodi</a>
                @endcan
                @can('read semesters')
                    <a href="{{ route('semesters.index') }}" class="btn btn-sm btn-outline-secondary text-start"><i class="bi bi-calendar3 me-2"></i>Semester</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body bg-light-subtle d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="h5 mb-1">Admin Panel OBEmetrics</div>
                    <div class="text-muted">Kelola data master, pengguna, dan konfigurasi akademik dalam satu tempat.</div>
                </div>
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Administrator</span>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 border-bottom">
                <div class="fw-semibold">Modul Akademik</div>
                <small class="text-muted">Pengelolaan data inti OBEmetrics</small>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @can('read evaluasis')
                        <a href="{{ route('evaluasis.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-clipboard-check me-1"></i>Evaluasi</a>
                    @endcan
                    @can('read mahasiswas')
                        <a href="{{ route('mahasiswas.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-mortarboard me-1"></i>Mahasiswa</a>
                    @endcan
                    @can('read kontrakmks')
                        <a href="{{ route('kontrakmks.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-text me-1"></i>Kontrak Mata Kuliah</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
