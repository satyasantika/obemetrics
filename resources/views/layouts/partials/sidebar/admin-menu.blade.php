@can('access admin dashboard')
    <li class="nav-header">ADMIN</li>

    <li class="nav-item">
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users-cog"></i>
            <p>Manajemen User</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*', 'rolepermissions.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>Role</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-key"></i>
            <p>Permission</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('prodis.index') }}" class="nav-link {{ request()->routeIs('prodis.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book-reader"></i>
            <p>Prodi</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('semesters.index') }}" class="nav-link {{ request()->routeIs('semesters.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Semester</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('evaluasis.index') }}" class="nav-link {{ request()->routeIs('evaluasis.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clipboard-check"></i>
            <p>Evaluasi</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('mahasiswas.index') }}" class="nav-link {{ request()->routeIs('mahasiswas.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Mahasiswa</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('kontrakmks.index') }}" class="nav-link {{ request()->routeIs('kontrakmks.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-signature"></i>
            <p>Kontrak Mata Kuliah</p>
        </a>
    </li>

@endcan
