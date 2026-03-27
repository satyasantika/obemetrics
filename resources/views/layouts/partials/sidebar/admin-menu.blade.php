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

    {{-- <li class="nav-item {{ request()->routeIs('settings.import.*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('settings.import.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-import"></i>
            <p>
                Bulk Import
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('settings.import.admin-master') }}" class="nav-link {{ request()->routeIs('settings.import.admin-master*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Program Studi</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('settings.import.users') }}" class="nav-link {{ request()->routeIs('settings.import.users*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Dosen</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('settings.import.mahasiswas') }}" class="nav-link {{ request()->routeIs('settings.import.mahasiswas*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Mahasiswa</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('settings.import.joinprodiusers') }}" class="nav-link {{ request()->routeIs('settings.import.joinprodiusers*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Dosen Prodi</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('settings.import.kontrakmks') }}" class="nav-link {{ request()->routeIs('settings.import.kontrakmks*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Kontrak MK</p>
                </a>
            </li>
        </ul>
    </li> --}}
@endcan
