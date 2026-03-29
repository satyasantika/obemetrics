@can('access dosen dashboard')
    <li class="nav-header">DOSEN</li>
    <li class="nav-item">
        <a href="{{ route('ruang.dosen') }}" class="nav-link {{ request()->routeIs('ruang.dosen') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chalkboard-teacher"></i>
            <p>Pilih Mata Kuliah</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('dosen.kontrakmks.index') }}" class="nav-link {{ request()->routeIs('dosen.kontrakmks.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-signature"></i>
            <p>Kontrak MK</p>
        </a>
    </li>
@endcan
