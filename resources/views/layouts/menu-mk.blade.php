<a href="{{ route('mks.cpmks.index',[$mk->id]) }}" class="btn btn-sm btn-primary mt-1">
    <i class="bi bi-sliders"></i> CPMK
</a>
<a href="{{ route('mks.joincplcpmks.index',[$mk->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-link-45deg"></i> Set CPL >< CPMK
</a>
<a href="{{ route('mks.subcpmks.index',[$mk->id]) }}" class="btn btn-sm btn-primary mt-1">
    <i class="bi bi-list-nested"></i> SubCPMK
</a>
<a href="{{ route('mks.penugasans.index',[$mk->id]) }}" class="btn btn-sm btn-primary mt-1">
    <i class="bi bi-list-task"></i> Tagihan Tugas
</a>
<a href="{{ route('mks.joinsubcpmkpenugasans.index',[$mk->id]) }}" class="btn btn-sm btn-secondary mt-1">
    <i class="bi bi-link-45deg"></i> Set SubCPMK >< Tugas
</a>
<a href="{{ route('mks.nilais.index',[$mk->id]) }}" class="btn btn-sm btn-primary mt-1">
    <i class="bi bi-clipboard-check"></i> Penilaian Tugas
</a>
