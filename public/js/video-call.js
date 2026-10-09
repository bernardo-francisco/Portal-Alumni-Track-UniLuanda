/*
|--------------------------------------------------------------------------
| UniLuanda — Video Call / Voice Call
|--------------------------------------------------------------------------
| PeerJS + PeerServer
|
| Este ficheiro lê a configuração de window.VIDEO_CALL_CONFIG,
| que é injetada pelo Blade nas views sala.blade.php.
|--------------------------------------------------------------------------
*/

(function () {

    'use strict';

    if (typeof Peer === 'undefined') {
        console.error('❌ PeerJS não foi carregado.');
        return;
    }

    const CONFIG = window.VIDEO_CALL_CONFIG || {};

    const roomId        = CONFIG.roomId        || null;
    const currentUserId = Number(CONFIG.currentUserId || 0);
    const targetUserId  = Number(CONFIG.targetUserId  || 0);
    const callType      = CONFIG.callType === 'audio' ? 'audio' : 'video';
    const amIChamador   = Boolean(CONFIG.amIChamador);
    const routes        = CONFIG.routes || {};
    const redirectUrl   = CONFIG.redirectUrl || '/';

    /*
    |--------------------------------------------------------------------------
    | PeerServer
    |--------------------------------------------------------------------------
    */

    const peerConfig = {
        host: CONFIG.peerHost || window.location.hostname,

        port: Number(
            CONFIG.peerPort ||
            (window.location.protocol === 'https:' ? 443 : 9000)
        ),

        path: CONFIG.peerPath || '/myapp',

        secure:
            typeof CONFIG.peerSecure === 'boolean'
                ? CONFIG.peerSecure
                : window.location.protocol === 'https:',

        debug: 2,

        config: {
            iceServers: [
                {
                    urls: [
                        'stun:stun.l.google.com:19302',
                        'stun:stun1.l.google.com:19302'
                    ]
                }
            ]
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Estado
    |--------------------------------------------------------------------------
    */

    let peer = null;
    let localStream = null;
    let currentCall = null;
    let peerId = null;
    let finalizado = false;
    let chamadaConectada = false;
    let chamadaIniciada = false;
    let estadoInterval = null;
    let aceitaçãoInterval = null;
    let peerIdInterval = null;
    let timerInterval = null;
    let reconnectTimeout = null;
    let callStartTime = null;

    /*
    |--------------------------------------------------------------------------
    | DOM
    |--------------------------------------------------------------------------
    */

    function $(id) {
        return document.getElementById(id);
    }

    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /*
    |--------------------------------------------------------------------------
    | POST Laravel
    |--------------------------------------------------------------------------
    */

    async function post(url, data = {}) {
        if (!url) throw new Error('URL não configurada.');

        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json'
            },
            body: JSON.stringify(data)
        });

        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            throw new Error(`Resposta inválida do servidor (${response.status}).`);
        }

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || `Erro HTTP ${response.status}`);
        }

        return result;
    }

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    function setStatus(text, type = '') {
        const badge = $('call-badge');
        const status = $('call-status');

        if (status) status.textContent = text;

        if (badge) {
            badge.className = 'call-badge';
            if (type) badge.classList.add(type);
        }
    }

    function setText(text) {
        const element = $('status-text');
        if (element) element.textContent = text;
    }

    function hidePlaceholder() {
        const element = $('placeholder');
        if (element) element.classList.add('hidden');
    }

    /*
    |--------------------------------------------------------------------------
    | Local stream
    |--------------------------------------------------------------------------
    */

    async function obterMedia() {
        setText(
            callType === 'video'
                ? 'A aceder à câmara e microfone...'
                : 'A aceder ao microfone...'
        );

        const constraints = {
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl:  true
            },
            video:
                callType === 'video'
                    ? {
                        width:  { ideal: 1280 },
                        height: { ideal: 720 },
                        frameRate: { ideal: 30, max: 30 }
                    }
                    : false
        };

        try {
            localStream = await navigator.mediaDevices.getUserMedia(constraints);
        } catch (error) {
            console.error('❌ getUserMedia:', error);

            let mensagem = 'Não foi possível aceder aos dispositivos.';

            if (error.name === 'NotAllowedError') {
                mensagem = 'Permita o acesso à câmara e ao microfone no navegador.';
            }

            if (error.name === 'NotFoundError') {
                mensagem = callType === 'video'
                    ? 'Não foi encontrada uma câmara ou microfone.'
                    : 'Não foi encontrado um microfone.';
            }

            if (error.name === 'NotReadableError') {
                mensagem = 'A câmara ou microfone está a ser utilizado por outra aplicação.';
            }

            setStatus('Dispositivo indisponível', 'danger');
            setText(mensagem);
            throw error;
        }

        const localVideo = $('local-video');

        if (localVideo && callType === 'video') {
            localVideo.srcObject = localStream;
            localVideo.muted = true;

            try { await localVideo.play(); } catch (e) {}
        }

        if (callType === 'audio') {
            const videoButton = $('btn-video');
            const localVideoElement = $('local-video');

            if (videoButton) videoButton.style.display = 'none';
            if (localVideoElement) localVideoElement.style.display = 'none';
        }

        console.log('✅ Stream local obtido.');
    }

    /*
    |--------------------------------------------------------------------------
    | Gerar Peer ID único
    |--------------------------------------------------------------------------
    */

    function gerarPeerId() {
        const uuid =
            typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function'
                ? crypto.randomUUID()
                : (Date.now().toString(36) + '-' + Math.random().toString(36).substring(2, 10));

        return 'uniluanda-' + currentUserId + '-' + uuid;
    }

    /*
    |--------------------------------------------------------------------------
    | Criar Peer
    |--------------------------------------------------------------------------
    */

    function criarPeer() {
        return new Promise(function (resolve, reject) {

            const novoPeerId = gerarPeerId();

            console.log('📡 Criando Peer:', novoPeerId);
            console.log('📡 Configuração PeerServer:', {
                host:   peerConfig.host,
                port:   peerConfig.port,
                path:   peerConfig.path,
                secure: peerConfig.secure
            });

            peer = new Peer(novoPeerId, peerConfig);

            peer.on('open', async function (id) {
                peerId = id;
                console.log('✅ PeerServer conectado:', id);
                setText('Ligação segura pronta.');

                try {
                    await post(routes.registarPeer, {
                        room_id: roomId,
                        peer_id: id
                    });

                    console.log('✅ Peer ID registado no Laravel.');
                    resolve(id);
                } catch (error) {
                    console.error('❌ Erro ao registar Peer ID:', error);
                    reject(error);
                }
            });

            peer.on('call', function (call) {
                console.log('📥 Chamada PeerJS recebida:', call.peer);

                if (finalizado) { call.close(); return; }

                if (currentCall) {
                    console.warn('⚠️ Já existe uma chamada ativa.');
                    call.close();
                    return;
                }

                currentCall = call;
                setText('A responder à chamada...');

                try {
                    call.answer(localStream);
                } catch (error) {
                    console.error('❌ Erro ao responder:', error);
                    call.close();
                    return;
                }

                ligarEventosCall(call);
            });

            peer.on('disconnected', function () {
                console.warn('⚠️ PeerServer desconectado.');
                if (finalizado) return;

                setStatus('Reconectando...', 'warning');
                tentarReconectarPeer();
            });

            peer.on('error', function (error) {
                console.error('❌ PeerJS:', error);

                if (error.type === 'peer-unavailable') {
                    setStatus('Destinatário indisponível', 'danger');
                    return;
                }
                if (error.type === 'network') {
                    setStatus('Problema de rede', 'danger');
                    return;
                }
                if (error.type === 'server-error') {
                    setStatus('PeerServer indisponível', 'danger');
                    return;
                }
                if (error.type === 'socket-error') {
                    setStatus('Erro de comunicação', 'danger');
                    return;
                }

                if (error.type === 'unavailable-id') {
                    console.warn('⚠️ ID Peer ocupado. Criando outro...');

                    if (peer && !peer.destroyed) {
                        try { peer.destroy(); } catch (e) {}
                    }

                    setTimeout(function () {
                        criarPeer().catch(console.error);
                    }, 500);
                }
            });

            peer.on('close', function () {
                console.log('🔴 Peer fechado.');
            });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reconectar PeerServer
    |--------------------------------------------------------------------------
    */

    function tentarReconectarPeer() {
        if (reconnectTimeout) return;

        reconnectTimeout = setTimeout(function () {
            reconnectTimeout = null;

            if (peer && !peer.destroyed && peer.disconnected) {
                try { peer.reconnect(); } catch (error) {
                    console.warn('Erro ao reconectar:', error);
                }
            }
        }, 2000);
    }

    /*
    |--------------------------------------------------------------------------
    | Eventos MediaConnection
    |--------------------------------------------------------------------------
    */

    function ligarEventosCall(call) {
        if (!call) return;

        call.on('stream', function (remoteStream) {
            console.log('🎥 STREAM REMOTO RECEBIDO!');

            const remoteVideo = $('remote-video');

            if (!remoteVideo) {
                console.error('❌ #remote-video não existe.');
                return;
            }

            remoteVideo.srcObject = remoteStream;
            remoteVideo.muted = false;
            remoteVideo.volume = 1;

            const promise = remoteVideo.play();

            if (promise) {
                promise.catch(function (error) {
                    console.warn('⚠️ Autoplay bloqueado:', error);

                    document.addEventListener('click', function playAgain() {
                        remoteVideo.play().catch(function () {});
                        document.removeEventListener('click', playAgain);
                    }, { once: true });
                });
            }

            chamadaConectada = true;
            setStatus('Em chamada', 'success');
            setText('Ligação estabelecida.');
            hidePlaceholder();
            iniciarTimer();
        });

        call.on('close', function () {
            console.log('📴 MediaConnection fechada.');
            if (!finalizado) {
                finalizado = true;
                encerrarLocalmente(false);
            }
        });

        call.on('error', function (error) {
            console.error('❌ MediaConnection:', error);
            setStatus('Erro na chamada', 'danger');
        });

        monitorarPeerConnection(call);
    }

    /*
    |--------------------------------------------------------------------------
    | Monitorar WebRTC
    |--------------------------------------------------------------------------
    */

    function monitorarPeerConnection(call) {
        let tentativas = 0;

        const verificar = setInterval(function () {
            if (finalizado || !call) {
                clearInterval(verificar);
                return;
            }

            const pc = call.peerConnection;
            if (!pc) return;

            const state = pc.connectionState;
            console.log('🌐 WebRTC:', state);

            if (state === 'connected') {
                chamadaConectada = true;
                setStatus('Em chamada', 'success');
                hidePlaceholder();
                iniciarTimer();
                clearInterval(verificar);
            }

            if (state === 'failed') {
                clearInterval(verificar);
                console.error('❌ WebRTC connection failed.');
                setStatus('Ligação falhou', 'danger');
                setText('Não foi possível estabelecer a ligação WebRTC. Verifique a rede/TURN.');
            }

            if (state === 'closed') {
                clearInterval(verificar);
            }

            tentativas++;
            if (tentativas > 60) clearInterval(verificar);
        }, 500);
    }

    /*
    |--------------------------------------------------------------------------
    | Chamador: esperar aceite
    |--------------------------------------------------------------------------
    */

    function aguardarAceitacao() {
        if (!amIChamador) return;

        setStatus('A chamar...', 'warning');
        setText('A aguardar que o outro utilizador atenda...');

        let tentativas = 0;

        if (aceitaçãoInterval) clearInterval(aceitaçãoInterval);

        aceitaçãoInterval = setInterval(async function () {
            if (finalizado) {
                clearInterval(aceitaçãoInterval);
                return;
            }

            tentativas++;

            if (tentativas > 120) {
                clearInterval(aceitaçãoInterval);
                setStatus('Sem resposta', 'danger');
                await encerrarServidor(false);
                sair();
                return;
            }

            try {
                const data = await post(routes.estado, { room_id: roomId });

                console.log('📡 Estado:', data.status);

                if (data.status === 'aceite') {
                    clearInterval(aceitaçãoInterval);
                    aceitaçãoInterval = null;

                    setStatus('A ligar...', 'warning');
                    setText('A estabelecer ligação segura...');

                    esperarPeerRecetor();
                    return;
                }

                if (['recusada', 'terminada', 'perdida'].includes(data.status)) {
                    clearInterval(aceitaçãoInterval);
                    aceitaçãoInterval = null;
                    sair();
                    return;
                }
            } catch (error) {
                console.warn('Erro estado:', error);
            }
        }, 1000);
    }

    /*
    |--------------------------------------------------------------------------
    | Esperar Peer ID do recetor
    |--------------------------------------------------------------------------
    */

    function esperarPeerRecetor() {
        let tentativas = 0;

        if (peerIdInterval) clearInterval(peerIdInterval);

        peerIdInterval = setInterval(async function () {
            if (finalizado || chamadaIniciada) {
                clearInterval(peerIdInterval);
                return;
            }

            tentativas++;

            if (tentativas > 30) {
                clearInterval(peerIdInterval);
                setStatus('Destinatário indisponível', 'danger');
                setText('O dispositivo do destinatário não está ligado ao PeerServer.');
                return;
            }

            try {
                const data = await post(routes.estado, { room_id: roomId });
                const remotePeerId = data.peer_id_recetor;

                if (!remotePeerId) return;

                clearInterval(peerIdInterval);
                peerIdInterval = null;

                chamarPeer(remotePeerId);
            } catch (error) {
                console.warn('Erro ao obter Peer ID:', error);
            }
        }, 700);
    }

    /*
    |--------------------------------------------------------------------------
    | Fazer chamada
    |--------------------------------------------------------------------------
    */

    function chamarPeer(remotePeerId) {
        if (chamadaIniciada) {
            console.warn('⚠️ A chamada PeerJS já foi iniciada.');
            return;
        }

        if (!peer || peer.destroyed || peer.disconnected) {
            console.error('❌ Peer não está pronto.');
            return;
        }

        if (!localStream) {
            console.error('❌ Stream local inexistente.');
            return;
        }

        chamadaIniciada = true;

        console.log('📞 peer.call() UMA VEZ:', remotePeerId);
        setStatus('A ligar...', 'warning');

        const call = peer.call(remotePeerId, localStream, {
            metadata: {
                roomId: roomId,
                userId: currentUserId,
                type:   callType
            }
        });

        if (!call) {
            chamadaIniciada = false;
            console.error('❌ peer.call() falhou.');
            return;
        }

        currentCall = call;
        ligarEventosCall(call);
    }

    /*
    |--------------------------------------------------------------------------
    | Estado da chamada — polling
    |--------------------------------------------------------------------------
    */

    function iniciarPollingEstado() {
        if (estadoInterval) clearInterval(estadoInterval);

        estadoInterval = setInterval(async function () {
            if (finalizado) return;

            try {
                const data = await post(routes.estado, { room_id: roomId });

                if (['terminada', 'recusada', 'perdida'].includes(data.status)) {
                    if (!chamadaConectada) {
                        setStatus('Chamada encerrada', 'danger');
                    }
                    encerrarLocalmente(false);
                }
            } catch (error) {
                console.warn('Polling:', error);
            }
        }, 2500);
    }

    /*
    |--------------------------------------------------------------------------
    | Timer
    |--------------------------------------------------------------------------
    */

    function iniciarTimer() {
        if (timerInterval) return;

        callStartTime = Date.now();

        timerInterval = setInterval(function () {
            const segundos = Math.floor((Date.now() - callStartTime) / 1000);
            const minutos  = String(Math.floor(segundos / 60)).padStart(2, '0');
            const segs     = String(segundos % 60).padStart(2, '0');

            const timer = $('call-timer');
            if (timer) timer.textContent = minutos + ':' + segs;
        }, 1000);
    }

    /*
    |--------------------------------------------------------------------------
    | Microfone
    |--------------------------------------------------------------------------
    */

    const muteButton = $('btn-mute');
    if (muteButton) {
        muteButton.addEventListener('click', function () {
            if (!localStream) return;

            const track = localStream.getAudioTracks()[0];
            if (!track) return;

            track.enabled = !track.enabled;

            this.classList.toggle('muted', !track.enabled);
            this.innerHTML = track.enabled
                ? '<i class="fas fa-microphone"></i>'
                : '<i class="fas fa-microphone-slash"></i>';
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Vídeo
    |--------------------------------------------------------------------------
    */

    const videoButton = $('btn-video');
    if (videoButton) {
        videoButton.addEventListener('click', function () {
            if (!localStream) return;

            const track = localStream.getVideoTracks()[0];
            if (!track) return;

            track.enabled = !track.enabled;

            this.classList.toggle('muted', !track.enabled);
            this.innerHTML = track.enabled
                ? '<i class="fas fa-video"></i>'
                : '<i class="fas fa-video-slash"></i>';
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Encerrar (botão)
    |--------------------------------------------------------------------------
    */

    const hangupButton = $('btn-hangup');
    if (hangupButton) {
        hangupButton.addEventListener('click', async function () {
            if (finalizado) return;

            if (!confirm('Encerrar chamada?')) return;

            finalizado = true;
            this.disabled = true;

            await encerrarServidor(true);
            destruirTudo();

            window.location.href = redirectUrl;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Encerrar no Laravel
    |--------------------------------------------------------------------------
    */

    async function encerrarServidor(mostrarEstado = true) {
        try {
            await post(routes.encerrar, { room_id: roomId });
        } catch (error) {
            console.warn('Não foi possível informar encerramento:', error);
        }

        if (mostrarEstado) {
            setStatus('Chamada encerrada', 'danger');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Encerrar localmente
    |--------------------------------------------------------------------------
    */

    function encerrarLocalmente(voltar = true) {
        if (finalizado) return;

        finalizado = true;
        destruirTudo();

        if (voltar) {
            window.location.href = redirectUrl;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Destruir tudo
    |--------------------------------------------------------------------------
    */

    function destruirTudo() {
        if (estadoInterval)    { clearInterval(estadoInterval); estadoInterval = null; }
        if (aceitaçãoInterval) { clearInterval(aceitaçãoInterval); aceitaçãoInterval = null; }
        if (peerIdInterval)    { clearInterval(peerIdInterval); peerIdInterval = null; }
        if (timerInterval)     { clearInterval(timerInterval); timerInterval = null; }
        if (reconnectTimeout)  { clearTimeout(reconnectTimeout); reconnectTimeout = null; }

        if (currentCall) {
            try { currentCall.close(); } catch (e) {}
            currentCall = null;
        }

        if (peer) {
            try { peer.destroy(); } catch (e) {}
            peer = null;
        }

        if (localStream) {
            localStream.getTracks().forEach(function (track) {
                try { track.stop(); } catch (e) {}
            });
            localStream = null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Visibilidade da página
    |--------------------------------------------------------------------------
    */

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            if (peer && peer.disconnected && !peer.destroyed) {
                try { peer.reconnect(); } catch (e) {}
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Antes de sair
    |--------------------------------------------------------------------------
    */

    window.addEventListener('beforeunload', function () {
        try {
            if (navigator.sendBeacon && routes.encerrar && roomId && !finalizado) {
                const formData = new FormData();
                formData.append('_token', csrf());
                formData.append('room_id', roomId);

                navigator.sendBeacon(routes.encerrar, formData);
            }
        } catch (e) {}

        destruirTudo();
    });

    /*
    |--------------------------------------------------------------------------
    | Inicialização
    |--------------------------------------------------------------------------
    */

    async function init() {
        console.log('================================================');
        console.log('🎥 UNILUANDA VIDEO CALL');
        console.log('Room:',     roomId);
        console.log('User:',     currentUserId);
        console.log('Target:',   targetUserId);
        console.log('Type:',     callType);
        console.log('Chamador:', amIChamador);
        console.log('PeerServer:', peerConfig);
        console.log('================================================');

        if (!roomId) {
            setStatus('Sala inválida', 'danger');
            return;
        }

        try {
            await obterMedia();
            await criarPeer();
            iniciarPollingEstado();

            if (amIChamador) {
                aguardarAceitacao();
            } else {
                setStatus('A aguardar...', 'warning');
                setText('A aguardar ligação do chamador...');
            }
        } catch (error) {
            console.error('❌ Inicialização falhou:', error);
            setStatus('Não foi possível iniciar', 'danger');
        }
    }

    init();

})();