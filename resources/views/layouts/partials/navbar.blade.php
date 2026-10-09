<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">
            <i class="fas fa-graduation-cap"></i> UniLuanda Alumni
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i> Entrar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="fas fa-user-plus"></i> Registar
                        </a>
                    </li>
                @else
                    <!-- Início do Dropdown do Usuário Autenticado -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1" 
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            
                            @if(Auth::user()->photo_url)
                                <img src="{{ asset(Auth::user()->photo_url) }}" 
                                     class="rounded-circle border border-white" 
                                     alt="Foto de perfil"
                                     style="width: 30px; height: 30px; object-fit: cover;">
                            @else
                                <i class="fas fa-user-circle" style="font-size: 26px; vertical-align: middle;"></i>
                            @endif
                            
                            <span>{{ Auth::user()->name }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                            <li>
                                <div class="dropdown-header d-flex flex-column text-muted">
                                    <span class="fw-bold text-dark text-truncate" style="max-width: 200px;">{{ Auth::user()->name }}</span>
                                    <small class="text-truncate" style="max-width: 200px;">{{ Auth::user()->email }}</small>
                                    <span class="badge bg-light text-secondary border mt-1 align-self-start text-uppercase" style="font-size: 10px;">
                                        {{ Auth::user()->tipo }}
                                    </span>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            
                            @if(Auth::user()->tipo === 'admin')
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-tachometer-alt fa-fw me-2 text-primary"></i>Painel Admin
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a class="dropdown-item" href="{{ route('egresso.dashboard') }}">
                                        <i class="fas fa-user-grad fa-fw me-2 text-primary"></i>Meu Perfil Alumni
                                    </a>
                                </li>
                            @endif
                            
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}" 
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt fa-fw me-2"></i>Sair
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                    <!-- Fim do Dropdown -->
                @endguest
            </ul>
        </div>
    </div>
</nav>