@extends('layouts.error')

@section('title', '503 - Em Manutenção')

@section('content')

<div class="error-page">
    <div class="error-card">
        <div class="error-logo">
            <i class="fas fa-tools"></i>
        </div>

        <div class="error-code">503</div>
        <h1 class="error-title">Em Manutenção</h1>
        <p class="error-message">
            Estamos a realizar manutenção programada ao sistema
            para melhorar a tua experiência. Voltamos em breve!
        </p>

        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn-error btn-error-primary">
                <i class="fas fa-home"></i> Voltar ao Início
            </a>
            <a href="javascript:location.reload()" class="btn-error btn-error-secondary">
                <i class="fas fa-redo"></i> Tentar Novamente
            </a>
        </div>

        <div class="error-footer">
            <strong>UniLuanda Alumni Track</strong> · Sistema de Controlo e Localização de Ex-Estudantes
        </div>
    </div>
</div>

@endsection