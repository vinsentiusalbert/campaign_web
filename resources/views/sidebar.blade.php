<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ url('/') }}" class="brand-link" style="text-align: center;">
        {{-- <img src="{{ asset('images/TRACERS_2.png') }}" alt="MyAds Logo" class="brand-image img-circle elevation-2"> --}}
        <span class="brand-text font-weight-bold">{{ Auth::user()->role }}</span>
    </a>

    <div class="sidebar">
        <!-- Sidebar user -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <span class="badge badge-danger">{{ Str::limit(Auth::user()->name, 25) }}</span><br>
                @php
                $user = Auth::user();
                $isSuper = $user->role === 'Super';
                $isAdmin = $user->role === 'Admin';
                $isTsel = $user->role === 'Tsel';
                $isTreg = $user->role === 'Treg';
                $isCanv = $user->role === 'Canvasser';
                $isUser = $user->role === 'User';
                $isKam = $user->role === 'KAM';
                @endphp

                @if($isAdmin && $user->email === 'admin@telkomsel.co.id')
                <span class="badge badge-warning">SUPER ADMIN</span>
                @elseif($user->role === 'Admin')
                <span class="badge badge-warning">ADMIN</span>
                @elseif($user->role === 'Tsel')
                <span class="badge badge-success">TSEL</span>
                @elseif($user->role === 'User')
                <span class="badge badge-success">User</span>
                @elseif($isKam)
                <span class="badge badge-primary">KAM</span>
                @elseif($isTreg)
                @php
                $treg_name = DB::table('treg')->where('id', $user->treg_id)->value('treg_name');
                @endphp
                <span class="badge badge-info">TREG {{ $treg_name ?? '-' }}</span>
                @endif
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @if($isUser)
                <li class="nav-header">Campaign</li>
                <li class="nav-item">
                    <a href="{{ route('campaign-waba-interaktif.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-waba-interaktif.index') ? 'active' : '' }}">
                        <i class="nav-icon fab fa-whatsapp" style="color:#25D366;"></i>
                        <p>WABA Interaktif</p>
                    </a>
                </li>
                @elseif($isKam)
                <li class="nav-header">Campaign</li>
                <li class="nav-item">
                    <a href="{{ route('campaign-kam-dashboard.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-kam-dashboard.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line" style="color:#c0392b;"></i>
                        <p>Dashboard KAM</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('campaign-kam.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-kam.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-briefcase" style="color:#fd7e14;"></i>
                        <p>Campaign KAM</p>
                    </a>
                </li>
                @else
                <li class="nav-header">Campaign</li>
                <li class="nav-item">
                    <a href="{{ route('campaign-mobile.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-mobile.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-mobile" style="color:#28a745;"></i>
                        <p>Campaign Mobile</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('campaign-indihome.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-indihome.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-house-signal" style="color:#ff8383;"></i>
                        <p>Campaign Indihome</p>
                    </a>
                </li>
                @if($isAdmin || $isSuper)
                <li class="nav-item">
                    <a href="{{ route('campaign-kam-dashboard.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-kam-dashboard.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line" style="color:#c0392b;"></i>
                        <p>Dashboard KAM</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('campaign-kam.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-kam.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-briefcase" style="color:#fd7e14;"></i>
                        <p>Campaign KAM</p>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('campaign-orbit.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-orbit.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-satellite-dish" style="color:#6f42c1;"></i>
                        <p>Campaign Orbit</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('campaign-soundbox.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-soundbox.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-volume-up" style="color:#20c997;"></i>
                        <p>Campaign Soundbox</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('campaign-nomor-cantik.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-nomor-cantik.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sim-card" style="color:#17a2b8;"></i>
                        <p>Nomor Cantik Telkomsel</p>
                    </a>
                </li>
                @if($isAdmin || $isSuper)
                <li class="nav-item">
                    <a href="{{ route('campaign-waba-interaktif.index') }}"
                        class="nav-link waves-effect {{ request()->routeIs('campaign-waba-interaktif.index') ? 'active' : '' }}">
                        <i class="nav-icon fab fa-whatsapp" style="color:#25D366;"></i>
                        <p>WABA Interaktif</p>
                    </a>
                </li>
                @endif
                @endif

                @if($isSuper || $isAdmin || $isTreg || $isTsel || $isCanv|| $isUser || $isKam)
                <li class="nav-header">System Management</li>
                @endif
                @if($isSuper)
                <li class="nav-item">
                    <a href="{{ route('users.page') }}"
                        class="nav-link waves-effect {{ request()->routeIs('users.page') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users-cog" style="color:#28a745;"></i>
                        <p>Manajemen Users</p>
                    </a>
                </li>
                @endif
                @if($isSuper || $isAdmin || $isTreg || $isTsel || $isCanv|| $isUser || $isKam)
                <li class="nav-item">
                    <a href="{{ url('change-password') }}"
                        class="nav-link waves-effect {{ request()->routeIs('change-password') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-key" style="color:rgb(173, 176, 86);"></i>
                        <p>Change Password</p>
                    </a>
                </li>
                @endif



                {{-- ===== Logout untuk semua role yang ditangani di atas ===== --}}
                @if($isSuper || $isAdmin || $isTreg || $isTsel || $isCanv || $isUser || $isKam)
                <li class="nav-header">LOGOUT</li>
                <li class="nav-item">
                    <a href="{{ url('logout') }}"
                        class="nav-link waves-effect {{ request()->routeIs('logout') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sign-out-alt" style="color:rgb(239,21,21);"></i>
                        <p>Logout</p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
