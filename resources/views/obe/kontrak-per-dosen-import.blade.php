@extends('layouts.panel')

@section('content')
<div class="container-fluid">
    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm import-hero-card">
                <x-obe.header
                    title="Import Kontrak MK"
                    subtitle="Import data kontrak mahasiswa dari file Excel"
                    icon="bi bi-upload" />

                <div class="card-body">
                    <div class="import-hero-copy mb-4">
                        <span class="import-kicker">Bulk Upload</span>
                        <h5 class="mb-2">Upload sekali, cek validasi sebelum simpan.</h5>
                        <p class="text-muted mb-0">Semester dipilih di form, lalu setiap baris preview akan diperiksa untuk duplikasi kombinasi NIM, mata kuliah, dan semester.</p>
                    </div>

                    <form action="{{ route('dosen.kontrakmks.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <select id="semesterIdForTemplate" name="semester_id" class="form-select @error('semester_id') is-invalid @enderror" required>
                                <option value="">-Pilih Semester-</option>
                                @foreach (($semesters ?? collect()) as $semester)
                                    <option value="{{ $semester->id }}" @selected(old('semester_id', $preview['semester_id'] ?? '') == $semester->id)>
                                        {{ $semester->kode }} - {{ $semester->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('semester_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">File Upload (Excel/CSV) <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror import-file-input"
                                   accept=".csv,.xlsx,.ods" required>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="import-note mt-3">
                                <div class="small text-muted">Format template: NIM, Nama Mahasiswa, Kode MK, Nama MK, Kelas.</div>
                                <div class="small text-muted">Semester ditentukan dari dropdown di atas dan dipakai untuk seluruh baris preview.</div>
                                @php
                                    $initialSemesterId = old('semester_id', $preview['semester_id'] ?? '');
                                    $templateBaseUrl = route('dosen.kontrakmks.import.template');
                                    $templateUrl = $initialSemesterId
                                        ? $templateBaseUrl . '?semester_id=' . urlencode((string) $initialSemesterId)
                                        : $templateBaseUrl;
                                @endphp
                                <a id="downloadTemplateKontrakMk"
                                   data-base-url="{{ $templateBaseUrl }}"
                                   href="{{ $templateUrl }}"
                                   class="btn btn-link p-0 mt-2 text-decoration-none">
                                    <i class="bi bi-download me-1"></i>Download Template
                                </a>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button id="submitImportKontrakMk"
                                    type="submit"
                                    class="btn btn-primary"
                                    @disabled(empty(old('semester_id', $preview['semester_id'] ?? '')))>
                                <i class="bi bi-upload"></i> Upload & Preview
                            </button>
                            <a href="{{ route('dosen.kontrakmks.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(!empty($preview) && !empty($preview['rows']))
        <div class="col-12">
            <div class="card border-0 shadow-sm preview-shell-card">
                <div class="card-header border-0 bg-transparent px-4 pt-4 pb-0">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                        <div>
                            <div class="preview-eyebrow">Preview Import</div>
                            <h4 class="mb-1">{{ count($preview['rows']) }} baris siap ditinjau</h4>
                            <small class="text-muted d-block">Semester: {{ $preview['semester_label'] ?? '-' }}</small>
                        </div>
                        <div class="preview-stats">
                            <div class="preview-stat-card success">
                                <span class="preview-stat-value">{{ collect($preview['rows'])->where('status', 'success')->count() }}</span>
                                <span class="preview-stat-label">Valid</span>
                            </div>
                            <div class="preview-stat-card danger">
                                <span class="preview-stat-value">{{ collect($preview['rows'])->where('status', '!=', 'success')->count() }}</span>
                                <span class="preview-stat-label">Invalid</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="alert import-alert mb-4">
                        <strong>Catatan:</strong> Baris dengan status invalid tidak akan diimport. Preview juga menolak kombinasi duplikat NIM, kode MK, dan semester, baik yang sudah ada di database maupun yang berulang di file upload.
                    </div>

                    <div class="preview-table-wrap">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle preview-table mb-0">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIM</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>Mata Kuliah</th>
                                    <th>Nama Dosen</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview['rows'] as $index => $row)
                                    <tr class="{{ $row['status'] === 'success' ? 'is-valid' : 'is-invalid' }}">
                                        <td class="text-muted fw-semibold">{{ $index + 1 }}</td>
                                        <td class="fw-semibold">{{ $row['nim'] ?? '-' }}</td>
                                        <td>{{ $row['mahasiswa_nama'] ?? '-' }}</td>
                                        <td>
                                            <span class="preview-code">{{ $row['mk_kode'] ?? '-' }}</span>
                                            <span class="text-muted">-</span>
                                            <span>{{ $row['mk_nama'] ?? '-' }}</span>
                                        </td>
                                        <td>{{ $row['nama_dosen'] ?? (auth()->user()->name ?? '-') }}</td>
                                        <td>{{ $row['kelas'] ?? '-' }}</td>
                                        <td>
                                            @if($row['status'] === 'success')
                                                <span class="badge rounded-pill text-bg-success">Valid</span>
                                            @else
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge rounded-pill text-bg-danger align-self-start">Invalid</span>
                                                    <small class="text-danger">{{ $row['error'] ?? 'Unknown error' }}</small>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    </div>

                    <form action="{{ route('dosen.kontrakmks.import.commit') }}" method="POST">
                        @csrf
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                            <div class="text-muted small">
                                File: <span class="fw-semibold">{{ $preview['filename'] ?? '-' }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Simpan & Import
                                </button>
                                <button type="submit" class="btn btn-outline-danger" form="formClearImportKontrakDosenMk">
                                    <i class="bi bi-x-circle"></i> Batalkan
                                </button>
                            </div>
                        </div>
                    </form>
                    <form id="formClearImportKontrakDosenMk" action="{{ route('dosen.kontrakmks.import.clear') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .import-hero-card,
    .preview-shell-card {
        border-radius: 20px;
        overflow: hidden;
    }

    .import-hero-card {
        background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
    }

    .import-hero-copy {
        padding: 1rem 1.1rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
        border: 1px solid #dbeafe;
    }

    .import-kicker,
    .preview-eyebrow {
        display: inline-block;
        margin-bottom: .5rem;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #2563eb;
    }

    .import-file-input {
        padding-top: .8rem;
        padding-bottom: .8rem;
    }

    .import-note {
        padding: .9rem 1rem;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
    }

    .preview-stats {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .preview-stat-card {
        min-width: 112px;
        padding: .9rem 1rem;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .15rem;
    }

    .preview-stat-card.success {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
    }

    .preview-stat-card.danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .preview-stat-value {
        font-size: 1.4rem;
        font-weight: 700;
        line-height: 1;
        color: #0f172a;
    }

    .preview-stat-label {
        font-size: .8rem;
        color: #475569;
    }

    .import-alert {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1e3a8a;
        border-radius: 14px;
    }

    .preview-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }

    .preview-table thead th {
        background: #0f172a;
        color: #f8fafc;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        padding: 1rem;
    }

    .preview-table tbody td {
        padding: .95rem 1rem;
        border-top: 1px solid #e2e8f0;
        vertical-align: top;
    }

    .preview-table tbody tr.is-valid {
        background: #fcfffd;
    }

    .preview-table tbody tr.is-invalid {
        background: #fff7f7;
    }

    .preview-code {
        display: inline-block;
        padding: .2rem .55rem;
        border-radius: 999px;
        background: #e2e8f0;
        color: #0f172a;
        font-weight: 600;
        font-size: .85rem;
    }

    @media (max-width: 991.98px) {
        .preview-shell-card .card-body,
        .preview-shell-card .card-header {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .preview-table thead th,
        .preview-table tbody td {
            padding: .8rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const semesterSelect = document.getElementById('semesterIdForTemplate');
        const downloadLink = document.getElementById('downloadTemplateKontrakMk');
        const uploadButton = document.getElementById('submitImportKontrakMk');

        if (!semesterSelect || !downloadLink) {
            return;
        }

        const baseUrl = downloadLink.dataset.baseUrl || downloadLink.getAttribute('href') || '';

        const updateTemplateHref = () => {
            const semesterId = (semesterSelect.value || '').trim();
            downloadLink.setAttribute('href', semesterId ? `${baseUrl}?semester_id=${encodeURIComponent(semesterId)}` : baseUrl);

            if (uploadButton) {
                uploadButton.disabled = semesterId === '';
            }
        };

        updateTemplateHref();
        semesterSelect.addEventListener('change', updateTemplateHref);
    });
</script>
@endpush
