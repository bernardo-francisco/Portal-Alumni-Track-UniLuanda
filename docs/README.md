# 📚 Documentação — UniLuanda Alumni Track

Documentação técnica do sistema de registo e validação de egressos e empresas.

## 📖 Índice

| Documento | Descrição |
|-----------|-----------|
| [SEGURANCA.md](SEGURANCA.md) | Camadas de segurança implementadas |
| [FLUXO_REGISTO.md](FLUXO_REGISTO.md) | Fluxo completo do registo de egressos e empresas |
| [MANUTENCAO.md](MANUTENCAO.md) | Como dar manutenção ao sistema |

## 🎯 Visão Geral

O sistema tem **3 camadas de segurança** que trabalham em conjunto:

1. **FASE 1 — Anti-Spam** (Rate limiting + Honeypot + Emails descartáveis)
2. **FASE 2 — Verificação de Email** (Link mágico por email)
3. **FASE 3 — Reivindicação de Conta** (OTP para egressos criados pelo admin)

## 🏗️ Tecnologias

- **Laravel 12**
- **MySQL/MariaDB**
- **Vite** (build de assets)
- **Bootstrap 5** (UI)
- **Gmail SMTP** (envio de emails)

## 🚀 Começar

Para entender o sistema, leia os documentos **por ordem**:

1. [FLUXO_REGISTO.md](FLUXO_REGISTO.md) — como funciona o registo
2. [SEGURANCA.md](SEGURANCA.md) — quais as proteções
3. [MANUTENCAO.md](MANUTENCAO.md) — como manter