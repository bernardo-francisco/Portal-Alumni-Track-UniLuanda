@extends('layouts.error')

@section('title', '429 - Muitas Requisições')

@section('content')

<div class="error-page">
    <div class="error-card">
        <div class="error-logo">
            <i class="fas fa-stopwatch"></i>
        </div>

        <div class="error-code">429</div>
        <h1 class="error-title">Muitas Requisições</h1>
        <p class="error-message">
            Fizeste demasiadas requisições em pouco tempo.
            Aguarda alguns segundos e tenta novamente.
        </p>

        <div class="error-actions">
            <a href="javascript:location.reload()" class="btn-error btn-error-primary">
                <i class="fas fa-redo"></i> Recarregar Página
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