@extends('layouts.app')

@section('title', 'Criar Conta')

@push('styles')
<style>
/* ============================================================
   REGISTER — Layout equilibrado e alinhado
============================================================ */

/* ---------- Welcome-side ---------- */
.register-panel .welcome-side {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 24px;
    padding: 32px 28px;
}

/* Logo — FORÇADO ao canto superior esquerdo, sem texto */
.register-panel .welcome-side .brand {
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    width: 100%;
    margin: 0;
    padding: 0;
    flex-shrink: 0;
}

.register-panel .welcome-side .brand .university-logo {
    width: 110px;
    height: 110px;
    padding: 6px;
    margin: 0;
    margin-right: auto;
    border-radius: 50%;
    background: #fff;
    object-fit: contain;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
    display: block;
}

/* ---------- Conteúdo do welcome-side ---------- */
.register-panel .welcome-content {
    max-width: 100%;
    margin: 0;
}

.register-panel .welcome-content .eyebrow {
    display: block;
    margin-bottom: 10px;
    color: #e0b93d;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.register-panel .welcome-content h2 {
    margin: 0 0 12px;
    color: #fff;
    font-size: 26px;
    font-weight: 700;
    line-height: 1.05;
    letter-spacing: -0.03em;
}

.register-panel .welcome-content p {
    max-width: 100%;
    margin: 0 0 14px;
    color: #e5dcc0;
    font-size: 12.5px;
    line-height: 1.55;
}

.register-panel .welcome-content ul {
    margin: 0 0 14px;
    padding: 0;
    list-style: none;
}

.register-panel .welcome-content ul li {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
    color: #e5dcc0;
    font-size: 11.5px;
    line-height: 1.4;
}

.register-panel .welcome-content ul li i {
    color: #c9a227;
    font-size: 10px;
    flex-shrink: 0;
}

.register-panel .quote {
    max-width: 100%;
    margin: 0;
    padding-top: 12px;
    border-top: 1px solid rgba(201, 162, 39, 0.4);
    color: #e5dcc0;
    font-size: 10.5px;
    line-height: 1.5;
}

.register-panel .quote strong {
    display: block;
    margin-top: 5px;
    color: #c9a227;
    font-size: 10px;
}

/* ---------- Formulário alinhado ---------- */
.register-form {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px 14px;
    align-items: start;
}

.register-form .field-group {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    gap: 4px;
    min-width: 0;
    height: auto;
}

.register-form .field-group label {
    min-height: 14px;
    margin: 0;
    color: #53647a;
    font-size: 10.5px;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: none;
    letter-spacing: 0;
}

.register-form .field-group .field {
    height: 38px;
    min-height: 38px;
    max-height: 38px;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 11px;
    border-radius: 8px;
}

.register-form .field-group .text-muted,
.register-form .field-group .help-text {
    display: block;
    min-height: 14px;
    margin: 0;
    color: var(--muted);
    font-size: 10px;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ---------- Larguras (grid spans) ---------- */
.register-form .field-group.full-width { grid-column: 1 / -1; }
.register-form .field-group.span-2    { grid-column: span 2; }
.register-form .field-group.span-3    { grid-column: span 3; }

/* ---------- Section dividers ---------- */
.register-form .section-divider {
    grid-column: 1 / -1;
    margin: 6px 0 0;
    padding-bottom: 4px;
    border-bottom: 2px solid #c9a227;
    color: #a67c00;
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

/* ---------- Botão submit ---------- */
.register-form .submit-button {
    grid-column: 1 / -1;
    width: 260px;
    height: 44px;
    margin: 12px auto 0;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%);
    color: #0a0a0a;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 0.3px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(201, 162, 39, 0.4);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.register-form .submit-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(201, 162, 39, 0.55);
}

/* ---------- Empresa: bloco interno ---------- */
#group_empresa_fields .register-form {
    margin-top: 0;
    grid-template-columns: repeat(4, 1fr);
}

/* ---------- Barra de força da senha ---------- */
.password-strength {
    display: flex;
    gap: 4px;
    margin-top: 4px;
}

.password-strength-bar {
    flex: 1;
    height: 3px;
    border-radius: 2px;
    background: #e6e2d3;
    transition: background 0.3s ease;
}

.password-strength-bar.weak   { background: #dc2626; }
.password-strength-bar.medium { background: #d97706; }
.password-strength-bar.strong { background: #16a34a; }

.password-strength-label {
    display: block;
    min-height: 12px;
    margin-top: 3px;
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

/* ============================================================
   TIPO DE CONTA — Alinhamento dinâmico
============================================================ */

/* Egresso E Empresa — CENTRADOS */
#tipo_wrapper.tipo-egresso,
#tipo_wrapper.tipo-empresa {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

#tipo_wrapper.tipo-egresso label,
#tipo_wrapper.tipo-empresa label {
    text-align: center;
    width: 100%;
}

#tipo_wrapper.tipo-egresso .field,
#tipo_wrapper.tipo-empresa .field {
    width: 100%;
    max-width: 420px;
}

/* Administrador — CANTO ESQUERDO */
#tipo_wrapper.tipo-admin {
    grid-column: span 2;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
}

#tipo_wrapper.tipo-admin label {
    text-align: left;
    width: auto;
}

#tipo_wrapper.tipo-admin .field {
    width: 100%;
    max-width: 100%;
}

/* ============================================================
   CAMPOS CENTRADOS — Descrição (Empresa) e Telefone (dinâmico)
============================================================ */
.register-form .field-group.centered {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.register-form .field-group.centered label {
    text-align: center;
    width: 100%;
}

.register-form .field-group.centered .field {
    width: 100%;
    max-width: 480px;
}

.register-form .field-group.centered .text-muted {
    text-align: center;
    width: 100%;
}

/* ---------- Responsivo ---------- */
@media (max-width: 1300px) {
    .register-form,
    #group_empresa_fields .register-form {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 1000px) {
    .register-form,
    #group_empresa_fields .register-form {
        grid-template-columns: repeat(2, 1fr);
    }
    .field-group.full-width,
    .field-group.span-2,
    .field-group.span-3 {
        grid-column: 1 / -1;
    }
    #tipo_wrapper.tipo-egresso,
    #tipo_wrapper.tipo-admin,
    #tipo_wrapper.tipo-empresa {
        grid-column: 1 / -1;
    }
    .register-form .field-group.centered {
        grid-column: 1 / -1;
    }
}

@media (max-width: 820px) {
    .register-panel .welcome-side {
        flex-direction: row;
        align-items: center;
        padding: 16px 20px;
        gap: 14px;
    }
    .register-panel .welcome-side .brand .university-logo {
        width: 70px;
        height: 70px;
    }
    .register-panel .welcome-content ul,
    .register-panel .welcome-content p,
    .register-panel .quote {
        display: none;
    }
    .register-panel .welcome-content h2 {
        font-size: 16px;
        margin-bottom: 0;
    }
    .register-panel .welcome-content .eyebrow {
        margin-bottom: 4px;
        font-size: 9px;
    }
    .register-form,
    #group_empresa_fields .register-form {
        grid-template-columns: 1fr 1fr;
        gap: 8px 12px;
    }
}

@media (max-width: 520px) {
    .register-form,
    #group_empresa_fields .register-form {
        grid-template-columns: 1fr;
    }
    .register-form .field-group.full-width,
    .register-form .field-group.span-2,
    .register-form .field-group.span-3 {
        grid-column: 1;
    }
    .register-form .field { height: 42px; min-height: 42px; max-height: 42px; }
    .register-form .submit-button { width: 100%; height: 46px; }
}

/* ============================================================
   ✅ FASE 1 — HONEYPOT (invisível mas presente no DOM)
============================================================ */
.hp-field {
    position: absolute !important;
    left: -9999px !important;
    top: -9999px !important;
    width: 1px;
    height: 1px;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
}

/* ============================================================
   ✅ FASE 3 — PAINEL DE REIVINDICAÇÃO
============================================================ */
.reivindicacao-panel {
    grid-column: 1 / -1;
    padding: 20px;
    margin: 8px 0;
    border: 2px solid #16a34a;
    border-radius: 12px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    animation: slideDown 0.3s ease-out;
}

.reivindicacao-panel .reivindicacao-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    color: #166534;
    font-size: 14px;
    font-weight: 700;
}

.reivindicacao-panel .reivindicacao-header i {
    font-size: 18px;
}

.reivindicacao-panel .reivindicacao-text {
    margin: 0 0 4px;
    color: #15803d;
    font-size: 12.5px;
    line-height: 1.5;
}

.reivindicacao-panel .reivindicacao-actions {
    display: flex;
    gap: 10px;
    margin-top: 16px;
    flex-wrap: wrap;
}

.reivindicacao-panel .btn-reivindicar {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.reivindicacao-panel .btn-sim {
    background: #16a34a;
    color: #fff;
}

.reivindicacao-panel .btn-sim:hover {
    background: #15803d;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.reivindicacao-panel .btn-nao {
    background: #fff;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.reivindicacao-panel .btn-nao:hover {
    background: #fef2f2;
    border-color: #f87171;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Campos bloqueados */
.register-form input[readonly],
.register-form select:disabled {
    background: #f0f0f0 !important;
    color: #666 !important;
    cursor: not-allowed !important;
}


</style>
@endpush

@section('content')

<main class="register-shell">
    <section class="register-panel" aria-label="Área de cadastro">

        {{-- ============================================================
             LADO ESQUERDO — BOAS-VINDAS
        ============================================================ --}}
        <aside class="welcome-side">

            {{-- LOGO (canto superior esquerdo, sem texto) --}}
            <div class="brand">
                <img src="{{ asset('uploads/home/uniluanda.webp') }}"
                     alt="Logotipo da Universidade de Luanda"
                     class="university-logo">
            </div>

            {{-- CONTEÚDO --}}
            <div class="welcome-content">
                <span class="eyebrow">Junte-se a nós!</span>
                <h2>Faça parte da<br>nossa rede</h2>

                <p>
                    Crie a sua conta e conecte-se a uma comunidade activa de
                    ex-estudantes da Universidade de Luanda. Acesso a
                    networking, oportunidades profissionais e eventos exclusivos.
                </p>

                <ul>
                    <li><i class="fas fa-check-circle"></i> Encontre antigos colegas de turma</li>
                    <li><i class="fas fa-check-circle"></i> Descubra vagas e estágios exclusivos</li>
                    <li><i class="fas fa-check-circle"></i> Participe em eventos e workshops</li>
                    <li><i class="fas fa-check-circle"></i> Fortaleça o seu percurso profissional</li>
                </ul>
            </div>

            {{-- QUOTE --}}
            <p class="quote">
                Rede de ex-estudantes — conexões que transformam carreiras.
                <strong>Portal Alumni Track</strong>
            </p>

        </aside>

        {{-- ============================================================
             LADO DIREITO — FORMULÁRIO
        ============================================================ --}}
        <div class="form-side">
            <div class="form-intro">
                <h1>Criar conta</h1>
                <p>Preencha os campos abaixo para concluir o seu cadastro.</p>
            </div>

            {{-- Mensagens de estado --}}
            @if ($errors->any())
                <div class="error-message">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('error'))
                <div class="error-message">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                @if(session('status') === 'pending_email_verification')
                    {{-- ✅ FASE 2 — Aguarda verificação de email --}}
                    <div class="pending-message">
                        <strong>{{ session('success') }}</strong>
                        <span>{{ session('prazo', 'Verifique também a pasta de spam') }}</span>
                        <br><small>Depois de verificar o email, o seu registo será enviado para a administração.</small>
                    </div>
                @elseif(session('status') === 'pending_validation')
                    {{-- Aguarda validação do admin (FASE 3 - reivindicação já aprovada) --}}
                    <div class="pending-message">
                        <strong>{{ session('success') }}</strong>
                        <span>Prazo estimado: {{ session('prazo', 'Até 5 dias úteis') }}</span>
                        <br><small>Você receberá um email quando sua conta for aprovada.</small>
                    </div>
                @else
                    <div class="success-message">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif
            @endif

            <form class="register-form"
                  method="POST"
                  action="{{ route('register') }}"
                  enctype="multipart/form-data"
                  id="registerForm">
                @csrf

                {{-- ============================================================
                     ✅ FASE 1 — Anti-Bot (Honeypot + Time-check)
                ============================================================ --}}

                {{-- Honeypot: invisível para humanos, visível para bots --}}
                <div class="hp-field" aria-hidden="true">
                    <label for="website_url">Não preencha este campo</label>
                    <input type="text"
                           id="website_url"
                           name="website_url"
                           tabindex="-1"
                           autocomplete="off">
                </div>

                {{-- Timestamp do carregamento do formulário --}}
                <input type="hidden"
                       name="form_loaded_at"
                       value="{{ now()->timestamp }}">

                {{-- ============================================================
                     TIPO DE CONTA (dinâmico) + CÓDIGO ADMIN
                ============================================================ --}}
                <div class="field-group span-2 tipo-wrapper tipo-egresso" id="tipo_wrapper">
                    <label for="tipo">Tipo de Conta <span class="required-star">*</span></label>
                    <div class="field">
                        <select id="tipo" name="tipo" required>
                            <option value="egresso" {{ old('tipo', 'egresso') == 'egresso' ? 'selected' : '' }}>Egresso (Ex-Estudante)</option>
                            <option value="admin" {{ old('tipo') == 'admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="empresa" {{ old('tipo') == 'empresa' ? 'selected' : '' }}>Empresa / Instituição</option>
                        </select>
                    </div>
                </div>

                <div class="field-group span-2 d-none" id="group_admin_code">
                    <label for="admin_code">Código de Autorização Admin <span class="required-star">*</span></label>
                    <div class="field">
                        <input id="admin_code" name="admin_code" type="password"
                               placeholder="Código: UNILUNDA2024"
                               value="{{ old('admin_code') }}">
                    </div>
                    <small class="text-muted">Código fornecido pela administração</small>
                </div>

                {{-- ============================================================
                     CAMPOS EMPRESA
                ============================================================ --}}
                <div id="group_empresa_fields" class="full-width" style="display: none; grid-column: 1 / -1;">

                    <div class="register-form" style="margin-top: 0;">

                        <div class="section-divider">Dados da Empresa</div>

                        <div class="field-group span-2">
                            <label for="empresa_nome">Nome da Empresa <span class="required-star">*</span></label>
                            <div class="field">
                                <input id="empresa_nome" name="empresa_nome" type="text"
                                       value="{{ old('empresa_nome') }}"
                                       placeholder="Ex: TechAngola Lda.">
                            </div>
                           
                        </div>

                        <div class="field-group">
                            <label for="empresa_nif">NIF</label>
                            <div class="field">
                                <input id="empresa_nif" name="empresa_nif" type="text"
                                       value="{{ old('empresa_nif') }}"
                                       placeholder="Ex: 5001234567">
                            </div>
                            <small class="text-muted">&nbsp;</small>
                        </div>

                        <div class="field-group">
                            <label for="empresa_sector">Sector <span class="required-star">*</span></label>
                            <div class="field">
                                <select id="empresa_sector" name="empresa_sector">
                                    <option value="">Selecione...</option>
                                    <option value="Tecnologia" {{ old('empresa_sector') == 'Tecnologia' ? 'selected' : '' }}>Tecnologia</option>
                                    <option value="Banca" {{ old('empresa_sector') == 'Banca' ? 'selected' : '' }}>Banca / Finanças</option>
                                    <option value="Saúde" {{ old('empresa_sector') == 'Saúde' ? 'selected' : '' }}>Saúde</option>
                                    <option value="Educação" {{ old('empresa_sector') == 'Educação' ? 'selected' : '' }}>Educação</option>
                                    <option value="Construção" {{ old('empresa_sector') == 'Construção' ? 'selected' : '' }}>Construção</option>
                                    <option value="Consultoria" {{ old('empresa_sector') == 'Consultoria' ? 'selected' : '' }}>Consultoria</option>
                                    <option value="Outro" {{ old('empresa_sector') == 'Outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                            </div>
                            <small class="text-muted">&nbsp;</small>
                        </div>

                        <div class="field-group">
                            <label for="empresa_website">Website</label>
                            <div class="field">
                                <input id="empresa_website" name="empresa_website" type="url"
                                       value="{{ old('empresa_website') }}"
                                       placeholder="https://suaempresa.ao">
                            </div>
                            <small class="text-muted">&nbsp;</small>
                        </div>

                        <div class="field-group">
                            <label for="empresa_provincia">Província</label>
                            <div class="field">
                                <input id="empresa_provincia" name="empresa_provincia" type="text"
                                       value="{{ old('empresa_provincia') }}"
                                       placeholder="Ex: Luanda">
                            </div>
                            <small class="text-muted">&nbsp;</small>
                        </div>

                        <div class="field-group">
                            <label for="empresa_localizacao">Localização</label>
                            <div class="field">
                                <input id="empresa_localizacao" name="empresa_localizacao" type="text"
                                       value="{{ old('empresa_localizacao') }}"
                                       placeholder="Ex: Talatona, Luanda">
                            </div>
                            <small class="text-muted">&nbsp;</small>
                        </div>

                        <div class="field-group">
                            <label for="empresa_logo">Logo da Empresa</label>
                            <div class="field">
                                <input id="empresa_logo" name="empresa_logo" type="file" accept="image/*">
                            </div>
                            
                        </div>

                        {{-- Descrição da Empresa — CENTRADO --}}
                        <div class="field-group centered">
                            <label for="empresa_descricao">Descrição da Empresa</label>
                            <div class="field">
                                <input id="empresa_descricao" name="empresa_descricao" type="text"
                                       value="{{ old('empresa_descricao') }}"
                                       placeholder="Breve descrição da empresa e actividades">
                            </div>
                            <small class="text-muted">Breve descrição sobre a empresa</small>
                        </div>

                    </div>
                </div>

                {{-- ============================================================
                     DADOS PESSOAIS
                ============================================================ --}}
                <div class="section-divider">Dados Pessoais</div>

                <div class="field-group span-2" id="group_nome_completo">
                    <label for="nome_completo">Nome Completo <span class="required-star" id="nome_required_star">*</span></label>
                    <div class="field">
                        <input id="nome_completo" name="nome_completo" type="text"
                               value="{{ old('nome_completo') }}"
                               placeholder="Ex: José Manuel Fernandes"
                               autocomplete="name">
                    </div>
                    
                </div>

                <div class="field-group span-2">
                    <label for="email">E-mail <span class="required-star">*</span></label>
                    <div class="field">
                        <input id="email" name="email" type="email"
                               value="{{ old('email') }}"
                               placeholder="seuemail@dominio.com"
                               autocomplete="email" required>
                    </div>
                  
                </div>

                {{-- Telefone — dinâmico (só centra em Empresa) --}}
                <div class="field-group span-2" id="telefone_wrapper">
                    <label for="telefone">Telefone</label>
                    <div class="field">
                        <input id="telefone" name="telefone" type="tel"
                               value="{{ old('telefone') }}"
                               placeholder="Ex: 923456789"
                               autocomplete="tel">
                    </div>
                    
                </div>

                <div class="field-group span-2" id="group_foto">
                    <label for="foto">Foto de Perfil</label>
                    <div class="field">
                        <input id="foto" name="foto" type="file" accept="image/*">
                    </div>
                    
                </div>

                {{-- ============================================================
                     VÍNCULO INSTITUCIONAL ADMIN
                ============================================================ --}}
                <div class="section-divider" id="group_unidade_admin_label" style="display: none;">
                    Vínculo Institucional
                </div>

                <div class="field-group full-width" id="group_unidade_admin" style="display: none;">
                    <label for="unidade_id">
                        Unidade Orgânica
                        <span style="color: var(--muted); font-weight: normal;">(opcional)</span>
                    </label>
                    <div class="field">
                        <select id="unidade_id" name="unidade_id">
                            <option value="" selected>Selecione a Fac./Instituto</option>
                            @if(isset($unidades) && $unidades->count() > 0)
                                @foreach($unidades as $unidade)
                                    <option value="{{ $unidade->id }}" {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                        {{ $unidade->sigla }} - {{ $unidade->nome }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>Nenhuma unidade disponível</option>
                            @endif
                        </select>
                    </div>
                    
                </div>

                {{-- ============================================================
                     DADOS ACADÉMICOS (EGRESSO)
                ============================================================ --}}
                <div class="section-divider" id="group_egresso_divider">Dados Académicos</div>

                <div class="field-group span-2" id="group_numero_processo">
                    <label for="numero_processo">Nº de Processo <span class="required-star" id="processo_required_star">*</span></label>
                    <div class="field">
                        <input id="numero_processo" name="numero_processo" type="text"
                               value="{{ old('numero_processo') }}"
                               placeholder="Ex: 20201234">
                    </div>
                    <div id="validationStatus" class="validation-status"></div>
                    
                </div>

                {{-- ============================================================
                    ✅ FASE 3 — Painel de confirmação de reivindicação
                ============================================================ --}}
                <div id="reivindicacaoPanel" class="reivindicacao-panel" style="display:none;">
                    <div class="reivindicacao-header">
                        <i class="fas fa-user-check"></i>
                        <strong>Encontrámos o seu registo!</strong>
                    </div>

                    <p class="reivindicacao-text">
                        Confirme se estes dados correspondem a si:
                    </p>

                    <div id="reivindicacaoInfo"></div>

                    <div class="reivindicacao-actions">
                        <button type="button" id="btnSimSouEu" class="btn-reivindicar btn-sim">
                            <i class="fas fa-check"></i> Sim, sou eu
                        </button>
                        <button type="button" id="btnNaoSouEu" class="btn-reivindicar btn-nao">
                            <i class="fas fa-times"></i> Não sou eu
                        </button>
                    </div>
                </div>

                <div class="field-group" id="group_genero">
                    <label for="genero">Género</label>
                    <div class="field">
                        <select id="genero" name="genero">
                            <option value="" selected>Selecione</option>
                            <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Feminino</option>
                            <option value="O" {{ old('genero') == 'O' ? 'selected' : '' }}>Outro</option>
                        </select>
                    </div>
                    <small class="text-muted">&nbsp;</small>
                </div>

                <div class="field-group" id="group_data_nascimento">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <div class="field">
                        <input id="data_nascimento" name="data_nascimento" type="date"
                               value="{{ old('data_nascimento') }}">
                    </div>
                    <small class="text-muted">&nbsp;</small>
                </div>

                {{-- Campos de validação manual --}}
                <div class="field-group span-3" id="group_curso" style="display: none;">
                    <label for="curso_id">Curso <span class="required-star" id="curso_required_star">*</span></label>
                    <div class="field">
                        <select id="curso_id" name="curso_id">
                            <option value="" {{ old('curso_id') ? '' : 'selected' }}>Selecione o curso</option>
                            @if(isset($cursos) && $cursos->count() > 0)
                                @foreach($cursos as $curso)
                                    <option value="{{ $curso->id }}" {{ old('curso_id') == $curso->id ? 'selected' : '' }}>
                                        {{ $curso->nome }} ({{ $curso->codigo }}) — {{ $curso->unidade->sigla ?? 'Sem unidade' }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>Nenhum curso disponível</option>
                            @endif
                        </select>
                    </div>
                    
                </div>

            <div class="field-group" id="group_ano_formatura" style="display: none;">
                <label for="ano_formatura">Ano de Formatura</label>
                <div class="field">
                    <input id="ano_formatura" name="ano_formatura" type="number"
                        min="1990" max="{{ date('Y') + 5 }}" step="1"
                        inputmode="numeric"
                        placeholder="Ex: {{ date('Y') }}"
                        value="{{ old('ano_formatura') }}"
                        autocomplete="off">
                </div>
                <small class="text-muted">&nbsp;</small>
            </div>
                {{-- ============================================================
                     SEGURANÇA
                ============================================================ --}}
                <div class="section-divider">Segurança</div>

                <div class="field-group span-2">
                    <label for="password">Senha <span class="required-star">*</span></label>
                    <div class="field">
                        <input id="password" name="password" type="password"
                               placeholder="Sua senha"
                               autocomplete="new-password"
                               required minlength="6">
                        <button type="button"
                                class="toggle-password"
                                id="togglePassword"
                                aria-label="Mostrar senha">◉</button>
                    </div>

                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="password-strength-bar"></div>
                        <div class="password-strength-bar"></div>
                        <div class="password-strength-bar"></div>
                        <div class="password-strength-bar"></div>
                    </div>
                    <span class="password-strength-label" id="passwordStrengthLabel"></span>

                    <small class="text-muted">Mínimo 6 caracteres</small>
                </div>

                <div class="field-group span-2">
                    <label for="password_confirmation">Confirmar Senha <span class="required-star">*</span></label>
                    <div class="field">
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               placeholder="Repita a senha"
                               autocomplete="new-password" required>
                    </div>
                    <small class="text-muted">&nbsp;</small>
                </div>

                <button type="submit" class="submit-button" id="submitButton">
                    <span id="buttonText">Criar Conta →</span>
                </button>
            </form>

            <p class="login-link">
                Já possui uma conta?
                <a href="{{ route('login') }}">Fazer login</a>
            </p>
        </div>
    </section>
</main>

@endsection
@push('scripts')

    {{-- ✅ FASE 3 — Rotas para o JS (window.APP_ROUTES) --}}
    <script>
        window.APP_ROUTES = {
            verificarProcesso: "{{ route('verificar.processo') }}",
            csrfToken: "{{ csrf_token() }}",
        };
    </script>

    @vite('resources/js/auth.js')

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo');
        const tipoWrapper = document.getElementById('tipo_wrapper');
        const telefoneWrapper = document.getElementById('telefone_wrapper');

        if (!tipoSelect || !tipoWrapper) return;

        function atualizarPosicaoTipo() {
            const valor = tipoSelect.value;

            // ---------- Tipo de Conta ----------
            tipoWrapper.classList.remove('tipo-egresso', 'tipo-admin', 'tipo-empresa');

            if (valor === 'egresso') {
                tipoWrapper.classList.add('tipo-egresso');
            } else if (valor === 'admin') {
                tipoWrapper.classList.add('tipo-admin');
            } else if (valor === 'empresa') {
                tipoWrapper.classList.add('tipo-empresa');
            }

            // ---------- Telefone (só centra em Empresa) ----------
            if (telefoneWrapper) {
                if (valor === 'empresa') {
                    telefoneWrapper.classList.add('centered');
                } else {
                    telefoneWrapper.classList.remove('centered');
                }
            }
        }

        atualizarPosicaoTipo();
        tipoSelect.addEventListener('change', atualizarPosicaoTipo);
    });
    </script>
@endpush