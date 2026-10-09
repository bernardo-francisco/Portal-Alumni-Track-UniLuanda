@extends('layouts.error')

@section('title', '500 - Erro Interno do Servidor')

@section('content')

<div class="error-page">
    <div class="error-card">
        <div class="error-logo">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <div class="error-code">500</div>
        <h1 class="error-title">Erro Interno do Servidor</h1>
        <p class="error-message">
            Algo correu mal no servidor. A nossa equipa já foi notificada
            e está a trabalhar para resolver o problema.
        </p>

        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn-error btn-error-primary">
                <i class="fas fa-home"></i> Voltar ao Início
            </a>
            <a href="javascript:location.reload()" class="btn-error btn-error-secondary">
                <i class="fas fa-redo"></i> Tentar Novamente
            </a>
        </div>

        @if(config('app.debug') && isset($exception))
            <div class="error-detail">
                <strong>🐛 Detalhes do Erro (debug):</strong>
                {{ $exception->getMessage() ?: 'Erro desconhecido' }}
            </div>
        @endif

        <div class="error-footer">
            <strong>UniLuanda Alumni Track</strong> · Sistema de Controlo e Localização de Ex-Estudantes
        </div>
    </div>
</div>

@endsection