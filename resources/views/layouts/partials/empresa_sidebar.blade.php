{{-- ============================================================
     SIDEBAR — EMPRESA (alinhado ao egresso)
============================================================ --}}
<aside class="empresa-sidebar" id="empSidebarPanel">

    {{-- LOGO --}}
    <div class="sidebar-brand">
        @php
            $empresaAtual = Auth::user()->empresa ?? null;
            $logoSidebar = $empresaAtual && $empresaAtual->logo_url
                ? asset($empresaAtual->logo_url)
                : asset('uploads/logos/Universidade-Logotipo.png');
        @endphp

        <img src="{{ $logoSidebar }}"
             alt="{{ $empresaAtual->nome ?? 'Universidade de Luanda' }}">
    </div>

    {{-- NAVEGAÇÃO --}}
    <nav class="sidebar-nav">

        {{-- PRINCIPAL --}}
        <div class="nav-section">
            <span>Principal</span>
        </div>

        <a href="{{ route('empresa.dashboard') }}"
           class="nav-item {{ request()->routeIs('empresa.dashboard') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-chart-pie"></i></span>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="{{ route('empresa.perfil') }}"
           class="nav-item {{ request()->routeIs('empresa.perfil*') ? 'active' : '' }}">
            <span class="nav-icon icon-info"><i class="fas fa-building"></i></span>
            <span class="nav-label">Perfil da Empresa</span>
        </a>

        {{-- GESTÃO --}}
        <div class="nav-section">
            <span>Gestão</span>
        </div>

        <a href="{{ route('empresa.oportunidades.index') }}"
           class="nav-item {{ request()->routeIs('empresa.oportunidades.*') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-briefcase"></i></span>
            <span class="nav-label">Oportunidades</span>
            @if(isset($totalOportunidades) && $totalOportunidades > 0)
                <span class="nav-badge badge-primary">{{ $totalOportunidades }}</span>
            @endif
        </a>

        <a href="{{ route('empresa.candidaturas.index') }}"
           class="nav-item {{ request()->routeIs('empresa.candidaturas.*') ? 'active' : '' }}">
            <span class="nav-icon icon-success"><i class="fas fa-file-signature"></i></span>
            <span class="nav-label">Candidaturas</span>
            @if(isset($totalCandidaturas) && $totalCandidaturas > 0)
                <span class="nav-badge badge-success">{{ $totalCandidaturas }}</span>
            @endif
        </a>

        {{-- CONTA --}}
        <div class="nav-section">
            <span>Conta</span>
        </div>

        <a href="#" class="nav-item nav-item-danger"
           onclick="event.preventDefault(); document.getElementById('logout-empresa-form').submit();">
            <span class="nav-icon icon-danger"><i class="fas fa-sign-out-alt"></i></span>
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