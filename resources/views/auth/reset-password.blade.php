@extends('layouts.app')

@section('title', 'Redefinir Senha')

@section('content')


<div class="reset-shell">
    <div class="reset-card">
        <h1>🔑 Redefinir Senha</h1>
        <p>Digite sua nova senha para acessar sua conta.</p>

        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="field">
                <span>🔒</span>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Nova senha"
                    required
                    minlength="6"
                >
            </div>

            <div class="field">
                <span>✓</span>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    placeholder="Confirmar nova senha"
                    required
                    minlength="6"
                >
            </div>

            <button type="submit" class="btn-reset" id="resetBtn">
                Redefinir Senha
            </button>

            <a href="{{ route('login') }}" class="back-link">
                ← Voltar ao login
            </a>
        </form>
    </div>
</div>
@endsection