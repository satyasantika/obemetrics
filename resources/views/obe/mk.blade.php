@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{-- header --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm"><i class="bi bi-house-door"></i></a>
                    Data Mata Kuliah (MK)</strong>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body">
                    @include('layouts.alert')

                    {{-- identitas kurikulum --}}
                    @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])
                    <hr>
                    {{-- menu kurikulum --}}
                    @include('components.menu-kurikulum',['kurikulum' => $kurikulum])
                    <hr>
                    <div class="row mb-2">
                        <div class="col">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateMk"><i class="bi bi-plus-circle"></i> Tambah Mata Kuliah</button>
                            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'joinmkusers']) }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-upload"></i> Import Dosen Pengampu</a>
                            <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'mks']) }}" class="btn btn-success btn-sm float-end me-1"><i class="bi bi-upload"></i> Upload Banyak MK</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-striped">
                                <thead></thead>
                                    <tr>
                                        <th class="text-center">Semester</th>
                                        <th>Kode & Nama MK (SKS)</th>
                                        <th class="text-center">Dosen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($mks as $mk)
                                    <tr style="vertical-align: text-top;">
                                        <td>
                                            <span class="badge bg-{{ $mk->semester % 2 == 0 ? 'primary' : 'secondary' }}">semester {{ $mk->semester }}</span>
                                            <br>
                                            {{-- Edit MK --}}
                                            <button type="button" class="btn btn-sm btn-white text-primary" data-bs-toggle="modal" data-bs-target="#modalEditMk-{{ $mk->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                        <td style="text-align: justify">
                                            {{ $mk->kode }} - {{ $mk->nama }}
                                            <br>
                                            <strong>{{ $mk->sks }} SKS</strong>
                                            (T: {{ $mk->sks_teori }}, P: {{ $mk->sks_praktik }}, L: {{ $mk->sks_lapangan }})
                                        </td>
                                        <td>
                                            @php
                                                $assignedUsers = \App\Models\JoinMkUser::where('kurikulum_id',$kurikulum->id)
                                                    ->where('mk_id',$mk->id)
                                                    ->get();

                                            @endphp
                                            @forelse ($assignedUsers as $user)
                                                <span class="badge bg-{{ $user->koordinator == true ? 'primary':'secondary' }}">{{ $user->user->name }}</span>
                                            @empty
                                                <span class="badge bg-warning text-dark">Belum ada</span>
                                            @endforelse
                                            <a href="{{ route('mks.users.index',$mk->id) }}" class="btn btn-white text-success btn-sm">
                                                <i class="bi bi-plus-circle"></i> Dosen
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3"><span class="bg-warning text-dark p-2">
                                            Belum ada data Mata Kuliah untuk kurikulum ini.</span>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCreateMk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('kurikulums.mks.store', $kurikulum) }}" method="post">
                @csrf
                <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Semester <span class="text-danger">(*)</span></label>
                            <select name="semester" class="form-select" required>
                                <option value="">- Pilih Semester -</option>
                                @for ($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label"><strong>Kode</strong> Mata Kuliah <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label"><strong>Nama</strong> Mata Kuliah <span class="text-danger">(*)</span></label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">SKS Teori</label>
                            <input type="number" min="0" max="6" value="0" name="sks_teori" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">SKS Praktikum</label>
                            <input type="number" min="0" max="6" value="0" name="sks_praktik" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">SKS Lapangan</label>
                            <input type="number" min="0" max="6" value="0" name="sks_lapangan" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="6" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($mks as $mk)
<div class="modal fade" id="modalEditMk-{{ $mk->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('kurikulums.mks.update',[$kurikulum->id,$mk->id]) }}" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit MK: {{ $mk->kode }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Semester <span class="text-danger">(*)</span></label>
                            <select name="semester" class="form-select" required>
                                <option value="">- Pilih Semester -</option>
                                @for ($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}" @selected($mk->semester == $i)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label"><strong>Kode</strong> Mata Kuliah <span class="text-danger">(*)</span></label>
                            <input type="text" name="kode" class="form-control" value="{{ $mk->kode }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label"><strong>Nama</strong> Mata Kuliah <span class="text-danger">(*)</span></label>
                            <input type="text" name="nama" class="form-control" value="{{ $mk->nama }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">SKS Teori</label>
                            <input type="number" min="0" max="6" value="{{ $mk->sks_teori ?? 0 }}" name="sks_teori" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">SKS Praktikum</label>
                            <input type="number" min="0" max="6" value="{{ $mk->sks_praktik ?? 0 }}" name="sks_praktik" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">SKS Lapangan</label>
                            <input type="number" min="0" max="6" value="{{ $mk->sks_lapangan ?? 0 }}" name="sks_lapangan" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="6" class="form-control">{{ $mk->deskripsi }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @php
                        $canDeleteMk =
                            !$mk->joinCplMks()->exists() &&
                            !$mk->joinMkUsers()->exists() &&
                            !$mk->kontrakMks()->exists() &&
                            !$mk->cpmks()->exists() &&
                            !$mk->penugasans()->exists();
                    @endphp
                    @if ($canDeleteMk)
                        <button type="button" class="btn btn-outline-danger btn-sm me-auto" onclick="if(confirm('Yakin akan menghapus MK {{ $mk->kode }} - {{ $mk->nama }}?')){ document.getElementById('delete-mk-{{ $mk->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
                    @else
                        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            @if ($canDeleteMk)
                <form id="delete-mk-{{ $mk->id }}" action="{{ route('kurikulums.mks.destroy',[$kurikulum->id,$mk->id]) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
            @endif
        </div>
    </div>
</div>
@endforeach


@endsection
