@extends('layouts.admin')

@section('title', 'Detalhes da Candidatura')

@section('page_title', 'Detalhes da Candidatura')
@section('page_subtitle', $candidatura->oportunidade->titulo ?? '')

@section('content')

@php
    $egresso = $candidatura->egresso;
    $nomeEgresso = $egresso?->nome_completo ?? 'Egresso';

    $iniciais = collect(explode(' ', trim($nomeEgresso)))
        ->filter()
        ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->take(2)
        ->implode('');

    $cores = [
        'pendente'   => ['bg' => 'warning', 'label' => 'Pendente',   'icon' => 'clock'],
        'em_analise' => ['bg' => 'info',    'label' => 'Em Análise', 'icon' => 'search'],
        'entrevista' => ['bg' => 'primary', 'label' => 'Entrevista', 'icon' => 'user-tie'],
        'aprovado'   => ['bg' => 'success', 'label' => 'Aprovado',   'icon' => 'check-circle'],
        'rejeitado'  => ['bg' => 'danger',  'label' => 'Rejeitado',  'icon' => 'times-circle'],
    ];

    $cor = $cores[$candidatura->status] ?? ['bg' => 'secondary', 'label' => $candidatura->status, 'icon' => 'circle'];

    // ✅ Verificações úteis
    $temEntrevista  = !empty($candidatura->data_entrevista);
    $podeMarcar     = $candidatura->status !== 'rejeitado';
@endphp


<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">



        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <div class="card border-0 shadow-sm detail-card mb-4">

            <div class="detail-header header-{{ $cor['bg'] }}">

                <div class="detail-header-content">

                    <div class="d-flex align-items-center gap-3 flex-grow-1">

                        <div class="service-big-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>

                        <div>

                            <h3 class="mb-1 fw-bold text-white">
                                {{ $candidatura->oportunidade->titulo ?? 'Oportunidade' }}
                            </h3>

                            <p class="mb-0 text-white-50 small">
                                <i class="far fa-calendar me-1"></i>
                                Candidatura em
                                {{ $candidatura->created_at->format('d/m/Y \à\s H:i') }}
                            </p>

                        </div>

                    </div>


                    <span class="status-pill">
                        <i class="fas fa-{{ $cor['icon'] }}"></i>
                        {{ $cor['label'] }}
                    </span>

                </div>

            </div>


            <div class="card-body p-4 p-lg-5">

                {{-- =====================================================
                     CANDIDATO
                ====================================================== --}}
                <section class="detail-section">

                    <h6 class="section-title">
                        <i class="fas fa-user-circle"></i>
                        Candidato
                    </h6>


                    <div class="egresso-card">

                        <div class="egresso-avatar-wrap">

                            @if($egresso?->foto_url)

                                <img
                                    src="{{ asset($egresso->foto_url) }}"
                                    class="egresso-avatar"
                                    alt="{{ $nomeEgresso }}"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                >

                                <div
                                    class="egresso-avatar-fallback"
                                    style="display:none;"
                                >
                                    {{ $iniciais }}
                                </div>

                            @else

                                <div class="egresso-avatar-fallback">
                                    {{ $iniciais }}
                                </div>

                            @endif

                        </div>


                        <div class="egresso-info">

                            <h5 class="mb-1 fw-bold">
                                {{ $nomeEgresso }}
                            </h5>


                            <div class="egresso-contacts">

                                @if($egresso?->email)

                                    <a
                                        href="mailto:{{ $egresso->email }}"
                                        class="contact-chip"
                                    >
                                        <i class="fas fa-envelope"></i>
                                        <span>{{ $egresso->email }}</span>
                                    </a>

                                @endif


                                @if($egresso?->telefone)

                                    <a
                                        href="tel:{{ $egresso->telefone }}"
                                        class="contact-chip"
                                    >
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $egresso->telefone }}</span>
                                    </a>

                                @endif


                                @if($egresso?->curso)

                                    <span class="contact-chip">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span>{{ $egresso->curso->nome }}</span>
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =====================================================
     ENTREVISTA MARCADA
     ✅ Aparece se existir data, independentemente do status
====================================================== --}}
@if($temEntrevista)

    <section class="detail-section">

        <h6 class="section-title">
            <i class="fas fa-calendar-check"></i>
            Entrevista Marcada
        </h6>


        <div class="entrevista-destaque">

            <div class="entrevista-destaque-header">

                <div class="entrevista-destaque-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>

                <div>
                    <h6 class="mb-0 fw-bold text-white">
                        Entrevista Agendada
                    </h6>
                    <small class="text-white-50">
                        O candidato foi notificado
                    </small>
                </div>

            </div>


            <div class="entrevista-destaque-body">

                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="entrevista-tile">
                            <div class="entrevista-tile-icon">
                                <i class="far fa-calendar"></i>
                            </div>
                            <div>
                                <small>Data e Hora</small>
                                <strong>
                                    {{ $candidatura->data_entrevista->format('d/m/Y \à\s H:i') }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="entrevista-tile">
                            <div class="entrevista-tile-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <small>Local</small>
                                <strong>
                                    {{ $candidatura->local_entrevista ?? 'A confirmar' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ✅ ESTADO DA CONFIRMAÇÃO --}}
                <div class="mt-3">
                    @if($candidatura->confirmado_pelo_egresso)
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle py-2 px-3">
                            <i class="fas fa-check-circle me-1"></i>
                            Egresso confirmou presença
                            @if($candidatura->confirmado_em)
                                <span class="ms-1">
                                    ({{ $candidatura->confirmado_em->format('d/m/Y \à\s H:i') }})
                                </span>
                            @endif
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-2 px-3">
                            <i class="fas fa-hourglass-half me-1"></i>
                            A aguardar confirmação do egresso
                        </span>
                    @endif
                </div>

                {{-- Botão para reagendar --}}
                @if($podeMarcar)
                    <div class="mt-3">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            id="btnAbrirEntrevistaReagendar"
                        >
                            <i class="fas fa-redo me-1"></i>
                            Reagendar Entrevista
                        </button>
                    </div>
                @endif

            </div>

        </div>

    </section>

@endif


                {{-- =====================================================
                     MENSAGEM MOTIVACIONAL
                ====================================================== --}}
                @if($candidatura->mensagem_motivacional)

                    <section class="detail-section">

                        <h6 class="section-title">
                            <i class="fas fa-comment-alt"></i>
                            Mensagem Motivacional
                        </h6>


                        <div class="description-box">

                            <i class="fas fa-quote-left quote-icon"></i>

                            <p class="mb-0">
                                {!! nl2br(e($candidatura->mensagem_motivacional)) !!}
                            </p>

                        </div>

                    </section>

                @endif


                {{-- =====================================================
                     CURRÍCULO
                ====================================================== --}}
                @if($candidatura->cv_anexo)

                    <section class="detail-section">

                        <h6 class="section-title">
                            <i class="fas fa-file-pdf"></i>
                            Currículo
                        </h6>


                        <a
                            href="{{ asset('storage/' . $candidatura->cv_anexo) }}"
                            target="_blank"
                            class="btn btn-outline-primary"
                        >
                            <i class="fas fa-download me-2"></i>
                            Descarregar CV
                        </a>

                    </section>

                @endif


                {{-- =====================================================
                     AÇÕES
                ====================================================== --}}
                <section class="detail-section">

                    <h6 class="section-title">
                        <i class="fas fa-tasks"></i>
                        Ações
                    </h6>


                    <div class="row g-3">

                        {{-- MUDAR STATUS --}}
                        <div class="col-md-6">

                            <form
                                action="{{ route('admin.candidaturas.status', $candidatura->id) }}"
                                method="POST"
                            >

                                @csrf
                                @method('PUT')


                                <label class="form-label small fw-bold">
                                    Mudar Status
                                </label>


                                <div class="input-group">

                                    <select
                                        name="status"
                                        class="form-select"
                                    >

                                        <option value="pendente" {{ $candidatura->status === 'pendente' ? 'selected' : '' }}>
                                            Pendente
                                        </option>

                                        <option value="em_analise" {{ $candidatura->status === 'em_analise' ? 'selected' : '' }}>
                                            Em Análise
                                        </option>

                                        {{-- ✅ Opção 'entrevista' aparece apenas se já houver data marcada --}}
                                        @if($temEntrevista)
                                            <option value="entrevista" {{ $candidatura->status === 'entrevista' ? 'selected' : '' }}>
                                                Entrevista
                                            </option>
                                        @else
                                            <option value="entrevista" disabled>
                                                Entrevista (marca primeiro)
                                            </option>
                                        @endif

                                        <option value="aprovado" {{ $candidatura->status === 'aprovado' ? 'selected' : '' }}>
                                            Aprovado
                                        </option>

                                        <option value="rejeitado" {{ $candidatura->status === 'rejeitado' ? 'selected' : '' }}>
                                            Rejeitado
                                        </option>

                                    </select>


                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        <i class="fas fa-save"></i>
                                    </button>

                                </div>

                            </form>

                        </div>


                        {{-- MARCAR / REAGENDAR ENTREVISTA --}}
                        @if($podeMarcar && !$temEntrevista)

                            <div class="col-md-6">

                                <label class="form-label small fw-bold">
                                    Entrevista
                                </label>

                                <button
                                    type="button"
                                    class="btn btn-outline-primary w-100"
                                    id="btnAbrirEntrevista"
                                >
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    Marcar Entrevista
                                </button>

                            </div>

                        @endif


                        {{-- REJEITAR --}}
                        @if(
                            $candidatura->status !== 'rejeitado' &&
                            $candidatura->status !== 'aprovado'
                        )

                            <div class="col-md-6">

                                <button
                                    type="button"
                                    class="btn btn-outline-danger w-100"
                                    id="btnAbrirRejeicao"
                                >
                                    <i class="fas fa-times me-2"></i>
                                    Rejeitar Candidatura
                                </button>

                            </div>

                        @endif

                    </div>

                </section>


                {{-- =====================================================
                     MOTIVO DA REJEIÇÃO
                ====================================================== --}}
                @if($candidatura->motivo_rejeicao)

                    <section class="detail-section">

                        <h6 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Motivo da Rejeição
                        </h6>


                        <div class="alert alert-danger">
                            {!! nl2br(e($candidatura->motivo_rejeicao)) !!}
                        </div>

                    </section>

                @endif

            </div>

        </div>

    </div>
</div>



{{-- ================================================================
     MODAL ENTREVISTA
================================================================ --}}
<div
    id="modalEntrevistaCustom"
    class="custom-modal"
    aria-hidden="true"
>

    <div
        class="custom-modal-backdrop"
        id="entrevistaBackdrop"
    ></div>


    <div
        class="custom-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalEntrevistaTitulo"
    >

        <form
            action="{{ route('admin.candidaturas.entrevista', $candidatura->id) }}"
            method="POST"
            id="formEntrevista"
        >

            @csrf
            @method('PUT')


            <div class="custom-modal-header">

                <div class="custom-modal-title">

                    <div class="custom-modal-icon icon-primary">
                        <i class="fas fa-calendar-plus"></i>
                    </div>

                    <h5 id="modalEntrevistaTitulo">
                        {{ $temEntrevista ? 'Reagendar Entrevista' : 'Marcar Entrevista' }}
                    </h5>

                </div>

                <button
                    type="button"
                    class="custom-modal-close"
                    id="btnFecharEntrevista"
                    aria-label="Fechar"
                >
                    <i class="fas fa-times"></i>
                </button>

            </div>


            <div class="custom-modal-body">

                <p class="custom-modal-description">
                    Preenche os dados da entrevista. O candidato será notificado.
                </p>


                <div class="custom-modal-field">

                    <label for="data_entrevista_input">
                        Data e Hora <span>*</span>
                    </label>

                    <input
                        type="datetime-local"
                        id="data_entrevista_input"
                        name="data_entrevista"
                        required
                        value="{{ $candidatura->data_entrevista ? $candidatura->data_entrevista->format('Y-m-d\TH:i') : '' }}"
                        min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                    >

                </div>


                <div class="custom-modal-field">

                    <label for="local_entrevista_input">
                        Local <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="local_entrevista_input"
                        name="local_entrevista"
                        placeholder="Ex: Escritório Central, Sala 3"
                        value="{{ $candidatura->local_entrevista }}"
                        required
                    >

                </div>

            </div>


            <div class="custom-modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    id="btnCancelarEntrevista"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-check me-1"></i>
                    {{ $temEntrevista ? 'Reagendar' : 'Marcar' }}
                </button>

            </div>

        </form>

    </div>

</div>



{{-- ================================================================
     MODAL REJEIÇÃO
================================================================ --}}
<div
    id="modalRejeitarCustom"
    class="custom-modal"
    aria-hidden="true"
>

    <div
        class="custom-modal-backdrop"
        id="rejectionBackdrop"
    ></div>


    <div
        class="custom-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalRejeitarTitulo"
    >

        <form
            action="{{ route('admin.candidaturas.rejeitar', $candidatura->id) }}"
            method="POST"
            id="formRejeitar"
        >

            @csrf
            @method('PUT')


            <div class="custom-modal-header">

                <div class="custom-modal-title">

                    <div class="custom-modal-icon icon-danger">
                        <i class="fas fa-times"></i>
                    </div>

                    <h5 id="modalRejeitarTitulo">
                        Rejeitar Candidatura
                    </h5>

                </div>

                <button
                    type="button"
                    class="custom-modal-close"
                    id="btnFecharRejeicao"
                    aria-label="Fechar"
                >
                    <i class="fas fa-times"></i>
                </button>

            </div>


            <div class="custom-modal-body">

                <p class="custom-modal-description">
                    O candidato será notificado com o motivo.
                </p>


                <div class="custom-modal-field">

                    <label for="motivo_rejeicao_input">
                        Motivo da Rejeição <span>*</span>
                    </label>

                    <textarea
                        id="motivo_rejeicao_input"
                        name="motivo_rejeicao"
                        rows="5"
                        placeholder="Explica brevemente o motivo..."
                        required
                    ></textarea>

                    <div
                        id="motivoErro"
                        class="custom-modal-error"
                    >
                        Por favor, informe o motivo da rejeição.
                    </div>

                </div>

            </div>


            <div class="custom-modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    id="btnCancelarRejeicao"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="btn btn-danger"
                    id="btnConfirmarRejeicao"
                >
                    <i class="fas fa-times me-1"></i>
                    Rejeitar
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
