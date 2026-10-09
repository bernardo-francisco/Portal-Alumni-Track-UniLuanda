@extends('layouts.admin')

@section('title', 'Inscritos — ' . $evento->titulo)

@section('page_title', '🎪 Inscritos no Evento')
@section('page_subtitle', $evento->titulo)

@section('content')

@php
    $inscricoes = $evento->inscricoes;

    $totalPendentes   = $inscricoes->where('status', 'pendente')->count();
    $totalConfirmadas = $inscricoes->where('status', 'confirmada')->count();
    $totalRejeitadas  = $inscricoes->where('status', 'rejeitada')->count();
@endphp

<div class="row justify-content-center">
    <div class="col-12">


        {{-- HEADER DO EVENTO --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                    <div class="d-flex align-items-center gap-3">
                        <div class="event-header-icon">
                            <i class="fas fa-calendar-star"></i>
                        </div>
                        <div>
                            <h3 class="mb-1 fw-bold">{{ $evento->titulo }}</h3>
                            <div class="text-muted small">
                                <i class="far fa-calendar me-1"></i>
                                {{ $evento->data_inicio
                                    ? $evento->data_inicio->format('d/m/Y \à\s H:i')
                                    : 'Data a definir' }}
                                @if($evento->local)
                                    · <i class="fas fa-map-marker-alt ms-2 me-1"></i>
                                    {{ $evento->local }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <span class="badge-stat">
                            <i class="fas fa-users"></i>
                            {{ $inscricoes->count() }} inscritos
                        </span>
                    </div>

                </div>
            </div>
        </div>

        {{-- ESTATÍSTICAS --}}
        <div class="row g-3 mb-4">

            <div class="col-md-3">
                <div class="mini-stat" style="background: linear-gradient(135deg, #1a56db, #3b82f6);">
                    <div class="mini-stat-icon"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="mini-stat-value">{{ $inscricoes->count() }}</div>
                        <div class="mini-stat-label">Total Inscritos</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="mini-stat" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                    <div class="mini-stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div>
                        <div class="mini-stat-value">{{ $totalPendentes }}</div>
                        <div class="mini-stat-label">Pendentes</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="mini-stat" style="background: linear-gradient(135deg, #16a34a, #22c55e);">
                    <div class="mini-stat-icon"><i class="fas fa-user-check"></i></div>
                    <div>
                        <div class="mini-stat-value">{{ $totalConfirmadas }}</div>
                        <div class="mini-stat-label">Confirmadas</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="mini-stat" style="background: linear-gradient(135deg, #dc2626, #f87171);">
                    <div class="mini-stat-icon"><i class="fas fa-user-times"></i></div>
                    <div>
                        <div class="mini-stat-value">{{ $totalRejeitadas }}</div>
                        <div class="mini-stat-label">Rejeitadas</div>
                    </div>
                </div>
            </div>

        </div>

        {{-- LISTA DE INSCRITOS --}}
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-list text-primary me-2"></i>
                        Lista de Inscritos
                    </h6>
                    <a href="{{ route('admin.eventos.inscritos.exportar.csv', $evento->id) }}"
                       class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-csv me-1"></i>
                        Exportar CSV
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if($inscricoes->count() > 0)

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">

                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Egresso</th>
                                    <th>Curso</th>
                                    <th>Inscrição</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Presença</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($inscricoes as $inscricao)

                                    @php
                                        $egresso      = $inscricao->egresso;
                                        $nomeEgresso  = $egresso?->nome_completo ?? 'Egresso';
                                        $iniciais     = collect(explode(' ', trim($nomeEgresso)))
                                            ->filter()
                                            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                                            ->take(2)
                                            ->implode('');

                                        $statusInfo = match($inscricao->status) {
                                            'pendente'   => ['class' => 'warning',  'icon' => 'hourglass-half', 'label' => 'Pendente'],
                                            'confirmada' => ['class' => 'success',  'icon' => 'check-circle',   'label' => 'Confirmada'],
                                            'rejeitada'  => ['class' => 'danger',   'icon' => 'times-circle',   'label' => 'Rejeitada'],
                                            'cancelada'  => ['class' => 'secondary','icon' => 'ban',            'label' => 'Cancelada'],
                                            default      => ['class' => 'secondary','icon' => 'question-circle','label' => ucfirst($inscricao->status)],
                                        };
                                    @endphp

                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                @if($egresso?->foto_url)
                                                    <img src="{{ asset($egresso->foto_url) }}"
                                                         class="rounded-circle border"
                                                         style="width:42px;height:42px;object-fit:cover;">
                                                @else
                                                    <div class="avatar-fallback">{{ $iniciais }}</div>
                                                @endif
                                                <div>
                                                    <strong class="d-block">{{ $nomeEgresso }}</strong>
                                                    <small class="text-muted">{{ $egresso?->email }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <small class="text-muted">{{ $egresso?->curso?->nome ?? 'N/A' }}</small>
                                        </td>

                                        <td>
                                            <small class="text-muted">
                                                {{ $inscricao->created_at?->format('d/m/Y H:i') }}
                                            </small>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-{{ $statusInfo['class'] }}-subtle text-{{ $statusInfo['class'] }}-emphasis border border-{{ $statusInfo['class'] }}-subtle">
                                                <i class="fas fa-{{ $statusInfo['icon'] }} me-1"></i>
                                                {{ $statusInfo['label'] }}
                                            </span>

                                            @if($inscricao->status === 'rejeitada' && !empty($inscricao->motivo_rejeicao))
                                                <div class="small text-danger mt-1"
                                                     title="{{ $inscricao->motivo_rejeicao }}">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    {{ \Illuminate\Support\Str::limit($inscricao->motivo_rejeicao, 40) }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($inscricao->status !== 'confirmada')
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border">—</span>
                                            @elseif($inscricao->checkin_em)
                                                @if($inscricao->presente)
                                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                                        <i class="fas fa-check me-1"></i> Presente
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                                        <i class="fas fa-times me-1"></i> Ausente
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border">
                                                    <i class="fas fa-clock me-1"></i> A confirmar
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-1 flex-wrap">

                                                {{-- APROVAR --}}
                                                @if($inscricao->status !== 'confirmada')
                                                    <form action="{{ route('admin.eventos.inscricoes.aprovar', $inscricao->id) }}"
                                                          method="POST"
                                                          class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-sm btn-success"
                                                                title="Aprovar inscrição">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- REJEITAR --}}
                                                @if($inscricao->status !== 'rejeitada')
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalRejeitar{{ $inscricao->id }}"
                                                            title="Rejeitar inscrição">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif

                                                {{-- MARCAR PRESENÇA --}}
                                                @if($inscricao->status === 'confirmada' && !$inscricao->checkin_em)
                                                    <form action="{{ route('admin.inscricoes.presenca', $inscricao->id) }}"
                                                          method="POST"
                                                          class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="presente" value="1">
                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-success"
                                                                title="Marcar presente">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($inscricao->checkin_em)
                                                    <span class="text-muted small ms-1">
                                                        {{ $inscricao->checkin_em->format('H:i') }}
                                                    </span>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @else

                    <div class="text-center py-5">
                        <div class="empty-icon mb-3"><i class="fas fa-user-slash"></i></div>
                        <h6 class="fw-bold mb-1">Sem inscritos</h6>
                        <p class="text-muted small mb-0">Ainda ninguém se inscreveu neste evento.</p>
                    </div>

                @endif
            </div>
        </div>

    </div>
</div>


{{-- ============================================================
     MODAIS DE REJEIÇÃO (FORA da tabela)
============================================================ --}}
@foreach($inscricoes as $inscricao)
    @if($inscricao->status !== 'rejeitada')

        @php
            $egressoModal = $inscricao->egresso;
            $nomeModal    = $egressoModal?->nome_completo ?? 'Egresso';
        @endphp

        <div class="modal fade"
             id="modalRejeitar{{ $inscricao->id }}"
             tabindex="-1"
             aria-labelledby="modalRejeitarLabel{{ $inscricao->id }}"
             aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('admin.eventos.inscricoes.rejeitar', $inscricao->id) }}"
                      method="POST"
                      class="modal-rejeicao-form w-100">
                    @csrf

                    <div class="modal-content border-0 shadow-lg">

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-bold"
                                    id="modalRejeitarLabel{{ $inscricao->id }}">
                                    <i class="fas fa-times-circle text-danger me-2"></i>
                                    Rejeitar inscrição
                                </h5>
                                <small class="text-muted">Inscrição de {{ $nomeModal }}</small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>

                        <div class="modal-body p-4">

                            <div class="alert alert-warning border-0 small mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Está prestes a rejeitar a inscrição de
                                <strong>{{ $nomeModal }}</strong>
                                no evento <strong>{{ $evento->titulo }}</strong>.
                            </div>

                            <label for="motivo_rejeicao_{{ $inscricao->id }}" class="form-label fw-semibold">
                                Motivo da rejeição
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>

                            <textarea id="motivo_rejeicao_{{ $inscricao->id }}"
                                      name="motivo_rejeicao"
                                      class="form-control motivo-rejeicao"
                                      rows="5"
                                      maxlength="500"
                                      placeholder="Digite aqui o motivo da rejeição..."
                                      autocomplete="off"></textarea>

                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">O motivo será associado à inscrição.</small>
                                <small class="text-muted contador-caracteres">0/500</small>
                            </div>

                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-ban me-1"></i> Confirmar rejeição
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    @endif
@endforeach

@endsection


@push('styles')
<style>
    .event-header-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: linear-gradient(135deg, #1a56db, #3b82f6);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .badge-stat {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem 1rem;
        background: #eff6ff;
        color: #1a56db;
        border-radius: 50px;
        font-weight: 600;
        font-size: .85rem;
    }

    .mini-stat {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: 16px;
        color: #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,.08);
    }

    .mini-stat-icon {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .mini-stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1; }
    .mini-stat-label { font-size: .8rem; opacity: .9; font-weight: 500; }

    .avatar-fallback {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a56db, #3b82f6);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .9rem;
        flex-shrink: 0;
    }

    .empty-icon { font-size: 3.5rem; color: #cbd5e1; }

    .modal-rejeicao-form { width: 100%; }
    .modal-rejeicao-form .modal-content { border-radius: 16px; overflow: hidden; }
    .modal-rejeicao-form .modal-header { padding: 1.25rem 1.5rem; background: #fff; border-bottom: 1px solid #e5e7eb; }
    .modal-rejeicao-form .modal-body { background: #fff; }
    .modal-rejeicao-form .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; }

    /* ⚠️ Correcção crítica: garantir que o textarea é clicável */
    .modal .motivo-rejeicao {
        display: block;
        width: 100%;
        min-height: 130px;
        resize: vertical;
        background-color: #fff;
        color: #212529;
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: .75rem 1rem;
        font-size: .95rem;
        line-height: 1.5;
        position: relative;
        z-index: 10;
        pointer-events: auto;
    }

    .modal .motivo-rejeicao:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 .2rem rgba(59,130,246,.15);
        outline: none;
    }

    /* Garantir que o próprio modal fica acima do backdrop */
    .modal { z-index: 1060 !important; }
    .modal-backdrop { z-index: 1050 !important; }

    @media (max-width: 576px) {
        .modal-rejeicao-form .modal-dialog { margin: .75rem; }
        .modal-rejeicao-form .modal-header,
        .modal-rejeicao-form .modal-footer { padding: 1rem; }
        .modal-rejeicao-form .modal-body { padding: 1rem !important; }
    }
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.modal-rejeicao-form').forEach(function (form) {

        const textarea = form.querySelector('.motivo-rejeicao');
        const contador = form.querySelector('.contador-caracteres');

        if (!textarea) return;

        function atualizarContador() {
            if (contador) {
                contador.textContent = textarea.value.length + '/500';
            }
        }

        textarea.addEventListener('input', atualizarContador);
        atualizarContador();

        const modal = form.closest('.modal');

        if (modal) {
            modal.addEventListener('shown.bs.modal', function () {
                setTimeout(function () {
                    textarea.focus();
                }, 150);
            });
        }
    });

});
</script>
@endpush