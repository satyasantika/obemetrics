@extends('layouts.setting-form')

@push('header')
    {{ $kontrakmk->id ? 'Edit' : 'Tambah' }} {{ $header }}
@endpush

@push('body')

<form id="formAction" action="{{ $kontrakmk->id ? route('kontrakmks.update',$kontrakmk->id) : route('kontrakmks.store') }}" method="post">
    @csrf
    @if ($kontrakmk->id)
        @method('PUT')
    @endif
    <div class="card-body">
        {{-- Program Studi --}}
        <div class="row mb-3">
            <label for="prodi_id" class="col-md-4 col-form-label text-md-end">Program Studi <span class="text-danger">(*)</span></label>
            <div class="col-md-8">
                <select name="prodi_id" id="prodi_id" class="form-control select2" required>
                    <option value="">-Pilih Program Studi-</option>
                    @foreach ($prodis as $prodi)
                        <option value="{{ $prodi->id }}"
                            @selected(old('prodi_id', $kontrakmk->mahasiswa->prodi_id ?? '') == $prodi->id)>
                            {{ $prodi->jenjang }} - {{ $prodi->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Mahasiswa --}}
        <div class="row mb-3">
            <label for="mahasiswa_id" class="col-md-4 col-form-label text-md-end">Mahasiswa <span class="text-danger">(*)</span></label>
            <div class="col-md-8">
                <select name="mahasiswa_id" id="mahasiswa_id" class="form-control select2" required>
                    <option value="">-Pilih Mahasiswa-</option>
                    @foreach ($mahasiswas as $mahasiswa)
                        <option value="{{ $mahasiswa->id }}"
                            data-prodi="{{ $mahasiswa->prodi_id }}"
                            @selected(old('mahasiswa_id', $kontrakmk->mahasiswa_id) == $mahasiswa->id)>
                            {{ $mahasiswa->nim }} - {{ $mahasiswa->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Mata Kuliah --}}
        <div class="row mb-3">
            <label for="mk_id" class="col-md-4 col-form-label text-md-end">Mata Kuliah <span class="text-danger">(*)</span></label>
            <div class="col-md-8">
                <select name="mk_id" id="mk_id" class="form-control select2" required>
                    <option value="">-Pilih Mata Kuliah-</option>
                    @foreach ($mks as $mk)
                        <option value="{{ $mk->id }}"
                            data-prodi="{{ $mk->kurikulum->prodi_id ?? '' }}"
                            @selected(old('mk_id', $kontrakmk->mk_id) == $mk->id)>
                            {{ $mk->kode }} - {{ $mk->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Dosen Pengampu --}}
        <div class="row mb-3">
            <label for="user_id" class="col-md-4 col-form-label text-md-end">Dosen Pengampu <span class="text-danger">(*)</span></label>
            <div class="col-md-8">
                <select name="user_id" id="user_id" class="form-control select2" required>
                    <option value="">-Pilih Dosen-</option>
                    @foreach ($dosens as $dosen)
                        <option value="{{ $dosen->id }}"
                            @selected(old('user_id', $kontrakmk->user_id) == $dosen->id)>
                            {{ $dosen->nidn }} - {{ $dosen->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Semester --}}
        <div class="row mb-3">
            <label for="semester_id" class="col-md-4 col-form-label text-md-end">Semester</label>
            <div class="col-md-8">
                <select name="semester_id" id="semester_id" class="form-control select2">
                    <option value="">-Pilih Semester-</option>
                    @foreach ($semesters as $semester)
                        <option value="{{ $semester->id }}"
                            @selected(old('semester_id', $kontrakmk->semester_id) == $semester->id)>
                            {{ $semester->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Kelas --}}
        <div class="row mb-3">
            <label for="kelas" class="col-md-4 col-form-label text-md-end">Kelas</label>
            <div class="col-md-8">
                <input type="text" placeholder="Contoh: A, B, C" value="{{ old('kelas', $kontrakmk->kelas) }}" name="kelas" class="form-control" id="kelas">
            </div>
        </div>

        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('kontrakmks.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
        <span class="text-danger">(*) Wajib diisi.</span>
    </div>
</form>

@if ($kontrakmk->id)
    <form id="delete-form" action="{{ route('kontrakmks.destroy',$kontrakmk->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus kontrak mata kuliah ini?');">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@endif

@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for all select elements with class select2
    $('.select2').select2({
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Pilih...';
        },
        allowClear: true
    });

    const prodiSelect = $('#prodi_id');
    const mahasiswaSelect = $('#mahasiswa_id');
    const mkSelect = $('#mk_id');

    // Store original options
    const mahasiswaOptions = Array.from(document.getElementById('mahasiswa_id').options);
    const mkOptions = Array.from(document.getElementById('mk_id').options);

    function filterOptions() {
        const selectedProdi = prodiSelect.val();

        // Filter mahasiswa
        mahasiswaSelect.empty();
        mahasiswaSelect.append(new Option('-Pilih Mahasiswa-', '', false, false));

        mahasiswaOptions.slice(1).forEach(option => {
            if (!selectedProdi || option.dataset.prodi === selectedProdi) {
                mahasiswaSelect.append(new Option(option.text, option.value, false, false));
            }
        });

        // Filter mata kuliah
        mkSelect.empty();
        mkSelect.append(new Option('-Pilih Mata Kuliah-', '', false, false));

        mkOptions.slice(1).forEach(option => {
            if (!selectedProdi || option.dataset.prodi === selectedProdi) {
                mkSelect.append(new Option(option.text, option.value, false, false));
            }
        });

        // Restore selected values if they exist and match the filter
        const oldMahasiswaId = '{{ old("mahasiswa_id", $kontrakmk->mahasiswa_id) }}';
        const oldMkId = '{{ old("mk_id", $kontrakmk->mk_id) }}';

        if (oldMahasiswaId) {
            mahasiswaSelect.val(oldMahasiswaId);
        }

        if (oldMkId) {
            mkSelect.val(oldMkId);
        }

        // Trigger Select2 update
        mahasiswaSelect.trigger('change.select2');
        mkSelect.trigger('change.select2');
    }

    // Auto-convert nilai angka to nilai huruf
    const nilaiAngkaInput = $('#nilai_angka');
    const nilaiHurufSelect = $('#nilai_huruf');

    nilaiAngkaInput.on('change', function() {
        const nilai = parseFloat(this.value);
        let huruf = '';

        if (nilai >= 85) huruf = 'A';
        else if (nilai >= 80) huruf = 'A-';
        else if (nilai >= 75) huruf = 'B+';
        else if (nilai >= 70) huruf = 'B';
        else if (nilai >= 65) huruf = 'B-';
        else if (nilai >= 60) huruf = 'C+';
        else if (nilai >= 55) huruf = 'C';
        else if (nilai >= 40) huruf = 'D';
        else if (nilai > 0) huruf = 'E';

        nilaiHurufSelect.val(huruf);
    });

    prodiSelect.on('change', filterOptions);

    // Initial filter on page load
    filterOptions();
});
</script>
@endpush
