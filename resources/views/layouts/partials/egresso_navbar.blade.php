<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('egresso.dashboard') }}">
            <i class="fas fa-graduation-cap"></i> UniLuanda Alumni
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('egresso.dashboard') ? 'active' : '' }}" 
                       href="{{ route('egresso.dashboard') }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>

                <!-- Perfil -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('egresso.perfil') ? 'active' : '' }}" 
                       href="{{ route('egresso.perfil') }}">
                        <i class="fas fa-user"></i> Perfil
                    </a>
                </li>

                <!-- Oportunidades -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('egresso.oportunidades') ? 'active' : '' }}" 
                       href="{{ route('egresso.oportunidades') }}">
                        <i class="fas fa-briefcase"></i> Oportunidades
                    </a>
                </li>

                <!-- Eventos -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('egresso.eventos') ? 'active' : '' }}" 
                       href="{{ route('egresso.eventos') }}">
                        <i class="fas fa-calendar"></i> Eventos
                    </a>
                </li>

                <!-- Rede -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('egresso.rede') ? 'active' : '' }}" 
                       href="{{ route('egresso.rede') }}">
                        <i class="fas fa-users"></i> Rede
                    </a>
                </li>

                <!-- NOVO: Mapa de Egressos -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('egresso.mapa') ? 'active' : '' }}" 
                       href="{{ route('egresso.mapa') }}">
                        <i class="fas fa-map"></i> Mapa
                    </a>
                </li>

                <!-- Dropdown do Usuário -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        @if(Auth::user()->photo_url)
                            <img src="{{ asset(Auth::user()->photo_url) }}" 
                                 class="rounded-circle" 
                                 style="width: 28px; height: 28px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
                        @else
                            <i class="fas fa-user-circle"></i>
                        @endif
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                       
                        <li><a class="dropdown-item" href="{{ route('egresso.localizacao') }}">
                            <i class="fas fa-map-marker-alt"></i> Minha Localização
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('egresso.profissional') }}">
                            <i class="fas fa-briefcase"></i> Situação Profissional
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('egresso.mensagens') }}">
                            <i class="fas fa-envelope"></i> Mensagens
                            <span class="badge bg-danger" id="notif-badge-msg-nav" style="display:none; font-size: 0.6rem;">0</span>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>