@extends('layouts.panel')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- identitas kurikulum --}}
            @include('components.identitas-kurikulum',['kurikulum' => $kurikulum])

            <div class="card">
                <x-obe.header
                    title="Data Mata Kuliah (MK)"
                    subtitle="Kelola mata kuliah pada kurikulum aktif"
                    icon="bi bi-journal-bookmark-fill"
                    />
                <div class="card-body bg-light-subtle">
                    <div class="row mb-2">
                        <div class="col">
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateMk"><i class="bi bi-plus-circle"></i> Tambah Mata Kuliah</button>
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'joinmkusers']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-sm float-end"><i class="bi bi-upload"></i> Import Dosen Pengampu</a>
                            <a href="{{ route('settings.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'mks']) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm float-end me-1"><i class="bi bi-upload"></i> Upload Banyak MK</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="table-responsive rounded-3 border bg-white shadow-sm">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
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
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" data-bs-toggle="modal" data-bs-target="#modalEditMk-{{ $mk->id }}" title="Edit MK">
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
                                                $assignedUsers = $assignedByMk->get($mk->id, collect());
                                            @endphp
                                            @forelse ($assignedUsers as $user)
                                                <span class="badge bg-{{ $user->koordinator == true ? 'primary':'secondary' }}">{{ $user->user->name }}</span>
                                            @empty
                                                <span class="badge bg-warning text-dark">Belum ada</span>
                                            @endforelse
                                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm mt-1" data-bs-toggle="modal" data-bs-target="#modalSetDosen-{{ $mk->id }}">
                                                <i class="bi bi-plus-circle"></i> Dosen
                                            </button>
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
</div>

<div class="modal fade" id="modalCreateMk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden">
            <form action="{{ route('kurikulums.mks.store', $kurikulum) }}" method="post">
                @csrf
                <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                <div class="modal-header bg-light-subtle border-bottom">
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
                <div class="modal-footer bg-light-subtle border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($mks as $mk)
<div class="modal fade" id="modalEditMk-{{ $mk->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden">
            <form action="{{ route('kurikulums.mks.update',[$kurikulum->id,$mk->id]) }}" method="post">
                @csrf
                @method('PUT')
                <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                <div class="modal-header bg-light-subtle border-bottom">
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
                <div class="modal-footer bg-light-subtle border-top">
                    @php $canDeleteMk = $canDeleteByMk[$mk->id] ?? false; @endphp
                    @if ($canDeleteMk)
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-sm me-auto" onclick="if(confirm('Yakin akan menghapus MK {{ $mk->kode }} - {{ $mk->nama }}?')){ document.getElementById('delete-mk-{{ $mk->id }}').submit(); }"><i class="bi bi-trash"></i> Hapus</button>
                    @else
                        <span class="badge bg-secondary me-auto">Data digunakan, tidak dapat dihapus</span>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
            @if ($canDeleteMk)
                <form id="delete-mk-{{ $mk->id }}" action="{{ route('kurikulums.mks.destroy',[$kurikulum->id,$mk->id]) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
            @endif
        </div>
    </div>
</div>
@endforeach

@foreach ($mks as $mk)
<div class="modal fade" id="modalSetDosen-{{ $mk->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="modal-header bg-light-subtle border-bottom">
                <h5 class="modal-title">Set Dosen Pengampu - {{ $mk->kode }} {{ $mk->nama }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    $linkedDosen = $linkedByMkUser->get($mk->id, collect());
                    $lockedUserIds = $lockedByMk->get($mk->id, collect());
                @endphp

                <div class="table-responsive rounded-3 border bg-white">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Dosen</th>
                                <th class="text-center" style="width: 180px;">Pengampu</th>
                                <th class="text-center" style="width: 180px;">Koordinator</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($prodiUsers as $prodiUser)
                            @php
                                $linkedRow = $linkedDosen->get($prodiUser->user_id);
                                $isLinked = !is_null($linkedRow);
                                $isKoordinator = $isLinked ? (bool) $linkedRow->koordinator : false;
                                $isLocked = $isLinked && $lockedUserIds->has($prodiUser->user_id);
                                $isLockedKoordinator = $isKoordinator && $lockedUserIds->has($prodiUser->user_id);
                            @endphp
                            <tr>
                                <td>{{ $prodiUser->user->name }}</td>
                                <td class="text-center">
                                    <form action="{{ route('mks.users.update',[$mk->id,$prodiUser->user_id]) }}" method="POST" class="d-inline-block js-joinmkusers-form" data-form-type="linked">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="user_id" value="{{ $prodiUser->user_id }}">
                                        <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_linked" data-role="linked" onchange="this.form.requestSubmit()" @checked($isLinked) @disabled($isLocked)>
                                        </div>
                                        @if($isLocked)
                                            <small class="text-muted">terkunci</small>
                                        @endif
                                    </form>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('mks.users.update',[$mk->id,$prodiUser->user_id]) }}" method="POST" class="d-inline-block js-joinmkusers-form" data-form-type="koordinator">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="user_id" value="{{ $prodiUser->user_id }}">
                                        <input type="hidden" name="mk_id" value="{{ $mk->id }}">
                                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulum->id }}">
                                        @if($isLinked)
                                            <input type="hidden" name="is_linked" value="1">
                                        @endif
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_koordinator" data-role="koordinator" onchange="this.form.requestSubmit()" @checked($isKoordinator) @disabled($isLockedKoordinator || !$isLinked)>
                                        </div>
                                        @if($isLockedKoordinator)
                                            <small class="text-muted d-block mt-1" data-role="locked-hint">terkunci</small>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Belum ada dosen terdaftar pada prodi ini.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light-subtle border-top">
                <a href="{{ route('mks.users.index',$mk->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-sm">Buka Halaman Penuh</a>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let hasJoinMkUsersMutation = false;

    const syncRowState = function (row) {
        if (!row) {
            return;
        }

        const linkedForm = row.querySelector('form[data-form-type="linked"]');
        const koordinatorForm = row.querySelector('form[data-form-type="koordinator"]');
        if (!linkedForm || !koordinatorForm) {
            return;
        }

        const linkedCheckbox = linkedForm.querySelector('input[data-role="linked"]');
        const koordinatorCheckbox = koordinatorForm.querySelector('input[data-role="koordinator"]');
        if (!linkedCheckbox || !koordinatorCheckbox) {
            return;
        }

        const isLinked = linkedCheckbox.checked;
        koordinatorCheckbox.disabled = !isLinked;

        const setPengampuHint = koordinatorForm.querySelector('[data-role="set-pengampu-hint"]');
        if (setPengampuHint) {
            setPengampuHint.style.display = isLinked ? 'none' : 'block';
        }

        const hiddenLinkedInput = koordinatorForm.querySelector('input[name="is_linked"]');
        if (!isLinked) {
            koordinatorCheckbox.checked = false;
            if (hiddenLinkedInput) {
                hiddenLinkedInput.remove();
            }
        } else {
            if (!hiddenLinkedInput) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'is_linked';
                hidden.value = '1';
                koordinatorForm.appendChild(hidden);
            }
        }
    };

    const syncKoordinatorForModal = function (row) {
        if (!row) {
            return;
        }

        const tbody = row.closest('tbody');
        if (!tbody) {
            return;
        }

        tbody.querySelectorAll('form[data-form-type="koordinator"]').forEach(function (form) {
            const checkbox = form.querySelector('input[data-role="koordinator"]');
            if (!checkbox) {
                return;
            }
            checkbox.checked = false;
        });

        const selectedCheckbox = row.querySelector('form[data-form-type="koordinator"] input[data-role="koordinator"]');
        if (selectedCheckbox) {
            selectedCheckbox.checked = true;
        }
    };

    const tryReloadIfDirty = function () {
        if (hasJoinMkUsersMutation) {
            window.location.reload();
        }
    };

    document.querySelectorAll('.modal[id^="modalSetDosen-"]').forEach(function (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', tryReloadIfDirty);
    });
    if (window.jQuery) {
        window.jQuery('.modal[id^="modalSetDosen-"]').on('hidden.bs.modal', tryReloadIfDirty);
    }

    document.querySelectorAll('.modal[id^="modalSetDosen-"] tbody tr').forEach(function (row) {
        syncRowState(row);
    });

    document.addEventListener('submit', async function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.classList.contains('js-joinmkusers-form')) {
            return;
        }

        event.preventDefault();

        if (form.dataset.loading === '1') {
            return;
        }

        const targetCheckbox = form.querySelector('input[type="checkbox"]');
        const previousChecked = targetCheckbox ? targetCheckbox.checked : null;
        const isKoordinatorForm = form.dataset.formType === 'koordinator';
        const payload = new FormData(form);
        const row = form.closest('tr');

        form.dataset.loading = '1';
        if (targetCheckbox) {
            targetCheckbox.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json, text/html;q=0.9,*/*;q=0.8'
                },
                body: payload,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Request gagal');
            }
            hasJoinMkUsersMutation = true;

            if (isKoordinatorForm && targetCheckbox && targetCheckbox.checked) {
                syncKoordinatorForModal(row);
            }
            syncRowState(row);
        } catch (error) {
            if (targetCheckbox && previousChecked !== null) {
                targetCheckbox.checked = !previousChecked;
            }
            syncRowState(row);
            window.alert('Gagal menyimpan perubahan dosen pengampu. Coba lagi.');
        } finally {
            form.dataset.loading = '0';
            if (targetCheckbox) {
                targetCheckbox.disabled = false;
            }
            syncRowState(row);
        }
    }, true);
});
</script>
@endpush


@endsection
