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
                        @forelse (auth()->user()->joinMkUsers->pluck('kurikulum')->where('prodi_id',$prodi->id) as $kurikulum)
                        <hr>
                        <div class="row mb-1">
                            <div class="col">
                                <li>
                                    {{ $kurikulum->nama }}
                                    <a href="{{ route('prodis.kurikulums.edit',[$prodi->id,$kurikulum->id]) }}" class="text-primary">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <br>
                                    @include('layouts.menu-kurikulum',$kurikulum)
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
