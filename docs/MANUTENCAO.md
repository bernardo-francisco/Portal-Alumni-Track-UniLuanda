# 🔧 Manutenção do Sistema de Registo

Guia prático para manutenção, debug e resolução de problemas.

---

## 🧹 Comandos frequentes

### Limpar todas as caches

```bash
php artisan optimize:clear
```

### Limpar rate limiter (desenvolvimento)

```bash
php artisan tinker --execute="cache()->flush(); echo 'OK';"
```

### Ver OTPs ativos

```bash
php artisan tinker --execute="dump(App\Models\OtpCode::validos()->get(['email', 'code', 'expires_at'])->toArray());"
```

### Ver tokens de verificação ativos

```bash
php artisan tinker --execute="dump(App\Models\EmailVerificationToken::validos()->get(['email', 'expires_at'])->toArray());"
```

---

## 🐛 Problemas comuns

### Problema 1 — "Demasiadas tentativas de registo"

**Causa:** rate limiter atingido (3 registos/hora).

**Solução (dev):**

```bash
php artisan tinker --execute="cache()->flush(); echo 'OK';"
```

**Solução (prod):** esperar 1 hora.

---

### Problema 2 — Email de verificação não chega

**Verificações:**

1. **Está no spam?** Verificar pasta de spam.
2. **O envio falhou?**

```bash
Get-Content "storage\logs\laravel.log" -Tail 20 | Select-String "Erro ao enviar"
```

3. **O SMTP está configurado?**

```bash
php artisan tinker --execute="dump(config('mail.mailers.smtp'));"
```

---

### Problema 3 — OTP sempre inválido

**Verificações:**

1. **O OTP existe?**

```bash
php artisan tinker --execute="dump(App\Models\OtpCode::where('email', 'xxx@email.com')->get()->toArray());"
```

2. **Expirou?** (30 minutos)
3. **Já foi usado?**

**Reset manual:**

```bash
php artisan tinker --execute="App\Models\OtpCode::where('email', 'xxx@email.com')->delete();"
```

---

### Problema 4 — Link de verificação expirado

**Causa:** o token tem 24h de validade.

**Solução:** o egresso tem de fazer o registo de novo.

---

### Problema 5 — Egresso reivindicou conta errada

**Reset:**

```bash
php artisan tinker --execute="
\$e = App\Models\Egresso::where('numero_processo', 'XXXXX')->first();
\$e->update(['claimed_at' => null, 'claimed_ip' => null, 'claimed_user_agent' => null]);
"
```

---

## 📊 Monitorização

### Ver registos de hoje

```bash
php artisan tinker --execute="
echo 'Egressos: ' . App\Models\Egresso::whereDate('created_at', today())->count() . PHP_EOL;
echo 'Empresas: ' . App\Models\Empresa::whereDate('created_at', today())->count() . PHP_EOL;
"
```

### Ver OTPs expirados a limpar

```bash
php artisan tinker --execute="
echo 'OTPs expirados: ' . App\Models\OtpCode::where('expires_at', '<', now())->count() . PHP_EOL;
echo 'Tokens expirados: ' . App\Models\EmailVerificationToken::where('expires_at', '<', now())->count() . PHP_EOL;
"
```

---

## 🔍 Logs importantes

### Ver últimos eventos de segurança

```bash
Get-Content "storage\logs\laravel.log" -Tail 50 | Select-String "🚨|✅|📧"
```

### Ver erros recentes

```bash
Get-Content "storage\logs\laravel.log" | Select-String "ERROR" | Select-Object -Last 20
```

---

## 📋 Checklist de deploy

Antes de fazer deploy para produção:

- [ ] `php artisan optimize:clear`
- [ ] `npm run build`
- [ ] `php artisan migrate --force`
- [ ] Verificar `MAIL_*` no `.env`
- [ ] Verificar `APP_ENV=production`
- [ ] Verificar `APP_DEBUG=false`
- [ ] Testar envio de email real
- [ ] Verificar rate limits apropriados
- [ ] Backup da base de dados