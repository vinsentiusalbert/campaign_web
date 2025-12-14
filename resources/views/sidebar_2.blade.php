 @php
                $isRetensiActive = Route::is('retensi2', 'churnPrev');
                @endphp

                <li class="nav-item has-treeview {{ $isRetensiActive ? 'menu-open' : 'menu-close' }}">
                    <a href="#" class="nav-link {{ $isRetensiActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            CAPS & Churn Prev
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('retensi2') }}"
                                class="nav-link waves-effect {{ Route::is('retensi2') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #ffffff;"></i>
                                <p>CAPS Indihome</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('churnPrev') }}"
                                class="nav-link waves-effect {{ Route::is('churnPrev') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon" style="color: #ffffff;"></i>
                                <p>Churn Prev Halo</p>
                            </a>
                        </li>
                    </ul>
                </li>