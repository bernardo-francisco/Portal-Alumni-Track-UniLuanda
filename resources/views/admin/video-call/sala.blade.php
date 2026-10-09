@extends('layouts.admin')

@section('title', 'Chamada de vídeo')

@section('content')

<div class="call-page">
    <video id="remote-video" autoplay playsinline class="remote-video"></video>

    <div class="placeholder" id="placeholder">
        <div class="placeholder-avatar"><i class="fas fa-user"></i></div>
        <h3 class="placeholder-name">A ligar…</h3>
        <p class="placeholder-status">
            <span class="spinner"></span>
            <span id="status-text">A estabelecer ligação</span>
        </p>
    </div>

    <video id="local-video" autoplay playsinline muted class="local-video"></video>

    <div class="call-info">
        <span class="call-badge" id="call-badge">
            <i class="fas fa-circle" style="font-size:0.5rem"></i>
            <span id="call-status">A ligar…</span>
        </span>
        <span class="call-timer" id="call-timer">00:00</span>
    </div>

    <div class="call-controls">
        <button class="ctrl-btn" id="btn-mute" type="button" title="Microfone">
            <i class="fas fa-microphone"></i>
        </button>
        <button class="ctrl-btn" id="btn-video" type="button" title="Câmara">
            <i class="fas fa-video"></i>
        </button>
        <button class="ctrl-btn ctrl-hangup" id="btn-hangup" type="button" title="Encerrar">
            <i class="fas fa-phone-slash"></i>
        </button>
    </div>

    <!-- Estado da chamada (admin) -->
<div style="position:absolute;top:20px;right:20px;z-index:10;display:flex;gap:8px;">
    <span style="padding:6px 12px;background:rgba(201,162,39,.15);border:1px solid rgba(201,162,39,.4);border-radius:8px;color:#c9a227;font-size:.75rem;font-weight:600;">
        🎯 Sala: <code style="color:#fff;">{{ $roomId }}</code>
    </span>
    <span style="padding:6px 12px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:8px;color:#fff;font-size:.75rem;font-weight:600;">
        {{ $isChamador ? '📞 Chamador' : '📥 Recetor' }}
    </span>
</div>
</div>

@endsection


@push('styles')
<style>
    /* ============================================================
       Chamada (admin) — paleta UniLuanda
    ============================================================ */
    .call-page {
        position: fixed;
        inset: 0;
        background: #0a0a0a;
        z-index: 9998;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
    }

    .remote-video {
        width: 100%; height: 100%;
        object-fit: cover;
        background: #1a1a1a;
        display: block;
    }

    .local-video {
        position: absolute;
        bottom: 120px; right: 20px;
        width: 180px; height: 240px;
        object-fit: cover;
        border-radius: 16px;
        border: 3px solid rgba(201,162,39,.6);
        box-shadow: 0 12px 32px rgba(0,0,0,.55);
        z-index: 10;
        background: #1a1a1a;
    }

    .placeholder {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 5;
        transition: opacity .4s ease;
    }
    .placeholder.hidden {
        opacity: 0;
        pointer-events: none;
    }

    .placeholder-avatar {
        width: 140px; height: 140px;
        border-radius: 50%;
        background: linear-gradient(135deg, #c9a227, #e0b93d);
        color: #0a0a0a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        margin-bottom: 24px;
        box-shadow: 0 0 0 12px rgba(201,162,39,.15), 0 0 0 24px rgba(201,162,39,.08);
        position: relative;
    }
    .placeholder-avatar::before,
    .placeholder-avatar::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2px solid rgba(201,162,39,.4);
        animation: callRipple 2s infinite ease-out;
    }
    .placeholder-avatar::after { animation-delay: 1s; }

    .placeholder-name {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0 0 12px;
    }

    .placeholder-status {
        display: flex;
        align-items: center;
        gap: 10px;
        color: rgba(255,255,255,.6);
        font-size: 1rem;
        margin: 0;
    }

    .spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.2);
        border-top-color: #c9a227;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .call-info {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10;
    }

    .call-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: rgba(255,255,255,.12);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 50px;
        color: #fff;
        font-weight: 600;
        font-size: .9rem;
        transition: background .3s ease;
    }
    .call-badge i { color: #c9a227; }
    .call-badge.success { background: rgba(22,163,74,.85); }
    .call-badge.success i { color: #86efac; }
    .call-badge.warning { background: rgba(217,119,6,.85); }
    .call-badge.warning i { color: #fcd34d; }
    .call-badge.danger  { background: rgba(220,38,38,.85); }
    .call-badge.danger  i { color: #fca5a5; }

    .call-timer {
        padding: 10px 18px;
        background: rgba(255,255,255,.12);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 50px;
        color: #fff;
        font-weight: 700;
        font-size: .9rem;
        font-family: 'SF Mono', monospace;
        min-width: 70px;
        text-align: center;
    }

    .call-controls {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 16px;
        z-index: 10;
        background: rgba(10,10,10,.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 14px 24px;
        border-radius: 60px;
        box-shadow: 0 8px 32px rgba(0,0,0,.5);
        border: 1px solid rgba(201,162,39,.2);
    }

    .ctrl-btn {
        width: 60px; height: 60px;
        border-radius: 50%;
        border: none;
        background: rgba(255,255,255,.12);
        color: #fff;
        font-size: 1.3rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
    }
    .ctrl-btn:hover {
        background: rgba(201,162,39,.3);
        transform: translateY(-2px);
    }
    .ctrl-btn.muted { background: #dc2626; }
    .ctrl-hangup { background: #dc2626; }
    .ctrl-hangup:hover { background: #b91c1c; }

    @media (max-width: 768px) {
        .local-video { width: 130px; height: 170px; bottom: 100px; right: 12px; }
        .placeholder-avatar { width: 110px; height: 110px; font-size: 2.4rem; }
        .ctrl-btn { width: 52px; height: 52px; font-size: 1.1rem; }
        .call-controls { padding: 12px 18px; gap: 12px; }
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    @keyframes callRipple {
        0%   { transform: scale(1);   opacity: .7; }
        100% { transform: scale(1.8); opacity: 0; }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/peerjs@1.5.5/dist/peerjs.min.js"></script>

<script>
    window.VIDEO_CALL_CONFIG = {
        roomId:        @json($roomId),
        currentUserId: @json((int) $adminEgresso->id),
        targetUserId:  @json((int) ($chamada->chamador_id === (int) $adminEgresso->id ? $chamada->recetor_id : $chamada->chamador_id)),
        callType:      @json($type),
        amIChamador:   @json((int) $chamada->chamador_id === (int) $adminEgresso->id),
        userName:      @json($adminEgresso->nome_completo ?? 'Administrador'),

        redirectUrl:   @json(url('/admin/mensagens')),

        peerHost:   @json(config('services.peer.host', request()->getHost())),
        peerPort:   @json((int) config('services.peer.port', 9000)),
        peerPath:   @json(config('services.peer.path', '/myapp')),
        peerSecure: @json((bool) config('services.peer.secure', false)),

        routes: {
            registarPeer: @json(url('/admin/video-call/registar-peer')),
            estado:       @json(url('/admin/video-call/estado')),
            encerrar:     @json(url('/admin/video-call/encerrar')),
        },
    };

    console.log('🎬 VIDEO_CALL_CONFIG (admin):', window.VIDEO_CALL_CONFIG);
</script>

@vite('resources/js/video-call.js')
@endpush