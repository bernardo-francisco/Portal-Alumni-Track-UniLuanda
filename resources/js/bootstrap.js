import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// ============================================================
// CONFIGURAÇÃO DO REVERB
// ============================================================

const reverbConfig = {
    key: import.meta.env.VITE_REVERB_APP_KEY || 'p9qjivkftnpfpso92p3k',
    host: import.meta.env.VITE_REVERB_HOST || 'localhost',
    port: parseInt(import.meta.env.VITE_REVERB_PORT || '8080', 10),
    scheme: import.meta.env.VITE_REVERB_SCHEME || 'http',
};

console.log('🔧 REVERB CONFIG:', reverbConfig);

// ============================================================
// CSRF TOKEN
// ============================================================

const csrfMeta = document.querySelector('meta[name="csrf-token"]');

const csrfToken = csrfMeta
    ? csrfMeta.getAttribute('content')
    : null;

if (csrfToken) {
    console.log('✅ CSRF Token encontrado.');
} else {
    console.warn(
        '⚠️ CSRF Token não encontrado. Verifica se o layout possui:',
        '<meta name="csrf-token" content="{{ csrf_token() }}">'
    );
}

// ============================================================
// LARAVEL ECHO
// ============================================================

window.Echo = new Echo({
    broadcaster: 'reverb',

    key: reverbConfig.key,

    wsHost: reverbConfig.host,
    wsPort: reverbConfig.port,
    wssPort: reverbConfig.port,

    forceTLS: reverbConfig.scheme === 'https',

    enabledTransports: ['ws', 'wss'],

    disableStats: true,

    // ========================================================
    // AUTENTICAÇÃO DOS CANAIS PRIVADOS
    // ========================================================

    authEndpoint: '/broadcasting/auth',

    auth: {
        headers: {
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    },

    // Permite enviar o cookie da sessão Laravel
    withCredentials: true,
});

// ============================================================
// DEBUG
// ============================================================

console.log('✅ Echo criado com sucesso:', window.Echo);

// ============================================================
// DEBUG DA CONEXÃO WEBSOCKET
// ============================================================

if (
    window.Echo.connector &&
    window.Echo.connector.pusher &&
    window.Echo.connector.pusher.connection
) {
    const connection =
        window.Echo.connector.pusher.connection;

    connection.bind('connected', () => {
        console.log('🟢 WebSocket LIGADO ao Reverb');
    });

    connection.bind('disconnected', () => {
        console.warn(
            '🔴 WebSocket DESLIGADO do Reverb'
        );
    });

    connection.bind('error', (err) => {
        console.error(
            '❌ Erro no WebSocket:',
            err
        );
    });

    connection.bind('state_change', (states) => {
        console.log(
            '🔄 Estado WebSocket:',
            states.previous,
            '→',
            states.current
        );
    });
}

// ============================================================
// FUNÇÃO DE DEBUG DA AUTENTICAÇÃO
// ============================================================
//
// Pode ser chamada no Console:
// window.debugBroadcastAuth()
//
// ============================================================

window.debugBroadcastAuth = async function () {

    console.log('🔐 Testando autenticação do broadcasting...');

    try {

        const response = await fetch('/broadcasting/auth', {
            method: 'POST',

            credentials: 'same-origin',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || '',
            },

            body: JSON.stringify({}),
        });

        console.log(
            '📡 Status /broadcasting/auth:',
            response.status
        );

        const text = await response.text();

        console.log(
            '📨 Resposta /broadcasting/auth:',
            text
        );

        return {
            status: response.status,
            response: text,
        };

    } catch (error) {

        console.error(
            '❌ Erro ao testar /broadcasting/auth:',
            error
        );

        return null;
    }
};

