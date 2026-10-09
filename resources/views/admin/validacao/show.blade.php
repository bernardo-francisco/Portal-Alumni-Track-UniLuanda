@extends('layouts.admin')

@section('title', 'Detalhes do Egresso')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-user-circle text-primary me-2"></i>
            Detalhes do Egresso
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.validacao.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informações do Egresso -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-id-card me-2"></i>
                        Informações Pessoais
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ $egresso->foto_url }}" 
                             alt="{{ $egresso->nome_completo }}" 
                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd;"
                             onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th class="bg-light" width="40%">Nome Completo</th>
                                    <td><strong>{{ $egresso->nome_completo }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Nº Processo</th>
                                    <td><code class="bg-light px-2 py-1 rounded">{{ $egresso->numero_processo }}</code></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Email</th>
                                    <td><a href="mailto:{{ $egresso->email }}">{{ $egresso->email }}</a></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Telefone</th>
                                    <td>{{ $egresso->telefone ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Gênero</th>
                                    <td>{{ $egresso->genero_label }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Data Nascimento</th>
                                    <td>{{ $egresso->data_nascimento?->format('d/m/Y') ?? 'Não informada' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Status Validação</th>
                                    <td>
                                        <span class="badge bg-{{ $egresso->status_validacao_badge }} fs-6 p-2">
                                            {{ $egresso->status_validacao_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Data Cadastro</th>
                                    <td>{{ $egresso->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações Acadêmicas e Ações -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Informações Acadêmicas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th class="bg-light" width="40%">Unidade Orgânica</th>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $egresso->unidade->sigla ?? 'N/A' }}
                                        </span>
                                        {{ $egresso->unidade->nome ?? 'Não informada' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Curso</th>
                                    <td>{{ $egresso->curso->nome ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Ano Formatura</th>
                                    <td>{{ $egresso->ano_formatura ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Nota Final</th>
                                    <td>{{ $egresso->nota_final ?? 'Não informada' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($egresso->observacoes)
                        <div class="alert alert-info mt-3">
                            <strong><i class="fas fa-info-circle me-2"></i>Observações:</strong><br>
                            {{ $egresso->observacoes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ações de Validação -->
            @if($egresso->status_validacao === 'pendente')
                <div class="card border-warning shadow-sm">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0 text-dark">
                            <i class="fas fa-bolt me-2"></i>
                            Ações de Validação
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Formulário de Aprovação -->
                        <form action="{{ route('admin.validacao.aprovar', $egresso->id) }}" 
                              method="POST" 
                              class="mb-3"
                              onsubmit="return confirm('Tem certeza que deseja APROVAR este egresso?')">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-comment me-1"></i>
                                    Observações (opcional)
                                </label>
                                <input type="text" 
                                       name="observacoes" 
                                       class="form-control" 
                                       placeholder="Observações sobre a aprovação...">
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-1"></i>
                                Aprovar Egresso
                            </button>
                        </form>

                        <hr>

                        <!-- Formulário de Reprovação -->
                        <form action="{{ route('admin.validacao.reprovar', $egresso->id) }}" 
                              method="POST"
                              onsubmit="return confirm('Tem certeza que deseja REPROVAR este egresso?')">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-exclamation-triangle me-1 text-danger"></i>
                                    Motivo da Reprovação <span class="text-danger">*</span>
                                </label>
                                <textarea name="motivo_reprovacao" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Explique o motivo da reprovação (mínimo 10 caracteres)..."
                                          required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-comment me-1"></i>
                                    Observações (opcional)
                                </label>
                                <input type="text" 
                                       name="observacoes" 
                                       class="form-control" 
                                       placeholder="Observações adicionais...">
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-times me-1"></i>
                                Reprovar Egresso
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-body text-center py-4">
                        @if($egresso->status_validacao === 'aprovado')
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <h4 class="text-success mt-3">✅ Egresso Aprovado!</h4>
                            <p class="text-muted">
                                Validado em: {{ $egresso->data_validacao?->format('d/m/Y H:i:s') }}
                            </p>
                        @elseif($egresso->status_validacao === 'reprovado')
                            <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
                            <h4 class="text-danger mt-3">❌ Egresso Reprovado!</h4>
                            @if($egresso->motivo_reprovacao)
                                <div class="alert alert-danger mt-3 text-start">
                                    <strong>Motivo:</strong><br>
                                    {{ $egresso->motivo_reprovacao }}
                                </div>
                            @endif
                            <p class="text-muted">
                                Validado em: {{ $egresso->data_validacao?->format('d/m/Y H:i:s') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

