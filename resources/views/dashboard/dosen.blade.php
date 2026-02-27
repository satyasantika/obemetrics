@push('title')
    Dashboard Dosen
@endpush
<div class="row mt-3">
    <div class="col">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <h5 class="mb-0">Manajemen Mata Kuliah Dosen</h5>
                <small class="text-muted">Daftar mata kuliah yang Anda ampu per program studi</small>
            </div>
            <div class="card-body">

                @forelse (auth()->user()->joinProdiUsers as $joinProdiUser)
                    <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <h6 class="mb-0">Program Studi {{ $joinProdiUser->prodi->jenjang }} {{ $joinProdiUser->prodi->nama }}</h6>
                            <span class="badge bg-primary-subtle text-primary">Pengampu Aktif</span>
                        </div>

                        @foreach (auth()->user()->joinMkUsers->unique('kurikulum_id') as $joinMkUser)
                            @if ($joinMkUser->kurikulum->prodi_id == $joinProdiUser->prodi->id)
                                <div class="border rounded-2 p-2 p-md-3 mb-2 bg-white">
                                    <div class="fw-semibold mb-2">{{ $joinMkUser->kurikulum->nama }}</div>
                                    <ol class="mb-0">
                                        @foreach (auth()->user()->joinMkUsers->pluck('mk')->where('kurikulum_id',$joinMkUser->kurikulum->id) as $mk)
                                            <li class="mb-2">
                                                <div>{{ $mk->kode }} {{ $mk->nama }}</div>
                                                <a href="{{ route('mks.cpmks.index',[$mk->id]) }}" class="btn btn-link btn-sm p-0 text-decoration-none">
                                                    <i class="bi bi-eye"></i> Selengkapnya
                                                </a>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif
                        @endforeach

                        @if (!auth()->user()->joinMkUsers->contains(fn ($item) => $item->kurikulum->prodi_id == $joinProdiUser->prodi->id))
                            <div class="text-muted small">Belum ada mata kuliah terdaftar pada program studi ini.</div>
                        @endif
                    </div>
                @empty
                    <div class="alert alert-warning mb-0">Anda belum terdaftar pada program studi manapun.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
