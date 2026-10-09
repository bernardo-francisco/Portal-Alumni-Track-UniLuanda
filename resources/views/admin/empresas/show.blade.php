@extends('layouts.admin')

@section('title', 'Detalhes da Empresa')

@section('content')
<div class="container-fluid">

    {{-- MENSAGENS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- VERIFICAÇÃO DE SEGURANÇA --}}
    @if(!isset($empresa) || !$empresa)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Empresa não encontrada.
        </div>
        <a href="{{ route('admin.empresas.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left me-1"></i> Voltar à lista
        </a>
    @else

    @php
        // ─── Cores e labels por status ─────────────────────
        $cores = [
            'pendente'  => 'warning',
            'aprovado'  => 'success',
            'reprovado' => 'danger',
        ];
        $cor = $cores[$empresa->status_validacao ?? 'pendente'] ?? 'secondary';

        $labels = [
            'pendente'  => 'Pendente de Validação',
            'aprovado'  => 'Empresa Aprovada',
            'reprovado' => 'Empresa Reprovada',
        ];
        $label = $labels[$empresa->status_validacao ?? 'pendente'] ?? 'Desconhecido';

        // ─── Normalizar logo_url ───────────────────────────
        $logoUrl = $empresa->logo_url ?? null;
        if (!empty($logoUrl) && is_string($logoUrl)
            && !str_starts_with($logoUrl, 'http://')
            && !str_starts_with($logoUrl, 'https://')) {
            $logoUrl = asset(ltrim($logoUrl, '/'));
        }
    @endphp

    {{-- CABEÇALHO DA EMPRESA --}}
    <div class="empresa-header-card mb-4">
        <div class="empresa-header-body">

            {{-- LOGO --}}
            <div class="empresa-header-logo">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}"
                         alt="Logótipo de {{ $empresa->nome ?? 'Empresa' }}"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="empresa-header-logo-placeholder" style="display: none;">
                        <i class="fas fa-building"></i>
                    </div>
                @else
                    <div class="empresa-header-logo-placeholder">
                        <i class="fas fa-building"></i>
                    </div>
                @endif
            </div>

            {{-- INFO PRINCIPAL --}}
            <div class="empresa-header-info">
                <h2 class="empresa-header-nome">{{ $empresa->nome ?? 'Sem nome' }}</h2>

                <div class="empresa-header-meta">
                    @if(!empty($empresa->sector))
                        <span class="meta-item">
                            <i class="fas fa-industry"></i> {{ $empresa->sector }}
                        </span>
                    @endif
                    @if(!empty($empresa->localizacao))
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i> {{ $empresa->localizacao }}
                        </span>
                    @endif
                    @if(!empty($empresa->provincia))
                        <span class="meta-item">
                            <i class="fas fa-map"></i> {{ $empresa->provincia }}
                        </span>
                    @endif
                </div>

                <span class="empresa-status-badge status-{{ $cor }}">
                    <i class="fas fa-circle"></i> {{ $label }}
                </span>
            </div>

        </div>
    </div>

    {{-- CONTEÚDO PRINCIPAL --}}
    <div class="row g-3">

        {{-- COLUNA ESQUERDA: DADOS --}}
        <div class="col-lg-7">

            {{-- CONTACTOS --}}
            <div class="info-card mb-3">
                <div class="info-card-header">
                    <i class="fas fa-address-card"></i>
                    <h6>Contactos</h6>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                        <span class="info-value">{{ $empresa->email ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-phone"></i> Telefone</span>
                        <span class="info-value">{{ $empresa->telefone ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-globe"></i> Website</span>
                        <span class="info-value">
                            @if(!empty($empresa->website))
                                <a href="{{ $empresa->website }}" target="_blank" rel="noopener">
                                    {{ $empresa->website }} <i class="fas fa-external-link-alt fa-xs"></i>
                                </a>
                            @else
                                —
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- DADOS FISCAIS --}}
            <div class="info-card mb-3">
                <div class="info-card-header">
                    <i class="fas fa-file-invoice"></i>
                    <h6>Dados Fiscais</h6>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-hashtag"></i> NIF</span>
                        <span class="info-value">{{ $empresa->nif ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-tag"></i> Tipo</span>
                        <span class="info-value">
                            <span class="badge bg-secondary">
                                {{ ucfirst($empresa->tipo ?? 'n/d') }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- DESCRIÇÃO --}}
            @if(!empty($empresa->descricao))
                <div class="info-card mb-3">
                    <div class="info-card-header">
                        <i class="fas fa-align-left"></i>
                        <h6>Descrição da Empresa</h6>
                    </div>
                    <div class="info-card-body">
                        <p class="info-descricao">{{ $empresa->descricao }}</p>
                    </div>
                </div>
            @endif

            {{-- INFORMAÇÕES DE REGISTO --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-clock"></i>
                    <h6>Informações de Registo</h6>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-calendar-plus"></i> Registada em</span>
                        <span class="info-value">
                            {{ $empresa->created_at ? $empresa->created_at->format('d/m/Y \à\s H:i') : '—' }}
                        </span>
                    </div>
                    @if(!empty($empresa->data_validacao))
                        <div class="info-row">
                            <span class="info-label"><i class="fas fa-calendar-check"></i> Validada em</span>
                            <span class="info-value">
                                {{ $empresa->data_validacao->format('d/m/Y \à\s H:i') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- COLUNA DIREITA: VALIDAÇÃO --}}
        <div class="col-lg-5">

            {{-- CARD DE ESTADO --}}
            <div class="validation-card validation-{{ $cor }} mb-3">
                <div class="validation-card-header">
                    <i class="fas fa-shield-alt"></i>
                    <h6>Estado da Validação</h6>
                </div>
                <div class="validation-card-body">

                    <div class="validation-status-display">
                        <div class="validation-status-icon status-icon-{{ $cor }}">
                            @if(($empresa->status_validacao ?? '') === 'pendente')
                                <i class="fas fa-hourglass-half"></i>
                            @elseif(($empresa->status_validacao ?? '') === 'aprovado')
                                <i class="fas fa-check-circle"></i>
                            @else
                                <i class="fas fa-times-circle"></i>
                            @endif
                        </div>
                        <div class="validation-status-text">
                            <strong>{{ $label }}</strong>
                            @if(!empty($empresa->data_validacao))
                                <small>{{ $empresa->data_validacao->format('d/m/Y H:i') }}</small>
                            @else
                                <small>A aguardar análise</small>
                            @endif
                        </div>
                    </div>

                    {{-- MOTIVO DA REPROVAÇÃO --}}
                    @if(($empresa->status_validacao ?? '') === 'reprovado' && !empty($empresa->motivo_reprovacao))
                        <div class="motivo-reprovacao">
                            <div class="motivo-reprovacao-header">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Motivo da Reprovação</strong>
                            </div>
                            <div class="motivo-reprovacao-body">
                                {{ $empresa->motivo_reprovacao }}
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- AÇÕES --}}
            @if(($empresa->status_validacao ?? '') === 'pendente')
                <div class="actions-card">
                    <div class="actions-card-header">
                        <i class="fas fa-gavel"></i>
                        <h6>Ações de Validação</h6>
                    </div>
                    <div class="actions-card-body">

                        <form method="POST"
                              action="{{ route('admin.empresas.aprovar', $empresa->id) }}"
                              class="mb-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="btn btn-success w-100 btn-action"
                                    onclick="return confirm('Aprovar esta empresa?\n\nA empresa receberá uma notificação e um email de confirmação.')">
                                <i class="fas fa-check-circle me-1"></i> Aprovar Empresa
                            </button>
                        </form>

                        <button type="button"
                                class="btn btn-danger w-100 btn-action"
                                data-bs-toggle="modal"
                                data-bs-target="#modalReprovar">
                            <i class="fas fa-times-circle me-1"></i> Reprovar Empresa
                        </button>

                    </div>
                </div>
            @else
                <div class="validation-finished-card">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Validação concluída</strong>
                        <small>
                            Esta empresa já foi
                            {{ ($empresa->status_validacao ?? '') === 'aprovado' ? 'aprovada' : 'reprovada' }}.
                        </small>
                    </div>
                </div>
            @endif

        </div>

    </div>

    @endif {{-- fim do if(!isset($empresa)) --}}
</div>

{{-- MODAL: Reprovar Empresa --}}
@if(isset($empresa) && $empresa)
<div class="modal fade" id="modalReprovar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" action="{{ route('admin.empresas.reprovar', $empresa->id) }}">
                @csrf
                @method('PATCH')

                <div class="modal-header modal-header-danger">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i> Reprovar Empresa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                        <i class="fas fa-exclamation-triangle mt-1"></i>
                        <div>
                            Vai reprovar a empresa <strong>{{ $empresa->nome ?? '' }}</strong>.
                            <br><small>A empresa será notificada por email e no sino.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Motivo da Reprovação <span class="text-danger">*</span>
                        </label>
                        <textarea name="motivo_reprovacao"
                                  class="form-control"
                                  rows="4"
                                  required
                                  maxlength="500"
                                  placeholder="Explique de forma clara o motivo da reprovação. Esta mensagem será enviada para o email da empresa."></textarea>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Máximo 500 caracteres. Este texto será enviado por email para a empresa.
                        </small>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle me-1"></i> Confirmar Reprovação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection