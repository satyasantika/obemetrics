@push('title')
    Dashboard ADMIN
@endpush
<h3>Selamat datang, {{ auth()->user()->name }}</h3>
<div class="row">
    <div class="col-auto">
        <div class="card">
            <div class="card-header">Manajemen OBEmetrics</div>
            <div class="card-body">
                @include('layouts.alert')
                @can('read users')
                Manajemen data user:<br>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-person"></i> User</a>
                    @can('read roles')
                    <a href="{{ route('roles.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-people"></i> Role</a>
                    @endcan
                    @can('read permissions')
                    <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-shield-lock"></i> Permission</a>
                    @endcan
                <br>
                <hr>
                @endcan

                Manajemen data lainnya:<br>
                @can('read prodis')
                <a href="{{ route('prodis.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-journal-bookmark"></i> Prodi</a>
                @endcan

                @can('read semesters')
                <a href="{{ route('semesters.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-calendar"></i> Semester</a>
                @endcan

                @can('read metodes')
                <a href="{{ route('metodes.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-people"></i> Metode Perkuliahan</a>
                @endcan

                @can('read evaluasis')
                <a href="{{ route('evaluasis.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-clipboard-check"></i> Evaluasi</a>
                @endcan
            </div>
        </div>
    </div>
</div>
