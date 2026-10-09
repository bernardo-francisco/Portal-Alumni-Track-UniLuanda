{{-- ============================================================
     SIDEBAR — EGRESSO
     Notificações → sino do topbar
     Mensagens    → ícone do topbar
     Contadores informativos → permanecem aqui
============================================================ --}}
<aside class="egresso-sidebar" id="egressoSidebar">

    {{-- LOGO --}}
    <div class="sidebar-brand">
        <img src="{{ asset('uploads/logos/Universidade-Logotipo.png') }}"
             alt="Universidade de Luanda">
    </div>

    {{-- NAVEGAÇÃO --}}
    <nav class="sidebar-nav">

        {{-- MINHA ÁREA --}}
        <div class="nav-section">
            <span>Minha Área</span>
        </div>

        <a href="{{ route('egresso.dashboard') }}"
           class="nav-item {{ request()->routeIs('egresso.dashboard') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-th-large"></i></span>
            <span class="nav-label">Dashboard</span>
        </a>


        {{-- SERVIÇOS --}}
        <div class="nav-section">
            <span>Serviços</span>
        </div>

        <a href="{{ route('egresso.servicos.index') }}"
           class="nav-item {{ request()->routeIs('egresso.servicos.index') ? 'active' : '' }}">
            <span class="nav-icon icon-success"><i class="fas fa-clipboard-list"></i></span>
            <span class="nav-label">Meus Pedidos</span>
        </a>

        <a href="{{ route('egresso.servicos.solicitar') }}"
           class="nav-item {{ request()->routeIs('egresso.servicos.solicitar') ? 'active' : '' }}">
            <span class="nav-icon icon-warning"><i class="fas fa-plus-circle"></i></span>
            <span class="nav-label">Solicitar Serviço</span>
        </a>

        {{-- COMUNICAÇÃO --}}
        <div class="nav-section">
            <span>Comunicação</span>
        </div>

        <a href="{{ route('egresso.mural.index') }}"
           class="nav-item {{ request()->routeIs('egresso.mural.*') ? 'active' : '' }}">
            <span class="nav-icon icon-info"><i class="fas fa-newspaper"></i></span>
            <span class="nav-label">Mural de Notícias</span>
        </a>

        <a href="{{ route('egresso.feed') }}"
           class="nav-item {{ request()->routeIs('egresso.feed') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-rss"></i></span>
            <span class="nav-label">Feed de Notícias</span>
        </a>

        {{-- PESQUISAS --}}
        <div class="nav-section">
            <span>Pesquisas</span>
        </div>

        <a href="{{ route('egresso.pesquisas.index') }}"
           class="nav-item {{ request()->routeIs('egresso.pesquisas.*') ? 'active' : '' }}">
            <span class="nav-icon icon-warning"><i class="fas fa-poll"></i></span>
            <span class="nav-label">Pesquisas</span>
        </a>

        {{-- FEEDBACK --}}
        <div class="nav-section">
            <span>Feedback</span>
        </div>

        <a href="{{ route('egresso.feedbacks.create') }}"
           class="nav-item {{ request()->routeIs('egresso.feedbacks.create') ? 'active' : '' }}">
            <span class="nav-icon icon-success"><i class="fas fa-comment-dots"></i></span>
            <span class="nav-label">Dar Feedback</span>
        </a>

        <a href="{{ route('egresso.feedbacks.index') }}"
           class="nav-item {{ request()->routeIs('egresso.feedbacks.index') ? 'active' : '' }}">
            <span class="nav-icon icon-secondary"><i class="fas fa-list"></i></span>
            <span class="nav-label">Meus Feedbacks</span>
        </a>

        {{-- LOCALIZAÇÃO --}}
        <div class="nav-section">
            <span>Localização</span>
        </div>

        <a href="{{ route('egresso.localizacao') }}"
           class="nav-item {{ request()->routeIs('egresso.localizacao*') ? 'active' : '' }}">
            <span class="nav-icon icon-danger"><i class="fas fa-map-marker-alt"></i></span>
            <span class="nav-label">Minha Localização</span>
        </a>

        {{-- ✅ Contador informativo --}}
        <a href="{{ route('egresso.mapa') }}"
           class="nav-item {{ request()->routeIs('egresso.mapa') ? 'active' : '' }}">
            <span class="nav-icon icon-info"><i class="fas fa-map-marked-alt"></i></span>
            <span class="nav-label">Mapa de Egressos</span>
            @if(isset($total_localizados) && $total_localizados > 0)
                <span class="nav-badge badge-success">{{ $total_localizados }}</span>
            @endif
        </a>

        {{-- OPORTUNIDADES --}}
        <div class="nav-section">
            <span>Oportunidades</span>
        </div>

        {{-- ✅ Contador informativo --}}
        <a href="{{ route('egresso.oportunidades') }}"
           class="nav-item {{ request()->routeIs('egresso.oportunidades') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-briefcase"></i></span>
            <span class="nav-label">Oportunidades</span>
            @if(isset($stats['oportunidades']) && $stats['oportunidades'] > 0)
                <span class="nav-badge badge-success">{{ $stats['oportunidades'] }}</span>
            @endif
        </a>

        <a href="{{ route('egresso.minhas.candidaturas') }}"
           class="nav-item {{ request()->routeIs('egresso.minhas.candidaturas') ? 'active' : '' }}">
            <span class="nav-icon icon-success"><i class="fas fa-file-signature"></i></span>
            <span class="nav-label">Minhas Candidaturas</span>
        </a>

        {{-- REDE E COMUNIDADE --}}
        <div class="nav-section">
            <span>Rede e Comunidade</span>
        </div>

        {{-- ✅ Contador informativo --}}
        <a href="{{ route('egresso.rede') }}"
           class="nav-item {{ request()->routeIs('egresso.rede') ? 'active' : '' }}">
            <span class="nav-icon icon-primary"><i class="fas fa-users"></i></span>
            <span class="nav-label">Rede de Contactos</span>
            @if(isset($stats['conexoes']) && $stats['conexoes'] > 0)
                <span class="nav-badge badge-primary">{{ $stats['conexoes'] }}</span>
            @endif
        </a>

        {{-- ❌ Sem badge: já está no topbar --}}
        <a href="{{ route('egresso.mensagens') }}"
           class="nav-item {{ request()->routeIs('egresso.mensagens*') ? 'active' : '' }}">
            <span class="nav-icon icon-info"><i class="fas fa-envelope"></i></span>
            <span class="nav-label">Mensagens</span>
        </a>

        {{-- ✅ Contador informativo --}}
        <a href="{{ route('egresso.eventos') }}"
           class="nav-item {{ request()->routeIs('egresso.eventos') ? 'active' : '' }}">
            <span class="nav-icon icon-warning"><i class="fas fa-calendar-alt"></i></span>
            <span class="nav-label">Eventos</span>
            @if(isset($stats['eventos']) && $stats['eventos'] > 0)
                <span class="nav-badge badge-info">{{ $stats['eventos'] }}</span>
            @endif
        </a>

        {{-- ✅ Contador informativo --}}
        <a href="{{ route('egresso.minhas.inscricoes') }}"
           class="nav-item {{ request()->routeIs('egresso.minhas.inscricoes*') ? 'active' : '' }}">
            <span class="nav-icon icon-warning"><i class="fas fa-ticket-alt"></i></span>
            <span class="nav-label">Minhas Inscrições</span>
            @php
                $totalInscricoes = \App\Models\InscricaoEvento::where('egresso_id', Auth::user()->egresso->id ?? 0)->count();
            @endphp
            @if($totalInscricoes > 0)
                <span class="nav-badge badge-warning">{{ $totalInscricoes }}</span>
            @endif
        </a>

        {{-- NOTIFICAÇÕES --}}
        <div class="nav-section">
            <span>Notificações</span>
        </div>

        {{-- ❌ Sem badge: já está no sino do topbar --}}
        <a href="{{ route('egresso.notificacoes.index') }}"
           class="nav-item {{ request()->routeIs('egresso.notificacoes.*') ? 'active' : '' }}">
            <span class="nav-icon icon-danger"><i class="fas fa-bell"></i></span>
            <span class="nav-label">Notificações</span>
        </a>

    </nav>

</aside>