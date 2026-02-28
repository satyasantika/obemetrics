@can('access prodi dashboard')
    <li class="nav-header">PRODI</li>
    <li class="nav-item">
        <a href="{{ route('ruang.prodi') }}" class="nav-link {{ request()->routeIs('ruang.prodi') ? 'active' : '' }}">
            <i class="nav-icon fas fa-sitemap"></i>
            <p>Pilih Kurikulum</p>
        </a>
    </li>
@endcan
