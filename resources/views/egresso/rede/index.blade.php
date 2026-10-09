@extends('layouts.egresso')

@section('title', 'Rede de Contactos')

@section('page_title', '🤝 Rede de Contactos')

@section('page_subtitle', 'Gerencie as suas conexões e solicitações')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | ESTATÍSTICAS
    |--------------------------------------------------------------------------
    */

    $totalSolicitacoes = isset($solicitacoes)
        ? $solicitacoes->count()
        : 0;

    $totalConexoes = isset($conexoes)
        ? $conexoes->count()
        : 0;

    $totalSugestoes = isset($sugestoes)
        ? $sugestoes->count()
        : 0;


    /*
    |--------------------------------------------------------------------------
    | EGRESO LOGADO
    |--------------------------------------------------------------------------
    */

    $egressoLogado = auth()->user()->egresso;


    /*
    |--------------------------------------------------------------------------
    | HELPER - INICIAIS
    |--------------------------------------------------------------------------
    */

    $getIniciais = function ($nome) {

        $nome = trim($nome ?? 'Egresso');

        if ($nome === '') {
            return 'EG';
        }

        $partes = preg_split(
            '/\s+/',
            $nome,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $iniciais = strtoupper(
            substr($partes[0] ?? 'E', 0, 1)
        );

        if (count($partes) > 1) {

            $ultimaParte = end($partes);

            $iniciais .= strtoupper(
                substr($ultimaParte, 0, 1)
            );
        }

        return $iniciais;
    };


    /*
    |--------------------------------------------------------------------------
    | HELPER - RESOLVER FOTO
    |--------------------------------------------------------------------------
    |
    | Prioridade:
    |
    | 1. Egresso.foto_url
    | 2. User.photo_url
    | 3. URL externa / Cloudflare
    | 4. Caminho public
    | 5. Storage
    |
    */

    $resolverFoto = function ($egresso) {

        if (!$egresso) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | 1. FOTO DO EGRESO
        |--------------------------------------------------------------------------
        */

        $fotoEgresso = null;

        try {

            $fotoEgresso = $egresso->getRawOriginal('foto_url');

        } catch (\Throwable $e) {

            $fotoEgresso = null;
        }


        if (!empty($fotoEgresso)) {

            /*
            | URL externa / Cloudflare
            */

            if (filter_var(
                $fotoEgresso,
                FILTER_VALIDATE_URL
            )) {

                return $fotoEgresso;
            }


            /*
            | public/
            */

            if (file_exists(
                public_path($fotoEgresso)
            )) {

                return asset($fotoEgresso);
            }


            /*
            | storage/app/public/
            */

            if (file_exists(
                storage_path(
                    'app/public/' . $fotoEgresso
                )
            )) {

                return asset(
                    'storage/' . $fotoEgresso
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 2. FOTO DO USER
        |--------------------------------------------------------------------------
        */

        if ($egresso->user) {

            $fotoUser = null;

            try {

                /*
                | IMPORTANTE:
                | getRawOriginal() evita que o accessor antigo
                | transforme uma URL Cloudflare em avatar padrão.
                */

                $fotoUser =
                    $egresso->user
                        ->getRawOriginal('photo_url');

            } catch (\Throwable $e) {

                $fotoUser = null;
            }


            if (!empty($fotoUser)) {

                /*
                |--------------------------------------------------------------------------
                | URL COMPLETA
                |--------------------------------------------------------------------------
                */

                if (filter_var(
                    $fotoUser,
                    FILTER_VALIDATE_URL
                )) {

                    return $fotoUser;
                }


                /*
                |--------------------------------------------------------------------------
                | public/...
                |--------------------------------------------------------------------------
                */

                if (file_exists(
                    public_path($fotoUser)
                )) {

                    return asset($fotoUser);
                }


                /*
                |--------------------------------------------------------------------------
                | uploads/users/...
                |--------------------------------------------------------------------------
                */

                $caminhoUploads = public_path(
                    'uploads/users/' .
                    basename($fotoUser)
                );

                if (file_exists($caminhoUploads)) {

                    return asset(
                        'uploads/users/' .
                        basename($fotoUser)
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | storage/app/public/...
                |--------------------------------------------------------------------------
                */

                if (file_exists(
                    storage_path(
                        'app/public/' . $fotoUser
                    )
                )) {

                    return asset(
                        'storage/' . $fotoUser
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | storage/app/public/users/...
                |--------------------------------------------------------------------------
                */

                $caminhoStorageUsers =
                    storage_path(
                        'app/public/users/' .
                        basename($fotoUser)
                    );

                if (file_exists(
                    $caminhoStorageUsers
                )) {

                    return asset(
                        'storage/users/' .
                        basename($fotoUser)
                    );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3. SEM FOTO
        |--------------------------------------------------------------------------
        */

        return null;
    };

@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}

<div class="row g-3 mb-4">

    <div class="col-lg-4 col-md-6">

        <div class="stat-card stat-card-primary">

            <div class="stat-icon-wrap">
                <i class="fas fa-handshake"></i>
            </div>

            <div class="stat-body">

                <div class="stat-value">
                    {{ $totalConexoes }}
                </div>

                <div class="stat-label">
                    Minhas Conexões
                </div>

                <small class="stat-desc">
                    Contactos ativos
                </small>

            </div>

        </div>

    </div>


    <div class="col-lg-4 col-md-6">

        <div class="stat-card stat-card-warning">

            <div class="stat-icon-wrap">
                <i class="fas fa-bell"></i>
            </div>

            <div class="stat-body">

                <div class="stat-value">
                    {{ $totalSolicitacoes }}
                </div>

                <div class="stat-label">
                    Solicitações Pendentes
                </div>

                <small class="stat-desc">
                    A aguardar resposta
                </small>

            </div>

        </div>

    </div>


    <div class="col-lg-4 col-md-6">

        <div class="stat-card stat-card-info">

            <div class="stat-icon-wrap">
                <i class="fas fa-user-plus"></i>
            </div>

            <div class="stat-body">

                <div class="stat-value">
                    {{ $totalSugestoes }}
                </div>

                <div class="stat-label">
                    Sugestões
                </div>

                <small class="stat-desc">
                    Egressos para conectar
                </small>

            </div>

        </div>

    </div>

</div>


{{-- ============================================================
     ALERTAS
============================================================ --}}

@if(session('success'))

    <div
        class="alert alert-success alert-dismissible fade show shadow-sm mb-4"
        role="alert"
    >

        <i class="fas fa-check-circle me-2"></i>

        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

@endif


@if(session('error'))

    <div
        class="alert alert-danger alert-dismissible fade show shadow-sm mb-4"
        role="alert"
    >

        <i class="fas fa-exclamation-circle me-2"></i>

        {{ session('error') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

@endif


{{-- ============================================================
     SOLICITAÇÕES PENDENTES
============================================================ --}}

@if($totalSolicitacoes > 0)

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-bottom py-3 px-4">

            <div class="d-flex align-items-center justify-content-between">

                <div class="d-flex align-items-center gap-2">

                    <div class="section-icon bg-warning bg-opacity-10 text-warning">

                        <i class="fas fa-bell"></i>

                    </div>

                    <div>

                        <h6 class="mb-0 fw-bold">
                            Solicitações Pendentes
                        </h6>

                        <small class="text-muted">
                            Egressos que querem conectar-se consigo
                        </small>

                    </div>

                </div>

                <span
                    class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3"
                >
                    {{ $totalSolicitacoes }}
                </span>

            </div>

        </div>


        <div class="card-body p-0">

            @foreach($solicitacoes as $solic)

                @php

                    $solicitante = $solic->solicitante;

                    if (!$solicitante) {
                        continue;
                    }

                    $nome =
                        $solicitante->nome_completo ??
                        'Egresso';

                    $curso =
                        $solicitante->curso->nome ??
                        'Sem curso';

                    $iniciais =
                        $getIniciais($nome);

                    $foto =
                        $resolverFoto($solicitante);

                @endphp


                <div
                    class="network-item d-flex align-items-center justify-content-between gap-2 {{ !$loop->last ? 'border-bottom' : '' }}"
                >

                    <div
                        class="d-flex align-items-center gap-3 flex-grow-1 min-width-0"
                    >

                        {{-- Avatar --}}

                        @if($foto)

                            <img
                                src="{{ $foto }}"
                                alt="{{ $nome }}"
                                class="rounded-circle border flex-shrink-0"
                                style="width:52px;height:52px;object-fit:cover;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >

                            <div
                                class="avatar-fallback rounded-circle align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                style="width:52px;height:52px;font-size:1.05rem;display:none;"
                            >
                                {{ $iniciais }}
                            </div>

                        @else

                            <div
                                class="avatar-fallback rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                style="width:52px;height:52px;font-size:1.05rem;"
                            >
                                {{ $iniciais }}
                            </div>

                        @endif


                        {{-- Info --}}

                        <div class="min-width-0">

                            <strong class="d-block text-truncate">
                                {{ $nome }}
                            </strong>

                            <small class="text-muted d-block text-truncate">

                                <i
                                    class="fas fa-graduation-cap me-1"
                                    style="font-size:0.7rem;"
                                ></i>

                                {{ $curso }}

                            </small>

                        </div>

                    </div>


                    {{-- Ações --}}

                    <div class="d-flex gap-2 flex-shrink-0">

                        <form
                            action="{{ route('egresso.rede.aceitar', $solic->id) }}"
                            method="POST"
                        >

                            @csrf

                            <button
                                type="submit"
                                class="btn btn-success btn-sm"
                                title="Aceitar"
                            >

                                <i class="fas fa-check me-1"></i>

                                <span class="d-none d-sm-inline">
                                    Aceitar
                                </span>

                            </button>

                        </form>


                        <form
                            action="{{ route('egresso.rede.recusar', $solic->id) }}"
                            method="POST"
                        >

                            @csrf

                            <button
                                type="submit"
                                class="btn btn-outline-danger btn-sm"
                                title="Recusar"
                                onclick="return confirm('Tem certeza que deseja recusar?');"
                            >

                                <i class="fas fa-times me-1"></i>

                                <span class="d-none d-sm-inline">
                                    Recusar
                                </span>

                            </button>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

@endif


{{-- ============================================================
     MINHAS CONEXÕES
============================================================ --}}

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white border-bottom py-3 px-4">

        <div class="d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-2">

                <div class="section-icon bg-success bg-opacity-10 text-success">

                    <i class="fas fa-handshake"></i>

                </div>

                <div>

                    <h6 class="mb-0 fw-bold">
                        Minhas Conexões
                    </h6>

                    <small class="text-muted">
                        Contactos ativos na rede
                    </small>

                </div>

            </div>

            <span
                class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill px-3"
            >
                {{ $totalConexoes }}
            </span>

        </div>

    </div>


    <div class="card-body p-0">

        @if($totalConexoes > 0)

            @foreach($conexoes as $con)

                @php

                    $contato =
                        ((int) $con->solicitante_id === (int) $egressoLogado->id)
                        ? $con->destinatario
                        : $con->solicitante;

                    if (!$contato) {
                        continue;
                    }

                    $nome =
                        $contato->nome_completo ??
                        'Egresso';

                    $curso =
                        $contato->curso->nome ??
                        'Sem curso associado';

                    $iniciais =
                        $getIniciais($nome);

                    /*
                    | FOTO:
                    | Egresso → User → Cloudflare
                    */

                    $foto =
                        $resolverFoto($contato);

                @endphp


                <div
                    class="network-item d-flex align-items-center justify-content-between gap-2 {{ !$loop->last ? 'border-bottom' : '' }}"
                >

                    <div
                        class="d-flex align-items-center gap-3 flex-grow-1 min-width-0"
                    >

                        {{-- Avatar --}}

                        <div class="position-relative flex-shrink-0">

                            @if($foto)

                                <img
                                    src="{{ $foto }}"
                                    alt="{{ $nome }}"
                                    class="rounded-circle border"
                                    style="width:52px;height:52px;object-fit:cover;"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                >

                                <div
                                    class="avatar-fallback rounded-circle align-items-center justify-content-center text-white fw-bold"
                                    style="width:52px;height:52px;font-size:1.05rem;display:none;"
                                >
                                    {{ $iniciais }}
                                </div>

                            @else

                                <div
                                    class="avatar-fallback rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                    style="width:52px;height:52px;font-size:1.05rem;"
                                >
                                    {{ $iniciais }}
                                </div>

                            @endif


                            <span
                                class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white"
                                style="width:14px;height:14px;"
                                title="Online"
                            ></span>

                        </div>


                        {{-- Info --}}

                        <div class="min-width-0">

                            <strong class="d-block text-truncate">
                                {{ $nome }}
                            </strong>

                            <small class="text-muted d-block text-truncate">

                                <i
                                    class="fas fa-graduation-cap me-1"
                                    style="font-size:0.7rem;"
                                ></i>

                                {{ $curso }}

                            </small>

                        </div>

                    </div>


                    {{-- Ação --}}

                    <div class="flex-shrink-0">

                        <a
                            href="{{ route('egresso.mensagens.conversa', $contato->id) }}"
                            class="btn btn-primary btn-sm"
                            title="Enviar mensagem"
                        >

                            <i class="fas fa-envelope me-1"></i>

                            <span class="d-none d-sm-inline">
                                Mensagem
                            </span>

                        </a>

                    </div>

                </div>

            @endforeach

        @else

            <div class="empty-state">

                <div class="empty-state-icon">

                    <i class="fas fa-handshake-slash"></i>

                </div>

                <h6 class="fw-bold mb-1">
                    Sem conexões na sua rede
                </h6>

                <p class="text-muted small mb-0">
                    Comece por enviar pedidos de conexão às sugestões abaixo.
                </p>

            </div>

        @endif

    </div>

</div>


{{-- ============================================================
     SUGESTÕES DE CONEXÃO
============================================================ --}}

@if($totalSugestoes > 0)

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-bottom py-3 px-4">

            <div class="d-flex align-items-center justify-content-between">

                <div class="d-flex align-items-center gap-2">

                    <div class="section-icon bg-primary bg-opacity-10 text-primary">

                        <i class="fas fa-user-plus"></i>

                    </div>

                    <div>

                        <h6 class="mb-0 fw-bold">
                            Sugestões de Conexão
                        </h6>

                        <small class="text-muted">
                            Egressos que talvez conheça
                        </small>

                    </div>

                </div>

                <span
                    class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill px-3"
                >
                    {{ $totalSugestoes }}
                </span>

            </div>

        </div>


        <div class="card-body p-0">

            @foreach($sugestoes as $sug)

                @php

                    $nome =
                        $sug->nome_completo ??
                        'Egresso';

                    $curso =
                        $sug->curso->nome ??
                        'Sem curso associado';

                    $iniciais =
                        $getIniciais($nome);

                    $foto =
                        $resolverFoto($sug);

                @endphp


                <div
                    class="network-item d-flex align-items-center justify-content-between gap-2 {{ !$loop->last ? 'border-bottom' : '' }}"
                >

                    <div
                        class="d-flex align-items-center gap-3 flex-grow-1 min-width-0"
                    >

                        {{-- Avatar --}}

                        @if($foto)

                            <img
                                src="{{ $foto }}"
                                alt="{{ $nome }}"
                                class="rounded-circle border flex-shrink-0"
                                style="width:52px;height:52px;object-fit:cover;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >

                            <div
                                class="avatar-fallback rounded-circle align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                style="width:52px;height:52px;font-size:1.05rem;display:none;"
                            >
                                {{ $iniciais }}
                            </div>

                        @else

                            <div
                                class="avatar-fallback rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                style="width:52px;height:52px;font-size:1.05rem;"
                            >
                                {{ $iniciais }}
                            </div>

                        @endif


                        {{-- Info --}}

                        <div class="min-width-0">

                            <strong class="d-block text-truncate">
                                {{ $nome }}
                            </strong>

                            <small class="text-muted d-block text-truncate">

                                <i
                                    class="fas fa-graduation-cap me-1"
                                    style="font-size:0.7rem;"
                                ></i>

                                {{ $curso }}

                            </small>

                        </div>

                    </div>


                    {{-- Ação --}}

                    <div class="flex-shrink-0">

                        <a
                            href="{{ route('egresso.rede.conectar', $sug->id) }}"
                            class="btn btn-outline-primary btn-sm"
                            title="Enviar pedido de conexão"
                            onclick="return confirm('Deseja enviar pedido de conexão para {{ addslashes($nome) }}?');"
                        >

                            <i class="fas fa-user-plus me-1"></i>

                            <span class="d-none d-sm-inline">
                                Conectar
                            </span>

                        </a>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

@elseif($totalConexoes > 0)

    <div class="card border-0 shadow-sm">

        <div class="card-body text-center py-5">

            <div class="empty-state-icon mb-3">

                <i class="fas fa-check-circle"></i>

            </div>

            <h6 class="fw-bold mb-1">
                Está em dia com a rede
            </h6>

            <p class="text-muted small mb-0">
                Não há novas sugestões de conexão de momento.
                Volte mais tarde.
            </p>

        </div>

    </div>

@endif

@endsection

