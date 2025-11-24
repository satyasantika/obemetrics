@push('title')
    Dashboard Program Studi
@endpush
<h3>Selamat datang, {{ auth()->user()->name }}</h3>
<div class="row">
    <div class="col-auto">
        <div class="card">
            <div class="card-header">Manajemen OBE pada Prodi</div>
            <div class="card-body">

                @can('read prodis')
                manajemen data kurikulum:<br>
                <a href="{{ route('kurikulums.index',) }}" class="btn btn-sm btn-primary">Kurikulum</a>
                <br>
                <hr>
                @endcan

                menu cpl:<br>
                {{-- <a href="{{ route('get.examinerscoringyet') }}" class="btn btn-sm btn-primary">belum menilai</a> --}}
                <br>
                <hr>
                menu cpmk:<br>
                {{-- <a href="{{ route('get.setscoringtoexamineryet') }}" class="btn btn-sm btn-primary">set jadwal ke penguji</a> --}}
            </div>
        </div>
    </div>
</div>
