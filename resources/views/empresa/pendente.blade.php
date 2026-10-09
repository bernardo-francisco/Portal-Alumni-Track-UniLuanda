@extends('layouts.empresa')

@section('title', 'Conta em Validação')
@section('page-title', 'Conta em Validação')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-5 text-center position-relative">

                <div class="status-icon-wrapper mb-4">
                    <div class="status-icon-bg">
                        <i class="fas fa-hourglass-half fa-3x"></i>
                    </div>
                </div>

                <h3 class="fw-bold mb-2">A sua conta está em validação</h3>
                <p class="text-muted mb-4">
                    A equipa da UniLuanda irá analisar o registo da sua empresa.<br>
                    Receberá uma notificação por email assim que for aprovada.
                </p>

                <div class="d-inline-flex align-items-center gap-2 bg-warning-subtle text-warning-emphasis px-4 py-2 rounded-pill mb-4">
                    <i class="fas fa-clock"></i>
                    <span class="fw-semibold">Estado: Pendente de Validação</span>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-4">
                        <div class="info-mini-card">
                            <i class="fas fa-building text-primary"></i>
                            <h6 class="mt-2 mb-1">{{ Auth::user()->empresa->nome ?? 'Empresa' }}</h6>
                            <small class="text-muted">Nome registado</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-mini-card">
                            <i class="fas fa-calendar text-info"></i>
                            <h6 class="mt-2 mb-1">{{ optional(Auth::user()->empresa->created_at ?? null)->format('d/m/Y') ?? '—' }}</h6>
                            <small class="text-muted">Data de registo</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-mini-card">
                            <i class="fas fa-hourglass text-warning"></i>
                            <h6 class="mt-2 mb-1">Até 5 dias</h6>
                            <small class="text-muted">Prazo estimado</small>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt me-2"></i> Sair
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
