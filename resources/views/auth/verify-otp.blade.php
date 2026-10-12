@extends('layouts.app')

@section('title', 'Verificação de Código')

@push('styles')
<style>
/* ============================================================
   VERIFY OTP — Layout centrado e limpo
============================================================ */

.otp-shell {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 40px 20px;
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
}

.otp-panel {
    width: 100%;
    max-width: 520px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    overflow: hidden;
}

/* Header */
.otp-header {
    padding: 32px 32px 24px;
    background: linear-gradient(135deg, #0a0a0a 0%, #262626 100%);
    border-bottom: 3px solid #c9a227;
    text-align: center;
}

.otp-header .icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    margin-bottom: 16px;
    border-radius: 50%;
    background: rgba(201, 162, 39, 0.15);
    color: #c9a227;
    font-size: 28px;
}

.otp-header h1 {
    color: #ffffff;
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.otp-header p {
    color: rgba(255, 255, 255, 0.7);
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}

.otp-header .email-highlight {
    color: #c9a227;
    font-weight: 600;
}

/* Body */
.otp-body {
    padding: 32px;
}

/* Alert */
.otp-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    margin-bottom: 24px;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.5;
}

.otp-alert.error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

.otp-alert.success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.otp-alert i {
    flex-shrink: 0;
    margin-top: 2px;
}

/* Code input */
.otp-input-wrapper {
    margin: 0 auto 24px;
    text-align: center;
}

.otp-input-wrapper label {
    display: block;
    margin-bottom: 12px;
    color: #53647a;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.otp-code-input {
    width: 100%;
    max-width: 320px;
    padding: 16px 20px;
    border: 2px solid #e5e5e5;
    border-radius: 12px;
    background: #fafafa;
    color: #0a0a0a;
    font-size: 32px;
    font-weight: 800;
    letter-spacing: 0.4em;
    text-align: center;
    font-family: 'Courier New', monospace;
    outline: none;
    transition: all 0.2s ease;
}

.otp-code-input:focus {
    border-color: #c9a227;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(201, 162, 39, 0.15);
}

.otp-code-input::placeholder {
    color: #ccc;
    letter-spacing: 0.4em;
}

/* Timer */
.otp-timer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 24px;
    padding: 10px 16px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    color: #92400e;
    font-size: 13px;
    font-weight: 600;
}

.otp-timer i {
    font-size: 14px;
}

/* Submit button */
.otp-submit {
    width: 100%;
    height: 52px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%);
    color: #0a0a0a;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 0.5px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(201, 162, 39, 0.4);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.otp-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(201, 162, 39, 0.55);
}

.otp-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Footer */
.otp-footer {
    padding: 20px 32px 28px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
    text-align: center;
}

.otp-footer p {
    margin: 0;
    color: #888;
    font-size: 12px;
    line-height: 1.5;
}

.otp-footer a {
    color: #a67c00;
    font-weight: 600;
    text-decoration: none;
}

.otp-footer a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 520px) {
    .otp-shell {
        padding: 20px 12px;
    }

    .otp-header,
    .otp-body,
    .otp-footer {
        padding-left: 20px;
        padding-right: 20px;
    }

    .otp-header h1 {
        font-size: 20px;
    }

    .otp-code-input {
        font-size: 26px;
        letter-spacing: 0.3em;
        padding: 14px 16px;
    }
}
</style>
@endpush

@section('content')

<main class="otp-shell">
    <section class="otp-panel">

        {{-- ============================================================
             HEADER
        ============================================================ --}}
        <header class="otp-header">
            <div class="icon-wrapper">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>Verificação de Código</h1>
            <p>
                Enviámos um código de 6 dígitos para<br>
                <span class="email-highlight">{{ $email }}</span>
            </p>
        </header>

        {{-- ============================================================
             BODY
        ============================================================ --}}
        <div class="otp-body">

            {{-- Mensagens --}}
            @if($errors->any())
                <div class="otp-alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="otp-alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="otp-alert success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Timer --}}
            <div class="otp-timer" id="otpTimer">
                <i class="fas fa-clock"></i>
                <span id="otpTimerText">O código expira em <strong>10:00</strong></span>
            </div>

            {{-- Formulário --}}
            <form method="POST" action="{{ route('register.verificar-otp.post') }}" id="otpForm">
                @csrf

                <input type="hidden" name="email" value="{{ $email }}">

                <div class="otp-input-wrapper">
                    <label for="code">Introduza o código</label>
                    <input
                        type="text"
                        name="code"
                        id="code"
                        class="otp-code-input"
                        placeholder="000000"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        autocomplete="one-time-code"
                        autofocus
                        required
                    >
                </div>

                <button type="submit" class="otp-submit" id="otpSubmit">
                    <i class="fas fa-check-circle me-2"></i>
                    Verificar Código
                </button>
            </form>

        </div>

        {{-- ============================================================
             FOOTER
        ============================================================ --}}
        <footer class="otp-footer">
            <p>
                Não recebeu o código?<br>
                Verifique a pasta de <strong>spam</strong> ou
                <a href="{{ route('register') }}">recomece o registo</a>.
            </p>
        </footer>

    </section>
</main>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ============================================================
    // INPUT — apenas dígitos
    // ============================================================
    const input = document.getElementById('code');
    if (input) {
        input.addEventListener('input', function (e) {
            // Remove tudo o que não seja dígito
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });

        // Auto-submit quando atingir 6 dígitos
        input.addEventListener('input', function () {
            if (this.value.length === 6) {
                // Pequeno delay para o utilizador ver o código
                setTimeout(() => {
                    document.getElementById('otpForm').submit();
                }, 800);
            }
        });

        // Focar no input ao carregar
        input.focus();
    }

    // ============================================================
    // TIMER — contagem decrescente
    // ============================================================
    const timerText = document.getElementById('otpTimerText');
    const expiraEm  = new Date('{{ $expira_em->toIso8601String() }}').getTime();

    function atualizarTimer() {
        const agora = new Date().getTime();
        const diff  = expiraEm - agora;

        if (diff <= 0) {
            timerText.innerHTML = '❌ O código expirou. <a href="{{ route("register") }}" style="color:#a67c00; font-weight:700;">Recomeçar</a>';
            document.getElementById('otpSubmit').disabled = true;
            return;
        }

        const minutos = Math.floor(diff / 60000);
        const segundos = Math.floor((diff % 60000) / 1000);

        timerText.innerHTML = `O código expira em <strong>${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}</strong>`;
    }

    atualizarTimer();
    setInterval(atualizarTimer, 1000);
});
</script>
@endpush