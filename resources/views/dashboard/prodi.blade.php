@push('title')
    Dashboard Program Studi
@endpush
<h3>Selamat datang, {{ auth()->user()->name }}</h3>
<div class="row">
    <div class="col-auto">
        <div class="card">
            <div class="card-header">Manajemen OBE pada Prodi</div>
            <div class="card-body">

                @can('read kurikulums')
                manajemen data kurikulum:<br>
                <a href="{{ route('kurikulums.index',) }}" class="btn btn-sm btn-primary"><i class="bi bi-diagram-3"></i> Kurikulum</a>
                <br>
                <hr>
                @endcan

                @can('read profils')
                manajemen data profil lulusan:<br>
                <a href="{{ route('kurikulums.index',) }}" class="btn btn-sm btn-primary"><i class="bi bi-person-badge"></i> Kurikulum</a>
                <br>
                <hr>
                @endcan
                
            </div>
        </div>
    </div>
</div>
