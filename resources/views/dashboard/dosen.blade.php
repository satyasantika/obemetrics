@push('title')
    Dashboard Dosen
@endpush
<div class="row mt-3">
    <div class="col">
        <div class="card">
            <div class="card-header">Manajemen Mata Kuliah oleh Dosen</div>
            <div class="card-body">
                @include('layouts.alert')
                {{-- Program Studi --}}
                @php
                    $user_id = auth()->id();
                    $mk_ids = \App\Models\JoinMkUser::where('user_id',$user_id)->pluck('mk_id');
                    $kurikulum_ids = \App\Models\JoinMkUser::where('user_id',$user_id)->pluck('kurikulum_id')->unique();
                    $prodi_ids = \App\Models\Kurikulum::whereIn('id',$kurikulum_ids)->pluck('prodi_id');
                    $prodis = \App\Models\Prodi::whereIn('id',$prodi_ids)->get();
                @endphp

                @forelse ($prodis as $prodi)
                    <h4>Program Studi {{ $prodi->jenjang }} {{ $prodi->nama }}</h4>
                    <hr>

                    {{-- Mata Kuliah --}}
                    Mata Kuliah pada program studi ini:
                    @php
                        $kurikulums = \App\Models\Kurikulum::whereIn('id',$kurikulum_ids)->get();
                    @endphp
                    @foreach ($kurikulums as $kurikulum)
                        @if ($kurikulum->prodi_id == $prodi->id)
                        @php
                            $join_mk_users = \App\Models\JoinMkUser::where('kurikulum_id',$kurikulum->id)->pluck('mk_id');
                            $mks = \App\Models\Mk::whereIn('id',$join_mk_users)->get();
                        @endphp
                        <div class="row">
                            <div class="col">
                            {{ $kurikulum->nama }}
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col">
                                <ol>
                                @foreach ($mks as $mk)
                                    <hr>
                                    <li>
                                        {{ $mk->kodemk }} {{ $mk->nama }}
                                        <br>
                                        @include('layouts.menu-mk',$mk)
                                    </li>
                                @endforeach
                            </ol>
                            </div>
                        </div>
                        @endif
                    @endforeach
                    <hr>
                @empty
                    Anda belum terdaftar pada program studi manapun.
                @endforelse
            </div>
        </div>
    </div>
</div>
