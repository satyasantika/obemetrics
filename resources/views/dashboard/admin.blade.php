@push('title')
    Dashboard ADMIN
@endpush
<h3>Selamat datang, {{ auth()->user()->name }}</h3>
<div class="row">
    <div class="col-auto">
        <div class="card">
            <div class="card-header">Manajemen OBEmetrics</div>
            <div class="card-body">
                @can('read users')
                Manajemen Akun:<br>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary">User</a>
                    @can('read roles')
                    <a href="{{ route('roles.index') }}" class="btn btn-sm btn-primary">Role</a>
                    @endcan
                    @can('read permissions')
                    <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-primary">Permission</a>
                    @endcan
                <br>
                <hr>
                @endcan

                @can('read prodis')
                menu prodi:<br>
                <a href="{{ route('prodis.index') }}" class="btn btn-sm btn-primary">Prodi</a>
                <br>
                <hr>
                @endcan

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
