@extends('layouts.admin')

@section('title', 'Gerenciar Perguntas')

@section('page_title', '❓ Gerenciar Perguntas')
@section('page_subtitle', $pesquisa->titulo)

@section('content')

<div class="row">
    <div class="col-12 col-xl-10 mx-auto">

        {{-- =========================================================
             CABEÇALHO
        ========================================================== --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.pesquisas.index') }}"
                   class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <div>
                    <h5 class="mb-0 fw-bold">{{ $pesquisa->titulo }}</h5>
                    <small class="text-muted">Adicione e gerencie as perguntas desta pesquisa</small>
                </div>
            </div>

            <div class="d-flex gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <i class="fas fa-question-circle me-1"></i>
                    {{ $perguntas->count() }} {{ $perguntas->count() == 1 ? 'pergunta' : 'perguntas' }}
                </span>

                <a href="{{ route('admin.pesquisas.resultados', $pesquisa->id) }}"
                   class="btn btn-outline-success btn-sm">
                    <i class="fas fa-chart-bar me-1"></i> Ver Resultados
                </a>
            </div>
        </div>


        {{-- =========================================================
             MENSAGENS
        ========================================================== --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <strong><i class="fas fa-exclamation-triangle me-1"></i> Verifique os erros:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


        {{-- =========================================================
             FORMULÁRIO — NOVA PERGUNTA
        ========================================================== --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Nova Pergunta</h6>
                        <small class="text-muted">Adicione uma pergunta à pesquisa</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST"
                      action="{{ route('admin.pesquisas.perguntas.store', $pesquisa->id) }}"
                      id="formPergunta">
                    @csrf

                    {{-- PERGUNTA --}}
                    <div class="mb-4">
                        <label for="pergunta" class="form-label fw-semibold">
                            Pergunta <span class="text-danger">*</span>
                        </label>
                        <textarea name="pergunta"
                                  id="pergunta"
                                  class="form-control"
                                  rows="3"
                                  maxlength="500"
                                  placeholder="Digite a pergunta que será apresentada ao egresso..."
                                  required>{{ old('pergunta') }}</textarea>
                        <div class="form-text">Máximo de 500 caracteres.</div>
                    </div>

                    {{-- TIPO --}}
                    <div class="mb-4">
                        <label for="tipo" class="form-label fw-semibold">
                            Tipo de Resposta <span class="text-danger">*</span>
                        </label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="texto"              {{ old('tipo', 'texto') === 'texto' ? 'selected' : '' }}>Texto Livre</option>
                            <option value="sim_nao"            {{ old('tipo') === 'sim_nao' ? 'selected' : '' }}>Sim / Não</option>
                            <option value="multipla_escolha"   {{ old('tipo') === 'multipla_escolha' ? 'selected' : '' }}>Múltipla Escolha (uma opção)</option>
                            <option value="selecao_multipla"   {{ old('tipo') === 'selecao_multipla' ? 'selected' : '' }}>Seleção Múltipla (várias opções)</option>
                            <option value="escala"             {{ old('tipo') === 'escala' ? 'selected' : '' }}>Escala (1 a 5)</option>
                        </select>
                        <div class="form-text" id="tipo_help">
                            O egresso poderá escrever uma resposta livre.
                        </div>
                    </div>

                    {{-- OPÇÕES PERSONALIZADAS --}}
                    <div id="opcoes_personalizadas" class="mb-4" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">
                                Opções de Resposta <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-light text-dark border" id="contador_opcoes">
                                2 opções
                            </span>
                        </div>

                        <div id="opcoes_container" class="mb-2">
                            @php
                                $oldOpcoes = old('opcoes', ['', '']);
                                if (!is_array($oldOpcoes) || count($oldOpcoes) < 2) {
                                    $oldOpcoes = ['', ''];
                                }
                            @endphp

                            @foreach($oldOpcoes as $index => $opcao)
                                <div class="input-group mb-2 opcao-item">
                                    <span class="input-group-text">{{ $index + 1 }}</span>
                                    <input type="text"
                                           name="opcoes[]"
                                           class="form-control opcao-input"
                                           placeholder="Digite a opção {{ $index + 1 }}"
                                           value="{{ $opcao }}"
                                           maxlength="255">
                                    <button type="button"
                                            class="btn btn-outline-danger remove-opcao"
                                            title="Remover opção"
                                            {{ count($oldOpcoes) <= 2 ? 'disabled' : '' }}>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="add_opcao">
                            <i class="fas fa-plus me-1"></i> Adicionar Opção
                        </button>

                        <div class="form-text mt-2" id="opcoes_help">
                            Adicione pelo menos 2 opções.
                        </div>
                    </div>

                    {{-- SIM / NÃO --}}
                    <div id="sim_nao_preview" class="mb-4" style="display: none;">
                        <label class="form-label fw-semibold">Opções disponíveis</label>
                        <div class="d-flex gap-2">
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle fs-6 px-3 py-2">
                                <i class="fas fa-check me-1"></i> Sim
                            </span>
                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle fs-6 px-3 py-2">
                                <i class="fas fa-times me-1"></i> Não
                            </span>
                        </div>
                        <div class="form-text">As opções Sim e Não são definidas automaticamente.</div>
                    </div>

                    {{-- ESCALA --}}
                    <div id="escala_preview" class="mb-4" style="display: none;">
                        <label class="form-label fw-semibold">Escala disponível</label>
                        <div class="d-flex flex-wrap gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                      style="width: 40px; height: 40px; font-size: 1rem;">
                                    {{ $i }}
                                </span>
                            @endfor
                        </div>
                        <div class="form-text mt-2">O egresso deverá escolher entre 1 e 5.</div>
                    </div>

                    {{-- OBRIGATÓRIA --}}
                    <div class="form-check mb-4">
                        <input type="checkbox"
                               name="obrigatoria"
                               value="1"
                               id="obrigatoria"
                               class="form-check-input"
                               {{ old('obrigatoria', '1') ? 'checked' : '' }}>
                        <label for="obrigatoria" class="form-check-label">
                            <strong>Pergunta obrigatória</strong>
                            <small class="text-muted d-block">O egresso deverá responder esta pergunta.</small>
                        </label>
                    </div>

                    {{-- BOTÕES --}}
                    <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="fas fa-save me-1"></i> Adicionar Pergunta
                        </button>

                        <button type="button" class="btn btn-outline-secondary" id="btnReset">
                            <i class="fas fa-undo me-1"></i> Limpar
                        </button>
                    </div>

                </form>
            </div>
        </div>


        {{-- =========================================================
             LISTA DE PERGUNTAS
        ========================================================== --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-list"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Perguntas da Pesquisa</h6>
                            <small class="text-muted">
                                {{ $perguntas->count() }} {{ $perguntas->count() == 1 ? 'pergunta' : 'perguntas' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                @if($perguntas->count() > 0)

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 report-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 60px;">#</th>
                                    <th>Pergunta</th>
                                    <th>Tipo</th>
                                    <th>Opções</th>
                                    <th class="text-center">Obrigatória</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perguntas as $pergunta)
                                    @php
                                        $tipos = [
                                            'texto'            => ['label' => 'Texto Livre',      'icon' => 'align-left',    'color' => 'secondary'],
                                            'sim_nao'          => ['label' => 'Sim / Não',        'icon' => 'toggle-on',     'color' => 'success'],
                                            'multipla_escolha' => ['label' => 'Múltipla Escolha', 'icon' => 'dot-circle',    'color' => 'primary'],
                                            'selecao_multipla' => ['label' => 'Seleção Múltipla', 'icon' => 'check-square',  'color' => 'info'],
                                            'escala'           => ['label' => 'Escala (1 a 5)',   'icon' => 'star',          'color' => 'warning'],
                                        ];
                                        $tipoInfo = $tipos[$pergunta->tipo] ?? ['label' => $pergunta->tipo, 'icon' => 'question', 'color' => 'secondary'];

                                        $opcoes = [];
                                        if (!empty($pergunta->opcoes)) {
                                            $decoded = json_decode($pergunta->opcoes, true);
                                            if (is_array($decoded)) {
                                                $opcoes = $decoded;
                                            }
                                        }
                                    @endphp

                                    <tr>
                                        <td class="ps-4">
                                            <span class="question-number">{{ $loop->iteration }}</span>
                                        </td>

                                        <td>
                                            <div class="fw-semibold" style="max-width: 400px;">
                                                {{ $pergunta->pergunta }}
                                            </div>
                                        </td>

                                        <td>
                                            <span class="badge bg-{{ $tipoInfo['color'] }}-subtle text-{{ $tipoInfo['color'] }}-emphasis border border-{{ $tipoInfo['color'] }}-subtle">
                                                <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                                                {{ $tipoInfo['label'] }}
                                            </span>
                                        </td>

                                        <td>
                                            @if(count($opcoes) > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($opcoes as $opcao)
                                                        <span class="badge bg-light text-dark border">{{ $opcao }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($pergunta->obrigatoria)
                                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                                    <i class="fas fa-check me-1"></i> Sim
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                                    Não
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-end pe-4">
                                            <form action="{{ route('admin.pesquisas.perguntas.destroy', $pergunta->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja eliminar esta pergunta? Esta ação não pode ser desfeita.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Eliminar pergunta">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @else

                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Nenhuma pergunta adicionada</h6>
                        <p class="text-muted small mb-0">
                            Utilize o formulário acima para adicionar a primeira pergunta.
                        </p>
                    </div>

                @endif
            </div>
        </div>


        {{-- =========================================================
             FINALIZAR
        ========================================================== --}}
        @if($perguntas->count() > 0)
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">
                <a href="{{ route('admin.pesquisas.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Voltar às Pesquisas
                </a>

                <a href="{{ route('admin.pesquisas.index') }}" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> Finalizar Pesquisa
                </a>
            </div>
        @endif

    </div>
</div>

