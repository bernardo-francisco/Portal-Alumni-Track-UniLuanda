# 🔄 Fluxo de Registo — Diagrama Completo

Documentação do fluxo de registo de egressos e empresas.

---

## 🎯 3 Cenários de Registo

### Cenário A — Egresso Nível Ouro (nº processo existe)

```
1. Egresso digita nº de processo (ex: 20211519)
2. AJAX: /verificar-processo
3. Sistema encontra egresso na BD
4. Devolve: dados mascarados + auto_preencher
5. JS mostra painel verde: "Encontramos o seu registo!"
6. Egresso clica "Sim, sou eu"
7. JS auto-preenche os campos + bloqueia
8. Egresso completa email + senha
9. Submete → RegisterController::register()
10. Sistema:
    - Atualiza User existente
    - Atualiza Egresso (claimed_at, claimed_ip)
    - Gera OTP de 6 dígitos (30 min)
    - Envia email com OTP
11. Redireciona para /registar/verificar-otp
12. Egresso introduz o código
13. OtpVerificationController::verify()
    - Valida código
    - Marca user como ativo + verificado
    - Marca egresso como aprovado
    - Notifica admin
14. Redireciona para /login
15. Egresso faz login ✅
```

### Cenário B — Egresso Nível Prata (nº processo NÃO existe)

```
1. Egresso digita nº de processo (ex: 99999999)
2. AJAX: /verificar-processo
3. Sistema NÃO encontra
4. Devolve: sugerir_pendente = true
5. JS mostra aviso: "Número não encontrado. Ficará pendente."
6. Egresso completa todos os campos
7. Submete → RegisterController::register()
8. Sistema:
    - Cria User novo (is_active = false)
    - Cria Egresso com status "pendente"
    - Gera token de verificação (24h)
    - Envia email com link mágico
9. Redireciona para /login com mensagem
10. Egresso vai ao email e clica no link
11. VerificarEmailController::verificar()
    - Valida token
    - Marca user.email_verificado_at = now()
    - Notifica admin
12. Redireciona para /login
13. Admin aprova no painel
14. Egresso faz login ✅
```

### Cenário C — Empresa

Mesmo fluxo que o Cenário B, mas:
- Cria `Empresa` em vez de `Egresso`
- Tipo no payload é `'empresa'`
- Admin recebe notificação específica

---

## 🔍 Diferenças entre cenários

| Aspeto | Nível Ouro | Nível Prata | Empresa |
|--------|-----------|-------------|---------|
| **Nº de processo** | Existe na BD | Não existe | N/A |
| **Auto-preenchimento** | Sim | Não | Não |
| **Verificação** | OTP | Link mágico | Link mágico |
| **Ativação** | Após OTP | Após aprovação admin | Após aprovação admin |
| **Admin notificado** | Após OTP | Após verificar email | Após verificar email |
| **Egresso espera** | Login imediato | Aprovação | Aprovação |

---

## 📌 Códigos HTTP

| Código | Significado | Quando |
|--------|-------------|--------|
| 200 | OK | Fluxo normal |
| 302 | Redirect | Após submeter |
| 419 | CSRF Token expired | Sessão expirou |
| 422 | Validation error | Dados inválidos |
| 429 | Too many requests | Rate limit atingido |

---

## 🔗 Rotas principais

| Método | Rota | Nome | Controller |
|--------|------|------|------------|
| GET | `/registar` | `register` | `RegisterController@showRegisterForm` |
| POST | `/registar` | `register.post` | `RegisterController@register` |
| POST | `/verificar-processo` | `verificar.processo` | `RegisterController@verificarNumeroProcesso` |
| GET | `/registar/verificar-otp` | `register.verificar-otp` | `OtpVerificationController@showForm` |
| POST | `/registar/verificar-otp` | `register.verificar-otp.post` | `OtpVerificationController@verify` |
| GET | `/verificar-email/{token}` | `verificar.email` | `VerificarEmailController@verificar` |