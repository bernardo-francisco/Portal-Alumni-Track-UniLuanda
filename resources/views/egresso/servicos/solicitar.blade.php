@extends('layouts.egresso')

@section('title', 'Solicitar Serviço')

@section('page_title', '📋 Solicitar Serviço')
@section('page_subtitle', 'Solicite um documento ou serviço')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        {{-- ============================================================
             CABEÇALHO / INTRO
        ============================================================ --}}
        <div class="alert alert-info d-flex align-items-start gap-3 border-0 shadow-sm mb-4">
            <div class="alert-icon bg-info bg-opacity-10 text-info">
                <i class="fas fa-info-circle"></i>
            </div>
            <div>
                <strong class="d-block">Como funciona?</strong>
                <small class="text-muted">
                    Escolha um serviço rápido abaixo, ou selecione manualmente no formulário.
                    O prazo de atendimento é de até <strong>5 dias úteis</strong>.
                </small>
            </div>
        </div>


        {{-- ============================================================
             CARDS DE SERVIÇOS RÁPIDOS
        ============================================================ --}}
        <h6 class="fw-bold text-muted text-uppercase small mb-3">
            <i class="fas fa-bolt me-1"></i> Serviços Rápidos
        </h6>

        <div class="row g-3 mb-4">

            <div class="col-lg-3 col-md-6 col-6">
                <div class="service-card" data-service="declaracao" onclick="selecionarServico('declaracao', this)">
                    <div class="service-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h6 class="service-title">Declaração</h6>
                    <small class="service-desc">Conclusão</small>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <div class="service-card" data-service="certificado" onclick="selecionarServico('certificado', this)">
                    <div class="service-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h6 class="service-title">Certificado</h6>
                    <small class="service-desc">Participação</small>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <div class="service-card" data-service="historico" onclick="selecionarServico('historico', this)">
                    <div class="service-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-history"></i>
                    </div>
                    <h6 class="service-title">Histórico</h6>
                    <small class="service-desc">Académico</small>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <div class="service-card" data-service="outro" onclick="selecionarServico('outro', this)">
                    <div class="service-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-ellipsis-h"></i>
                    </div>
                    <h6 class="service-title">Outro</h6>
                    <small class="service-desc">Serviço</small>
                </div>
            </div>

        </div>


        {{-- ============================================================
             FORMULÁRIO
        ============================================================ --}}
        <form method="POST" action="{{ route('egresso.servicos.store') }}" id="formServico">
            @csrf

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Dados do Pedido</h6>
                            <small class="text-muted">Preencha as informações para o serviço</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">

                    {{-- Tipo de Serviço --}}
                    <div class="mb-4">
                        <label for="servicoSelect" class="form-label fw-semibold">
                            Tipo de Serviço <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-concierge-bell text-muted"></i>
                            </span>
                            <select name="servico"
                                    id="servicoSelect"
                                    class="form-select border-start-0 @error('servico') is-invalid @enderror"
                                    required>
                                <option value="">Selecione o tipo de serviço</option>
                                <option value="declaracao"  {{ old('servico') == 'declaracao'  ? 'selected' : '' }}>Declaração de Conclusão</option>
                                <option value="certificado" {{ old('servico') == 'certificado' ? 'selected' : '' }}>Certificado de Participação</option>
                                <option value="historico"   {{ old('servico') == 'historico'   ? 'selected' : '' }}>Histórico Académico</option>
                                <option value="email"       {{ old('servico') == 'email'       ? 'selected' : '' }}>Reativação de E-mail Institucional</option>
                                <option value="carta"       {{ old('servico') == 'carta'       ? 'selected' : '' }}>Carta de Recomendação</option>
                                <option value="outro"       {{ old('servico') == 'outro'       ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('servico')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Descrição --}}
                    <div class="mb-4">
                        <label for="descricao" class="form-label fw-semibold">
                            Descrição <span class="text-danger">*</span>
                        </label>
                        <textarea name="descricao"
                                  id="descricao"
                                  class="form-control @error('descricao') is-invalid @enderror"
                                  rows="5"
                                  maxlength="500"
                                  placeholder="Descreva detalhadamente o serviço que deseja solicitar..."
                                  required>{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Forneça o máximo de detalhes para facilitar o atendimento.
                            </div>
                            <small class="text-muted" id="charCounter">0 / 500</small>
                        </div>
                    </div>

                    {{-- Aviso --}}
                    <div class="info-box">
                        <div class="info-box-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-box-content">
                            <strong class="d-block">Prazo de Atendimento</strong>
                            <small class="text-muted">
                                O prazo máximo para atendimento é de <strong>5 dias úteis</strong>.
                                Será notificado quando o pedido for atendido.
                            </small>
                        </div>
                    </div>

                </div>
            </div>


            {{-- ============================================================
                 AÇÕES
            ============================================================ --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">
                        <a href="{{ route('egresso.servicos.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>

                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Limpar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Enviar Solicitação
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

@endsection

