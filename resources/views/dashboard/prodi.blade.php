@push('title')
    Dashboard Program Studi
@endpush
<h3>Selamat datang, {{ auth()->user()->name }}</h3>
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header">Manajemen OBE pada oleh UPPS</div>
            <div class="card-body">
                @include('layouts.alert')
                {{-- Program Studi --}}
                @php
                    $user_id = auth()->id();
                    $prodi_ids = \App\Models\JoinProdiUser::where('user_id',$user_id)->pluck('prodi_id');
                    $prodis = \App\Models\Prodi::whereIn('id',$prodi_ids)->get();
                    $kurikulums = \App\Models\Kurikulum::whereIn('prodi_id',$prodi_ids)->get();
                @endphp

                @forelse ($prodis as $prodi)
                    <h4>Program Studi {{ $prodi->jenjang }} {{ $prodi->nama }}</h4>
                    <hr>

                    {{-- Kurikulum --}}
                    Kurikulum pada program studi ini:
                    <ol>
                        @forelse ($kurikulums as $kurikulum)
                            @if ($kurikulum->prodi_id == $prodi->id)
                            <div class="row mb-1">
                                <div class="col">
                                    <li>
                                        {{ $kurikulum->nama }}
                                        <a href="{{ route('prodis.kurikulums.edit',[$prodi->id,$kurikulum->id]) }}" class="text-primary">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <br>
                                        <a href="{{ route('kurikulums.profils.index',[$kurikulum->id]) }}" class="btn btn-sm btn-primary mt-1">
                                            <i class="bi bi-mortarboard"></i> Profil Lulusan
                                        </a>
                                        <a href="{{ route('kurikulums.cpls.index',[$kurikulum->id]) }}" class="btn btn-sm btn-primary mt-1">
                                            <i class="bi bi-bullseye"></i> CPL
                                        </a>
                                        <a href="{{ route('kurikulums.bks.index',[$kurikulum->id]) }}" class="btn btn-sm btn-primary mt-1">
                                            <i class="bi bi-book"></i> BK
                                        </a>
                                        <a href="{{ route('kurikulums.mks.index',[$kurikulum->id]) }}" class="btn btn-sm btn-primary mt-1">
                                            <i class="bi bi-journal-bookmark"></i> MK
                                        </a>
                                        <br>
                                        <a href="{{ route('kurikulums.joinprofilcpls.index',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
                                            <i class="bi bi-gear"></i> Interaksi Profil >< CPL
                                        </a>
                                        <a href="{{ route('kurikulums.joincplbks.index',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
                                            <i class="bi bi-gear"></i> Interaksi CPL >< BK
                                        </a>
                                        <a href="{{ route('kurikulums.joinbkmks.index',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
                                            <i class="bi bi-gear"></i> Interaksi BK >< Mata Kuliah
                                        </a>
                                    </li>
                                </div>
                            </div>
                            <hr>
                            @endif
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
