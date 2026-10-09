@extends('layouts.egresso')

@section('title', 'Chatbot - Egresso')

@section('content')

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="fas fa-robot text-primary fa-2x"></i>
                </div>
                <div>
                    <h1 class="h3 fw-bold mb-0">Assistente Virtual</h1>
                    <p class="text-muted small">Tire suas dúvidas aqui</p>
                </div>
                <div class="ms-auto">
                    <span class="badge bg-success rounded-pill px-3 py-2">
                        <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i> Online
                    </span>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    
                    <!-- Mensagens -->
                    <div id="chat-messages" class="p-4" style="height: 500px; overflow-y: auto;">
                        @if(isset($mensagens) && $mensagens->count() > 0)
                            @foreach($mensagens as $msg)
                                @if($msg->origem == 'egresso')
                                    <div class="message message-sent mb-3">
                                        <div class="d-flex flex-row-reverse align-items-end">
                                            <div class="bg-primary text-white rounded-4 px-3 py-2 ms-2" style="max-width: 75%;">
                                                {{ $msg->mensagem }}
                                                <small class="text-white-50 d-block text-end" style="font-size: 10px;">
                                                    {{ $msg->created_at->format('H:i') }}
                                                </small>
                                            </div>
                                            <div class="avatar-circle bg-primary bg-opacity-10">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="message message-received mb-3">
                                        <div class="d-flex align-items-end">
                                            <div class="avatar-circle bg-info bg-opacity-10">
                                                <i class="fas fa-robot text-info"></i>
                                            </div>
                                            <div class="bg-light rounded-4 px-3 py-2 ms-2" style="max-width: 75%;">
                                                {!! nl2br(e($msg->mensagem)) !!}
                                                <small class="text-muted d-block" style="font-size: 10px;">
                                                    {{ $msg->created_at->format('H:i') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                    <i class="fas fa-comment-dots fa-3x text-muted"></i>
                                </div>
                                <h5 class="text-dark">Nenhuma mensagem</h5>
                                <p class="text-muted small">Inicie uma conversa com o assistente</p>
                            </div>
                        @endif
                    </div>

                    <!-- Input -->
                    <div class="border-top p-3 bg-light">
                        <form id="chat-form" class="d-flex gap-2">
                            @csrf
                            <input type="text" id="chat-input" class="form-control rounded-pill border-0 shadow-sm" 
                                   placeholder="Digite sua mensagem..." required>
                            <button type="submit" id="send-btn" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                        <div class="mt-2 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Suas conversas são seguras
                            </small>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Sugestões rápidas -->
            <div class="row g-2 mt-3">
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-primary w-100 rounded-pill btn-sm quick-btn" data-msg="Quero saber sobre eventos">
                        <i class="fas fa-calendar me-1"></i> Eventos
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-success w-100 rounded-pill btn-sm quick-btn" data-msg="Oportunidades de emprego">
                        <i class="fas fa-briefcase me-1"></i> Oportunidades
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-info w-100 rounded-pill btn-sm quick-btn" data-msg="Ver notícias do mural">
                        <i class="fas fa-newspaper me-1"></i> Mural
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-warning w-100 rounded-pill btn-sm quick-btn" data-msg="Ajuda com o sistema">
                        <i class="fas fa-question-circle me-1"></i> Ajuda
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>


@endsection