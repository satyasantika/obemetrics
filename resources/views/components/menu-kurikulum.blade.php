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
<a href="{{ route('kurikulums.joinprofilcpls.index',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-gear"></i> Interaksi Profil >< CPL
</a>
<a href="{{ route('kurikulums.joincplbks.index',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-gear"></i> Interaksi CPL >< BK
</a>
<a href="{{ route('kurikulums.joinbkmks.index',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-gear"></i> Interaksi BK >< MK
</a>
<a href="{{ route('kurikulums.joincplmks.index',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-gear"></i> Bobot CPL tiap MK
</a>
<a href="{{ route('kurikulums.rencana-asesmen',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-gear"></i> Pemetaan Rencana Asesmen CPL
</a>
<a href="{{ route('kurikulums.analisis-asesmen',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-gear"></i> Hasil Analisis Asesmen CPL
</a>
<a href="{{ route('kurikulums.spyderweb-cpl',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-gear"></i> Grafik Jaring Laba-laba CPL
</a>
<a href="{{ route('kurikulums.laporan-mahasiswa',[$kurikulum->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-gear"></i> Resume Mahasiswa
</a>
