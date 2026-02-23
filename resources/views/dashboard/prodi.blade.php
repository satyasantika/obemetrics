@push('title')
    Dashboard Program Studi
@endpush
<div class="row">
    <div class="col">
        <div class="card bg-primary bg-opacity-10 mt-3">
            <div class="card-header"><span class="h4">Manajemen Kurikulum OBE oleh UPPS</span></div>
            <div class="card-body">
                @include('layouts.alert')
                {{-- Program Studi --}}
                @forelse (auth()->user()->joinProdiUsers->pluck('prodi') as $prodi)
                    <span class="h5">Program Studi {{ $prodi->jenjang }} {{ $prodi->nama }}</span>
                    <hr>

                    {{-- Kurikulum --}}
                    Kurikulum pada program studi ini:
                    <ol>
                        @forelse (auth()->user()->joinProdiUsers->pluck('prodi.kurikulums')->flatten() as $kurikulum)
                        <hr>
                        <div class="row mb-1">
                            <div class="col">
                                <li>
                                    {{ $kurikulum->nama }}
                                    <br>
                                    <a href="{{ route('prodis.kurikulums.edit',[$prodi->id,$kurikulum->id]) }}" class="text-primary" style="text-decoration: none;">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    |
                                    <a href="{{ route('kurikulums.profils.index',[$kurikulum->id]) }}" class="text-primary" style="text-decoration: none;">
                                        <i class="bi bi-eye"></i> Selengkapnya ...
                                    </a>
                                </li>
                            </div>
                        </div>
                        @empty
                            Tidak ada kurikulum pada program studi ini.
                        @endforelse
                    </ol>
                    <hr>
                    <a href="{{ route('prodis.kurikulums.create',$prodi->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Tambah Kurikulum
                    </a>

                @empty
                    Anda belum terdaftar pada program studi manapun.
                @endforelse
            </div>
        </div>
    </div>
</div>
