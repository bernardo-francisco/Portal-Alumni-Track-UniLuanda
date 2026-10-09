@extends('layouts.admin')

@section('title', 'Chatbot - Admin')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 fw-bold mb-0">
                        <i class="fas fa-robot text-primary me-2"></i>Chatbot
                    </h1>
                    <p class="text-muted">Gerencie as conversas com os egressos</p>
                </div>
                <div>
                    <span class="badge bg-success rounded-pill px-3 py-2">
                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                        {{ $conversas->count() }} conversas ativas
                    </span>
                    @if($totalNovas > 0)
                        <span class="badge bg-danger rounded-pill px-3 py-2 ms-2">
                            <i class="fas fa-bell me-1"></i>
                            {{ $totalNovas }} novas
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lista de Conversas -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-users me-2"></i>Conversas
                    </h6>
                    <small class="text-muted">Egressos que iniciaram conversas</small>
                </div>
                <div class="card-body p-0">
                    @if($conversas->count() > 0)
                        @foreach($conversas as $egressoId => $msgs)
                            @php
                                $egresso = $egressos[$egressoId] ?? null;
                                $ultima = $msgs->first();
                                $naoLidas = $msgs->where('origem', 'egresso')->where('lida', false)->count();
                            @endphp
                            <a href="{{ route('admin.chatbot.conversa', $egressoId) }}" 
                               class="text-decoration-none d-block conversation-item border-bottom p-3 {{ request()->segment(3) == $egressoId ? 'bg-light' : '' }}">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary bg-opacity-10 me-3">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong class="text-dark">{{ $egresso->nome_completo ?? 'Egresso' }}</strong>
                                            <small class="text-muted">{{ $ultima->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted small">{{ Str::limit($ultima->mensagem, 40) }}</span>
                                            @if($naoLidas > 0)
                                                <span class="badge bg-danger rounded-pill">{{ $naoLidas }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Nenhuma conversa ainda</h6>
                            <p class="text-muted small">Aguardando egressos iniciarem conversas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Área do Chat -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex flex-column p-0">
                    <div class="p-3 border-bottom d-flex align-items-center">
                        <div class="bg-light rounded-circle p-2 me-3">
                            <i class="fas fa-robot text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Selecione uma conversa</h6>
                            <small class="text-muted">Clique em um egresso para iniciar</small>
                        </div>
                    </div>
                    <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
                        <div class="text-center">
                            <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                <i class="fas fa-comment-dots fa-4x text-muted"></i>
                            </div>
                            <h5 class="text-dark">Nenhuma conversa selecionada</h5>
                            <p class="text-muted">Selecione um egresso na lista ao lado</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

