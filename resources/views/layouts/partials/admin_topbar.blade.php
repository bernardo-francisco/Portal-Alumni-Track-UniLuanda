<!-- =========================
     TOPBAR ADMINISTRATIVA
========================= -->
<header class="navbar navbar-expand-lg bg-white border-bottom shadow-sm admin-topbar py-2">

    <div class="container-fluid px-3 px-lg-4">

        <!-- LADO ESQUERDO -->
        <div class="d-flex align-items-center gap-3">

            {{-- ✅ BOTÃO HAMBURGER ÚNICO — funciona em desktop E mobile --}}
            <button type="button"
        class="btn btn-light border rounded-circle p-2"
        onclick="toggleSidebar()"
        aria-label="Abrir/fechar menu"
        title="Abrir/fechar menu"
        id="btnToggleSidebar"
        style="width: 40px; height: 40px;">
    <i class="fas fa-bars"></i>
</button>

            <div class="lh-sm">
                <h1 class="h5 fw-semibold text-dark mb-0">Dashboard</h1>
                <small class="text-secondary d-none d-sm-block">Bem-vindo à área administrativa</small>
            </div>

        </div>

        <!-- LADO DIREITO -->
        <div class="d-flex align-items-center gap-2 gap-md-3">

            <!-- PESQUISA -->
            <div class="position-relative flex-grow-1 d-none d-md-block" style="max-width: 420px;">
                <form action="" method="GET">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"
                              style="border-radius: 50px 0 0 50px; border-color: #dee2e6; padding: 0.5rem 1rem;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                               name="q"
                               class="form-control bg-light border-start-0"
                               placeholder="Pesquisar egressos, oportunidades..."
                               style="border-radius: 0 50px 50px 0; border-color: #dee2e6;
                                      font-size: 0.875rem; padding: 0.5rem 1rem; height: 42px;"
                               value="{{ request('q') }}">
                    </div>
                </form>
            </div>

            <button type="button"
                    class="btn btn-light border rounded-circle p-2 d-md-none"
                    style="width: 38px; height: 38px;"
                    data-bs-toggle="collapse"
                    data-bs-target="#mobileSearch"
                    aria-expanded="false"
                    aria-controls="mobileSearch"
                    title="Pesquisar">
                <i class="fas fa-search text-muted"></i>
            </button>

            @php
                $user = Auth::user();
                $adminEgressoTopbar = $user ? $user->egresso : null;
                $adminRecord = $user ? \App\Models\Admin::where('user_id', $user->id)->first() : null;

                $totalNotificacoesSino = 0;
                $notificacoesSino = collect();

                if ($adminRecord) {
                    $totalNotificacoesSino = \App\Models\Notificacao::where('admin_id', $adminRecord->id)
                        ->where('lida', false)
                        ->whereNotIn('tipo', ['mensagem', 'audio', 'ficheiro'])
                        ->count();

                    $notificacoesSino = \App\Models\Notificacao::where('admin_id', $adminRecord->id)
                        ->where('lida', false)
                        ->whereNotIn('tipo', ['mensagem', 'audio', 'ficheiro'])
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                }

                $totalMensagensNaoLidas = 0;
                if ($adminEgressoTopbar) {
                    $totalMensagensNaoLidas = \App\Models\Mensagem::where('destinatario_id', $adminEgressoTopbar->id)
                        ->where('lida', false)
                        ->count();
                }

                $conversasTopbar = collect();
                $coresAvatarTopbar = [
                    'linear-gradient(135deg, #1a56db, #3b82f6)',
                    'linear-gradient(135deg, #22c55e, #16a34a)',
                    'linear-gradient(135deg, #f59e0b, #d97706)',
                    'linear-gradient(135deg, #a855f7, #7e22ce)',
                    'linear-gradient(135deg, #ec4899, #db2777)',
                ];

                if ($adminEgressoTopbar) {
                    $ultimasConversasTopbar = \App\Models\Mensagem::where('remetente_id', $adminEgressoTopbar->id)
                        ->orWhere('destinatario_id', $adminEgressoTopbar->id)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->groupBy(function ($msg) use ($adminEgressoTopbar) {
                            return $msg->remetente_id == $adminEgressoTopbar->id
                                ? $msg->destinatario_id
                                : $msg->remetente_id;
                        })
                        ->map(function ($msgs) { return $msgs->first(); })
                        ->values();

                    foreach ($ultimasConversasTopbar as $msg) {
                        $idContatoTopbar = $msg->remetente_id == $adminEgressoTopbar->id
                            ? $msg->destinatario_id
                            : $msg->remetente_id;

                        $contatoTopbar = \App\Models\Egresso::find($idContatoTopbar);

                        if ($contatoTopbar) {
                            $naoLidasTopbar = \App\Models\Mensagem::where('remetente_id', $idContatoTopbar)
                                ->where('destinatario_id', $adminEgressoTopbar->id)
                                ->where('lida', false)
                                ->count();

                            $conversasTopbar->push([
                                'contato'         => $contatoTopbar,
                                'ultima_mensagem' => $msg->tipo === 'audio'
                                    ? '🎤 Mensagem de voz'
                                    : ($msg->tipo === 'ficheiro'
                                        ? '📎 ' . ($msg->ficheiro_nome ?? 'Ficheiro')
                                        : ($msg->mensagem ?? '')),
                                'data_ultima'     => $msg->created_at,
                                'nao_lidas'       => $naoLidasTopbar,
                            ]);
                        }
                    }

                    $conversasTopbar = $conversasTopbar->sortByDesc('data_ultima')->take(5)->values();
                }
            @endphp

            <!-- SINO -->
            <div class="dropdown">
                <button type="button"
                        class="btn btn-light border-0 position-relative rounded-circle p-2"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        style="width: 40px; height: 40px;"
                        title="Notificações">

                    <i class="fas fa-bell text-warning fs-5"></i>

                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          id="badge-notificacoes"
                          data-total="{{ $totalNotificacoesSino }}"
                          style="font-size: 0.6rem; padding: 3px 7px; border: 2px solid white; {{ $totalNotificacoesSino > 0 ? '' : 'display: none;' }}">
                        {{ $totalNotificacoesSino }}
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0"
                    style="min-width: 340px; max-height: 420px; overflow-y: auto; border-radius: 16px;">

                    <li class="dropdown-header bg-light py-3 px-4 fw-bold text-muted d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-bell me-2"></i> Notificações</span>
                        <span class="badge bg-primary rounded-pill px-3">{{ $totalNotificacoesSino }} novas</span>
                    </li>

                    <li><hr class="dropdown-divider m-0"></li>

                    @if($notificacoesSino->count() > 0)
                        @foreach($notificacoesSino as $notif)
                            <li id="notif-item-{{ $notif->id }}">
                                <div class="dropdown-item py-3 px-4 border-bottom hover-bg-light d-flex align-items-start gap-3">
                                    <a href="{{ $notif->link ?? '#' }}"
                                       class="flex-grow-1 text-decoration-none text-dark d-flex gap-3"
                                       onclick="marcarNotifAdmin({{ $notif->id }})">
                                        <div class="bg-{{ $notif->tipo_cor ?? 'primary' }} bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center"
                                             style="width: 42px; height: 42px; flex-shrink: 0;">
                                            <i class="fas fa-{{ $notif->tipo_icone ?? 'bell' }} text-{{ $notif->tipo_cor ?? 'primary' }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong class="d-block">{{ $notif->titulo }}</strong>
                                            <span class="text-muted small">{{ $notif->mensagem }}</span>
                                            <div class="text-muted small mt-1">
                                                <i class="far fa-clock me-1"></i> {{ $notif->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </a>

                                    <button type="button"
                                            class="btn btn-sm btn-link text-muted p-0 flex-shrink-0"
                                            onclick="event.preventDefault(); event.stopPropagation(); marcarNotifAdmin({{ $notif->id }})"
                                            title="Marcar como lida">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    @else
                        <li>
                            <div class="text-center py-4 px-3">
                                <i class="fas fa-bell-slash fa-2x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Nenhuma notificação</p>
                                <small class="text-muted">Você está em dia!</small>
                            </div>
                        </li>
                    @endif

                    <li><hr class="dropdown-divider m-0"></li>
                    <li>
                        <a class="dropdown-item text-center fw-bold py-3 text-primary"
                           href="{{ route('admin.notificacoes.index') }}">
                            Ver todas as notificações
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- MENSAGENS -->
            <div class="dropdown">
                <button type="button"
                        class="btn btn-light border-0 position-relative rounded-circle p-2"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        style="width: 40px; height: 40px;"
                        title="Mensagens">

                    <i class="fas fa-envelope-open text-success fs-5"></i>

                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary"
                          id="badge-mensagens"
                          data-total="{{ $totalMensagensNaoLidas }}"
                          style="font-size: 0.6rem; padding: 3px 7px; border: 2px solid white; {{ $totalMensagensNaoLidas > 0 ? '' : 'display: none;' }}">
                        {{ $totalMensagensNaoLidas }}
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0"
                    style="min-width: 340px; max-height: 420px; overflow-y: auto; border-radius: 16px;">

                    <li class="dropdown-header bg-light py-3 px-4 fw-bold text-muted d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-envelope me-2"></i> Mensagens</span>
                        <span class="badge bg-primary rounded-pill px-3">{{ $totalMensagensNaoLidas }} não lidas</span>
                    </li>

                    <li><hr class="dropdown-divider m-0"></li>

                    @if($conversasTopbar->count() > 0)
                        @foreach($conversasTopbar as $index => $conversa)
                            @php
                                $contato = $conversa['contato'];
                                $iniciais = collect(explode(' ', trim($contato->nome_completo ?? 'Egresso')))
                                    ->filter()
                                    ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                                    ->take(2)
                                    ->implode('');
                                $corAvatar = $coresAvatarTopbar[$index % count($coresAvatarTopbar)];
                                $temFoto = !empty($contato->foto_url);
                                $fotoUrl = $temFoto ? asset($contato->foto_url) : null;
                            @endphp
                            <li>
                                <a class="dropdown-item py-3 px-4 border-bottom hover-bg-light"
                                   href="{{ route('admin.mensagens.conversa', $contato->id) }}">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="position-relative flex-shrink-0" style="width: 44px; height: 44px;">
                                            @if($temFoto)
                                                <img src="{{ $fotoUrl }}" class="rounded-circle border"
                                                     style="width: 44px; height: 44px; object-fit: cover;"
                                                     onerror="this.style.display='none'; document.getElementById('topbar-fallback-{{ $contato->id }}').style.display='flex';">
                                                <div id="topbar-fallback-{{ $contato->id }}"
                                                     class="rounded-circle align-items-center justify-content-center text-white fw-bold"
                                                     style="width: 44px; height: 44px; background: {{ $corAvatar }}; font-size: 0.95rem; display: none;">
                                                    {{ $iniciais ?: '??' }}
                                                </div>
                                            @else
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                     style="width: 44px; height: 44px; background: {{ $corAvatar }}; font-size: 0.95rem;">
                                                    {{ $iniciais ?: '??' }}
                                                </div>
                                            @endif
                                            @if($conversa['nao_lidas'] > 0)
                                                <span class="position-absolute bottom-0 end-0 bg-primary rounded-circle border border-2 border-white"
                                                      style="width: 14px; height: 14px;"></span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <strong>{{ $contato->nome_completo ?? 'Egresso' }}</strong>
                                                <small class="text-muted flex-shrink-0 ms-2">{{ $conversa['data_ultima']->diffForHumans() }}</small>
                                            </div>
                                            <div class="text-muted small text-truncate">
                                                {{ $conversa['ultima_mensagem'] }}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach

                        <li><hr class="dropdown-divider m-0"></li>
                        <li>
                            <a class="dropdown-item text-center fw-bold py-3 text-primary"
                               href="{{ route('admin.mensagens.index') }}">
                                Ver todas as mensagens
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </li>
                    @else
                        <li>
                            <div class="text-center py-4 px-3">
                                <i class="fas fa-inbox fa-2x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Nenhuma mensagem</p>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="vr mx-1 d-none d-sm-block" style="height: 30px;"></div>

            <!-- UTILIZADOR -->
            @php
                $currentUser = Auth::user();
                $fotoAdmin = null;
                if ($currentUser) {
                    try {
                        $admin = \App\Models\Admin::where('user_id', $currentUser->id)->first();
                        if ($admin && $admin->foto_url) $fotoAdmin = $admin->foto_url;
                    } catch (\Exception $e) {}
                }
            @endphp

            <div class="dropdown">
                <button type="button"
                        class="btn btn-light border-0 dropdown-toggle d-flex align-items-center gap-2 p-1"
                        data-bs-toggle="dropdown"
                        data-bs-display="static"
                        aria-expanded="false"
                        style="border-radius: 50px;">
                    @if($fotoAdmin)
                        <img src="{{ asset($fotoAdmin) }}" class="rounded-circle border" width="36" height="36"
                             style="object-fit: cover; pointer-events: none;">
                    @elseif($currentUser && $currentUser->photo_url)
                        <img src="{{ asset($currentUser->photo_url) }}" class="rounded-circle border" width="36" height="36"
                             style="object-fit: cover; pointer-events: none;">
                    @else
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center border"
                             style="width:36px; height:36px; pointer-events: none;">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <span class="d-none d-md-flex flex-column text-start lh-sm" style="pointer-events: none;">
                        <span class="fw-semibold text-dark">{{ $currentUser->name ?? 'Administrador' }}</span>
                        <small class="text-secondary">Administrador</small>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" style="min-width: 240px; border-radius: 12px;">
                    <li>
                        <div class="dropdown-header pt-3">
                            <div class="fw-semibold text-dark">{{ $currentUser->name ?? 'Administrador' }}</div>
                            <small class="text-secondary">{{ $currentUser->email ?? '' }}</small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 px-4" href="{{ route('admin.perfil.index') }}"><i class="fas fa-user fa-fw me-2"></i> Meu Perfil</a></li>
                    <li><a class="dropdown-item py-2 px-4" href="{{ route('admin.perfil.edit') }}"><i class="fas fa-user-edit fa-fw me-2"></i> Editar Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 px-4" href="{{ route('admin.dashboard') }}"><i class="fas fa-chart-pie fa-fw me-2"></i> Dashboard</a></li>
                    <li><a class="dropdown-item py-2 px-4" href="{{ route('admin.notificacoes.index') }}"><i class="fas fa-bell fa-fw me-2"></i> Minhas Notificações</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger py-2 px-4" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt fa-fw me-2"></i> Sair
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <div class="collapse container-fluid px-3 px-lg-4 mt-2 d-md-none" id="mobileSearch">
        <form action="" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"
                      style="border-radius: 50px 0 0 50px; border-color: #dee2e6;">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="q" class="form-control bg-light border-start-0"
                       placeholder="Pesquisar..."
                       style="border-radius: 0 50px 50px 0; border-color: #dee2e6;"
                       value="{{ request('q') }}">
            </div>
        </form>
    </div>

</header>


