# 🛡️ Política de Segurança — Registo

Documentação das camadas de segurança implementadas no sistema de registo.

## 🎯 Objetivo

Proteger o sistema contra:
- **Spam registrations** (bots)
- **Enumeration attacks** (adivinhar nº de processo)
- **Emails descartáveis** (mailinator, tempmail, etc.)
- **Reivindicação indevida** de contas
- **DDoS no registo** (rate limiting)

---

## 🔒 3 Camadas de Segurança

### FASE 1 — Anti-Spam

| Camada | Descrição | Ficheiro |
|--------|-----------|----------|
| **Rate Limiting** | 3 registos/hora por IP + 2/hora por email | `AppServiceProvider` |
| **Honeypot** | Campo invisível `website_url` que só bots preenchem | `register.blade.php` + `RegisterController` |
| **Time-check** | Submissões < 3 segundos são rejeitadas | `RegisterController::validarAntiBot()` |
| **Padrões suspeitos** | Nomes com 5+ dígitos, emails muito longos | `RegisterController::validarAntiBot()` |
| **Emails descartáveis** | 96.296 domínios bloqueados (indisposable) | Pacote `propaganistas/laravel-disposable-email` |
| **Rastreio** | IP + user agent guardados no user | Colunas `registration_ip` + `registration_user_agent` |

### FASE 2 — Verificação de Email

| Camada | Descrição |
|--------|-----------|
| **Link mágico** | Token único (64 chars) enviado por email |
| **Validade** | 24 horas |
| **Uso único** | Marcado como usado após clique |
| **Notificação condicional** | Admin só é notificado APÓS verificação |

### FASE 3 — Reivindicação de Conta

| Camada | Descrição |
|--------|-----------|
| **Dados mascarados** | Só mostra `Ana B***` (nunca dados completos) |
| **Confirmação explícita** | Egresso clica "Sim, sou eu" |
| **OTP por email** | Código de 6 dígitos (30 min) |
| **Bloqueio de campos** | Dados da BD não são editáveis |
| **Auditoria** | `claimed_ip` + `claimed_user_agent` guardados |

---

## 🚨 Como cada camada responde a ataques

### Ataque 1 — Bot cria 100 contas em 1 minuto

- ✅ **Rate Limiting** bloqueia após 3 registos (HTTP 429)
- ✅ **Honeypot** deteta o campo invisível
- ✅ **Time-check** deteta submissão rápida

### Ataque 2 — Atacante adivinha nº de processo

- ✅ **`verificarNumeroProcesso`** mostra só dados mascarados
- ✅ **Rate limit** no endpoint (5/min por IP)
- ✅ **Confirmação explícita** requer clique
- ✅ **OTP por email** prova acesso ao email

### Ataque 3 — Email descartável para spam

- ✅ **`indisposable`** bloqueia 96.296 domínios
- ✅ **Verificação de email** filtra os restantes
- ✅ **Admin só é notificado** após verificação

### Ataque 4 — Reivindicação indevida de conta

- ✅ **Nº de processo** é o segredo partilhado
- ✅ **OTP** prova acesso ao email real
- ✅ **Campos bloqueados** impedem alteração
- ✅ **`claimed_at`** marca permanentemente (não permite 2ª reivindicação)

---

## 📊 Ficheiros-chave

| Ficheiro | Responsabilidade |
|----------|------------------|
| `app/Providers/AppServiceProvider.php` | Rate limiters |
| `app/Http/Controllers/Auth/RegisterController.php` | Anti-bot + fluxo principal |
| `app/Http/Controllers/Auth/VerificarEmailController.php` | Verificação de email |
| `app/Http/Controllers/Auth/OtpVerificationController.php` | OTP de reivindicação |
| `app/Models/EmailVerificationToken.php` | Tokens de verificação |
| `app/Models/OtpCode.php` | Códigos OTP |
| `resources/views/auth/register.blade.php` | Formulário + honeypot |
| `resources/views/auth/verify-otp.blade.php` | Ecrã de OTP |
| `bootstrap/app.php` | Handler 429 personalizado |

---

## 🔍 Logs de segurança

Eventos registados em `storage/logs/laravel.log`:

| Evento | Emoji | Significado |
|--------|-------|-------------|
| Honeypot detectado | 🚨 | Bot preencheu campo invisível |
| Submissão rápida | 🚨 | Form submetido em < 3s |
| OTP inválido | 🚨 | Código de verificação errado |
| Token inválido | 🚨 | Link de verificação inválido |
| Email verificado | ✅ | Verificação bem-sucedida |
| Conta ativada | ✅ | OTP validado |
| Email enviado | 📧 | Notificação para admin |

---

## 🚦 Ajustar limites

Para alterar os limites, editar `app/Providers/AppServiceProvider.php`:

```php
// Registo: 3/hora por IP + 2/hora por email
RateLimiter::for('register', function (Request $request) {
    return [
        Limit::perHour(3)->by('ip:' . $request->ip()),
        Limit::perHour(2)->by('email:' . strtolower((string) $request->input('email'))),
    ];
});