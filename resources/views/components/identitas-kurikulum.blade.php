<div class="border rounded-3 p-3 my-2 bg-light-subtle">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>
            <div class="text-muted small text-uppercase">Nama Kurikulum</div>
            <div class="fw-semibold">{{ $kurikulum->nama }}</div>
        </div>
        <div class="text-md-end">
            <div class="text-muted small text-uppercase">Program Studi</div>
            <div class="fw-semibold">{{ $kurikulum->prodi->jenjang }} {{ $kurikulum->prodi->nama }}</div>
        </div>
    </div>
    <hr>
    @include('components.kurikulum-flow-info',['kurikulum' => $kurikulum])
</div>

