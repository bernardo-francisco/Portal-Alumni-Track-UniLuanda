<header class="navbar navbar-expand-lg bg-white border-bottom shadow-sm empresa-topbar py-2">

    <div class="container-fluid px-3 px-lg-4">

        {{-- LADO ESQUERDO --}}
        <div class="d-flex align-items-center gap-3">

            <button type="button"
                    class="btn btn-light border rounded-circle p-2 topbar-icon-btn"
                    onclick="toggleSidebar()"
                    aria-label="Abrir/fechar menu"
                    title="Abrir/fechar menu"
                    id="btnToggleSidebar">
                <i class="fas fa-bars"></i>
            </button>

            <div class="lh-sm">
                <h1 class="h5 fw-semibold text-dark mb-0">@yield('page-title', 'Dashboard')</h1>
                <small class="text-secondary d-none d-sm-block">Bem-vindo à área da sua empresa</small>
            </div>

        </div>

        {{-- LADO DIREITO --}}
        <div class="d-flex align-items-center gap-2 gap-md-3">

            {{-- NOVA OPORTUNIDADE --}}
            <a href="{{ route('empresa.oportunidades.create') }}"
               class="btn btn-primary btn-sm d-none d-md-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                <span>Nova Oportunidade</span>
            </a>

            {{-- ============================================================
                 SINO DE NOTIFICAÇÕES (notificações reais + resumo)
            ============================================================ --}}
            <div class="dropdown">
                <button type="button"
                        class="btn btn-light border-0 position-relative rounded-circle p-2 topbar-icon-btn"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        title="Notificações">
                    <i class="fas fa-bell text-warning fs-5"></i>

                    @if(($notificacoesNaoLidasEmpresa ?? 0) > 0)
                        <span class="notif-badge">
                            {{ $notificacoesNaoLidasEmpresa > 99 ? '99+' : $notificacoesNaoLidasEmpresa }}
                        </span>
                    @endif
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0 notif-menu">

                    {{-- HEADER --}}
                    <li class="notif-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="notif-header-title">
                                    <i class="fas fa-bell me-2 text-warning"></i> Notificações
                                </span>
                                @if(($notificacoesNaoLidasEmpresa ?? 0) > 0)
                                    <span class="notif-header-count">{{ $notificacoesNaoLidasEmpresa }} novas</span>
                                @endif
                            </div>
                            @if(($notificacoesNaoLidasEmpresa ?? 0) > 0)
                                <button type="button"
                                        class="btn btn-link btn-sm text-decoration-none p-0 notif-mark-all"
                                        onclick="marcarTodasComoLidas(event)">
                                    Marcar todas
                                </button>
                            @endif
                        </div>
                    </li>

                    <li><hr class="dropdown-divider m-0"></li>

                    {{-- RESUMO (contagens) --}}
                    <li class="notif-summary">
                        <a href="{{ route('empresa.oportunidades.index') }}" class="notif-summary-item">
                            <div class="notif-summary-icon notif-summary-primary">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="notif-summary-text">
                                <strong>{{ $contagemOportunidades ?? 0 }}</strong>
                                <small>Oportunidades</small>
                            </div>
                        </a>

                        <a href="{{ route('empresa.candidaturas.index') }}" class="notif-summary-item">
                            <div class="notif-summary-icon notif-summary-success">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div class="notif-summary-text">
                                <strong>{{ $contagemCandidaturas ?? 0 }}</strong>
                                <small>Candidaturas</small>
                            </div>
                        </a>
                    </li>

                    <li><hr class="dropdown-divider m-0"></li>

                    {{-- NOTIFICAÇÕES REAIS --}}
                    <li class="notif-scroll">
                        @if(isset($notificacoesEmpresa) && $notificacoesEmpresa->count() > 0)
                            @foreach($notificacoesEmpresa as $notif)
                                <a href="{{ $notif->link ?? '#' }}"
                                   class="notif-item {{ !$notif->lida ? 'notif-unread' : '' }}"
                                   onclick="marcarComoLida({{ $notif->id }}, event)">

                                    <div class="notif-item-icon notif-item-{{ $notif->tipo_cor }}">
                                        <i class="fas fa-{{ $notif->tipo_icone }}"></i>
                                    </div>

                                    <div class="notif-item-body">
                                        <strong class="notif-item-title">{{ $notif->titulo }}</strong>
                                        @if($notif->mensagem)
                                            <p class="notif-item-msg">{{ $notif->mensagem }}</p>
                                        @endif
                                        <small class="notif-item-time">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $notif->created_at->diffForHumans() }}
                                        </small>
                                    </div>

                                    @if(!$notif->lida)
                                        <span class="notif-dot"></span>
                                    @endif
                                </a>
                            @endforeach
                        @else
                            <div class="text-center py-5 px-3">
                                <i class="fas fa-bell-slash fa-2x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Nenhuma notificação</p>
                                <small class="text-muted">Você está em dia!</small>
                            </div>
                        @endif
                    </li>
                </ul>
            </div>

            <div class="vr mx-1 d-none d-sm-block" style="height: 30px;"></div>

            {{-- UTILIZADOR --}}
            @php
                $currentUser = Auth::user();
                $empresaAtual = $currentUser->empresa ?? null;
            @endphp

            <div class="dropdown">
                <button type="button"
                        class="btn btn-light border-0 dropdown-toggle d-flex align-items-center gap-2 p-1 user-menu-btn"
                        data-bs-toggle="dropdown"
                        data-bs-display="static"
                        aria-expanded="false">

                    @if($empresaAtual && $empresaAtual->logo_url)
                        <img src="{{ asset($empresaAtual->logo_url) }}"
                             alt="Logótipo"
                             class="rounded-circle border"
                             width="36" height="36"
                             style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center border user-avatar-placeholder">
                            <i class="fas fa-building"></i>
                        </div>
                    @endif

                    <span class="d-none d-md-flex flex-column text-start lh-sm">
                        <span class="fw-semibold text-dark">{{ $empresaAtual->nome ?? 'Minha Empresa' }}</span>
                        <small class="text-secondary">Empresa</small>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 user-menu">
                    <li>
                        <div class="dropdown-header pt-3">
                            <div class="fw-semibold text-dark">{{ $empresaAtual->nome ?? 'Minha Empresa' }}</div>
                            <small class="text-secondary">{{ $currentUser->email ?? '' }}</small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 px-4" href="{{ route('empresa.dashboard') }}"><i class="fas fa-chart-pie fa-fw me-2"></i> Dashboard</a></li>
                    <li><a class="dropdown-item py-2 px-4" href="{{ route('empresa.perfil') }}"><i class="fas fa-building fa-fw me-2"></i> Perfil da Empresa</a></li>
                    <li><a class="dropdown-item py-2 px-4" href="{{ route('empresa.oportunidades.index') }}"><i class="fas fa-briefcase fa-fw me-2"></i> Oportunidades</a></li>
                    <li><a class="dropdown-item py-2 px-4" href="{{ route('empresa.candidaturas.index') }}"><i class="fas fa-file-signature fa-fw me-2"></i> Candidaturas</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger py-2 px-4" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-topbar-form').submit();">
                            <i class="fas fa-sign-out-alt fa-fw me-2"></i> Sair
                        </a>
                        <form id="logout-topbar-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </li>
                </ul>
            </div>

        </div>
    </div>

</header>
