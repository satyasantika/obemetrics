@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Bulk Import Data {{ $targets[$target]['label'] ?? 'N/A' }}
                    <a href="{{ $returnUrl }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>

                <div class="card-body">
                    @include('layouts.alert')

                    <div class="row mb-2">
                        <div class="col-md-3 text-end">Kurikulum</div>
                        <div class="col"><strong>{{ $kurikulum->nama }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 text-end">Program Studi</div>
                        <div class="col"><strong>{{ $kurikulum->prodi->jenjang }} {{ $kurikulum->prodi->nama }}</strong></div>
                    </div>

                    <form action="{{ route('setting.import.kurikulum-master.upload', $kurikulum->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="target" id="target" value="{{ $target }}">
                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                        <div class="row mt-2">
                            <div class="col-md-3 text-end">Import Data <span class="text-danger">*</span></div>
                            <div class="col">
                                <input type="text" class="form-control" id="target-label" value="{{ $targets[$target]['label'] ?? 'N/A' }}" disabled>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-3 text-end">File Upload <span class="text-danger">*</span></div>
                            <div class="col">
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.ods" required>
                                <small class="text-muted d-block mt-1">
                                    Unduh template: <a href="#" id="download-template">template-import.xlsx</a>
                                </small>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-3"></div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-upload"></i> Upload &amp; Preview</button>
                            </div>
                        </div>
                        <span class="text-danger">(*) Wajib diisi.</span>
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
                        <span class="h5">Preview @if(!empty($preview['filename']))({{ $preview['filename'] }})@endif</span>
                        <form action="{{ route('setting.import.kurikulum-master.clear', $kurikulum->id) }}" method="POST" class="float-end" style="display:inline;">
                            @csrf
                            <input type="hidden" name="target" value="{{ $target }}">
                            <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus preview data?');">
                                <i class="bi bi-x-circle"></i> Kosongkan Preview
                            </button>
                        </form>
                    </div>
                    <div class="card-body border-top">
                        <div class="mb-2">
                            <input type="checkbox" id="select-all" class="form-check-input">
                            <label for="select-all" class="form-check-label">Pilih semua</label>
                        </div>

                        @php
                            $columns = $targets[$target]['columns'] ?? [];
                        @endphp

                        <form action="{{ route('setting.import.kurikulum-master.commit', $kurikulum->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="target" value="{{ $target }}">
                            <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                            <div class="table-responsive">
                                <table class="table table-bordered" id="preview-table">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;">Pilih</th>
                                            @foreach ($columns as $column)
                                                <th>{{ $column }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($preview['rows'] as $index => $row)
                                            <tr>
                                                <td><input type="checkbox" class="form-check-input row-check" name="selected[]" value="{{ $index }}" checked></td>
                                                @foreach ($columns as $column)
                                                    <td>{{ $row[$column] ?? '' }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Simpan Data Terpilih</button>
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
    const targetSelect = document.getElementById('target');
    const downloadTemplate = document.getElementById('download-template');
    const selectAll = document.getElementById('select-all');
    const rowChecks = document.querySelectorAll('.row-check');

    function updateTemplateLink() {
        if (!targetSelect || !downloadTemplate) {
            return;
        }

        const target = targetSelect.value || '';
        const base = '{{ route("setting.import.kurikulum-master.template", $kurikulum->id) }}';
        const url = base + '?target=' + encodeURIComponent(target);
        const label = '{{ $targets[$target]["label"] }}' || 'template';
        const prodiSegment = '{{ Str::slug((string) ($kurikulum->prodi->jenjang . '-' . $kurikulum->prodi->nama ?? 'kurikulum'), '-') }}';
        const fileName = 'import-' + label.toLowerCase().replace(/\s+/g, '-') + '-' + prodiSegment  + '.xlsx';
        downloadTemplate.href = url;
        downloadTemplate.textContent = fileName;
    }

    if (targetSelect) {
        targetSelect.addEventListener('change', updateTemplateLink);
        updateTemplateLink();
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
