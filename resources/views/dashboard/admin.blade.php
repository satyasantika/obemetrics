@push('title')
    Dashboard ADMIN
@endpush
<div class="row mt-3">
    <div class="col-xl-10 col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0">Manajemen OBEmetrics</h5>
                        <small class="text-muted">Kelola data master dan konfigurasi sistem</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">Admin Panel</span>
                </div>
            </div>
            <div class="card-body pt-3">

                @can('read users')
                    <div class="mb-4">
                        <div class="text-muted small text-uppercase mb-2">Manajemen Pengguna</div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-person"></i> User</a>
                            @can('read roles')
                                <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-people"></i> Role</a>
                            @endcan
                            @can('read permissions')
                                <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-shield-lock"></i> Permission</a>
                            @endcan
                        </div>
                    </div>
                @endcan

                <div>
                    <div class="text-muted small text-uppercase mb-2">Data Akademik</div>
                    <div class="d-flex flex-wrap gap-2">
                        @can('read prodis')
                            <a href="{{ route('prodis.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-journal-bookmark"></i> Prodi</a>
                        @endcan
                        @can('read semesters')
                            <a href="{{ route('semesters.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-calendar"></i> Semester</a>
                        @endcan
                        @can('read evaluasis')
                            <a href="{{ route('evaluasis.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-clipboard-check"></i> Evaluasi</a>
                        @endcan
                        @can('read mahasiswas')
                            <a href="{{ route('mahasiswas.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-people-fill"></i> Mahasiswa</a>
                        @endcan
                        @can('read kontrakmks')
                            <a href="{{ route('kontrakmks.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-text"></i> Kontrak Mata Kuliah</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
