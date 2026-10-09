<aside class="empresa-sidebar" id="empSidebarPanel">

    {{-- LOGO / MARCA --}}
    <div class="sidebar-brand">
        @php
            $empresaAtual = Auth::user()->empresa ?? null;
        @endphp

        <a href="{{ route('empresa.dashboard') }}" class="sidebar-brand-link">
            @if($empresaAtual && $empresaAtual->logo_url)
                <img src="{{ asset($empresaAtual->logo_url) }}"
                     alt="Logótipo de {{ $empresaAtual->nome }}"
                     class="sidebar-logo">
            @else
                <div class="sidebar-logo-placeholder">
                    <i class="fas fa-building"></i>
                </div>
            @endif

            <h6 class="sidebar-empresa-nome mt-2 mb-0">
                {{ $empresaAtual->nome ?? 'Minha Empresa' }}
            </h6>
            <small class="sidebar-empresa-sector">
                {{ $empresaAtual->sector ?? 'Empresa' }}
            </small>
        </a>
    </div>

    {{-- NAVEGAÇÃO (sem badges — agora vão para o sino) --}}
    <nav class="sidebar-nav">

        <div class="nav-section"><span>Principal</span></div>

        <a href="{{ route('empresa.dashboard') }}"
           class="nav-item {{ request()->routeIs('empresa.dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-chart-pie"></i></span>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="{{ route('empresa.perfil') }}"
           class="nav-item {{ request()->routeIs('empresa.perfil*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-building"></i></span>
            <span class="nav-label">Perfil da Empresa</span>
        </a>

        <div class="nav-section"><span>Gestão</span></div>

        <a href="{{ route('empresa.oportunidades.index') }}"
           class="nav-item {{ request()->routeIs('empresa.oportunidades.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-briefcase"></i></span>
            <span class="nav-label">Oportunidades</span>
        </a>

        <a href="{{ route('empresa.candidaturas.index') }}"
           class="nav-item {{ request()->routeIs('empresa.candidaturas.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-file-signature"></i></span>
            <span class="nav-label">Candidaturas</span>
        </a>

        <div class="nav-section"><span>Conta</span></div>

        <a href="#" class="nav-item nav-item-danger"
           onclick="event.preventDefault(); document.getElementById('logout-empresa-form').submit();">
            <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
            <span class="nav-label">Sair</span>
        </a>

        <form id="logout-empresa-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>

    </nav>

    {{-- RODAPÉ --}}
    <div class="sidebar-footer">
        <div class="sidebar-footer-text">
            <i class="fas fa-shield-alt me-1"></i>
            Portal Alumni Track
        </div>
    </div>

</aside>
