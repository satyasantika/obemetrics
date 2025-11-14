@push('title')
    Dashboard ADMIN
@endpush
<h3>Selamat datang, {{ auth()->user()->name }}</h3>
<div class="row">
    <div class="col-auto">
        <div class="card">
            <div class="card-header">Manajemen OBEmetrics</div>
            <div class="card-body">
                menu user:<br>
                {{-- <a href="{{ route('guideexaminers.index') }}" class="btn btn-sm btn-primary">Penjadwalan</a> --}}
                <br>
                <hr>
                menu prodi:<br>
                {{-- <a href="{{ route('examregistrations.index') }}" class="btn btn-sm btn-primary">Jadwal Ujian</a> --}}
                <br>
                <hr>
                menu cpl:<br>
                {{-- <a href="{{ route('get.examinerscoringyet') }}" class="btn btn-sm btn-primary">belum menilai</a> --}}
                <br>
                <hr>
                menu cpmk:<br>
                {{-- <a href="{{ route('get.setscoringtoexamineryet') }}" class="btn btn-sm btn-primary">set jadwal ke penguji</a> --}}
                <br>
                <hr>
                menu sub-cpmk:<br>
                {{-- <a href="{{ route('get.setscoringtoexamineryet') }}" class="btn btn-sm btn-primary">set jadwal ke penguji</a> --}}
                <br>
                <hr>
                menu tagihan:<br>
                {{-- <a href="{{ route('get.setscoringtoexamineryet') }}" class="btn btn-sm btn-primary">set jadwal ke penguji</a> --}}
                <br>
                <hr>
                menu kontrak KRS:<br>
                {{-- <a href="{{ route('get.setscoringtoexamineryet') }}" class="btn btn-sm btn-primary">set jadwal ke penguji</a> --}}
                <br>
            </div>
        </div>
    </div>
</div>
