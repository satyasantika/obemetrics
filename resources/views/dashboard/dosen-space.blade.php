@extends('layouts.panel')

@push('title')
    Ruang Dosen
@endpush

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-info-subtle text-info-emphasis border-0 border-bottom border-info-subtle fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-mortarboard-fill"></i>
        <span>Manajemen Mata Kuliah Dosen</span>
    </div>
    <div class="card-body">
        @forelse ($joinProdiUsers as $joinProdiUser)
            @php
                $prodi = $joinProdiUser->prodi;
                $kurikulumGroups = $mkByProdiKurikulum->get($prodi->id, collect());
            @endphp

            <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h6 class="mb-0">Program Studi {{ $prodi->jenjang }} {{ $prodi->nama }}</h6>
                    <span class="badge bg-primary-subtle text-primary">{{ $kurikulumGroups->flatten(1)->count() }} MK Diampu</span>
                </div>

                @forelse ($kurikulumGroups as $kurikulumId => $mkRows)
                    @php
                        $kurikulum = $mkRows->first()?->kurikulum;
                    @endphp
                    <div class="border rounded-3 p-3 mb-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <div class="fw-semibold">{{ $kurikulum?->nama ?? 'Kurikulum' }}</div>
                                <small class="text-muted">Daftar mata kuliah yang Anda ampu</small>
                            </div>
                            <span class="badge bg-info-subtle text-info">{{ $mkRows->count() }} Mata Kuliah</span>
                        </div>

                        <div class="row g-2">
                            @foreach ($mkRows as $joinMkUser)
                                @php $mk = $joinMkUser->mk; @endphp
                                <div class="col-12 col-md-6 col-xl-4">
                                    <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <span class="badge bg-secondary-subtle text-secondary">{{ $mk->kode }}</span>
                                                <div class="d-flex gap-1 flex-wrap justify-content-end">
                                                    @if($joinMkUser->koordinator)
                                                        <span class="badge bg-primary">Koordinator</span>
                                                    @endif
                                                    @if($mk->status instanceof \App\States\Mk\NonAktif)
                                                        <span class="badge rounded-pill bg-danger-subtle text-danger" style="font-size:.7rem;border:1px solid rgba(220,53,69,.2)">
                                                            <i class="bi bi-slash-circle"></i> Non-Aktif
                                                        </span>
                                                    @elseif($mk->status instanceof \App\States\Mk\Aktif)
                                                        <span class="badge rounded-pill bg-success-subtle text-success" style="font-size:.7rem;border:1px solid rgba(25,135,84,.2)">
                                                            <i class="bi bi-check2-circle"></i> Selesai
                                                        </span>
                                                    @elseif($mk->status instanceof \App\States\Mk\BelumNilai)
                                                        <span class="badge rounded-pill bg-info-subtle text-info" style="font-size:.7rem;border:1px solid rgba(13,202,240,.2)">
                                                            <i class="bi bi-clipboard2-data"></i> Menilai
                                                        </span>
                                                    @elseif($mk->status instanceof \App\States\Mk\MappingSubCPMK)
                                                        <span class="badge rounded-pill bg-primary-subtle text-primary" style="font-size:.7rem;border:1px solid rgba(13,110,253,.2)">
                                                            <i class="bi bi-diagram-3"></i> Set SubCPMK ke Penugasan
                                                        </span>
                                                    @else
                                                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis" style="font-size:.7rem;border:1px solid rgba(255,193,7,.2)">
                                                            <i class="bi bi-pencil-square"></i> Draft
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="fw-semibold">{{ $mk->nama }}</div>
                                            <small class="text-muted">{{ $mk->sks }} SKS</small>
                                        </div>
                                        <div class="mt-3">
                                            @if ($mk->cpmks_count > 0)
                                                <a href="{{ route('mks.cpmks.index', [$mk->id]) }}" class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="bi bi-eye"></i> Lihat Detail MK
                                                </a>
                                            @else
                                                <a href="{{ route('settings.import.mk-master', [$mk->id]) }}" class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="bi bi-upload"></i> Lihat Detail MK
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada mata kuliah terdaftar pada program studi ini.</div>
                @endforelse
            </div>
        @empty
            <div class="alert alert-warning mb-0">Anda belum terdaftar pada program studi manapun.</div>
        @endforelse
    </div>
</div>
@endsection
