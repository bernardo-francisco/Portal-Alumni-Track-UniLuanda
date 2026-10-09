@extends('layouts.egresso')

@section('title', 'Novo Feedback')

@section('page_title', '💬 Novo Feedback')
@section('page_subtitle', 'Compartilhe a sua opinião sobre o curso')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">

        {{-- ============================================================
             INTRO CARD
        ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4 feedback-intro-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3">

                    <div class="feedback-intro-icon">
                        <i class="fas fa-comment-dots"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">A sua opinião importa</h5>
                        <p class="text-muted small mb-0">
                            Partilhe a sua experiência ou sugestão.
                            O seu feedback será analisado pela administração
                            e poderá ser publicado na comunidade.
                        </p>
                    </div>

                </div>
            </div>
        </div>


        {{-- ============================================================
             FORMULÁRIO
        ============================================================ --}}
        <form method="POST" action="{{ route('egresso.feedbacks.store') }}" id="formFeedback">
            @csrf

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Detalhes do Feedback</h6>
                            <small class="text-muted">Preencha os campos abaixo</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">

                    {{-- ============================================================
                         CURSO
                    ============================================================ --}}
                    <div class="mb-4">
                        <label for="curso_id" class="form-label fw-semibold">
                            <i class="fas fa-graduation-cap text-muted me-1"></i>
                            Curso
                            <span class="text-muted fw-normal ms-1">(opcional)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-book text-muted"></i>
                            </span>
                            <select name="curso_id"
                                    id="curso_id"
                                    class="form-select border-start-0 @error('curso_id') is-invalid @enderror">
                                <option value="">Selecione um curso (opcional)</option>
                                @foreach($cursos as $curso)
                                    <option value="{{ $curso->id }}" {{ old('curso_id') == $curso->id ? 'selected' : '' }}>
                                        {{ $curso->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('curso_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Associe o feedback a um curso específico, se aplicável.
                        </div>
                    </div>


                    {{-- ============================================================
                         TÍTULO
                    ============================================================ --}}
                    <div class="mb-4">
                        <label for="titulo" class="form-label fw-semibold">
                            <i class="fas fa-heading text-muted me-1"></i>
                            Título <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-pen text-muted"></i>
                            </span>
                            <input type="text"
                                   name="titulo"
                                   id="titulo"
                                   class="form-control border-start-0 @error('titulo') is-invalid @enderror"
                                   placeholder="Ex: Excelente experiência no curso de Engenharia"
                                   value="{{ old('titulo') }}"
                                   maxlength="255"
                                   required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    {{-- ============================================================
                         MENSAGEM
                    ============================================================ --}}
                    <div class="mb-4">
                        <label for="mensagem" class="form-label fw-semibold">
                            <i class="fas fa-align-left text-muted me-1"></i>
                            Mensagem <span class="text-danger">*</span>
                        </label>
                        <textarea name="mensagem"
                                  id="mensagem"
                                  class="form-control @error('mensagem') is-invalid @enderror"
                                  rows="6"
                                  maxlength="2000"
                                  placeholder="Partilhe a sua opinião, sugestão ou crítica construtiva..."
                                  required>{{ old('mensagem') }}</textarea>
                        @error('mensagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Seja específico e construtivo no seu feedback.
                            </div>
                            <small class="text-muted" id="charCounter">0 / 2000</small>
                        </div>
                    </div>


                    {{-- ============================================================
                         AVALIAÇÃO (ESTRELAS)
                    ============================================================ --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-star text-warning me-1"></i>
                            Avaliação
                            <span class="text-muted fw-normal ms-1">(opcional)</span>
                        </label>

                        <div class="rating-wrapper">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="rating-item" data-value="{{ $i }}" title="{{ $i }} estrela{{ $i > 1 ? 's' : '' }}">
                                    <input type="radio"
                                           name="nota"
                                           id="nota{{ $i }}"
                                           value="{{ $i }}"
                                           class="rating-input"
                                           {{ old('nota') == $i ? 'checked' : '' }}>
                                    <i class="fas fa-star rating-star"></i>
                                </label>
                            @endfor

                            <span class="rating-label ms-2" id="ratingLabel">Selecione uma avaliação</span>
                        </div>

                        @error('nota')
                            <div class="text-danger small mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>
            </div>


            {{-- ============================================================
                 INFO / PRIVACIDADE
            ============================================================ --}}
            <div class="card border-0 shadow-sm mb-4 info-notice-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start gap-3">

                        <div class="info-notice-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>

                        <div>
                            <strong class="d-block small mb-1">Processo de Análise</strong>
                            <small class="text-muted">
                                O seu feedback será analisado pela administração.
                                Será notificado quando for <strong>aprovado</strong> ou <strong>rejeitado</strong>.
                                Feedbacks aprovados podem ser publicados na comunidade.
                            </small>
                        </div>

                    </div>
                </div>
            </div>


            {{-- ============================================================
                 AÇÕES
            ============================================================ --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">

                        <a href="{{ route('egresso.feedbacks.index') }}"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>

                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Limpar
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-paper-plane me-1"></i>
                                Enviar Feedback
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

@endsection

