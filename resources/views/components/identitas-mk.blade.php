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
