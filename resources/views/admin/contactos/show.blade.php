@extends('layouts.admin')

@section('title', 'Mensagem de ' . $contacto->nome)

@section('page_title', '📩 Detalhes da Mensagem')
@section('page_subtitle', 'Mensagem de ' . $contacto->nome)

@section('content')

@php
    $statusMap = [
        'novo'       => ['color' => 'primary',   'icon' => 'circle',        'label' => 'Novo'],
        'lido'       => ['color' => 'info',      'icon' => 'eye',           'label' => 'Lido'],
        'respondido' => ['color' => 'success',   'icon' => 'check-circle',  'label' => 'Respondido'],
        'arquivado'  => ['color' => 'secondary', 'icon' => 'archive',       'label' => 'Arquivado'],
    ];

    $statusInfo = $statusMap[$contacto->status]
        ?? ['color' => 'secondary', 'icon' => 'info-circle', 'label' => ucfirst($contacto->status)];
@endphp

<div class="row justify-content-center">
    <div class="col-lg-11 col-xl-10">

        {{-- ============================================
             CARD PRINCIPAL
        ============================================ --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">

            {{-- HEADER --}}
            <div class="detail-header header-{{ $statusInfo['color'] }}">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="service-big-icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1 fw-bold text-white">
                            {{ $contacto->nome }}
                        </h3>
                        <p class="mb-0 text-white-50 small">
                            <i class="fas fa-envelope me-1"></i>
                            {{ $contacto->email }}
                            <span class="mx-1">•</span>
                            <i class="far fa-clock me-1"></i>
                            {{ $contacto->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <span class="status-pill">
                        <i class="fas fa-{{ $statusInfo['icon'] }}"></i>
                        {{ $statusInfo['label'] }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4 p-lg-5">

                {{-- ============================================
                     DADOS DO REMETENTE
                ============================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-user"></i> Dados do Remetente
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-tile-icon"><i class="fas fa-user"></i></div>
                                <div class="info-tile-body">
                                    <small>Nome</small>
                                    <strong>{{ $contacto->nome }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-tile-icon"><i class="fas fa-envelope"></i></div>
                                <div class="info-tile-body">
                                    <small>Email</small>
                                    <strong>
                                        <a href="mailto:{{ $contacto->email }}" class="text-decoration-none">
                                            {{ $contacto->email }}
                                        </a>
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-tile-icon"><i class="far fa-calendar"></i></div>
                                <div class="info-tile-body">
                                    <small>Recebido em</small>
                                    <strong>{{ $contacto->created_at->format('d/m/Y \à\s H:i') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-tile-icon"><i class="fas fa-network-wired"></i></div>
                                <div class="info-tile-body">
                                    <small>Endereço IP</small>
                                    <strong>{{ $contacto->ip ?? 'Não registado' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ============================================
                     ASSUNTO
                ============================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-tag"></i> Assunto
                    </h6>
                    <div class="info-tile">
                        <div class="info-tile-icon"><i class="fas fa-tag"></i></div>
                        <div class="info-tile-body">
                            <strong>{{ $contacto->assunto }}</strong>
                        </div>
                    </div>
                </section>

                {{-- ============================================
                     MENSAGEM
                ============================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-comment-alt"></i> Mensagem
                    </h6>
                    <div class="description-box">
                        <i class="fas fa-quote-left quote-icon"></i>
                        <p class="mb-0">{!! nl2br(e($contacto->mensagem)) !!}</p>
                    </div>
                </section>

                {{-- ============================================
                     RESPOSTA (se já existir)
                ============================================ --}}
                @if($contacto->status === 'respondido')
                    <section class="detail-section">
                        <h6 class="section-title text-success">
                            <i class="fas fa-check-circle"></i> Resposta Enviada
                        </h6>

                        <div class="description-box" style="border-left-color: #16a34a;">
                            <i class="fas fa-reply quote-icon" style="color: #16a34a;"></i>
                            @if($contacto->resposta ?? null)
                                <p class="mb-0">{!! nl2br(e($contacto->resposta)) !!}</p>
                            @else
                                <p class="mb-0">
                                    <strong>Resposta enviada por email</strong><br>
                                    <small class="text-muted">Enviada para <strong>{{ $contacto->email }}</strong></small>
                                </p>
                            @endif
                        </div>

                        <div class="text-muted small mt-2">
                            <i class="far fa-clock me-1"></i>
                            Atualizado {{ $contacto->updated_at->diffForHumans() }}
                        </div>
                    </section>
                @endif

                {{-- ============================================
                     FORMULÁRIO DE RESPOSTA
                ============================================ --}}
                @if($contacto->status !== 'respondido')
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-reply"></i> Responder
                        </h6>

                        <form action="{{ route('admin.contactos.responder', $contacto->id) }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="resposta" class="form-label small fw-semibold text-muted">
                                    Resposta
                                </label>
                                <textarea name="resposta"
                                          id="resposta"
                                          class="form-control @error('resposta') is-invalid @enderror"
                                          rows="8"
                                          placeholder="Escreve a tua resposta com detalhes..."
                                          required
                                          style="font-size: .95rem; line-height: 1.7;">{{ old('resposta') }}</textarea>
                                @error('resposta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    A resposta será enviada automaticamente para <strong>{{ $contacto->email }}</strong>.
                                </small>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar Resposta
                                </button>
                                <a href="mailto:{{ $contacto->email }}?subject=Re: {{ $contacto->assunto }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-envelope me-1"></i> Abrir no Cliente de Email
                                </a>
                            </div>
                        </form>
                    </section>
                @endif

                {{-- ============================================
                     HISTÓRICO
                ============================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-history"></i> Histórico
                    </h6>

                    <div class="d-flex flex-column gap-3">
                        {{-- Recebida --}}
                        <div class="d-flex gap-3">
                            <div class="info-tile-icon text-primary">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <strong class="d-block small">Mensagem recebida</strong>
                                <small class="text-muted">{{ $contacto->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>

                        {{-- Lida --}}
                        @if(in_array($contacto->status, ['lido', 'respondido', 'arquivado']))
                            <div class="d-flex gap-3">
                                <div class="info-tile-icon text-info">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div>
                                    <strong class="d-block small">Marcada como lida</strong>
                                    <small class="text-muted">Visualizada pelo admin</small>
                                </div>
                            </div>
                        @endif

                        {{-- Respondida --}}
                        @if($contacto->status === 'respondido')
                            <div class="d-flex gap-3">
                                <div class="info-tile-icon text-success">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <strong class="d-block small">Resposta enviada</strong>
                                    <small class="text-muted">{{ $contacto->updated_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- ============================================
                     AÇÕES
                ============================================ --}}
                <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                   

                    <a href="mailto:{{ $contacto->email }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-envelope me-1"></i> Enviar Email Direto
                    </a>

                    <form action="{{ route('admin.contactos.destroy', $contacto->id) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('Tens a certeza que queres eliminar esta mensagem?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i> Eliminar
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection