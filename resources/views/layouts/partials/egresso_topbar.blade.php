<!-- =========================
     TOPBAR EGRESSO
========================= -->
<header class="navbar navbar-expand-lg bg-white border-bottom shadow-sm admin-topbar egresso-topbar">

    <div class="container-fluid px-3 px-lg-4">

        <div class="d-flex align-items-center gap-3">
            <button type="button"
        class="btn btn-light border rounded-circle p-2"
        onclick="toggleSidebar()"
        aria-label="Abrir/fechar menu"
        title="Abrir/fechar menu"
        id="btnToggleSidebarEgresso"
        style="width: 40px; height: 40px;">
    <i class="fas fa-bars"></i>
</button>
            <div class="lh-sm">
                <h1 class="h5 fw-semibold text-dark mb-1">Dashboard</h1>
                <small class="text-secondary d-none d-sm-block">Bem-vindo à área do Egresso</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">

            <div class="position-relative flex-grow-1" style="max-width: 500px;">
                <form action="{{ route('egresso.pesquisar') }}" method="GET">
                    <div class="input-group input-group-sm w-100">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"
                              style="border-radius: 20px 0 0 20px; border-color: #dee2e6; padding: 0.5rem 1rem;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0"
                               placeholder="Pesquisar egressos, oportunidades, eventos..."
                               style="border-radius: 0 20px 20px 0; border-color: #dee2e6; background: #f8f9fa;
                                      font-size: 0.9rem; padding: 0.5rem 1rem; height: 42px;"
                               value="{{ request('q') }}">
                    </div>
                </form>
            </div>

            @php
                $user = Auth::user();
                $egressoLogado = $user ? $user->egresso : null;

                // SINO
                $totalNotificacoesSino = 0;
                $notificacoesSino = collect();

                if ($egressoLogado) {
                    $totalNotificacoesSino = \App\Models\Notificacao::where('egresso_id', $egressoLogado->id)
                        ->where('lida', false)
                        ->whereNotIn('tipo', ['mensagem', 'audio', 'ficheiro'])
                        ->count();

                    $notificacoesSino = \App\Models\Notificacao::where('egresso_id', $egressoLogado->id)
                        ->where('lida', false)
                        ->whereNotIn('tipo', ['mensagem', 'audio', 'ficheiro'])
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                }

                // MENSAGENS
                $totalMensagensNaoLidas = 0;
                if ($egressoLogado) {
                    $totalMensagensNaoLidas = \App\Models\Mensagem::where('destinatario_id', $egressoLogado->id)
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

                if ($egressoLogado) {
                    $ultimasConversasTopbar = \App\Models\Mensagem::where('remetente_id', $egressoLogado->id)
                        ->orWhere('destinatario_id', $egressoLogado->id)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->groupBy(function ($msg) use ($egressoLogado) {
                            return $msg->remetente_id == $egressoLogado->id
                                ? $msg->destinatario_id
                                : $msg->remetente_id;
                        })
                        ->map(function ($msgs) { return $msgs->first(); })
                        ->values();

                    foreach ($ultimasConversasTopbar as $msg) {
                        $idContatoTopbar = $msg->remetente_id == $egressoLogado->id
                            ? $msg->destinatario_id
                            : $msg->remetente_id;

                        $contatoTopbar = \App\Models\Egresso::find($idContatoTopbar);

                        if ($contatoTopbar) {
                            $naoLidasTopbar = \App\Models\Mensagem::where('remetente_id', $idContatoTopbar)
                                ->where('destinatario_id', $egressoLogado->id)
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
                        style="width: 38px; height: 38px;"
                        title="Notificações">

                    <span style="background: linear-gradient(135deg, #3b82f6, #1a56db); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        <i class="fas fa-bell fs-6"></i>
                    </span>

                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          id="badge-notificacoes"
                          data-total="{{ $totalNotificacoesSino }}"
                          style="font-size: 0.55rem; padding: 2px 6px; border: 2px solid white; {{ $totalNotificacoesSino > 0 ? '' : 'display: none;' }}">
                        {{ $totalNotificacoesSino }}
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-0"
                    style="min-width: 340px; max-height: 420px; overflow-y: auto; border-radius: 12px;">

                    <li class="dropdown-header bg-light py-2 px-3 fw-bold text-muted d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-bell me-2"></i> Notificações</span>
                        <span class="badge bg-primary">{{ $totalNotificacoesSino }} novas</span>
                    </li>

                    <li><hr class="dropdown-divider m-0"></li>

                    @if($notificacoesSino->count() > 0)
                        @foreach($notificacoesSino as $notif)
                            <li id="notif-item-{{ $notif->id }}">
                                <div class="dropdown-item py-3 px-3 border-bottom d-flex align-items-start gap-3">
                                    <a href="{{ $notif->link ?? '#' }}"
                                       class="flex-grow-1 text-decoration-none text-dark d-flex gap-3"
                                       onclick="marcarNotifEgresso({{ $notif->id }})">
                                        <div class="bg-{{ $notif->tipo_cor ?? 'primary' }} bg-opacity-10 rounded-circle p-2"
                                             style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-{{ $notif->tipo_icone ?? 'bell' }} text-{{ $notif->tipo_cor ?? 'primary' }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong class="d-block small">{{ $notif->titulo }}</strong>
                                            <div class="small text-muted">{{ $notif->mensagem }}</div>
                                            <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                                        </div>
                                    </a>

                                    <button type="button"
                                            class="btn btn-sm btn-link text-muted p-0 flex-shrink-0"
                                            onclick="event.preventDefault(); event.stopPropagation(); marcarNotifEgresso({{ $notif->id }})"
                                            title="Marcar como lida">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    @else
                        <li>
                            <div class="text-center py-4">
                                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Nenhuma notificação</p>
                            </div>
                        </li>
                    @endif

                    <li><hr class="dropdown-divider m-0"></li>
                    <li>
                        <a class="dropdown-item text-center fw-bold py-2 text-primary" href="{{ route('egresso.notificacoes.index') }}">
                            Ver todas as notificações
                            <i class="fas fa-arrow-right ms-1"></i>
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
                        style="width: 38px; height: 38px;"
                        title="Mensagens">

                    <span style="background: linear-gradient(135deg, #22c55e, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        <i class="fas fa-envelope-open fs-6"></i>
                    </span>

                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary"
                          id="badge-mensagens"
                          data-total="{{ $totalMensagensNaoLidas }}"
                          style="font-size: 0.55rem; padding: 2px 6px; border: 2px solid white; {{ $totalMensagensNaoLidas > 0 ? '' : 'display: none;' }}">
                        {{ $totalMensagensNaoLidas }}
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-0"
                    style="min-width: 340px; max-height: 420px; overflow-y: auto; border-radius: 12px;">

                    <li class="dropdown-header bg-light py-2 px-3 fw-bold text-muted d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-envelope me-2"></i> Mensagens</span>
                        <span class="badge bg-primary">{{ $totalMensagensNaoLidas }} não lidas</span>
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
                                <a class="dropdown-item py-3 px-3 border-bottom"
                                   href="{{ route('egresso.mensagens.conversa', $contato->id) }}">
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
                            <a class="dropdown-item text-center fw-bold py-2 text-primary" href="{{ route('egresso.mensagens') }}">
                                Ver todas as mensagens
                                <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </li>
                    @else
                        <li>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Nenhuma mensagem</p>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="vr mx-1 d-none d-sm-block"></div>

            <!-- UTILIZADOR -->
            @php
                $currentUser = Auth::user();
                $fotoEgresso = null;
                $tipoUsuario = 'Egresso';

                if ($currentUser) {
                    if ($currentUser->egresso) {
                        $fotoEgresso = $currentUser->egresso->foto_url ?? null;
                        $tipoUsuario = 'Egresso';
                    } elseif ($currentUser->admin) {
                        $fotoEgresso = $currentUser->admin->foto_url ?? null;
                        $tipoUsuario = 'Administrador';
                    }
                }
            @endphp

            <div class="dropdown">
                <button type="button"
                        class="btn btn-light border-0 dropdown-toggle d-flex align-items-center gap-2 p-1"
                        data-bs-toggle="dropdown"
                        data-bs-display="static"
                        aria-expanded="false"
                        style="border-radius: 50px;">
                    @if($fotoEgresso)
                        <img src="{{ asset($fotoEgresso) }}" class="rounded-circle border" width="36" height="36"
                             style="object-fit: cover; pointer-events: none;">
                    @else
                        <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center border"
                             style="width:36px; height:36px; pointer-events: none;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    @endif
                    <span class="d-none d-md-flex flex-column text-start lh-sm" style="pointer-events: none;">
                        <span class="fw-semibold text-dark">{{ $currentUser->name ?? 'Usuário' }}</span>
                        <small class="text-secondary">{{ $tipoUsuario }}</small>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="min-width: 230px; border-radius: 12px;">
                    <li>
                        <div class="dropdown-header pt-3">
                            <div class="fw-semibold text-dark">{{ $currentUser->name ?? 'Usuário' }}</div>
                            <small class="text-secondary">{{ $currentUser->email ?? '' }}</small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2" href="{{ route('egresso.perfil') }}"><i class="fas fa-user fa-fw me-2"></i> Meu Perfil</a></li>
                    <li><a class="dropdown-item py-2" href="{{ route('egresso.perfil.editar') }}"><i class="fas fa-user-edit fa-fw me-2"></i> Editar Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2" href="{{ route('egresso.dashboard') }}"><i class="fas fa-chart-pie fa-fw me-2"></i> Dashboard</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger py-2" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form-egresso-topbar').submit();">
                            <i class="fas fa-sign-out-alt fa-fw me-2"></i> Sair
                        </a>
                        <form id="logout-form-egresso-topbar" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</header>

