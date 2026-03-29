@extends('layouts.panel')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <x-obe.header
                    title="Mahasiswa Kontrak MK"
                    subtitle="Daftar kontrak mata kuliah yang Anda ampu"
                    icon="bi bi-file-earmark-text" />
                <div class="card-header border-top d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateKontrakDosenMk" title="Tambah Kontrak">
                        <i class="bi bi-plus-circle"></i> Add
                    </button>
                    <a href="{{ route('dosen.kontrakmks.import') }}" class="btn btn-success btn-sm" title="Import Kontrak">
                        <i class="bi bi-upload"></i> Import
                    </a>
                </div>
                <div class="card-body bg-light-subtle">
                    <div class="row g-2 mb-3" id="kontrak-filters">
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1">Program Studi</label>
                            <select id="filter-prodi" class="form-select form-select-sm">
                                <option value="">Semua</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1">Mata Kuliah</label>
                            <select id="filter-mk" class="form-select form-select-sm">
                                <option value="">Semua</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1">Semester</label>
                            <select id="filter-semester" class="form-select form-select-sm">
                                <option value="">Semua</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1">Kelas</label>
                            <select id="filter-kelas" class="form-select form-select-sm">
                                <option value="">Semua</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table-kontrak-per-dosen" class="table table-bordered table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIM</th>
                                    <th>Mahasiswa</th>
                                    <th>Program Studi</th>
                                    <th>Kode MK</th>
                                    <th>Mata Kuliah</th>
                                    <th>Semester</th>
                                    <th>Kelas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

{{-- Modal Tambah Kontrak --}}
<div class="modal fade" id="modalCreateKontrakDosenMk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('dosen.kontrakmks.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kontrak MK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Mahasiswa <span class="text-danger">*</span></label>
                            <select name="mahasiswa_id" class="form-select" required>
                                <option value="">-Pilih Mahasiswa-</option>
                                @foreach (($mahasiswas ?? collect()) as $mahasiswa)
                                    <option value="{{ $mahasiswa->id }}">{{ $mahasiswa->nim }} - {{ $mahasiswa->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                            <select name="mk_id" class="form-select" required>
                                <option value="">-Pilih Mata Kuliah-</option>
                                @foreach (($mks ?? collect()) as $mk)
                                    <option value="{{ $mk->id }}">{{ $mk->kode }} - {{ $mk->nama }}</option>
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
                        <div class="col-md-6">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" class="form-control" placeholder="Contoh: A" maxlength="10">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Kontrak --}}
<div class="modal fade" id="modalEditKontrakDosenMk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditKontrakDosenMk" action="#" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editKontrakDosenMkTitle">Edit Kontrak MK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Mahasiswa <span class="text-danger">*</span></label>
                            <select name="mahasiswa_id" class="form-select" id="editKontrakDosenMkMahasiswa" required>
                                <option value="">-Pilih Mahasiswa-</option>
                                @foreach (($mahasiswas ?? collect()) as $mahasiswa)
                                    <option value="{{ $mahasiswa->id }}">{{ $mahasiswa->nim }} - {{ $mahasiswa->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                            <select name="mk_id" class="form-select" id="editKontrakDosenMkMataKuliah" required>
                                <option value="">-Pilih Mata Kuliah-</option>
                                @foreach (($mks ?? collect()) as $mk)
                                    <option value="{{ $mk->id }}">{{ $mk->kode }} - {{ $mk->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" class="form-select" id="editKontrakDosenMkSemester">
                                <option value="">-Pilih Semester-</option>
                                @foreach (($semesters ?? collect()) as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" id="editKontrakDosenMkKelas" class="form-control" maxlength="10">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="editKontrakDosenMkDeleteBtn" class="btn btn-outline-danger btn-sm me-auto" type="button"><i class="bi bi-trash"></i> Hapus</button>
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-save"></i> Simpan</button>
                </div>
            </form>
            <form id="formDeleteKontrakDosenMk" action="#" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!(window.jQuery && $.fn.DataTable)) {
                return;
            }

            function escapeRegex(value) {
                return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function toProdiLabel(row) {
                if (!row.prodi_nama && !row.prodi_jenjang) {
                    return '-';
                }

                return [row.prodi_jenjang || '', row.prodi_nama || '']
                    .filter(Boolean)
                    .join('-');
            }

            function fillSelect($select, values) {
                const currentValue = $select.val() || '';
                $select.find('option:not(:first)').remove();

                values.forEach(function (value) {
                    $select.append(new Option(value, value));
                });

                if (currentValue && values.includes(currentValue)) {
                    $select.val(currentValue);
                } else {
                    $select.val('');
                }
            }

            $('#table-kontrak-per-dosen').DataTable({
                processing: true,
                ajax: {
                    url: '{{ route('dosen.kontrakmks.data') }}',
                    dataSrc: ''
                },
                order: [[1, 'asc']],
                columns: [
                    {
                        data: null,
                        searchable: false,
                        orderable: false,
                        className: 'text-center',
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: 'mahasiswa_nim' },
                    { data: 'mahasiswa_nama' },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return toProdiLabel(row);
                        }
                    },
                    { data: 'mk_kode' },
                    { data: 'mk_nama' },
                    {
                        data: 'semester_kode',
                        render: function (data, type, row) {
                            return row.semester_nama || data || '-';
                        }
                    },
                    {
                        data: 'kelas',
                        className: 'text-center',
                        render: function (data) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'id',
                        searchable: false,
                        orderable: false,
                        className: 'text-center',
                        render: function (data, type, row) {
                            // Escape data for safe HTML attributes
                            const id = String(data).replace(/"/g, '&quot;');
                            const mahasiswaId = String(row.mahasiswa_id || '').replace(/"/g, '&quot;');
                            const mahasiswaNim = String(row.mahasiswa_nim || '').replace(/"/g, '&quot;');
                            const mahasiswaNama = String(row.mahasiswa_nama || '').replace(/"/g, '&quot;');
                            const mkId = String(row.mk_id || '').replace(/"/g, '&quot;');
                            const mkKode = String(row.mk_kode || '').replace(/"/g, '&quot;');
                            const mkNama = String(row.mk_nama || '').replace(/"/g, '&quot;');
                            const semesterId = String(row.semester_id || '').replace(/"/g, '&quot;');
                            const semesterKode = String(row.semester_kode || '').replace(/"/g, '&quot;');
                            const semesterNama = String(row.semester_nama || '').replace(/"/g, '&quot;');
                            const kelas = String(row.kelas || '').replace(/"/g, '&quot;');
                            const canDelete = row.can_delete ? '1' : '0';
                            const lockReason = String(row.lock_reason || '').replace(/"/g, '&quot;');

                            return `
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditKontrakDosenMk"
                                        data-kontrak-id="${id}"
                                        data-mahasiswa-id="${mahasiswaId}"
                                        data-mahasiswa-nim="${mahasiswaNim}"
                                        data-mahasiswa-nama="${mahasiswaNama}"
                                        data-mk-id="${mkId}"
                                        data-mk-kode="${mkKode}"
                                        data-mk-nama="${mkNama}"
                                        data-semester-id="${semesterId}"
                                        data-semester-kode="${semesterKode}"
                                        data-semester-nama="${semesterNama}"
                                        data-kelas="${kelas}"
                                        data-can-delete="${canDelete}"
                                        data-lock-reason="${lockReason}"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ entri per halaman',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 entri',
                    infoFiltered: '(disaring dari _MAX_ total entri)',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Tidak ada data tersedia',
                    processing: 'Memproses...',
                    loadingRecords: 'Memuat...',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    },
                    aria: {
                        sortAscending: ': aktifkan untuk mengurutkan kolom naik',
                        sortDescending: ': aktifkan untuk mengurutkan kolom turun'
                    }
                },
                initComplete: function () {
                    const api = this.api();
                    const allRows = api.rows().data().toArray();

                    // Filter state tracking
                    const filterState = { prodi: '', mk: '', semester: '', kelas: '' };

                    // Function to rebuild dropdown options based on current filters
                    function rebuildDropdowns() {
                        // Filter rows based on current filter state
                        const filteredRows = allRows.filter(function (row) {
                            const prodiMatch = !filterState.prodi || toProdiLabel(row) === filterState.prodi;
                            const mkMatch = !filterState.mk || row.mk_nama === filterState.mk;
                            const semesterMatch = !filterState.semester || (row.semester_nama || row.semester_kode) === filterState.semester;
                            const kelasMatch = !filterState.kelas || row.kelas === filterState.kelas;
                            return prodiMatch && mkMatch && semesterMatch && kelasMatch;
                        });

                        // Extract unique values from filtered rows
                        const prodiOptions = [...new Set(filteredRows.map(function (row) { return toProdiLabel(row); }).filter(Boolean))].sort();
                        const mkOptions = [...new Set(filteredRows.map(function (row) { return row.mk_nama || ''; }).filter(Boolean))].sort();
                        const semesterOptions = [...new Set(filteredRows.map(function (row) { return row.semester_nama || row.semester_kode || ''; }).filter(Boolean))].sort();
                        const kelasOptions = [...new Set(filteredRows.map(function (row) { return row.kelas || ''; }).filter(Boolean))].sort();

                        // Update dropdowns
                        fillSelect($filterProdi, prodiOptions);
                        fillSelect($filterMk, mkOptions);
                        fillSelect($filterSemester, semesterOptions);
                        fillSelect($filterKelas, kelasOptions);

                        // Restore filter state in dropdowns
                        if (filterState.prodi) $filterProdi.val(filterState.prodi);
                        if (filterState.mk) $filterMk.val(filterState.mk);
                        if (filterState.semester) $filterSemester.val(filterState.semester);
                        if (filterState.kelas) $filterKelas.val(filterState.kelas);
                    }

                    const $filterProdi = $('#filter-prodi');
                    const $filterMk = $('#filter-mk');
                    const $filterSemester = $('#filter-semester');
                    const $filterKelas = $('#filter-kelas');

                    // Initial dropdown population
                    rebuildDropdowns();

                    $filterProdi.on('change', function () {
                        filterState.prodi = $(this).val();
                        rebuildDropdowns();
                        applyFilters();
                    });

                    $filterMk.on('change', function () {
                        filterState.mk = $(this).val();
                        rebuildDropdowns();
                        applyFilters();
                    });

                    $filterSemester.on('change', function () {
                        filterState.semester = $(this).val();
                        rebuildDropdowns();
                        applyFilters();
                    });

                    $filterKelas.on('change', function () {
                        filterState.kelas = $(this).val();
                        applyFilters();
                    });

                    function applyFilters() {
                        api.column(3).search(filterState.prodi ? '^' + escapeRegex(filterState.prodi) + '$' : '', true, false);
                        api.column(5).search(filterState.mk ? '^' + escapeRegex(filterState.mk) + '$' : '', true, false);
                        api.column(6).search(filterState.semester ? '^' + escapeRegex(filterState.semester) + '$' : '', true, false);
                        api.column(7).search(filterState.kelas ? '^' + escapeRegex(filterState.kelas) + '$' : '', true, false);
                        api.draw();
                    }
                }
            });

            // Handle modal edit trigger
            const editKontrakModal = document.getElementById('modalEditKontrakDosenMk');
            if (editKontrakModal) {
                editKontrakModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }
                    const id = trigger.getAttribute('data-kontrak-id') || '';
                    const mahasiswaId = trigger.getAttribute('data-mahasiswa-id') || '';
                    const mkId = trigger.getAttribute('data-mk-id') || '';
                    const semesterId = trigger.getAttribute('data-semester-id') || '';
                    const kelas = trigger.getAttribute('data-kelas') || '';
                    const canDelete = (trigger.getAttribute('data-can-delete') || '1') === '1';
                    const lockReason = trigger.getAttribute('data-lock-reason') || 'Kontrak tidak dapat dihapus karena sudah digunakan pada data penilaian.';

                    document.getElementById('editKontrakDosenMkTitle').textContent = `Edit Kontrak MK - #${id}`;
                    document.getElementById('editKontrakDosenMkMahasiswa').value = mahasiswaId;
                    document.getElementById('editKontrakDosenMkMataKuliah').value = mkId;
                    document.getElementById('editKontrakDosenMkSemester').value = semesterId;
                    document.getElementById('editKontrakDosenMkKelas').value = kelas;

                    const editForm = document.getElementById('formEditKontrakDosenMk');
                    const deleteForm = document.getElementById('formDeleteKontrakDosenMk');
                    const deleteBtn = document.getElementById('editKontrakDosenMkDeleteBtn');

                    editForm.action = `/dosen/kontrakmks/${id}`;
                    deleteForm.action = `/dosen/kontrakmks/${id}`;

                    if (!canDelete) {
                        deleteBtn.classList.add('disabled');
                        deleteBtn.setAttribute('aria-disabled', 'true');
                        deleteBtn.setAttribute('title', lockReason);
                        deleteBtn.onclick = function () {
                            alert(lockReason);
                        };
                    } else {
                        deleteBtn.classList.remove('disabled');
                        deleteBtn.removeAttribute('aria-disabled');
                        deleteBtn.setAttribute('title', 'Hapus');
                        deleteBtn.onclick = function () {
                            if (confirm('Apakah Anda yakin ingin menghapus kontrak ini?')) {
                                deleteForm.submit();
                            }
                        };
                    }
                });
            }
        });
    </script>
@endpush
