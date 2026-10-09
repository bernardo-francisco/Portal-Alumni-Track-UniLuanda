{{-- ============================================================
     SIDEBAR — ADMIN
     Notificações → sino do topbar
     Mensagens    → ícone do topbar
============================================================ --}}
<aside class="admin-sidebar" id="adminSidebar">

    <!-- LOGO DA UNIVERSIDADE -->
    <div class="sidebar-brand">
        <img src="{{ asset('uploads/logos/Universidade-Logotipo.png') }}"
             alt="Universidade de Luanda">
    </div>

    <!-- NAVEGAÇÃO -->
    <nav class="sidebar-nav">

        <!-- ADMINISTRAÇÃO -->
        <div class="nav-section">
            <span>Administração</span>
        </div>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-chart-pie"></i></span>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="{{ route('admin.validacao.index') }}"
           class="nav-item {{ request()->routeIs('admin.validacao.*') ? 'active' : '' }}">
            <span class="nav-icon icon-warning"><i class="fas fa-check-circle"></i></span>
            <span class="nav-label">Validação de Egressos</span>
        </a>

        <a href="{{ route('admin.egressos.index') }}"
           class="nav-item {{ request()->routeIs('admin.egressos.*') ? 'active' : '' }}">
            <span class="nav-icon icon-info"><i class="fas fa-users"></i></span>
            <span class="nav-label">Egressos</span>
        </a>

        {{-- ❌ Badge removida: já notificado no sino --}}
        <a href="{{ route('admin.empresas.index') }}"
           class="nav-item {{ request()->routeIs('admin.empresas.*') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-building"></i></span>
            <span class="nav-label">Empresas</span>
        </a>

        <a href="{{ route('admin.unidades.index') }}"
           class="nav-item {{ request()->routeIs('admin.unidades.*') ? 'active' : '' }}">
            <span class="nav-icon icon-secondary"><i class="fas fa-building"></i></span>
            <span class="nav-label">Unidades</span>
        </a>

        <a href="{{ route('admin.cursos.index') }}"
           class="nav-item {{ request()->routeIs('admin.cursos.*') ? 'active' : '' }}">
            <span class="nav-icon icon-secondary"><i class="fas fa-book"></i></span>
            <span class="nav-label">Cursos</span>
        </a>

        <a href="{{ route('admin.oportunidades.index') }}"
           class="nav-item {{ request()->routeIs('admin.oportunidades.*') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-briefcase"></i></span>
            <span class="nav-label">Oportunidades</span>
        </a>

        {{-- ❌ Badge removida: já notificado no sino --}}
        <a href="{{ route('admin.candidaturas.index') }}"
           class="nav-item {{ request()->routeIs('admin.candidaturas.*') ? 'active' : '' }}">
            <span class="nav-icon icon-success"><i class="fas fa-clipboard-list"></i></span>
            <span class="nav-label">Candidaturas</span>
        </a>

        <a href="{{ route('admin.eventos.index') }}"
           class="nav-item {{ request()->routeIs('admin.eventos.*') ? 'active' : '' }}">
            <span class="nav-icon icon-warning"><i class="fas fa-calendar"></i></span>
            <span class="nav-label">Eventos</span>
        </a>

        <!-- COMUNICAÇÃO -->
        <div class="nav-section">
            <span>Comunicação</span>
        </div>

        <a href="{{ route('admin.mural.index') }}"
           class="nav-item {{ request()->routeIs('admin.mural.*') ? 'active' : '' }}">
            <span class="nav-icon icon-info"><i class="fas fa-newspaper"></i></span>
            <span class="nav-label">Mural de Notícias</span>
        </a>

        <a href="{{ route('admin.feed.index') }}"
           class="nav-item {{ request()->routeIs('admin.feed.*') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-rss"></i></span>
            <span class="nav-label">Feed de Publicações</span>
        </a>

        <!-- PESQUISAS -->
        <div class="nav-section">
            <span>Pesquisas</span>
        </div>

        <a href="{{ route('admin.pesquisas.index') }}"
           class="nav-item {{ request()->routeIs('admin.pesquisas.*') ? 'active' : '' }}">
            <span class="nav-icon icon-warning"><i class="fas fa-poll"></i></span>
            <span class="nav-label">Pesquisas com Egressos</span>
        </a>

        <!-- FEEDBACK -->
        <div class="nav-section">
            <span>Feedback</span>
        </div>

        <a href="{{ route('admin.feedbacks.index') }}"
           class="nav-item {{ request()->routeIs('admin.feedbacks.*') ? 'active' : '' }}">
            <span class="nav-icon icon-success"><i class="fas fa-comment-dots"></i></span>
            <span class="nav-label">Feedbacks dos Egressos</span>
        </a>

        <!-- SERVIÇOS -->
        <div class="nav-section">
            <span>Serviços</span>
        </div>

        <a href="{{ route('admin.servicos.index') }}"
           class="nav-item {{ request()->routeIs('admin.servicos.*') ? 'active' : '' }}">
            <span class="nav-icon icon-success"><i class="fas fa-concierge-bell"></i></span>
            <span class="nav-label">Pedidos de Serviços</span>
        </a>

        <!-- LOCALIZAÇÃO -->
        <div class="nav-section">
            <span>Localização</span>
        </div>

        <a href="{{ route('admin.mapa') }}"
           class="nav-item {{ request()->routeIs('admin.mapa') ? 'active' : '' }}">
            <span class="nav-icon icon-danger"><i class="fas fa-map-marked-alt"></i></span>
            <span class="nav-label">Mapa de Egressos</span>
        </a>

        <!-- REDE -->
        <div class="nav-section">
            <span>Rede</span>
        </div>

        <a href="{{ route('admin.rede.index') }}"
           class="nav-item {{ request()->routeIs('admin.rede.*') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-handshake"></i></span>
            <span class="nav-label">Rede de Contactos</span>
        </a>

        {{-- ❌ Sem badge: já está no topbar (ícone de envelope) --}}
        <a href="{{ route('admin.mensagens.index') }}"
           class="nav-item {{ request()->routeIs('admin.mensagens.*') ? 'active' : '' }}">
            <span class="nav-icon icon-info"><i class="fas fa-envelope"></i></span>
            <span class="nav-label">Mensagens</span>
        </a>

        <a href="{{ route('admin.contactos.index') }}"
           class="nav-item {{ request()->routeIs('admin.contactos.*') ? 'active' : '' }}">
            <span class="nav-icon icon-warning"><i class="fas fa-envelope-open-text"></i></span>
            <span class="nav-label">Mensagens de Apoio</span>
        </a>

        <!-- RELATÓRIOS -->
        <div class="nav-section">
            <span>Relatórios</span>
        </div>

        <a href="{{ route('admin.relatorios.index') }}"
           class="nav-item {{ request()->routeIs('admin.relatorios') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-file-alt"></i></span>
            <span class="nav-label">Relatórios</span>
        </a>

    </nav>

</aside>

