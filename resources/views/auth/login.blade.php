@extends('layouts.app')

@section('title', 'Entrar na Conta')


@section('content')

<main class="login-shell">
    <section class="login-panel" aria-label="Área de acesso">

        <aside class="welcome-side">
            <div class="brand">
                <img src="{{ asset('uploads/home/uniluanda.webp') }}"
                     alt="Logotipo da Universidade"
                     class="university-logo">
            </div>

            <div class="welcome-content">
                <span class="eyebrow">Universidade de Luanda</span>
                <h2>Bem-Vindo!</h2>
                <p>Este Sistema conecta Ex-estudantes da Universidade de Luanda, promovendo networking, oportunidades profissionais e fortalecendo a comunidade académica.</p>
            </div>

            <p class="quote">
                Rede de ex-estudantes — conexões que transformam carreiras..
                <strong>Portal Alumni Track</strong>
            </p>
        </aside>

        <div class="form-side">
            <div class="form-intro">
                <h1>Entrar</h1>
                <p>Login diferenciado, digite seus dados para continuar.</p>
            </div>

            @if ($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('error'))
                <div class="error-message">
                    {{ session('error') }}
                </div>
            @endif

                    @if(session('status'))
                <div class="success-message">
                    @php
                        $mensagensStatus = [
                            'pending_email_verification' => '📧 Enviámos um email de verificação. Confirme o seu email para concluir o registo.',
                            'pending_validation'         => '⏳ O seu cadastro está pendente de validação. Receberá um email quando for aprovado.',
                            'empresa_pendente'           => '⏳ A sua empresa está pendente de validação pela administração.',
                            'verified'                   => '✅ Conta verificada com sucesso!',
                            'resent'                     => '📧 Email de verificação reenviado com sucesso.',
                            'password_reset'             => '🔑 Se o email existir, receberá um link para redefinir a senha.',
                        ];
                    @endphp

                    {{ $mensagensStatus[session('status')] ?? session('status') }}
                </div>
            @endif

            <form class="login-form" method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="field-group">
                    <label for="email">E-mail</label>
                    <div class="field">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="voce@exemplo.com"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

             <div class="field-group">
            <label for="password">Senha</label>
            <div class="field">
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Sua senha"
                    autocomplete="current-password"
                    required
                >
                <button type="button"
                        class="toggle-password"
                        id="togglePassword"
                        aria-label="Mostrar senha">
                    <i class="fas fa-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                </button>
            </div>
        </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Lembrar meu acesso</span>
                    </label>

                    <a href="#" class="forgot-link" id="forgotPasswordBtn">Esqueci minha senha</a>
                </div>

                <button type="submit" class="btn-login" id="submitButton">
                    <span id="buttonText">Entrar</span>
                </button>
            </form>

            <div class="secure-note">
                Seus dados estão protegidos
            </div>

            <p class="signup">
                Ainda não tem uma conta?
                <a href="{{ route('register') }}">Criar conta</a>
            </p>
        </div>
    </section>
</main>

{{-- ============================================================
     MODAL "ESQUECI MINHA SENHA"
============================================================ --}}
<div class="modal-overlay" id="forgotModal">
    <div class="modal-content">
        <button type="button" class="modal-close" id="modalClose">&times;</button>

        <div id="modalForm">
            <h2 class="modal-title">Recuperar Senha</h2>
            <p class="modal-subtitle">
                Digite o seu endereço de email para receber um link de recuperação de senha.
            </p>

            <div class="modal-error" id="modalError">
                <span id="modalErrorMessage">Erro ao enviar. Tente novamente.</span>
            </div>

                        <div class="field-group">
                <label for="email">E-mail</label>
                <div class="field">
                    <input
                                type="email"
                                id="resetEmail"
                                placeholder="voce@exemplo.com"
                                required
                            >
                </div>
            </div>


            <button type="button" class="modal-btn" id="resetPasswordBtn">
                <span id="resetButtonText">Enviar Link de Recuperação</span>
            </button>
        </div>

        <div class="modal-success" id="modalSuccess">
            <h3>Email Enviado!</h3>
            <p>
                Um link de recuperação foi enviado para o seu endereço de email.<br>
                Verifique sua caixa de entrada e spam.
            </p>
            <button type="button" class="modal-btn" id="modalSuccessBtn" style="margin-top: 16px;">
                Voltar ao Login
            </button>
        </div>
    </div>
</div>

{{-- ============================================================
     TOAST
============================================================ --}}
<div class="toast" id="toast">
    <span id="toastMessage">Mensagem</span>
</div>

@endsection

@push('scripts')
    @vite('resources/js/auth.js')
@endpush