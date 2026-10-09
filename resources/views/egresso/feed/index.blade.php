@extends('layouts.egresso')

@section('title', 'Feed de Notícias')

@section('page_title', '📰 Feed de Notícias')
@section('page_subtitle', 'Veja e partilhe novidades com a comunidade de egressos')

@section('content')

{{-- ============================================================
     FORMULÁRIO DE NOVA PUBLICAÇÃO
============================================================ --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
    <div class="card-body">
        @php
            $egressoAuth = Auth::user()->egresso;
            $iniciais = mb_strtoupper(mb_substr($egressoAuth->nome_completo ?? 'E', 0, 1));
        @endphp

        <form action="{{ route('egresso.feed.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="d-flex align-items-start gap-3 mb-3">
                @if($egressoAuth && $egressoAuth->foto_url)
                    <img src="{{ asset($egressoAuth->foto_url) }}"
                         alt="{{ $egressoAuth->nome_completo }}"
                         class="rounded-circle border"
                         style="width: 48px; height: 48px; object-fit: cover;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                         style="width: 48px; height: 48px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 1.1rem;">
                        {{ $iniciais }}
                    </div>
                @endif

                <div class="flex-grow-1">
                    <textarea name="conteudo"
                              class="form-control border-0 bg-light @error('conteudo') is-invalid @enderror"
                              rows="3"
                              placeholder="No que estás a pensar, {{ $egressoAuth->nome_completo ?? 'egresso' }}?"
                              maxlength="2000"
                              style="resize: none; border-radius: 12px;">{{ old('conteudo') }}</textarea>

                    @error('conteudo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div id="previewImagem" class="mb-3 d-none">
                <div class="position-relative d-inline-block">
                    <img src="" id="previewImg" class="img-thumbnail" style="max-height: 200px;">
                    <button type="button"
                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle"
                            onclick="removerImagem()"
                            style="width: 28px; height: 28px; padding: 0;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            @error('imagem')
                <div class="alert alert-danger py-2 px-3 mb-2" style="font-size: 0.85rem;">{{ $message }}</div>
            @enderror

            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                <label for="imagem" class="btn btn-sm btn-light border" style="cursor: pointer;">
                    <i class="fas fa-image text-success"></i>
                    <span class="d-none d-sm-inline ms-1">Imagem</span>
                    <input type="file"
                           name="imagem"
                           id="imagem"
                           class="d-none"
                           accept="image/*"
                           onchange="previewImagem(this)">
                </label>

                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-paper-plane me-1"></i> Publicar
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ============================================================
     ALERTAS
============================================================ --}}
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


{{-- ============================================================
     LISTA DE PUBLICAÇÕES
============================================================ --}}
@forelse($publicacoes as $publicacao)
    @php
        $autor         = $publicacao->autor;
        $isAdmin       = $autor['tipo'] === 'admin';
        $nomeAutor     = $autor['nome'];
        $fotoAutor     = $autor['foto'];
        $iniciaisAutor = mb_strtoupper(mb_substr($nomeAutor, 0, 1));

        $corFundo = $isAdmin
            ? 'linear-gradient(135deg, #dc3545, #b02a37)'
            : 'linear-gradient(135deg, #1a56db, #3b82f6)';

        $curtidoPeloUser = in_array($publicacao->id, $curtidas ?? []);

        $meuEgressoId = Auth::user()->egresso->id ?? null;
        $isAutor = !$isAdmin
            && $meuEgressoId !== null
            && $publicacao->egresso_id == $meuEgressoId;
    @endphp

    <div class="card border-0 shadow-sm mb-4"
         style="border-radius: 16px;"
         data-publicacao-id="{{ $publicacao->id }}"
         data-conteudo="{{ e($publicacao->conteudo) }}"
         data-imagem="{{ $publicacao->imagem_url ? asset($publicacao->imagem_url) : '' }}">

        {{-- HEADER --}}
        <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center gap-3">
                    @if($fotoAutor)
                        <img src="{{ asset($fotoAutor) }}"
                             alt="{{ $nomeAutor }}"
                             class="rounded-circle border"
                             style="width: 48px; height: 48px; object-fit: cover;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width: 48px; height: 48px; background: {{ $corFundo }}; font-size: 1.1rem;">
                            {{ $iniciaisAutor }}
                        </div>
                    @endif

                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <strong>{{ $nomeAutor }}</strong>
                            <span class="badge {{ $isAdmin ? 'bg-danger' : 'bg-primary' }}" style="font-size: 0.65rem;">
                                {{ $isAdmin ? 'Admin' : 'Egresso' }}
                            </span>
                        </div>
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i>
                            {{ $publicacao->tempo_decorrido }}
                        </small>
                    </div>
                </div>

                {{-- BOTÃO ⋯ SÓ PARA O AUTOR --}}
                @if($isAutor)
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <button type="button"
                                        class="dropdown-item"
                                        onclick="abrirModalEditar({{ $publicacao->id }})">
                                    <i class="fas fa-edit me-2 text-primary"></i> Editar
                                </button>
                            </li>
                            <li>
                                <form action="{{ route('egresso.feed.destroy', $publicacao->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Tem certeza que deseja eliminar esta publicação?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash me-2"></i> Eliminar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        {{-- BODY --}}
        <div class="card-body pt-2">
            <p class="card-text mb-3" style="white-space: pre-wrap; line-height: 1.6;">
                {{ $publicacao->conteudo }}
            </p>

            @if($publicacao->imagem_url)
                <div class="mb-3">
                    <img src="{{ asset($publicacao->imagem_url) }}"
                         alt="Imagem da publicação"
                         class="img-fluid rounded"
                         style="max-height: 500px; cursor: pointer;"
                         onclick="abrirImagem('{{ asset($publicacao->imagem_url) }}')">
                </div>
            @endif
        </div>

        {{-- FOOTER --}}
        <div class="card-footer bg-white border-top py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-3">
                    <button type="button"
                            class="btn btn-link text-decoration-none p-0 btn-curtir {{ $curtidoPeloUser ? 'text-danger' : 'text-muted' }}"
                            onclick="curtir({{ $publicacao->id }})">
                        <i class="fas fa-heart me-1"></i>
                        <span class="curtidas-count">{{ $publicacao->total_curtidas ?? 0 }}</span>
                    </button>

                    <button type="button" class="btn btn-link text-decoration-none p-0 text-muted">
                        <i class="fas fa-comment me-1"></i>
                        <span>{{ $publicacao->total_comentarios ?? 0 }}</span>
                    </button>
                </div>

                <small class="text-muted">{{ $publicacao->created_at->format('d/m/Y') }}</small>
            </div>
        </div>
    </div>
@empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
            <h5 class="fw-bold">Ainda não há publicações</h5>
            <p class="text-muted mb-0">Sê o primeiro a partilhar algo com a comunidade!</p>
        </div>
    </div>
@endforelse

<div class="d-flex justify-content-center mt-4">
    {{ $publicacoes->links() }}
</div>

@endsection
{{-- ⬆️ FIM DA SECTION 'content' ⬆️ --}}


{{-- ============================================================
     MODAIS (FORA DO @section('content') — IMPORTANTE!)
============================================================ --}}

{{-- MODAL EDITAR --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form id="formEditar" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-edit text-primary me-2"></i> Editar Publicação
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Conteúdo</label>
                        <textarea name="conteudo"
                                  id="editConteudo"
                                  class="form-control"
                                  rows="5"
                                  maxlength="2000"
                                  required></textarea>
                    </div>

                    <div class="mb-3" id="imagemAtualBox" style="display: none;">
                        <label class="form-label fw-semibold">Imagem Atual</label>
                        <div class="d-flex align-items-start gap-3">
                            <img id="imagemAtualPreview" class="img-thumbnail" style="max-height: 120px;">
                            <div class="form-check">
                                <input type="checkbox"
                                       name="remover_imagem"
                                       value="1"
                                       class="form-check-input"
                                       id="removerImagemCheck">
                                <label class="form-check-label text-danger small" for="removerImagemCheck">
                                    <i class="fas fa-trash me-1"></i> Remover imagem
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Substituir Imagem (opcional)</label>
                        <input type="file"
                               name="imagem"
                               id="editImagem"
                               class="form-control"
                               accept="image/*"
                               onchange="previewNovaImagem(this)">
                        <small class="text-muted">Deixe em branco para manter a atual. Máx. 4MB.</small>
                    </div>

                    <div id="previewNovaImagemBox" class="d-none mt-3">
                        <label class="form-label fw-semibold small">Nova imagem:</label>
                        <div>
                            <img id="previewNovaImagemImg" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL IMAGEM AMPLIADA --}}
<div class="modal fade" id="modalImagem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 text-center">
                <img src="" id="imagemAmpliada" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</div>

