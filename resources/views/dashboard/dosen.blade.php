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

                @forelse (auth()->user()->joinProdiUsers as $joinProdiUser)
                    <h4>Program Studi {{ $joinProdiUser->prodi->jenjang }} {{ $joinProdiUser->prodi->nama }}</h4>
                    <hr>

                    {{-- Mata Kuliah --}}
                    Mata Kuliah pada program studi ini:
                    {{-- mata kuliah yang ditampilkan adalah mata kuliah yang diampu oleh dosen tersebut pada kurikulum yang terkait dengan program studi ini. --}}
                    @foreach (auth()->user()->joinMkUsers as $joinMkUser)
                        @if ($joinMkUser->kurikulum->prodi_id == $joinProdiUser->prodi->id)
                        <div class="row">
                            <div class="col">
                            {{ $joinMkUser->kurikulum->nama }}
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col">
                                <ol>
                                {{-- berikut mata kuliah yang Anda ampu pada kurikulum ini: --}}
                                @foreach (auth()->user()->joinMkUsers->pluck('mk')->where('kurikulum_id',$joinMkUser->kurikulum->id) as $mk)
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
