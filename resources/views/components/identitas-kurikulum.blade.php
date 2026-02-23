<div class="row">
    <div class="col-md-3">Nama Kurikulum</div>
    <div class="col"><strong>{{ $kurikulum->nama }}</strong></div>
</div>
<div class="row">
    <div class="col-md-3">Program Studi</div>
    <div class="col"><strong>{{ $kurikulum->prodi->jenjang }} {{ $kurikulum->prodi->nama }}</strong></div>
</div>
<div class="row">
    <div class="col text-end">
        <a href="{{ route('setting.import.kurikulum-master', ['kurikulum' => $kurikulum->id, 'target' => 'kurikulum_bundle', 'return_url' => url()->current()]) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-upload"></i> Import Data Master</a>
    </div>
</div>

