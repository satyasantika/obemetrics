@can('access dosen dashboard')
    <li class="nav-header">DOSEN</li>
    <li class="nav-item">
        <a href="{{ route('ruang.dosen') }}" class="nav-link {{ request()->routeIs('ruang.dosen') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chalkboard-teacher"></i>
            <p>Pilih Mata Kuliah</p>
        </a>
    </li>
@endcan
