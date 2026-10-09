@extends('layouts.error')

@section('title', '403 - Acesso Negado')

@section('content')

<div class="error-page">
    <div class="error-card">
        <div class="error-logo">
            <i class="fas fa-lock"></i>
        </div>

        <div class="error-code">403</div>
        <h1 class="error-title">Acesso Negado</h1>
        <p class="error-message">
            Você não tem permissão para acessar esta página.
            Se acredita que isto é um erro, contacte o administrador.
        </p>

        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn-error btn-error-primary">
                <i class="fas fa-home"></i> Voltar ao Início
            </a>
            <a href="javascript:history.back()" class="btn-error btn-error-secondary">
                <i class="fas fa-arrow-left"></i> Voltar Atrás
            </a>
        </div>

        <div class="error-footer">
            <strong>UniLuanda Alumni Track</strong> · Sistema de Controlo e Localização de Ex-Estudantes
        </div>
    </div>
</div>

@endsection