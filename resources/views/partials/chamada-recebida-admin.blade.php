{{-- ============================================================
     MODAL: CHAMADA RECEBIDA (ADMIN)
============================================================ --}}

@php
    $adminId = auth()->check()
        ? auth()->user()->egresso?->id
        : null;
@endphp

@if($adminId)

<div class="modal fade"
     id="modalChamadaRecebida"
     tabindex="-1"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     aria-labelledby="chamadaNome"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg text-center"
             style="border-radius: 20px; overflow: hidden;">

            <div class="call-incoming-header">
                <div class="call-ripple"></div>
                <div class="call-ripple call-ripple-2"></div>
                <div class="call-avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="modal-body py-4 px-4">
                <h5 class="fw-bold mb-1" id="chamadaNome">A carregar...</h5>
                <p class="text-muted small mb-0" id="chamadaTipo">Chamada de vídeo</p>

                <div class="d-flex justify-content-center gap-4 mt-4">
                    <button type="button"
                            class="btn btn-danger rounded-circle call-action-btn"
                            id="btnRecusarChamada"
                            title="Recusar">
                        <i class="fas fa-phone-slash"></i>
                    </button>

                    <button type="button"
                            class="btn btn-success rounded-circle call-action-btn"
                            id="btnAceitarChamada"
                            title="Aceitar">
                        <i class="fas fa-phone"></i>
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>

@endif


