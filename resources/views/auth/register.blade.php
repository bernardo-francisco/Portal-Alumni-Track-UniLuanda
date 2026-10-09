@extends('layouts.app')

@section('title', 'Criar Conta')

@section('content')

<main class="register-shell">
    <section class="register-panel" aria-label="Área de cadastro">

        {{-- ============================================================
             LADO ESQUERDO — BOAS-VINDAS
        ============================================================ --}}
        <aside class="welcome-side">
            <div class="brand">
                <img src="{{ asset('uploads/home/uniluanda.webp') }}"
                     alt="Logotipo da Universidade"
                     class="university-logo">
            </div>

            <div class="welcome-content">
                <span class="eyebrow">Universidade de Luanda</span>
                <h2>Junte-se a nós!</h2>
                <p>Crie sua conta para se conectar à nossa rede institucional e aproveitar todas as oportunidades.</p>
            </div>

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
                <p>Preencha os campos abaixo para concluir seu cadastro.</p>
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
                @if(session('status') === 'pending_validation')
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
                     TIPO DE CONTA
                ============================================================ --}}
                <div class="field-group full-width">
                    <label for="tipo">Tipo de Conta <span class="required-star">*</span></label>
                    <div class="field">
                        <select id="tipo" name="tipo" required>
                            <option value="egresso" {{ old('tipo', 'egresso') == 'egresso' ? 'selected' : '' }}>Egresso (Ex-Estudante)</option>
                            <option value="admin" {{ old('tipo') == 'admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="empresa" {{ old('tipo') == 'empresa' ? 'selected' : '' }}>Empresa / Instituição</option>
                        </select>
                    </div>
                </div>

                {{-- ============================================================
                     CÓDIGO ADMIN
                ============================================================ --}}
                <div class="field-group full-width d-none" id="group_admin_code">
                    <label for="admin_code">Código de Autorização Admin <span class="required-star">*</span></label>
                    <div class="field">
                        <input id="admin_code" name="admin_code" type="password"
                               placeholder="Código: UNILUNDA2024"
                               value="{{ old('admin_code') }}">
                    </div>
                    <small class="text-muted">Código fornecido pela administração do sistema</small>
                </div>

                {{-- ============================================================
                     CAMPOS EMPRESA
                ============================================================ --}}
                <div id="group_empresa_fields" style="display: none; grid-column: 1 / -1;">
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
                        </div>

                        <div class="field-group">
                            <label for="empresa_website">Website</label>
                            <div class="field">
                                <input id="empresa_website" name="empresa_website" type="url"
                                       value="{{ old('empresa_website') }}"
                                       placeholder="https://suaempresa.ao">
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="empresa_provincia">Província</label>
                            <div class="field">
                                <input id="empresa_provincia" name="empresa_provincia" type="text"
                                       value="{{ old('empresa_provincia') }}"
                                       placeholder="Ex: Luanda">
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="empresa_localizacao">Localização</label>
                            <div class="field">
                                <input id="empresa_localizacao" name="empresa_localizacao" type="text"
                                       value="{{ old('empresa_localizacao') }}"
                                       placeholder="Ex: Talatona, Luanda">
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="empresa_logo">Logo da Empresa</label>
                            <div class="field">
                                <input id="empresa_logo" name="empresa_logo" type="file" accept="image/*">
                            </div>
                            <small class="text-muted">JPG, PNG ou JPEG. Máx: 2MB</small>
                        </div>

                        <div class="field-group span-3">
                            <label for="empresa_descricao">Descrição da Empresa</label>
                            <div class="field">
                                <input id="empresa_descricao" name="empresa_descricao" type="text"
                                       value="{{ old('empresa_descricao') }}"
                                       placeholder="Breve descrição da empresa e actividades">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ============================================================
                     DADOS PESSOAIS
                ============================================================ --}}
                <div class="section-divider">Dados Pessoais</div>

                <div class="field-group" id="group_nome_completo">
                    <label for="nome_completo">Nome Completo <span class="required-star" id="nome_required_star">*</span></label>
                    <div class="field">
                        <input id="nome_completo" name="nome_completo" type="text"
                               value="{{ old('nome_completo') }}"
                               placeholder="Ex: José Manuel Fernandes"
                               autocomplete="name">
                    </div>
                    <small class="text-muted" id="nome_help">Digite o seu nome completo</small>
                </div>

                <div class="field-group">
                    <label for="email">E-mail <span class="required-star">*</span></label>
                    <div class="field">
                        <input id="email" name="email" type="email"
                               value="{{ old('email') }}"
                               placeholder="seuemail@dominio.com"
                               autocomplete="email" required>
                    </div>
                </div>

                <div class="field-group">
                    <label for="telefone">Telefone</label>
                    <div class="field">
                        <input id="telefone" name="telefone" type="tel"
                               value="{{ old('telefone') }}"
                               placeholder="Ex: 923456789"
                               autocomplete="tel">
                    </div>
                </div>

                <div class="field-group" id="group_foto">
                    <label for="foto">Foto de Perfil</label>
                    <div class="field">
                        <input id="foto" name="foto" type="file" accept="image/*">
                    </div>
                    <small class="text-muted">JPG, PNG ou JPEG. Máx: 2MB</small>
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
                    <small class="text-muted">Unidade onde o administrador está vinculado (opcional)</small>
                </div>

                {{-- ============================================================
                     DADOS ACADÉMICOS (EGRESSO)
                ============================================================ --}}
                <div class="section-divider" id="group_egresso_divider">Dados Académicos</div>

                <div class="field-group" id="group_numero_processo">
                    <label for="numero_processo">Nº de Processo <span class="required-star" id="processo_required_star">*</span></label>
                    <div class="field">
                        <input id="numero_processo" name="numero_processo" type="text"
                               value="{{ old('numero_processo') }}"
                               placeholder="Ex: 20201234">
                    </div>
                    <div id="validationStatus" class="validation-status"></div>
                    <small class="text-muted" id="processo_help">Digite o número do seu processo académico</small>
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
                </div>

                <div class="field-group" id="group_data_nascimento">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <div class="field">
                        <input id="data_nascimento" name="data_nascimento" type="date"
                               value="{{ old('data_nascimento') }}">
                    </div>
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
                    <small class="text-muted" id="curso_help">Selecione o curso em que se formou</small>
                </div>

                <div class="field-group" id="group_ano_formatura" style="display: none;">
                    <label for="ano_formatura">Ano de Formatura</label>
                    <div class="field">
                        <input id="ano_formatura" name="ano_formatura" type="number"
                               min="1990" max="{{ date('Y') }}"
                               placeholder="Ex: 2024"
                               value="{{ old('ano_formatura') }}">
                    </div>
                </div>

                {{-- ============================================================
                     SEGURANÇA
                ============================================================ --}}
                <div class="section-divider">Segurança</div>

                <div class="field-group">
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

                    {{-- Barra de força da senha --}}
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="password-strength-bar"></div>
                        <div class="password-strength-bar"></div>
                        <div class="password-strength-bar"></div>
                        <div class="password-strength-bar"></div>
                    </div>
                    <span class="password-strength-label" id="passwordStrengthLabel"></span>

                    <small class="text-muted">Mínimo 6 caracteres</small>
                </div>

                <div class="field-group">
                    <label for="password_confirmation">Confirmar Senha <span class="required-star">*</span></label>
                    <div class="field">
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               placeholder="Repita a senha"
                               autocomplete="new-password" required>
                    </div>
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