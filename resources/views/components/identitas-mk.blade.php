<div class="row">
    <div class="col-md-3">Nama Mata Kuliah</div>
    <div class="col"><strong>{{ $mk->nama }}</strong></div>
</div>
<div class="row">
    <div class="col-md-3">Nama Kurikulum</div>
    <div class="col"><strong>{{ $mk->kurikulum->nama }}</strong></div>
</div>
<div class="row">
    <div class="col-md-3">Program Studi</div>
    <div class="col"><strong>{{ $mk->kurikulum->prodi->jenjang }} {{ $mk->kurikulum->prodi->nama }}</strong></div>
</div>
<div class="row">
    <div class="col text-end">
        <a href="{{ route('setting.import.mk-master', ['mk' => $mk->id, 'target' => 'mk_bundle', 'return_url' => url()->current()]) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-upload"></i> Import Data Master</a>
    </div>
</div>
