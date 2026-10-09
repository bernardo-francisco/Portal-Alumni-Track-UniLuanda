@extends('layouts.app')

@section('title', 'Recuperar Palavra-passe')

@section('content')

<div class="reset-shell">
    <div class="reset-card">
        <h1><span>📧</span> Recuperar Palavra-passe</h1>
        <p class="subtitle">
            Introduz o teu email para receberes a ligação de redefinição de palavra-passe.
        </p>

        @if ($errors->any())
            <div class="error-message">
                <strong>⚠️ Erro:</strong> {{ $errors->first() }}
            </div>
        @endif

        @if(session('status'))
            <div class="success-message">
                <strong>✅ Sucesso!</strong> {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="field">
                <span class="icon">✉️</span>
                <input
                    type="email"
                    name="email"
                    placeholder="O teu email registado"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="btn-reset">
                Enviar Ligação de Recuperação
            </button>

            <a href="{{ route('login') }}" class="back-link">
                ← Voltar ao login
            </a>

            <p class="info-text">
                🔒 Os teus dados estão protegidos
            </p>
        </form>
    </div>
</div>
@endsection