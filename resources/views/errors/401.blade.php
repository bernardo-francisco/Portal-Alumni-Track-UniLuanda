@extends('layouts.error')

@section('title', '401 - Não Autenticado')

@section('content')

<div class="error-page">
    <div class="error-card">
        <div class="error-logo">
            <i class="fas fa-user-lock"></i>
        </div>

        <div class="error-code">401</div>
        <h1 class="error-title">Não Autenticado</h1>
        <p class="error-message">
            Precisas de iniciar sessão para aceder a esta página.
        </p>

        <div class="error-actions">
            <a href="{{ url('/login') }}" class="btn-error btn-error-primary">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sessão
            </a>
            <a href="{{ url('/') }}" class="btn-error btn-error-secondary">
                <i class="fas fa-home"></i> Voltar ao Início
            </a>
        </div>

        <div class="error-footer">
            <strong>UniLuanda Alumni Track</strong> · Sistema de Controlo e Localização de Ex-Estudantes
        </div>
    </div>
</div>

@endsection