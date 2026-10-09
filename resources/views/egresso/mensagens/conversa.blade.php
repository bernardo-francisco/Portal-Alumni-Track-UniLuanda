@extends('layouts.egresso')

@section('title', 'Conversa com ' . $contato->nome_completo)

@section('page_title', '💬 Conversa')
@section('page_subtitle', 'A conversar com ' . $contato->nome_completo)

@section('content')

@php
    $egressoLogado = auth()->user()->egresso;

    $temFoto = !empty($contato->foto_url);
    $fotoUrl = null;
    if ($temFoto) {
        $fotoUrl = filter_var($contato->foto_url, FILTER_VALIDATE_URL)
            ? $contato->foto_url
            : asset($contato->foto_url);
    }

    $iniciais = collect(explode(' ', trim($contato->nome_completo ?? 'E')))
        ->filter()
        ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">

        {{-- ============================================================
             HEADER DA CONVERSA
        ============================================================ --}}
        <div class="card border-0 shadow-sm mb-3 chat-header-card">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center justify-content-between gap-3">

                    <div class="d-flex align-items-center gap-3 min-width-0">
                        <a href="{{ route('egresso.mensagens') }}"
                           class="btn btn-link text-muted p-0 flex-shrink-0"
                           title="Voltar">
                            <i class="fas fa-arrow-left fs-5"></i>
                        </a>

                        <div class="position-relative flex-shrink-0">
                            @if($temFoto)
                                <img src="{{ $fotoUrl }}"
                                     alt="{{ $contato->nome_completo }}"
                                     class="rounded-circle border border-2 border-white shadow-sm"
                                     style="width: 48px; height: 48px; object-fit: cover;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="rounded-circle align-items-center justify-content-center text-white fw-bold shadow-sm"
                                     style="width: 48px; height: 48px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 1rem; display: none;">
                                    {{ $iniciais }}
                                </div>
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                     style="width: 48px; height: 48px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 1rem;">
                                    {{ $iniciais }}
                                </div>
                            @endif

                            <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white"
                                  style="width: 12px; height: 12px;"></span>
                        </div>

                        <div class="min-width-0">
                            <strong class="d-block text-truncate">
                                {{ $contato->nome_completo }}
                            </strong>
                            <small class="text-muted text-truncate d-block">
                                <i class="fas fa-graduation-cap me-1" style="font-size: 0.7rem;"></i>
                                {{ $contato->curso->nome ?? 'Egresso UniLuanda' }}
                            </small>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-shrink-0">
                        <button type="button"
                                class="btn btn-success rounded-circle call-btn"
                                onclick="iniciarChamadaVideo({{ $contato->id }})"
                                title="Chamada de vídeo">
                            <i class="fas fa-video"></i>
                        </button>
                        <button type="button"
                                class="btn btn-primary rounded-circle call-btn"
                                onclick="iniciarChamadaAudio({{ $contato->id }})"
                                title="Chamada de voz">
                            <i class="fas fa-phone"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>


        {{-- ============================================================
             ÁREA DE MENSAGENS
        ============================================================ --}}
        <div class="card border-0 shadow-sm chat-card">
            <div class="card-body chat-body" id="chatContainer">

                @if(isset($mensagens) && $mensagens->count() > 0)

                    @php $ultimaData = null; @endphp

                    @foreach($mensagens as $msg)
                        @php
                            $isMine     = $msg->remetente_id == $egressoLogado->id;
                            $isAudio    = ($msg->tipo ?? 'texto') === 'audio';
                            $isFicheiro = ($msg->tipo ?? 'texto') === 'ficheiro';
                            $isChamada  = ($msg->tipo ?? 'texto') === 'chamada';

                            $dataMsg = $msg->created_at->format('Y-m-d');
                            $mostrarData = $dataMsg !== $ultimaData;
                            $ultimaData = $dataMsg;
                        @endphp

                        @if($mostrarData)
                            <div class="date-separator">
                                <span>
                                    @if($msg->created_at->isToday())
                                        Hoje
                                    @elseif($msg->created_at->isYesterday())
                                        Ontem
                                    @else
                                        {{ $msg->created_at->format('d \d\e F \d\e Y') }}
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="chat-row {{ $isMine ? 'chat-row-sent' : 'chat-row-received' }}"
                             id="mensagem-{{ $msg->id }}">

                            <div class="chat-bubble {{ $isMine ? 'chat-bubble-sent' : 'chat-bubble-received' }}">

                                {{-- ===== CONTEÚDO ===== --}}
                                <div class="chat-content" id="conteudo-{{ $msg->id }}">
                                    @if($isAudio)
                                        <div class="chat-audio">
                                            <audio controls preload="metadata"
                                                   src="{{ asset('storage/' . $msg->audio_url) }}"></audio>
                                        </div>
                                    @elseif($isFicheiro)
                                        <a href="{{ asset('storage/' . $msg->ficheiro_url) }}"
                                           download="{{ $msg->ficheiro_nome }}"
                                           class="chat-ficheiro {{ $isMine ? 'chat-ficheiro-sent' : 'chat-ficheiro-received' }}"
                                           target="_blank">
                                            <div class="chat-ficheiro-icone">
                                                <i class="fas {{ $msg->iconeFicheiro() }}"></i>
                                            </div>
                                            <div class="chat-ficheiro-info">
                                                <span class="chat-ficheiro-nome">{{ $msg->ficheiro_nome }}</span>
                                                <span class="chat-ficheiro-tamanho">{{ $msg->tamanhoFormatado() }}</span>
                                            </div>
                                            <div class="chat-ficheiro-download">
                                                <i class="fas fa-download"></i>
                                            </div>
                                        </a>
                                    @elseif($isChamada)
                                        @php $tipoChamada = $msg->mensagem ?? 'video'; @endphp
                                        <div class="chat-chamada {{ $isMine ? 'chat-chamada-sent' : 'chat-chamada-received' }}">
                                            <i class="fas {{ $tipoChamada === 'video' ? 'fa-video' : 'fa-phone' }}"></i>
                                            <span>
                                                Chamada de {{ $tipoChamada === 'video' ? 'vídeo' : 'voz' }}
                                                {{ $isMine ? 'iniciada' : 'recebida' }}
                                            </span>
                                        </div>
                                    @else
                                        {{ $msg->mensagem }}
                                    @endif
                                </div>

                                {{-- ===== EDITOR (só texto) ===== --}}
                                @if(!$isAudio && !$isFicheiro && !$isChamada)
                                    <div class="chat-edit-container d-none" id="edit-container-{{ $msg->id }}">
                                        <textarea class="form-control form-control-sm mb-2"
                                                  id="edit-input-{{ $msg->id }}"
                                                  rows="2">{{ $msg->mensagem }}</textarea>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-success" onclick="salvarEdicao({{ $msg->id }})">
                                                <i class="fas fa-check"></i> Salvar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelarEdicao({{ $msg->id }})">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <div class="chat-meta">
                                    <span class="chat-time">
                                        {{ $msg->created_at->format('H:i') }}
                                        @if($msg->editado)
                                            <span class="chat-edited">(editado)</span>
                                        @endif
                                        @if($isAudio && $msg->audio_duracao)
                                            <span class="chat-audio-duration">
                                                · {{ gmdate('i:s', $msg->audio_duracao) }}
                                            </span>
                                        @endif
                                    </span>

                                    @if($isMine && !$isChamada)
                                        <div class="chat-actions">
                                            @if(!$isAudio && !$isFicheiro)
                                                <button type="button"
                                                        class="chat-action-btn"
                                                        onclick="ativarEdicao({{ $msg->id }})"
                                                        title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            @endif
                                            <button type="button"
                                                    class="chat-action-btn chat-action-danger"
                                                    onclick="confirmarEliminar({{ $msg->id }})"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                @else

                    <div class="text-center py-5">
                        <div class="empty-chat-icon mb-3">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Nenhuma mensagem ainda</h6>
                        <p class="text-muted small mb-0">
                            Envie a primeira mensagem para iniciar a conversa.
                        </p>
                    </div>

                @endif

            </div>

            {{-- ============================================================
                 FOOTER — INPUT + ANEXO + ÁUDIO
            ============================================================ --}}
            <div class="card-footer bg-white border-top py-3 px-4">

                <form method="POST"
                      action="{{ route('egresso.mensagens.enviar', $contato->id) }}"
                      class="d-flex gap-2 align-items-center"
                      id="formMensagem">
                    @csrf

                    {{-- Botão de anexo --}}
                    <button type="button"
                            class="btn btn-outline-secondary rounded-circle chat-mic-btn"
                            id="btnAnexo"
                            title="Anexar ficheiro">
                        <i class="fas fa-paperclip"></i>
                    </button>

                    {{-- Input escondido para ficheiros --}}
                    <input type="file"
                           id="inputFicheiro"
                           class="d-none">

                    {{-- Botão de microfone --}}
                    <button type="button"
                            class="btn btn-outline-secondary rounded-circle chat-mic-btn"
                            id="btnGravarAudio"
                            title="Gravar áudio">
                        <i class="fas fa-microphone"></i>
                    </button>

                    <input type="text"
                           name="mensagem"
                           class="form-control chat-input"
                           placeholder="Escreve uma mensagem..."
                           autocomplete="off"
                           id="inputMensagem"
                           required>

                    <button type="submit"
                            class="btn btn-primary chat-send-btn"
                            title="Enviar">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>

                {{-- UI de gravação --}}
                <div class="audio-recording-ui d-none" id="audioRecordingUI">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <button type="button"
                                class="btn btn-danger rounded-circle"
                                id="btnCancelarGravacao"
                                title="Cancelar">
                            <i class="fas fa-trash"></i>
                        </button>

                        <div class="flex-grow-1 d-flex align-items-center gap-2">
                            <span class="recording-dot"></span>
                            <span class="recording-time" id="recordingTime">00:00</span>
                            <div class="recording-wave flex-grow-1">
                                <span></span><span></span><span></span><span></span><span></span>
                                <span></span><span></span><span></span><span></span><span></span>
                            </div>
                        </div>

                        <button type="button"
                                class="btn btn-success rounded-circle"
                                id="btnEnviarAudio"
                                title="Enviar áudio">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>


{{-- ============================================================
     MODAL: CONFIRMAR ELIMINAÇÃO
============================================================ --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Eliminar Mensagem
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-2">Tens a certeza que queres eliminar esta mensagem?</p>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Esta ação não pode ser desfeita.
                </p>
                <input type="hidden" id="mensagem_id_eliminar">
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="eliminarMensagem()">
                    <i class="fas fa-trash me-1"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

