@extends('layouts.admin')

@section('title', 'Chatbot - Conversa')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 fw-bold mb-0">
                        <i class="fas fa-robot text-primary me-2"></i>Conversa
                    </h1>
                    <p class="text-muted">Conversando com <strong>{{ $egresso->nome_completo }}</strong></p>
                </div>
                <a href="{{ route('admin.chatbot.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    
                    <!-- Header -->
                    <div class="p-3 border-bottom bg-light">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary bg-opacity-10 me-3">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $egresso->nome_completo }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i>
                                    Online
                                </small>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-light text-dark rounded-pill px-3">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $mensagens->count() }} mensagens
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Mensagens -->
                    <div id="chat-messages" class="p-4" style="height: 450px; overflow-y: auto;">
                        @foreach($mensagens as $msg)
                            @if($msg->origem == 'admin')
                                <div class="message message-sent mb-3">
                                    <div class="d-flex flex-row-reverse align-items-end">
                                        <div class="bg-primary text-white rounded-4 px-3 py-2 ms-2" style="max-width: 75%;">
                                            {{ $msg->mensagem }}
                                            <small class="text-white-50 d-block text-end" style="font-size: 10px;">
                                                {{ $msg->created_at->format('H:i') }}
                                            </small>
                                        </div>
                                        <div class="avatar-circle bg-primary bg-opacity-10">
                                            <i class="fas fa-user-shield text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="message message-received mb-3">
                                    <div class="d-flex align-items-end">
                                        <div class="avatar-circle bg-info bg-opacity-10">
                                            <i class="fas fa-user text-info"></i>
                                        </div>
                                        <div class="bg-light rounded-4 px-3 py-2 ms-2" style="max-width: 75%;">
                                            {{ $msg->mensagem }}
                                            <small class="text-muted d-block" style="font-size: 10px;">
                                                {{ $msg->created_at->format('H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Input -->
                    <div class="border-top p-3 bg-light">
                        <form id="chat-form" class="d-flex gap-2">
                            @csrf
                            <input type="text" id="chat-input" class="form-control rounded-pill border-0 shadow-sm" 
                                   placeholder="Digite sua resposta..." required>
                            <button type="submit" id="send-btn" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

