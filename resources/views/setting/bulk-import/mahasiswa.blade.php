@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Import Data Mahasiswa
                    <a href="{{ $returnUrl ?? route('mahasiswas.index') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                    @stack('header')
                </div>

                <div class="card-body">
                    @include('layouts.alert')
                    @php
                        $selectedProdi = old('prodi_id') ?? ($preview['prodi_id'] ?? request('prodi_id'));
                    @endphp
                    <form action="{{ route('setting.import.mahasiswas') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('mahasiswas.index') }}">
                        {{-- identitas kurikulum --}}
                        <div class="row">
                            <div class="col-md-3 text-end">Program Studi <span class="text-danger">*</span></div>
                            <div class="col">
                                <select name="prodi_id" id="prodi_id" class="form-control">
                                    <option value="">-Pilih Program Studi-</option>
                                    @foreach ($prodis as $prodi)
                                        <option value="{{ $prodi->id }}"
                                            data-kode="{{ $prodi->kode ?? '' }}"
                                            @selected($selectedProdi == $prodi->id)>
                                            {{ $prodi->jenjang }} - {{ $prodi->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3 text-end">
                                File Upload <span class="text-danger">*</span>
                            </div>
                            <div class="col">
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.ods" required>
                                <small class="text-muted d-block mt-1">
                                    Unduh template: <a href="#" id="download-template">template-import-mahasiswa.xlsx</a>
                                </small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3"></div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload"></i> Upload &amp; Preview
                                </button>
                            </div>
                        </div>
                        <span class="text-danger">(*) Wajib diisi.</span></label>
                    </form>
                </div>


            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col">

            @if (!empty($preview['rows']))
            <div class="card mt-3">
                <div class="card-header">
                    <span class="h5">Preview Data Mahasiswa @if(!empty($preview['filename']))({{ $preview['filename'] }})@endif</span>
                    <form action="{{ route('setting.import.mahasiswas.clear') }}" method="POST" class="float-end" style="display: inline;">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('mahasiswas.index') }}">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus preview data?');">
                            <i class="bi bi-x-circle"></i> Kosongkan Preview
                        </button>
                    </form>
                </div>
                <div class="card-body border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <input type="checkbox" id="select-all" class="form-check-input">
                            <label for="select-all" class="form-check-label">Pilih semua</label>
                        </div>
                    </div>
                    <form action="{{ route('setting.import.mahasiswas.commit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="prodi_id" value="{{ $preview['prodi_id'] }}">
                        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('mahasiswas.index') }}">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="preview-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">Pilih</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Angkatan</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview['rows'] as $index => $row)
                                        <tr class="{{ $row['exists'] ? 'table-warning' : '' }}">
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    name="selected[]"
                                                    value="{{ $index }}"
                                                    class="form-check-input row-check"
                                                    @checked(!$row['exists'])
                                                >
                                            </td>
                                            <td>{{ $row['nim'] }}</td>
                                            <td>{{ $row['nama'] }}</td>
                                            <td>{{ $row['angkatan'] }}</td>
                                            <td>{{ $row['email'] }}</td>
                                            <td>{{ $row['phone'] }}</td>
                                            <td>
                                                @if ($row['exists'])
                                                    <span class="badge bg-warning text-dark">Sudah ada</span>
                                                @else
                                                    <span class="badge bg-success">Baru</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-save"></i> Simpan Data Terpilih
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const rowChecks = document.querySelectorAll('.row-check');
    const prodiSelect = document.getElementById('prodi_id');
    const downloadTemplate = document.getElementById('download-template');

    // Update template download link when prodi is selected
    if (prodiSelect && downloadTemplate) {
        function updateTemplateLink() {
            const prodiId = prodiSelect.value;
            const selectedOption = prodiSelect.options[prodiSelect.selectedIndex];
            const kodeProdi = selectedOption.getAttribute('data-kode');

            let url = '{{ route("setting.import.mahasiswas.template") }}';
            let fileName = 'template-import-mahasiswa';

            if (prodiId) {
                url += '?prodi_id=' + prodiId;
                if (kodeProdi) {
                    fileName += '-' + kodeProdi;
                }
            }

            fileName += '.xlsx';
            downloadTemplate.href = url;
            downloadTemplate.textContent = fileName;
        }

        prodiSelect.addEventListener('change', updateTemplateLink);
        updateTemplateLink(); // Initial update
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowChecks.forEach(function (chk) {
                chk.checked = selectAll.checked;
            });
        });
    }

    if (window.jQuery && $.fn.DataTable && document.getElementById('preview-table')) {
        $('#preview-table').DataTable({
            pageLength: 10,
            order: [],
        });
    }
});
</script>
@endpush
