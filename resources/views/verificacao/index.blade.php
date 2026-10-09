@extends('layouts.simple')

@section('title', 'Verificar Documento — UniLuanda Alumni')

@section('content')

<div class="container">
    <div class="verify-card">

        <div class="logo-wrap">
            @if(file_exists(public_path('uploads/home/uniluanda.webp')))
                <img src="{{ asset('uploads/home/uniluanda.webp') }}" alt="Universidade de Luanda">
            @else
                <div class="logo-fallback">UL</div>
            @endif
        </div>

        <h1 class="title">Verificar Documento</h1>
        <p class="subtitle">
            Introduza o código de verificação presente no comprovativo ou certificado
        </p>

        <form action="{{ route('verificacao.index') }}" method="GET"
              onsubmit="this.action='/verificar/' + document.getElementById('codigo').value.trim().toUpperCase();">

            <label for="codigo" class="form-label">Código de Verificação</label>
            <input type="text"
                   id="codigo"
                   name="codigo"
                   class="form-control"
                   placeholder="EX: 02522A2B2726"
                   maxlength="20"
                   autocomplete="off"
                   autofocus
                   required>

            <button type="submit" class="btn-verificar">
                <i class="fas fa-shield-alt me-2"></i>
                Verificar Autenticidade
            </button>
        </form>

        <p class="help-text">
            <i class="fas fa-info-circle"></i>
            O código encontra-se no canto inferior direito do documento,<br>
            junto ao rótulo <strong>"Código de Verificação"</strong>.
        </p>

    </div>
</div>

@endsection