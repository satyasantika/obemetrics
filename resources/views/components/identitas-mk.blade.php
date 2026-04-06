<div class="border rounded-3 p-3 my-2 bg-light-subtle">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>
            <div class="text-muted small text-uppercase">Mata Kuliah</div>
            <div class="fw-semibold">{{ $mk->nama }}</div>
        </div>
        <div class="text-md-center">
            <div class="text-muted small text-uppercase">Kurikulum</div>
            <div class="fw-semibold">{{ $mk->kurikulum->nama }}</div>
        </div>
        <div class="text-md-end">
            <div class="text-muted small text-uppercase">Program Studi</div>
            <div class="fw-semibold">{{ $mk->kurikulum->prodi->jenjang }} {{ $mk->kurikulum->prodi->nama }}</div>
        </div>
    </div>
    <hr>
    @include('components.mk-flow-info', ['mk' => $mk])
</div>
