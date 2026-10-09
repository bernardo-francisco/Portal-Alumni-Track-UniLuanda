@extends('layouts.empresa')

@section('title', 'Detalhes da Candidatura')
@section('page-title', 'Detalhes da Candidatura')

@section('content')

<div class="page-header">
    <div>
        <h5 class="mb-1">Detalhes da Candidatura</h5>
        <p class="text-muted small mb-0">
            Candidatura de <strong>{{ $candidatura->egresso->nome_completo ?? 'Egresso' }}</strong>
        </p>
    </div>
    <a href="{{ route('empresa.candidaturas.index') }}" class="btn btn-light">
        <i class="fas fa-arrow-left me-2"></i> Voltar
    </a>
</div>

<div class="row g-3">

    {{-- COLUNA ESQUERDA --}}
    <div class="col-lg-7">

        {{-- CANDIDATO --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-user me-2"></i> Dados do Candidato</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4 mb-4">
                    @if($candidatura->egresso && $candidatura->egresso->foto_url)
                        <img src="{{ asset($candidatura->egresso->foto_url) }}"
                             alt="Foto de {{ $candidatura->egresso->nome_completo }}"
                             class="rounded-circle"
                             style="width: 90px; height: 90px; object-fit: cover; border: 3px solid #e0e9ff;">
                    @else
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                             style="width: 90px; height: 90px; font-size: 1.8rem; border: 3px solid #e0e9ff;">
                            {{ strtoupper(substr($candidatura->egresso->nome_completo ?? 'EG', 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h5 class="fw-bold mb-1">{{ $candidatura->egresso->nome_completo ?? 'Egresso' }}</h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-graduation-cap me-1"></i>
                            {{ $candidatura->egresso->curso->nome ?? '—' }}
                        </p>
                        <span class="badge bg-secondary">{{ $candidatura->egresso->numero_processo ?? '—' }}</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-box">
                            <i class="fas fa-envelope text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <strong>{{ $candidatura->egresso->email ?? '—' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <i class="fas fa-phone text-success"></i>
                            <div>
                                <small class="text-muted d-block">Telefone</small>
                                <strong>{{ $candidatura->egresso->telefone ?? '—' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

               {{-- ✅ Ver CV --}}
                @if($candidatura->cv_anexo)
                    <a href="{{ asset('storage/' . $candidatura->cv_anexo) }}"
                    target="_blank"
                    rel="noopener"
                    class="btn btn-sm btn-outline-primary"
                    title="Ver CV que enviei">
                        <i class="fas fa-file-pdf me-1"></i> CV
                    </a>
                @endif
            </div>
        </div>

        {{-- OPORTUNIDADE --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-briefcase text-primary me-2"></i> Oportunidade</h6>
            </div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-2">{{ $candidatura->oportunidade->titulo ?? '—' }}</h6>
                <p class="text-muted small mb-3">
                    <span class="badge bg-info-subtle text-info-emphasis">
                        {{ ucfirst($candidatura->oportunidade->tipo ?? '—') }}
                    </span>
                    <span class="ms-2">
                        <i class="fas fa-calendar me-1"></i>
                        Candidatura: {{ $candidatura->created_at->format('d/m/Y H:i') }}
                    </span>
                </p>

                @if($candidatura->mensagem_motivacional)
                    <div class="mensagem-box">
                        <label class="small fw-bold text-muted mb-2">
                            <i class="fas fa-comment me-1"></i> Mensagem Motivacional:
                        </label>
                        <p class="mb-0">{{ $candidatura->mensagem_motivacional }}</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- COLUNA DIREITA --}}
    <div class="col-lg-5">

        {{-- ACTUALIZAR ESTADO --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-gradient-success text-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-pencil-alt me-2"></i> Actualizar Estado</h6>
            </div>
            <div class="card-body p-4">

                <form method="POST" action="{{ route('empresa.candidaturas.status', $candidatura->id) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Estado da Candidatura</label>
                        <select name="status" class="form-select" required>
                            <option value="pendente" {{ $candidatura->status == 'pendente' ? 'selected' : '' }}>⏳ Pendente</option>
                            <option value="em_analise" {{ $candidatura->status == 'em_analise' ? 'selected' : '' }}>🔍 Em Análise</option>
                            <option value="entrevista" {{ $candidatura->status == 'entrevista' ? 'selected' : '' }}>💬 Entrevista</option>
                            <option value="aprovado" {{ $candidatura->status == 'aprovado' ? 'selected' : '' }}>✅ Aprovado</option>
                            <option value="aceite" {{ $candidatura->status == 'aceite' ? 'selected' : '' }}>🎉 Aceite</option>
                            <option value="rejeitado" {{ $candidatura->status == 'rejeitado' ? 'selected' : '' }}>❌ Rejeitado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Data de Entrevista</label>
                        <input type="datetime-local" name="data_entrevista" class="form-control"
                               value="{{ $candidatura->data_entrevista ? \Carbon\Carbon::parse($candidatura->data_entrevista)->format('Y-m-d\TH:i') : '' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Local da Entrevista</label>
                        <input type="text" name="local_entrevista" class="form-control"
                               value="{{ $candidatura->local_entrevista }}"
                               placeholder="Ex: Sede da Empresa, Luanda">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Motivo de Rejeição (se aplicável)</label>
                        <textarea name="motivo_rejeicao" class="form-control" rows="3"
                                  placeholder="Explique o motivo...">{{ $candidatura->motivo_rejeicao }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-save me-2"></i> Actualizar Estado
                    </button>
                </form>
            </div>
        </div>

        {{-- ESTADO ACTUAL --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 text-center">
                <small class="text-muted d-block mb-2">Estado Actual</small>
                @php
                    $coresStatus = [
                        'pendente'   => 'warning',
                        'em_analise' => 'info',
                        'entrevista' => 'primary',
                        'aprovado'   => 'success',
                        'aceite'     => 'success',
                        'rejeitado'  => 'danger',
                    ];
                    $cor = $coresStatus[$candidatura->status] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $cor }} fs-6 px-4 py-2">
                    {{ ucfirst(str_replace('_', ' ', $candidatura->status)) }}
                </span>

                @if($candidatura->avaliado_em)
                    <p class="text-muted small mt-3 mb-0">
                        <i class="fas fa-clock me-1"></i>
                        Avaliado em {{ $candidatura->avaliado_em->format('d/m/Y H:i') }}
                    </p>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
