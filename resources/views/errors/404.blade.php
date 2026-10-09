@extends('layouts.error')

@section('title', '404 - Página Não Encontrada')

@section('content')

<div class="error-page">
    <div class="error-card">
        <div class="error-logo">
            <i class="fas fa-compass"></i>
        </div>

        <div class="error-code">404</div>
        <h1 class="error-title">Página Não Encontrada</h1>
        <p class="error-message">
            A página que você está procurando não existe, foi removida
            ou o endereço está incorreto.
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