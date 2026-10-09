import Chart from 'chart.js/auto';
window.Chart = Chart;

import './bootstrap';

/* ================================================================
   APP.JS — UniLuanda Alumni
   Ficheiro único de JavaScript do projecto.

   Contém:
     1.  Sidebar (admin / egresso / empresa) — toggle e persistência
     2.  Auto-dismiss de alertas
     3.  Badge de notificações (admin + egresso + empresa)
     4.  Marcar notificações como lidas (admin / egresso / empresa)
     5.  Filtros de notificações
     6.  Echo — notificações em tempo real
     7.  Sistema de chamadas (admin + egresso) — MODAL + VIBRAÇÃO + NÃO FECHA SOZINHO
     8.  Modais de entrevista e rejeição
     9.  Modais de feed (editar / imagem / curtir)
    10.  Chat (admin + egresso) — editar, eliminar, áudio, ficheiros
    11.  Chatbot (admin + egresso)
    12.  Formulário de registo (tipo de utilizador, validação de processo)
    13.  Login (recuperação de senha via modal)
    14.  Geolocalização (mapa, reverse geocoding, coordenadas)
    15.  Filtros dinâmicos (unidade → curso)
    16.  Avaliação por estrelas (feedback)
    17.  Contadores de caracteres
    18.  Scroll animations + counters (site público)
    19.  Modal de egressos públicos (filtros + pesquisa)
    20.  Navbar scroll + scroll spy + smooth scroll
    21.  Confirmações de inscrição / candidatura / cancelamento
    22.  Helpers (Toast, CSRF, fetch com token)
================================================================ */

'use strict';

/* ================================================================
   HELPERS GLOBAIS
================================================================ */
const UniLuanda = (function () {

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function mostrarToast(msg, tipo = 'success') {
        const cores = { success: '#16a34a', info: '#c9a227', danger: '#dc2626' };
        const antigo = document.querySelector('.app-toast');
        if (antigo) antigo.remove();

        const toast = document.createElement('div');
        toast.className = 'app-toast';
        toast.style.cssText = `
            position: fixed; top: 24px; right: 24px; z-index: 99999;
            background: ${cores[tipo] || cores.success};
            color: #fff; padding: 12px 22px; border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
            font-weight: 500; font-size: .9rem;
        `;
        toast.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + msg;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 2600);
    }

    async function postJSON(url, dados = {}, method = 'POST') {
        const resp = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify(dados),
        });
        return resp.json();
    }

    function debounce(fn, espera = 300) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), espera);
        };
    }

    return { csrf, mostrarToast, postJSON, debounce };
})();

window.mostrarToast = UniLuanda.mostrarToast;


/* ================================================================
   1. SIDEBAR
================================================================ */
(function () {
    const MAPA = [
        { id: 'adminSidebar', overlay: 'sidebarOverlay', bodyClass: 'sidebar-collapsed', storageKey: 'adminSidebarCollapsed' },
        { id: 'egressoSidebar', overlay: 'sidebarOverlay', bodyClass: 'egresso-sidebar-collapsed', storageKey: 'egressoSidebarCollapsed' },
        { id: 'empSidebarPanel', overlay: 'empSidebarOverlay', bodyClass: 'emp-sidebar-collapsed', storageKey: 'empSidebarCollapsedPref' },
    ];

    function getConfig() {
        return MAPA.find(c => document.getElementById(c.id)) || null;
    }

    window.toggleSidebar = function () {
        const cfg = getConfig();
        if (!cfg) return;
        const sidebar = document.getElementById(cfg.id);
        const overlay = document.getElementById(cfg.overlay);
        if (!sidebar) return;
        const isDesktop = window.innerWidth >= 992;
        if (isDesktop) {
            document.body.classList.toggle(cfg.bodyClass);
            const collapsed = document.body.classList.contains(cfg.bodyClass);
            localStorage.setItem(cfg.storageKey, collapsed ? '1' : '0');
        } else {
            const abrir = !sidebar.classList.contains('open');
            sidebar.classList.toggle('open', abrir);
            if (overlay) overlay.classList.toggle('open', abrir);
        }
    };

    window.fecharSidebarMobile = function () {
        const cfg = getConfig();
        if (!cfg) return;
        const sidebar = document.getElementById(cfg.id);
        const overlay = document.getElementById(cfg.overlay);
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    };

    document.addEventListener('DOMContentLoaded', function () {
        const cfg = getConfig();
        if (!cfg) return;
        if (window.innerWidth >= 992 && localStorage.getItem(cfg.storageKey) === '1') {
            document.body.classList.add(cfg.bodyClass);
        }
        document.querySelectorAll(`#${cfg.id} a`).forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) window.fecharSidebarMobile();
            });
        });
    });

    window.addEventListener('resize', function () {
        const cfg = getConfig();
        if (!cfg) return;
        if (window.innerWidth >= 992) {
            window.fecharSidebarMobile();
            if (localStorage.getItem(cfg.storageKey) === '1') {
                document.body.classList.add(cfg.bodyClass);
            } else {
                document.body.classList.remove(cfg.bodyClass);
            }
        } else {
            document.body.classList.remove(cfg.bodyClass);
        }
    });

    document.addEventListener('click', function (event) {
        if (window.innerWidth >= 992) return;
        const cfg = getConfig();
        if (!cfg) return;
        const sidebar = document.getElementById(cfg.id);
        const topbar = document.querySelector('.admin-topbar, .empresa-topbar');
        if (!sidebar) return;
        if (sidebar.contains(event.target)) return;
        if (topbar && topbar.contains(event.target)) return;
        window.fecharSidebarMobile();
    });

    document.addEventListener('show.bs.modal', function () {
        const overlay = document.getElementById('sidebarOverlay');
        if (overlay) {
            overlay.style.display = 'none';
            overlay.classList.remove('open');
        }
    });
    document.addEventListener('hidden.bs.modal', function () {
        const overlay = document.getElementById('sidebarOverlay');
        if (overlay) overlay.style.display = '';
    });
})();


/* ================================================================
   2. AUTO-DISMISS DE ALERTAS
================================================================ */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
        setTimeout(() => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });
});


/* ================================================================
   3. BADGE DE NOTIFICAÇÕES
================================================================ */
function decrementarBadgeNotificacoes() {
    const badge = document.getElementById('badge-notificacoes');
    if (!badge) return;
    let atual = parseInt(badge.dataset.total || badge.textContent.trim()) || 0;
    atual = Math.max(0, atual - 1);
    if (atual === 0) { badge.remove(); return; }
    badge.dataset.total = atual;
    const hidden = badge.querySelector('.visually-hidden');
    badge.textContent = atual;
    if (hidden) badge.appendChild(hidden);
}

function atualizarBadgeNotificacoes(url) {
    if (!url) return;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('badge-notificacoes');
        const btn = document.querySelector('[title="Notificações"]');
        if (!data.total || data.total <= 0) { if (badge) badge.remove(); return; }
        if (badge) {
            badge.dataset.total = data.total;
            const hidden = badge.querySelector('.visually-hidden');
            badge.textContent = data.total > 99 ? '99+' : data.total;
            if (hidden) badge.appendChild(hidden);
        } else if (btn) {
            const novo = document.createElement('span');
            novo.id = 'badge-notificacoes';
            novo.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
            novo.style.cssText = 'font-size: .6rem; padding: 3px 7px; border: 2px solid white;';
            novo.dataset.total = data.total;
            novo.textContent = data.total > 99 ? '99+' : data.total;
            btn.appendChild(novo);
        }
    })
    .catch(err => console.log('Erro ao atualizar badge:', err));
}


/* ================================================================
   4. MARCAR NOTIFICAÇÕES COMO LIDAS
================================================================ */
function marcarComoLida(id, event) {
    if (event && typeof decrementarBadgeNotificacoes === 'function') decrementarBadgeNotificacoes();
    const url = `/admin/notificacoes/${id}/marcar-lida`;
    const payload = new Blob([JSON.stringify({ _token: UniLuanda.csrf() })], { type: 'application/json' });
    if (navigator.sendBeacon) {
        navigator.sendBeacon(url, payload);
    } else {
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UniLuanda.csrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        }).catch(() => {});
    }
    if (!event) {
        const item = document.querySelector(`[data-notif-id="${id}"]`);
        if (!item) return;
        item.classList.remove('notification-unread');
        item.classList.add('notification-read');
        item.setAttribute('data-status', 'lida');
        item.querySelector('.badge.bg-danger')?.remove();
    }
}

function marcarNotifEgresso(id) {
    UniLuanda.postJSON(`/egresso/notificacoes/${id}/marcar-lida`).then(data => {
        if (!data.success) return;
        const badge = document.getElementById('badge-notificacoes');
        if (badge) {
            let atual = parseInt(badge.dataset.total || badge.textContent) || 0;
            atual = Math.max(0, atual - 1);
            badge.dataset.total = atual;
            badge.textContent = atual;
            if (atual === 0) badge.style.display = 'none';
        }
        const item = document.getElementById('notif-item-' + id);
        if (item) { item.style.transition = 'opacity 0.3s ease'; item.style.opacity = '0'; setTimeout(() => item.remove(), 300); }
    }).catch(() => {});
}

function marcarNotifAdmin(id) {
    UniLuanda.postJSON(`/admin/notificacoes/${id}/marcar-lida`).then(data => {
        if (!data.success) return;
        const badge = document.getElementById('badge-notificacoes');
        if (badge) {
            let atual = parseInt(badge.dataset.total || badge.textContent) || 0;
            atual = Math.max(0, atual - 1);
            badge.dataset.total = atual;
            badge.textContent = atual;
            if (atual === 0) badge.style.display = 'none';
        }
        const item = document.getElementById('notif-item-' + id);
        if (item) { item.style.transition = 'opacity 0.3s ease'; item.style.opacity = '0'; setTimeout(() => item.remove(), 300); }
    }).catch(() => {});
}

function marcarComoLidaEmpresa(id) {
    fetch(`/empresa/notificacoes/${id}/lida`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': UniLuanda.csrf(), 'Accept': 'application/json' },
    }).catch(() => {});
}

function marcarTodasComoLidas(event) {
    if (event) { event.preventDefault(); event.stopPropagation(); }
    fetch('/empresa/notificacoes/todas-lidas', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': UniLuanda.csrf(), 'Accept': 'application/json' },
    }).then(() => {
        document.querySelectorAll('.notif-unread').forEach(el => { el.classList.remove('notif-unread'); el.querySelector('.notif-dot')?.remove(); });
        document.querySelector('.notif-badge')?.remove();
        document.querySelector('.notif-header-count')?.remove();
        document.querySelector('.notif-mark-all')?.remove();
    }).catch(() => {});
}


/* ================================================================
   5. FILTROS DE NOTIFICAÇÕES
================================================================ */
function filtrar(filtro, btn) {
    document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    document.querySelectorAll('.notification-item').forEach(item => {
        const status = item.getAttribute('data-status');
        if (filtro === 'todas') { item.style.display = ''; }
        else if (filtro === 'nao-lidas') { item.style.display = status === 'nao-lida' ? '' : 'none'; }
        else if (filtro === 'lidas') { item.style.display = status === 'lida' ? '' : 'none'; }
    });
}


/* ================================================================
   6. ECHO — NOTIFICAÇÕES EM TEMPO REAL
================================================================ */
(function () {
    const body = document.body;
    const egressoId = body.dataset.egressoId || window.APP_EGRESSO_ID || null;
    if (!egressoId) return;

    function waitForEcho(cb, tentativas = 0) {
        if (typeof window.Echo !== 'undefined' && window.Echo) { cb(); return; }
        if (tentativas > 100) return;
        setTimeout(() => waitForEcho(cb, tentativas + 1), 100);
    }

    waitForEcho(function () {
        window.Echo.private('user.' + egressoId)
            .listen('.NovaNotificacao', (e) => {
                const tiposMensagem = ['mensagem', 'audio', 'ficheiro'];
                if (!tiposMensagem.includes(e.tipo)) {
                    const badgeSino = document.getElementById('badge-notificacoes');
                    if (badgeSino) {
                        let atual = parseInt(badgeSino.dataset.total || badgeSino.textContent) || 0;
                        atual++;
                        badgeSino.dataset.total = atual;
                        badgeSino.textContent = atual > 99 ? '99+' : atual;
                        badgeSino.style.display = '';
                    }
                }
                if (tiposMensagem.includes(e.tipo)) {
                    const badgeMsg = document.getElementById('badge-mensagens');
                    if (badgeMsg) {
                        let atual = parseInt(badgeMsg.dataset.total || badgeMsg.textContent) || 0;
                        atual++;
                        badgeMsg.dataset.total = atual;
                        badgeMsg.textContent = atual > 99 ? '99+' : atual;
                        badgeMsg.style.display = '';
                    }
                }
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.frequency.value = 900;
                    gain.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                    osc.start(); osc.stop(ctx.currentTime + 0.3);
                } catch (err) {}
                if (typeof window.mostrarToast === 'function' && e.titulo) {
                    window.mostrarToast(e.titulo, 'info');
                }
            });
        console.log('📡 Echo: a escutar canal user.' + egressoId);
    });
})();

/* ================================================================
   7. LISTENER — CHAMADA RECEBIDA (modal + ringtone + vibração)
================================================================ */
(function () {
    const body = document.body;
    const egressoId = body.dataset.egressoId || window.APP_EGRESSO_ID || null;
    const isAdmin = body.dataset.panel === 'admin';

    if (!egressoId) return;

    function waitForEcho(cb, tentativas = 0) {
        if (typeof window.Echo !== 'undefined' && window.Echo) { cb(); return; }
        if (tentativas > 100) return;
        setTimeout(() => waitForEcho(cb, tentativas + 1), 100);
    }

    // ============================================================
    // RINGTONE + VIBRAÇÃO EM LOOP
    // ============================================================
    let ringtoneInterval = null;
    let vibracaoInterval = null;
    let ringtoneAudioCtx = null;

    function iniciarRingtone() {
        pararRingtone();

        // Vibração em loop (a cada 3s)
        if (navigator.vibrate) {
            try { navigator.vibrate([1000, 500, 1000, 500]); } catch (e) {}
            vibracaoInterval = setInterval(() => {
                try { navigator.vibrate([1000, 500, 1000, 500]); } catch (e) {}
            }, 3000);
        }

        // Som de toque
        try {
            ringtoneAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const tocar = () => {
                if (!ringtoneAudioCtx) return;
                try {
                    const osc = ringtoneAudioCtx.createOscillator();
                    const gain = ringtoneAudioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(ringtoneAudioCtx.destination);
                    osc.frequency.value = 800;
                    osc.type = 'sine';
                    gain.gain.setValueAtTime(0.0001, ringtoneAudioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.3, ringtoneAudioCtx.currentTime + 0.1);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ringtoneAudioCtx.currentTime + 0.6);
                    osc.start();
                    osc.stop(ringtoneAudioCtx.currentTime + 0.6);
                } catch (e) {}
            };
            tocar();
            ringtoneInterval = setInterval(tocar, 1500);
        } catch (e) {
            console.warn('Ringtone falhou:', e);
        }
    }

    function pararRingtone() {
        if (ringtoneInterval) { clearInterval(ringtoneInterval); ringtoneInterval = null; }
        if (vibracaoInterval) { clearInterval(vibracaoInterval); vibracaoInterval = null; }
        if (navigator.vibrate) { try { navigator.vibrate(0); } catch (e) {} }
        if (ringtoneAudioCtx) {
            try { ringtoneAudioCtx.close(); } catch (e) {}
            ringtoneAudioCtx = null;
        }
    }

    window.pararRingtoneChamada = pararRingtone;

    // ============================================================
    // LISTENER DO MODAL
    // ============================================================
    waitForEcho(function () {
        const modalEl = document.getElementById('modalChamadaRecebida');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        const nomeEl = document.getElementById('chamadaNome');
        const tipoEl = document.getElementById('chamadaTipo');
        const btnAceitar = document.getElementById('btnAceitarChamada');
        const btnRecusar = document.getElementById('btnRecusarChamada');
        if (!nomeEl || !tipoEl || !btnAceitar || !btnRecusar) return;

        const modal = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false,
            focus: true,
        });

        let chamadaAtual = null;
        let timeoutAuto = null;

        // Quando o modal fechar (qualquer motivo), para o ringtone
        modalEl.addEventListener('hidden.bs.modal', function () {
            pararRingtone();
            if (timeoutAuto) { clearTimeout(timeoutAuto); timeoutAuto = null; }
        });

        window.Echo.private('user.' + egressoId)
            .listen('.StartVideoCall', (evento) => {
                console.log('📞 StartVideoCall recebido:', evento);
                if (!evento) return;

                chamadaAtual = evento;
                nomeEl.textContent = evento.caller_name || 'Utilizador';
                tipoEl.textContent = evento.type === 'video' ? '📹 Chamada de vídeo' : '📞 Chamada de voz';

                modal.show();
                iniciarRingtone();  // toca + vibra em loop até haver ação

                // Auto-fechar após 45s se ninguém atender
                if (timeoutAuto) clearTimeout(timeoutAuto);
                timeoutAuto = setTimeout(() => {
                    console.log('⏰ Timeout — ninguém atendeu');
                    pararRingtone();
                    modal.hide();
                    chamadaAtual = null;
                }, 45000);
            });

        // ============================================================
        // ACEITAR
        // ============================================================
        btnAceitar.addEventListener('click', async function () {
            if (!chamadaAtual) return;
            const { room_id, caller_id, type } = chamadaAtual;
            if (!room_id) return;

            btnAceitar.disabled = true;
            pararRingtone();  // ← PARA O RINGTONE IMEDIATAMENTE

            const base = isAdmin ? '/admin' : '/egresso';

            // 1) CHAMAR /aceitar primeiro — ESSENCIAL
            try {
                const resp = await fetch(`${base}/video-call/aceitar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ room_id }),
                });

                const data = await resp.json();

                if (!data.success) {
                    console.warn('⚠️ Aceitar falhou:', data);
                    btnAceitar.disabled = false;
                    return;
                }

                console.log('✅ Chamada aceite no servidor:', data);
            } catch (e) {
                console.error('❌ Erro ao chamar /aceitar:', e);
                btnAceitar.disabled = false;
                return;
            }

            // 2) Fechar modal e redirecionar
            modal.hide();
            chamadaAtual = null;

            let url = `${base}/video-call/${encodeURIComponent(room_id)}`;
            if (caller_id) {
                url += `?target_user=${encodeURIComponent(caller_id)}&type=${encodeURIComponent(type || 'video')}&role=recetor`;
            }
            window.location.href = url;
        });

        // ============================================================
        // RECUSAR
        // ============================================================
        btnRecusar.addEventListener('click', async function () {
            if (!chamadaAtual) return;
            const chamada = chamadaAtual;
            btnRecusar.disabled = true;
            pararRingtone();  // ← PARA O RINGTONE IMEDIATAMENTE

            const rotaEncerrar = isAdmin ? '/admin/video-call/encerrar' : '/egresso/video-call/encerrar';

            try {
                await fetch(rotaEncerrar, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ room_id: chamada.room_id }),
                });
            } catch (e) {
                console.warn('Erro ao recusar:', e);
            } finally {
                modal.hide();
                chamadaAtual = null;
                btnRecusar.disabled = false;
            }
        });
    });
})();

/* ================================================================
   8. MODAIS DE ENTREVISTA E REJEIÇÃO
================================================================ */
(function () {
    function abrirModal(modal) {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open-custom');
    }
    function fecharModal(modal) {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open-custom');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modalEntrevista = document.getElementById('modalEntrevistaCustom');
        const btnAbrir = document.getElementById('btnAbrirEntrevista');
        const btnReagendar = document.getElementById('btnAbrirEntrevistaReagendar');
        const btnFechar = document.getElementById('btnFecharEntrevista');
        const btnCancelar = document.getElementById('btnCancelarEntrevista');
        const backdrop = document.getElementById('entrevistaBackdrop');
        const inputData = document.getElementById('data_entrevista_input');

        function abrirEntrevista(e) {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            abrirModal(modalEntrevista);
            setTimeout(() => inputData?.focus(), 100);
        }

        if (modalEntrevista) {
            btnAbrir?.addEventListener('click', abrirEntrevista);
            btnReagendar?.addEventListener('click', abrirEntrevista);
            btnFechar?.addEventListener('click', e => { e.preventDefault(); fecharModal(modalEntrevista); });
            btnCancelar?.addEventListener('click', e => { e.preventDefault(); fecharModal(modalEntrevista); });
            backdrop?.addEventListener('click', () => fecharModal(modalEntrevista));
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && modalEntrevista.classList.contains('is-open')) fecharModal(modalEntrevista);
            });
        }

        const modalRejeitar = document.getElementById('modalRejeitarCustom');
        const btnAbrirRej = document.getElementById('btnAbrirRejeicao');
        const btnFecharRej = document.getElementById('btnFecharRejeicao');
        const btnCancelarRej = document.getElementById('btnCancelarRejeicao');
        const backdropRej = document.getElementById('rejectionBackdrop');
        const textareaRej = document.getElementById('motivo_rejeicao_input');
        const formRej = document.getElementById('formRejeitar');
        const erroRej = document.getElementById('motivoErro');

        if (btnAbrirRej && modalRejeitar) {
            btnAbrirRej.addEventListener('click', e => {
                e.preventDefault(); e.stopPropagation();
                abrirModal(modalRejeitar);
                setTimeout(() => textareaRej?.focus(), 100);
            });
            btnFecharRej?.addEventListener('click', e => { e.preventDefault(); fecharModal(modalRejeitar); });
            btnCancelarRej?.addEventListener('click', e => { e.preventDefault(); fecharModal(modalRejeitar); });
            backdropRej?.addEventListener('click', () => fecharModal(modalRejeitar));
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && modalRejeitar.classList.contains('is-open')) fecharModal(modalRejeitar);
            });

            if (formRej && textareaRej) {
                formRej.addEventListener('submit', e => {
                    const motivo = textareaRej.value.trim();
                    if (!motivo) {
                        e.preventDefault();
                        textareaRej.classList.add('is-invalid');
                        erroRej?.classList.add('show');
                        textareaRej.focus();
                        return;
                    }
                    textareaRej.classList.remove('is-invalid');
                    erroRej?.classList.remove('show');
                });
                textareaRej.addEventListener('input', () => {
                    if (textareaRej.value.trim()) {
                        textareaRej.classList.remove('is-invalid');
                        erroRej?.classList.remove('show');
                    }
                });
            }
        }
    });
})();


/* ================================================================
   9. MODAIS DE FEED
================================================================ */
document.addEventListener('DOMContentLoaded', function () {
    const modalEditar = document.getElementById('modalEditar');
    const modalImagem = document.getElementById('modalImagem');

    if (modalEditar && modalEditar.parentElement !== document.body) document.body.appendChild(modalEditar);
    if (modalImagem && modalImagem.parentElement !== document.body) document.body.appendChild(modalImagem);

    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');

    [modalEditar, modalImagem].forEach(modalEl => {
        if (!modalEl) return;
        modalEl.addEventListener('hidden.bs.modal', function () {
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }, 50);
        });
    });

    if (modalImagem) {
        modalImagem.addEventListener('hidden.bs.modal', function () {
            const img = document.getElementById('imagemAmpliada');
            if (img) img.src = '';
        });
    }
});

function previewImagem(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const preview = document.getElementById('previewImg');
        const box = document.getElementById('previewImagem');
        if (preview) preview.src = e.target.result;
        if (box) box.classList.remove('d-none');
    };
    reader.readAsDataURL(input.files[0]);
}

function removerImagem() {
    const input = document.getElementById('imagem');
    const box = document.getElementById('previewImagem');
    const preview = document.getElementById('previewImg');
    if (input) input.value = '';
    if (preview) preview.src = '';
    if (box) box.classList.add('d-none');
}

function previewNovaImagem(input) {
    const box = document.getElementById('previewNovaImagemBox');
    const img = document.getElementById('previewNovaImagemImg');
    if (!input.files || !input.files[0]) {
        if (box) box.classList.add('d-none');
        if (img) img.src = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
        if (img) img.src = e.target.result;
        if (box) box.classList.remove('d-none');
    };
    reader.readAsDataURL(input.files[0]);
}

document.addEventListener('change', function (event) {
    if (event.target?.id === 'editImagem') {
        const remover = document.getElementById('removerImagemCheck');
        if (remover && event.target.files.length > 0) remover.checked = false;
    }
});

function curtir(id) {
    const card = document.querySelector(`[data-publicacao-id="${id}"]`);
    if (!card) return;
    const btn = card.querySelector('.btn-curtir');
    const span = card.querySelector('.curtidas-count');
    if (!btn || !span) return;
    btn.disabled = true;

    fetch(`/egresso/feed/${id}/curtir`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': UniLuanda.csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(r => r.ok ? r.json() : Promise.reject('HTTP ' + r.status))
    .then(data => {
        if (data.success) {
            span.textContent = data.total;
            btn.classList.toggle('text-danger', data.curtido);
            btn.classList.toggle('text-muted', !data.curtido);
        }
    })
    .catch(err => console.error('Erro ao curtir:', err))
    .finally(() => { btn.disabled = false; });
}

function abrirImagem(src) {
    const modalEl = document.getElementById('modalImagem');
    if (!modalEl) return;
    const img = document.getElementById('imagemAmpliada');
    if (img) img.src = src;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

function abrirModalEditar(id) {
    const modalEl = document.getElementById('modalEditar');
    const card = document.querySelector(`[data-publicacao-id="${id}"]`);
    if (!modalEl || !card) return;

    const conteudo = document.getElementById('editConteudo');
    const form = document.getElementById('formEditar');
    const inputImagem = document.getElementById('editImagem');
    const previewBox = document.getElementById('previewNovaImagemBox');
    const previewImg = document.getElementById('previewNovaImagemImg');
    const removerCheck = document.getElementById('removerImagemCheck');
    const imagemAtualBox = document.getElementById('imagemAtualBox');
    const imagemAtualPreview = document.getElementById('imagemAtualPreview');

    if (conteudo) conteudo.value = card.dataset.conteudo || '';
    if (form) form.action = `/egresso/feed/${id}`;
    if (inputImagem) inputImagem.value = '';
    if (previewBox) previewBox.classList.add('d-none');
    if (previewImg) previewImg.src = '';
    if (removerCheck) removerCheck.checked = false;

    const imagemAtual = card.dataset.imagem || '';
    if (imagemAtual) {
        if (imagemAtualPreview) imagemAtualPreview.src = imagemAtual;
        if (imagemAtualBox) imagemAtualBox.style.display = 'block';
    } else {
        if (imagemAtualPreview) imagemAtualPreview.src = '';
        if (imagemAtualBox) imagemAtualBox.style.display = 'none';
    }

    const modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false, focus: true });
    modal.show();

    modalEl.addEventListener('shown.bs.modal', function handler() {
        if (conteudo) {
            conteudo.focus();
            conteudo.setSelectionRange(conteudo.value.length, conteudo.value.length);
        }
        modalEl.removeEventListener('shown.bs.modal', handler);
    });
}


/* ================================================================
   10. CHAT — EDITAR / ELIMINAR / ÁUDIO / FICHEIROS
================================================================ */
(function () {
    const body = document.body;
    const panel = body.dataset.panel;
    if (panel !== 'admin' && panel !== 'egresso') return;

    const contatoId = body.dataset.contatoId;
    if (!contatoId) return;

    const base = panel === 'admin' ? '/admin' : '/egresso';

    window.ativarEdicao = function (id) {
        const c = document.getElementById('conteudo-' + id);
        const e = document.getElementById('edit-container-' + id);
        const ta = document.getElementById('edit-input-' + id);
        if (c) c.style.display = 'none';
        if (e) e.classList.remove('d-none');
        if (ta) { ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length); }
    };

    window.cancelarEdicao = function (id) {
        const c = document.getElementById('conteudo-' + id);
        const e = document.getElementById('edit-container-' + id);
        if (c) c.style.display = 'block';
        if (e) e.classList.add('d-none');
    };

    window.salvarEdicao = function (id) {
        const ta = document.getElementById('edit-input-' + id);
        if (!ta) return;
        const novoTexto = ta.value.trim();
        if (!novoTexto) { alert('A mensagem não pode estar vazia.'); return; }

        const btns = document.querySelectorAll(`#edit-container-${id} .btn`);
        btns.forEach(b => b.disabled = true);

        fetch(`${base}/mensagens/${id}/editar`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UniLuanda.csrf(), 'Accept': 'application/json' },
            body: JSON.stringify({ mensagem: novoTexto }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const c = document.getElementById('conteudo-' + id);
                if (c) { c.textContent = data.mensagem; c.style.display = 'block'; }
                document.getElementById('edit-container-' + id)?.classList.add('d-none');
                const bubble = document.querySelector(`#mensagem-${id} .chat-bubble`);
                const time = bubble?.querySelector('.chat-time');
                if (time && !time.querySelector('.chat-edited')) {
                    const span = document.createElement('span');
                    span.className = 'chat-edited';
                    span.textContent = ' (editado)';
                    time.appendChild(span);
                }
                UniLuanda.mostrarToast('Mensagem editada com sucesso!');
            } else {
                alert(data.message || 'Erro ao editar mensagem.');
            }
        })
        .catch(() => alert('Erro ao editar mensagem.'))
        .finally(() => btns.forEach(b => b.disabled = false));
    };

    window.confirmarEliminar = function (id) {
        const campo = document.getElementById('mensagem_id_eliminar');
        if (campo) campo.value = id;
        const modalEl = document.getElementById('modalEliminar');
        if (modalEl) new bootstrap.Modal(modalEl).show();
    };

    window.eliminarMensagem = function () {
        const id = document.getElementById('mensagem_id_eliminar')?.value;
        if (!id) return;
        const btn = document.querySelector('#modalEliminar .btn-danger');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> A eliminar...';
        }
        fetch(`${base}/mensagens/${id}/eliminar`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UniLuanda.csrf(), 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById('mensagem-' + id);
                if (el) {
                    el.style.transition = 'all 0.3s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'translateX(-30px)';
                    setTimeout(() => {
                        el.remove();
                        const c = document.getElementById('chatContainer');
                        if (c && !c.querySelector('.chat-bubble')) {
                            c.innerHTML = `<div class="text-center py-5"><div class="empty-chat-icon mb-3"><i class="fas fa-comment-dots"></i></div><h6 class="fw-bold mb-1">Nenhuma mensagem ainda</h6><p class="text-muted small mb-0">Envie a primeira mensagem para iniciar a conversa.</p></div>`;
                        }
                    }, 300);
                }
                const modalEl = document.getElementById('modalEliminar');
                if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();
                UniLuanda.mostrarToast('Mensagem eliminada com sucesso!');
            } else { alert(data.message || 'Erro ao eliminar mensagem.'); }
        })
        .catch(() => alert('Erro ao eliminar mensagem.'))
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash me-1"></i> Eliminar';
            }
        });
    };

    window.iniciarChamadaVideo = function (contatoId) { iniciarChamada(contatoId, 'video'); };
    window.iniciarChamadaAudio = function (contatoId) { iniciarChamada(contatoId, 'audio'); };

    function iniciarChamada(contatoId, tipo) {
        console.log('📞 iniciarChamada() chamado', { contatoId, tipo, panel });
        const btns = document.querySelectorAll('.call-btn');
        btns.forEach(b => b.disabled = true);

        const url = panel === 'admin' ? '/admin/video-call/iniciar' : '/egresso/video-call/iniciar';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UniLuanda.csrf(), 'Accept': 'application/json' },
            body: JSON.stringify({ target_user_id: contatoId, type: tipo }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const base = panel === 'admin' ? '/admin' : '/egresso';
                const role = panel === 'egresso' ? '&role=chamador' : '';
                window.location.href = `${base}/video-call/${data.room_id}?target_user=${contatoId}&type=${tipo}${role}`;
            } else {
                alert(data.message || 'Erro ao iniciar chamada.');
                btns.forEach(b => b.disabled = false);
            }
        })
        .catch(() => { alert('Erro ao iniciar chamada.'); btns.forEach(b => b.disabled = false); });
    }

    (function gravarAudio() {
        const btnGravar = document.getElementById('btnGravarAudio');
        if (!btnGravar) return;
        const btnEnviar = document.getElementById('btnEnviarAudio');
        const btnCancelar = document.getElementById('btnCancelarGravacao');
        const ui = document.getElementById('audioRecordingUI');
        const form = document.getElementById('formMensagem');
        const input = document.getElementById('inputMensagem');
        const timeEl = document.getElementById('recordingTime');
        let mediaRecorder = null, audioChunks = [], stream = null;
        let inicio = null, timer = null, gravando = false;

        btnGravar.addEventListener('click', async () => {
            if (gravando) return;
            try { stream = await navigator.mediaDevices.getUserMedia({ audio: true }); }
            catch (err) { alert('Não foi possível aceder ao microfone: ' + err.message); return; }

            audioChunks = [];
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
            mediaRecorder.start();
            gravando = true;
            inicio = Date.now();
            btnGravar.classList.add('recording');
            btnGravar.innerHTML = '<i class="fas fa-stop"></i>';
            form?.classList.add('d-none');
            ui?.classList.remove('d-none');
            if (input) input.required = false;

            timer = setInterval(() => {
                const seg = Math.floor((Date.now() - inicio) / 1000);
                const m = String(Math.floor(seg / 60)).padStart(2, '0');
                const s = String(seg % 60).padStart(2, '0');
                if (timeEl) timeEl.textContent = `${m}:${s}`;
            }, 200);
        });

        btnCancelar?.addEventListener('click', () => parar(true));
        btnEnviar?.addEventListener('click', () => parar(false));

        function parar(cancelar) {
            if (!mediaRecorder) return;
            mediaRecorder.onstop = async () => {
                const duracao = Math.floor((Date.now() - inicio) / 1000);
                if (stream) stream.getTracks().forEach(t => t.stop());
                if (cancelar || audioChunks.length === 0) { reset(); return; }

                const blob = new Blob(audioChunks, { type: 'audio/webm' });
                const fd = new FormData();
                fd.append('audio', blob, 'audio.webm');
                fd.append('duracao', duracao);

                try {
                    const resp = await fetch(`${base}/mensagens/${contatoId}/audio`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': UniLuanda.csrf(), 'Accept': 'application/json' },
                        body: fd,
                    });
                    const data = await resp.json();
                    if (data.success) { adicionarAudio(data.mensagem); UniLuanda.mostrarToast('Áudio enviado!'); }
                    else { alert(data.message || 'Erro ao enviar áudio.'); }
                } catch { alert('Erro ao enviar áudio.'); }
                reset();
            };
            mediaRecorder.stop();
            gravando = false;
        }

        function reset() {
            clearInterval(timer);
            timer = null; audioChunks = []; mediaRecorder = null; stream = null; gravando = false;
            btnGravar.classList.remove('recording');
            btnGravar.innerHTML = '<i class="fas fa-microphone"></i>';
            form?.classList.remove('d-none');
            ui?.classList.add('d-none');
            if (timeEl) timeEl.textContent = '00:00';
            if (input) { input.required = true; input.focus(); }
        }

        function adicionarAudio(msg) {
            const c = document.getElementById('chatContainer');
            if (!c) return;
            c.querySelector('.text-center.py-5')?.remove();
            c.insertAdjacentHTML('beforeend', `
                <div class="chat-row chat-row-sent" id="mensagem-${msg.id}">
                    <div class="chat-bubble chat-bubble-sent">
                        <div class="chat-content">
                            <div class="chat-audio"><audio controls preload="metadata" src="${msg.audio_url}"></audio></div>
                        </div>
                        <div class="chat-meta">
                            <span class="chat-time">${msg.created_at}${msg.audio_duracao ? '· ' + fmt(msg.audio_duracao) : ''}</span>
                            <div class="chat-actions">
                                <button type="button" class="chat-action-btn chat-action-danger" onclick="confirmarEliminar(${msg.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>`);
            c.scrollTop = c.scrollHeight;
        }

        function fmt(seg) {
            const m = String(Math.floor(seg / 60)).padStart(2, '0');
            const s = String(seg % 60).padStart(2, '0');
            return `${m}:${s}`;
        }
    })();

    (function enviarFicheiros() {
        const btnAnexo = document.getElementById('btnAnexo');
        const input = document.getElementById('inputFicheiro');
        if (!btnAnexo || !input) return;

        input.accept = '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.7z,.jpg,.jpeg,.png,.gif,.webp,.mp3,.wav,.mp4,.mov';

        btnAnexo.addEventListener('click', () => input.click());

        input.addEventListener('change', async function () {
            const file = this.files[0];
            if (!file) return;
            if (file.size > 50 * 1024 * 1024) { alert('O ficheiro é demasiado grande. Máximo: 50 MB.'); input.value = ''; return; }

            btnAnexo.disabled = true;
            btnAnexo.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            const fd = new FormData();
            fd.append('ficheiro', file);
            try {
                const resp = await fetch(`${base}/mensagens/${contatoId}/ficheiro`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': UniLuanda.csrf(), 'Accept': 'application/json' },
                    body: fd,
                });
                const data = await resp.json();
                if (data.success) { adicionarFicheiro(data.mensagem); UniLuanda.mostrarToast('Ficheiro enviado!'); }
                else { alert(data.message || 'Erro ao enviar ficheiro.'); }
            } catch { alert('Erro ao enviar ficheiro.'); }
            finally {
                btnAnexo.disabled = false;
                btnAnexo.innerHTML = '<i class="fas fa-paperclip"></i>';
                input.value = '';
            }
        });

        function adicionarFicheiro(msg) {
            const c = document.getElementById('chatContainer');
            if (!c) return;
            c.querySelector('.text-center.py-5')?.remove();
            c.insertAdjacentHTML('beforeend', `
                <div class="chat-row chat-row-sent" id="mensagem-${msg.id}">
                    <div class="chat-bubble chat-bubble-sent">
                        <div class="chat-content">
                            <a href="${msg.ficheiro_url}" download="${msg.ficheiro_nome}" class="chat-ficheiro chat-ficheiro-sent" target="_blank">
                                <div class="chat-ficheiro-icone"><i class="fas ${msg.ficheiro_icone}"></i></div>
                                <div class="chat-ficheiro-info">
                                    <span class="chat-ficheiro-nome">${msg.ficheiro_nome}</span>
                                    <span class="chat-ficheiro-tamanho">${msg.ficheiro_tamanho}</span>
                                </div>
                                <div class="chat-ficheiro-download"><i class="fas fa-download"></i></div>
                            </a>
                        </div>
                        <div class="chat-meta">
                            <span class="chat-time">${msg.created_at}</span>
                            <div class="chat-actions">
                                <button type="button" class="chat-action-btn chat-action-danger" onclick="confirmarEliminar(${msg.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>`);
            c.scrollTop = c.scrollHeight;
        }
    })();

    document.addEventListener('DOMContentLoaded', () => {
        const c = document.getElementById('chatContainer');
        if (c) c.scrollTop = c.scrollHeight;
        const input = document.getElementById('inputMensagem');
        if (input) {
            input.focus();
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    document.getElementById('formMensagem')?.submit();
                }
            });
        }
    });
})();


/* ================================================================
   11. CHATBOT
================================================================ */
(function () {
    const admin = document.getElementById('chat-form');
    const egresso = document.getElementById('chatbot-form-egresso');
    if (!admin && !egresso) return;

    const base = admin ? '/admin/chatbot' : '/egresso/chatbot';
    const containerId = admin ? 'chat-messages' : 'chatbot-messages';
    const inputId = admin ? 'chat-input' : 'chatbot-input';
    const btnId = admin ? 'send-btn' : 'chatbot-send';

    const container = document.getElementById(containerId);
    const form = admin || egresso;
    const input = document.getElementById(inputId);
    const sendBtn = document.getElementById(btnId);
    if (!container || !form || !input) return;

    container.scrollTop = container.scrollHeight;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const mensagem = input.value.trim();
        if (!mensagem) return;
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        addMessage(mensagem, 'sent');

        fetch(`${base}/enviar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UniLuanda.csrf() },
            body: JSON.stringify({ mensagem }),
        })
        .then(r => r.json())
        .then(data => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            input.value = '';
            input.focus();
            if (data.success) {
                if (data.resposta_html) setTimeout(() => addMessage(data.resposta_html, 'received'), 500);
            } else {
                addMessage('❌ Erro ao enviar mensagem. Tente novamente.', 'received');
            }
        })
        .catch(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            addMessage('❌ Erro de conexão. Tente novamente.', 'received');
        });
    });

    document.querySelectorAll('.quick-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            input.value = this.dataset.msg;
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        });
    });

    function addMessage(mensagem, tipo) {
        if (mensagem.includes('<div')) {
            const d = document.createElement('div');
            d.innerHTML = mensagem;
            container.appendChild(d.firstElementChild);
        } else {
            const div = document.createElement('div');
            div.className = `message message-${tipo} mb-3`;
            const hora = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            if (tipo === 'sent') {
                div.innerHTML = `<div class="d-flex flex-row-reverse align-items-end"><div class="bg-primary text-white rounded-4 px-3 py-2 ms-2" style="max-width: 75%;">${mensagem}<small class="text-white-50 d-block text-end" style="font-size: 10px;">${hora}</small></div><div class="avatar-circle bg-primary bg-opacity-10"><i class="fas fa-user text-primary"></i></div></div>`;
            } else {
                div.innerHTML = `<div class="d-flex align-items-end"><div class="avatar-circle bg-info bg-opacity-10"><i class="fas fa-robot text-info"></i></div><div class="bg-light rounded-4 px-3 py-2 ms-2" style="max-width: 75%;">${mensagem}<small class="text-muted d-block" style="font-size: 10px;">${hora}</small></div></div>`;
            }
            container.appendChild(div);
        }
        container.scrollTop = container.scrollHeight;
    }

    if (!admin) {
        setInterval(() => {
            fetch(`${base}/verificar`, { headers: { 'X-CSRF-TOKEN': UniLuanda.csrf() } })
                .then(r => r.json())
                .then(data => {
                    if (data.total > 0) {
                        const badge = document.getElementById('chatbot-badge');
                        if (badge) { badge.textContent = data.total; badge.style.display = 'block'; }
                    }
                })
                .catch(() => {});
        }, 5000);
    }
})();


/* ================================================================
   12. FORMULÁRIO DE REGISTO
================================================================ */
(function () {
    const tipoSelect = document.getElementById('tipo');
    if (!tipoSelect) return;

    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const registerForm = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitButton');
    const btnText = document.getElementById('buttonText');
    const groupAdminCode = document.getElementById('group_admin_code');
    const groupEmpresaFields = document.getElementById('group_empresa_fields');
    const groupEgressoDivider = document.getElementById('group_egresso_divider');
    const groupDataNascimento = document.getElementById('group_data_nascimento');
    const groupGenero = document.getElementById('group_genero');
    const groupNumeroProcesso = document.getElementById('group_numero_processo');
    const groupCurso = document.getElementById('group_curso');
    const groupAnoFormatura = document.getElementById('group_ano_formatura');
    const groupUnidadeAdmin = document.getElementById('group_unidade_admin');
    const groupUnidadeAdminLabel = document.getElementById('group_unidade_admin_label');
    const adminCodeInput = document.getElementById('admin_code');
    const numeroProcessoInput = document.getElementById('numero_processo');
    const nomeInput = document.getElementById('nome_completo');
    const nomeRequiredStar = document.getElementById('nome_required_star');
    const nomeHelp = document.getElementById('nome_help');
    const groupNomeCompleto = document.getElementById('group_nome_completo');
    const groupFoto = document.getElementById('group_foto');
    const validationStatus = document.getElementById('validationStatus');
    const processoHelp = document.getElementById('processo_help');
    const processoRequiredStar = document.getElementById('processo_required_star');
    const cursoRequiredStar = document.getElementById('curso_required_star');
    const cursoHelp = document.getElementById('curso_help');
    const cursoSelect = document.getElementById('curso_id');
    let timeoutId = null;

    function updateFormFields() {
        const val = tipoSelect.value;
        groupAdminCode?.classList.add('d-none');
        if (groupEmpresaFields) groupEmpresaFields.style.display = 'none';
        if (groupEgressoDivider) groupEgressoDivider.style.display = 'none';
        if (groupDataNascimento) groupDataNascimento.style.display = 'none';
        if (groupGenero) groupGenero.style.display = 'none';
        if (groupNumeroProcesso) groupNumeroProcesso.style.display = 'none';
        if (groupCurso) groupCurso.style.display = 'none';
        if (groupAnoFormatura) groupAnoFormatura.style.display = 'none';
        if (groupUnidadeAdmin) groupUnidadeAdmin.style.display = 'none';
        if (groupUnidadeAdminLabel) groupUnidadeAdminLabel.style.display = 'none';

        adminCodeInput?.removeAttribute('required');
        numeroProcessoInput?.removeAttribute('required');
        cursoSelect?.removeAttribute('required');
        document.getElementById('empresa_nome')?.removeAttribute('required');
        document.getElementById('empresa_sector')?.removeAttribute('required');

        if (val === 'admin') {
            groupAdminCode?.classList.remove('d-none');
            adminCodeInput?.setAttribute('required', 'required');
            if (groupUnidadeAdmin) groupUnidadeAdmin.style.display = 'block';
            if (groupUnidadeAdminLabel) groupUnidadeAdminLabel.style.display = 'block';
            if (nomeRequiredStar) nomeRequiredStar.style.display = 'inline';
            if (nomeHelp) nomeHelp.textContent = 'Nome completo do administrador';
            if (groupNomeCompleto) groupNomeCompleto.style.display = 'block';
            if (groupFoto) groupFoto.style.display = 'block';
            if (numeroProcessoInput) numeroProcessoInput.value = '';
            if (validationStatus) { validationStatus.className = 'validation-status'; validationStatus.textContent = ''; }
        } else if (val === 'empresa') {
            if (groupEmpresaFields) groupEmpresaFields.style.display = 'block';
            document.getElementById('empresa_nome')?.setAttribute('required', 'required');
            document.getElementById('empresa_sector')?.setAttribute('required', 'required');
            if (nomeRequiredStar) nomeRequiredStar.style.display = 'none';
            if (nomeHelp) nomeHelp.textContent = 'Nome do responsável ou representante da empresa (opcional)';
            if (groupNomeCompleto) groupNomeCompleto.style.display = 'block';
            if (groupFoto) groupFoto.style.display = 'none';
            if (numeroProcessoInput) numeroProcessoInput.value = '';
            if (validationStatus) { validationStatus.className = 'validation-status'; validationStatus.textContent = ''; }
        } else {
            if (groupEgressoDivider) groupEgressoDivider.style.display = 'block';
            if (groupDataNascimento) groupDataNascimento.style.display = 'block';
            if (groupGenero) groupGenero.style.display = 'block';
            if (groupNumeroProcesso) groupNumeroProcesso.style.display = 'block';
            if (nomeRequiredStar) nomeRequiredStar.style.display = 'inline';
            if (nomeHelp) nomeHelp.textContent = 'Digite o seu nome completo';
            if (groupNomeCompleto) groupNomeCompleto.style.display = 'block';
            if (groupFoto) groupFoto.style.display = 'block';
            numeroProcessoInput?.setAttribute('required', 'required');
            mostrarCamposPendentes(false);
        }
    }

    function mostrarCamposPendentes(mostrar) {
        const display = mostrar ? 'block' : 'none';
        if (groupCurso) groupCurso.style.display = display;
        if (groupAnoFormatura) groupAnoFormatura.style.display = display;
        if (mostrar) {
            cursoSelect?.setAttribute('required', 'required');
            if (processoHelp) { processoHelp.textContent = 'Número não encontrado. Preencha os campos abaixo para validação manual.'; processoHelp.style.color = '#856404'; }
            if (processoRequiredStar) processoRequiredStar.style.display = 'none';
            if (cursoRequiredStar) cursoRequiredStar.style.display = 'inline';
            if (cursoHelp) cursoHelp.textContent = 'Selecione o curso em que se formou (obrigatório para validação manual)';
        } else {
            cursoSelect?.removeAttribute('required');
            if (processoHelp) { processoHelp.textContent = 'Digite o número do seu processo académico'; processoHelp.style.color = ''; }
            if (processoRequiredStar) processoRequiredStar.style.display = 'inline';
            if (cursoRequiredStar) cursoRequiredStar.style.display = 'none';
            if (cursoHelp) cursoHelp.textContent = 'Selecione o curso em que se formou';
        }
    }

    tipoSelect.addEventListener('change', function () {
        updateFormFields();
        if (validationStatus) { validationStatus.className = 'validation-status'; validationStatus.textContent = ''; }
        mostrarCamposPendentes(false);
    });

    updateFormFields();

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const isPass = passwordInput.type === 'password';
            passwordInput.type = isPass ? 'text' : 'password';
            togglePassword.setAttribute('aria-label', isPass ? 'Ocultar senha' : 'Mostrar senha');
        });
    }

    if (numeroProcessoInput) {
        numeroProcessoInput.addEventListener('input', function () {
            if (tipoSelect.value !== 'egresso') return;
            const numero = this.value.trim();
            if (timeoutId) clearTimeout(timeoutId);
            if (numero.length === 0) {
                if (validationStatus) { validationStatus.className = 'validation-status'; validationStatus.textContent = ''; }
                mostrarCamposPendentes(false);
                this.setAttribute('required', 'required');
                if (processoRequiredStar) processoRequiredStar.style.display = 'inline';
                return;
            }
            if (validationStatus) { validationStatus.className = 'validation-status loading'; validationStatus.innerHTML = 'Verificando número de processo...'; }

            timeoutId = setTimeout(() => {
                fetch('/verificar-processo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': UniLuanda.csrf() },
                    body: JSON.stringify({ numero_processo: numero }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (validationStatus) {
                            validationStatus.className = 'validation-status valid';
                            validationStatus.innerHTML = `${data.message}${data.egresso?.nome ? `<br><small>Nome: ${data.egresso.nome}</small>` : ''}${data.egresso?.curso ? `<br><small>Curso: ${data.egresso.curso}</small>` : ''}${data.egresso?.unidade ? `<br><small>Unidade: ${data.egresso.unidade}</small>` : ''}`;
                        }
                        if (data.egresso?.nome && nomeInput) nomeInput.value = data.egresso.nome;
                        if (data.egresso?.email) { const emailInput = document.getElementById('email'); if (emailInput) emailInput.value = data.egresso.email; }
                        mostrarCamposPendentes(false);
                        numeroProcessoInput.setAttribute('required', 'required');
                        if (processoRequiredStar) processoRequiredStar.style.display = 'inline';
                    } else {
                        if (validationStatus) {
                            validationStatus.className = 'validation-status pending-info';
                            validationStatus.innerHTML = `${data.message}<br><small style="display:block;margin-top:4px;">Seu cadastro será submetido para <strong>validação manual</strong>. Preencha os campos adicionais abaixo.</small>`;
                        }
                        mostrarCamposPendentes(true);
                        numeroProcessoInput.removeAttribute('required');
                        if (processoRequiredStar) processoRequiredStar.style.display = 'none';
                    }
                })
                .catch(() => {
                    if (validationStatus) { validationStatus.className = 'validation-status invalid'; validationStatus.innerHTML = 'Erro ao verificar o número de processo.'; }
                });
            }, 500);
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const tipo = tipoSelect.value;
            if (tipo === 'admin') { submitBtn.disabled = true; btnText.innerHTML = '<span class="loader"></span> Processando...'; return; }
            if (tipo === 'empresa') {
                const nome = document.getElementById('empresa_nome')?.value.trim();
                const sector = document.getElementById('empresa_sector')?.value;
                if (!nome || !sector) {
                    e.preventDefault();
                    submitBtn.disabled = false;
                    btnText.innerHTML = 'Criar Conta →';
                    alert('Por favor, preencha o Nome da Empresa e o Sector.');
                    return false;
                }
                submitBtn.disabled = true;
                btnText.innerHTML = '<span class="loader"></span> Processando...';
                return;
            }
            if (tipo === 'egresso') {
                const numero = numeroProcessoInput?.value.trim();
                if (!numero) {
                    if (groupCurso?.style.display === 'block') {
                        if (!cursoSelect?.value) {
                            e.preventDefault();
                            submitBtn.disabled = false;
                            btnText.innerHTML = 'Criar Conta →';
                            validationStatus?.classList.add('invalid');
                            if (validationStatus) validationStatus.innerHTML = 'Selecione o curso para continuar com a validação manual.';
                            return false;
                        }
                    } else {
                        e.preventDefault();
                        submitBtn.disabled = false;
                        btnText.innerHTML = 'Criar Conta →';
                        validationStatus?.classList.add('invalid');
                        if (validationStatus) validationStatus.innerHTML = 'Digite o número de processo.';
                        return false;
                    }
                }
            }
            submitBtn.disabled = true;
            btnText.innerHTML = '<span class="loader"></span> Processando...';
        });
    }
})();


/* ================================================================
   13. LOGIN — RECUPERAÇÃO DE PALAVRA-PASSE
================================================================ */
(function () {
    const forgotModal = document.getElementById('forgotModal');
    if (!forgotModal) return;

    const forgotBtn        = document.getElementById('forgotPasswordBtn');
    const modalClose       = document.getElementById('modalClose');
    const resetEmail       = document.getElementById('resetEmail');
    const resetBtn         = document.getElementById('resetPasswordBtn');
    const resetText        = document.getElementById('resetButtonText');
    const modalForm        = document.getElementById('modalForm');
    const modalSuccess     = document.getElementById('modalSuccess');
    const modalError       = document.getElementById('modalError');
    const modalErrorMsg    = document.getElementById('modalErrorMessage');
    const modalSuccessBtn  = document.getElementById('modalSuccessBtn');

    const TEXTO_BOTAO = 'Enviar Ligação de Recuperação';

    forgotBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        forgotModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        resetEmail?.focus();
        modalError?.classList.remove('active');
        if (modalForm) modalForm.style.display = 'block';
        modalSuccess?.classList.remove('active');
        if (resetText) resetText.textContent = TEXTO_BOTAO;
        if (resetBtn) resetBtn.disabled = false;
        if (resetEmail) resetEmail.value = '';
    });

    modalClose?.addEventListener('click', closeModal);
    forgotModal.addEventListener('click', e => { if (e.target === forgotModal) closeModal(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && forgotModal.classList.contains('active')) closeModal();
    });

    function closeModal() {
        forgotModal.classList.remove('active');
        document.body.style.overflow = '';
        if (resetEmail) resetEmail.value = '';
        modalError?.classList.remove('active');
        if (modalForm) modalForm.style.display = 'block';
        modalSuccess?.classList.remove('active');
        if (resetText) resetText.textContent = TEXTO_BOTAO;
        if (resetBtn) resetBtn.disabled = false;
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showError(msg) {
        if (modalErrorMsg) modalErrorMsg.textContent = msg;
        modalError?.classList.add('active');
        setTimeout(() => modalError?.classList.remove('active'), 5000);
    }

    resetBtn?.addEventListener('click', function () {
        const email = resetEmail?.value.trim();

        if (!email) {
            showError('Por favor, introduz o teu endereço de email.');
            return;
        }
        if (!isValidEmail(email)) {
            showError('Por favor, introduz um endereço de email válido.');
            return;
        }

        resetBtn.disabled = true;
        if (resetText) resetText.innerHTML = `<span class="loader"></span> A enviar...`;
        modalError?.classList.remove('active');

        fetch('/esqueci-palavra-passe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': UniLuanda.csrf(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ email }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (modalForm) modalForm.style.display = 'none';
                modalSuccess?.classList.add('active');
                if (resetBtn) resetBtn.disabled = false;
                if (resetText) resetText.textContent = TEXTO_BOTAO;
                UniLuanda.mostrarToast('✅ ' + data.message, 'success');
            } else {
                showError(data.message || 'Erro ao enviar. Tenta novamente.');
                if (resetBtn) resetBtn.disabled = false;
                if (resetText) resetText.textContent = TEXTO_BOTAO;
            }
        })
        .catch(() => {
            showError('Erro de conexão. Tenta novamente.');
            if (resetBtn) resetBtn.disabled = false;
            if (resetText) resetText.textContent = TEXTO_BOTAO;
        });
    });

    modalSuccessBtn?.addEventListener('click', closeModal);
    resetEmail?.addEventListener('keydown', e => {
        if (e.key === 'Enter') resetBtn?.click();
    });
})();

/* ================================================================
   14. GEOLOCALIZAÇÃO
================================================================ */
(function () {
    const btnLoc = document.getElementById('btnLocalizacao');
    const btnBuscar = document.getElementById('btnBuscarEndereco');
    if (!btnLoc && !btnBuscar) return;

    window.limparCoordenadas = function () {
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        document.getElementById('coordsInfo')?.classList.remove('active', 'd-none');
        document.getElementById('mapPreview')?.classList.remove('has-coords');
        const preview = document.getElementById('mapPreview');
        if (preview) preview.innerHTML = `<div class="map-placeholder"><div class="map-placeholder-icon"><i class="fas fa-map-marked-alt"></i></div><h6 class="fw-bold mb-1">Sem localização</h6><p class="text-muted small mb-0">Preencha as coordenadas ou use a localização automática</p></div>`;
    };

    function atualizarMapa(lat, lng) {
        const preview = document.getElementById('mapPreview');
        if (!preview) return;
        preview.classList.add('has-coords');
        preview.innerHTML = `<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=${lng - 0.05}%2C${lat - 0.05}%2C${lng + 0.05}%2C${lat + 0.05}&amp;layer=mapnik&amp;marker=${lat}%2C${lng}" allowfullscreen></iframe><div class="map-overlay"><i class="fas fa-map-pin text-primary"></i> ${lat.toFixed(4)}, ${lng.toFixed(4)}</div>`;
    }

    function buscarEnderecoPorCoordenadas(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
            .then(r => r.json())
            .then(data => {
                if (!data?.address) return;
                const a = data.address;
                if (a.country) document.getElementById('pais').value = a.country;
                if (a.state || a.province) document.getElementById('provincia').value = a.state || a.province;
                if (a.city || a.town || a.village) document.getElementById('cidade').value = a.city || a.town || a.village;
                if (a.road) document.getElementById('endereco').value = a.road + (a.house_number ? ', ' + a.house_number : '');
            })
            .catch(() => {});
    }

    function atualizarCoordsInfo(lat, lng) {
        const latEl = document.getElementById('coordsLat');
        const lngEl = document.getElementById('coordsLng');
        const info = document.getElementById('coordsInfo');
        if (latEl) latEl.textContent = typeof lat === 'number' ? lat.toFixed(6) : lat;
        if (lngEl) lngEl.textContent = typeof lng === 'number' ? lng.toFixed(6) : lng;
        if (info) info.classList.add('active');
    }

    btnLoc?.addEventListener('click', function () {
        if (!navigator.geolocation) { alert('Seu navegador não suporta geolocalização.'); return; }
        const spinner = document.getElementById('spinnerLocalizacao');
        const icon = document.getElementById('iconLocalizacao');
        const text = document.getElementById('textLocalizacao');
        spinner?.classList.add('active');
        if (icon) icon.style.display = 'none';
        if (text) text.textContent = 'A obter localização...';
        btnLoc.disabled = true;

        navigator.geolocation.getCurrentPosition(
            position => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
                atualizarCoordsInfo(lat, lng);
                atualizarMapa(lat, lng);
                buscarEnderecoPorCoordenadas(lat, lng);
                spinner?.classList.remove('active');
                if (icon) icon.style.display = 'inline';
                if (text) text.textContent = 'Localização obtida';
                btnLoc.disabled = false;
                setTimeout(() => { if (text) text.textContent = 'Usar Localização Atual'; }, 3000);
            },
            error => {
                let msg = 'Não foi possível obter a localização. ';
                switch (error.code) {
                    case error.PERMISSION_DENIED: msg += 'Permissão negada.'; break;
                    case error.POSITION_UNAVAILABLE: msg += 'Informação indisponível.'; break;
                    case error.TIMEOUT: msg += 'Tempo limite excedido.'; break;
                    default: msg += 'Erro desconhecido.';
                }
                alert(msg);
                spinner?.classList.remove('active');
                if (icon) icon.style.display = 'inline';
                if (text) text.textContent = 'Usar Localização Atual';
                btnLoc.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });

    btnBuscar?.addEventListener('click', function () {
        const pais = document.getElementById('pais').value;
        const provincia = document.getElementById('provincia').value;
        const cidade = document.getElementById('cidade').value;
        const endereco = document.getElementById('endereco').value;
        let q = '';
        if (endereco) q += endereco + ', ';
        if (cidade) q += cidade + ', ';
        if (provincia) q += provincia + ', ';
        if (pais) q += pais;
        if (!q) { alert('Preencha pelo menos um campo (País, Cidade ou Endereço).'); return; }

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1`)
            .then(r => r.json())
            .then(data => {
                if (data?.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                    atualizarCoordsInfo(lat, lng);
                    atualizarMapa(lat, lng);
                } else { alert('Endereço não encontrado.'); }
            })
            .catch(() => alert('Erro ao buscar endereço.'));
    });

    document.getElementById('latitude')?.addEventListener('input', atualizarCoordsManuais);
    document.getElementById('longitude')?.addEventListener('input', atualizarCoordsManuais);

    function atualizarCoordsManuais() {
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        if (lat && lng) { atualizarCoordsInfo(lat, lng); atualizarMapa(parseFloat(lat), parseFloat(lng)); }
    }
})();


/* ================================================================
   15. FILTROS DINÂMICOS — UNIDADE → CURSO
================================================================ */
(function () {
    const selectUnidade = document.getElementById('unidade_id');
    const selectCurso = document.getElementById('curso_id');
    const aviso = document.getElementById('aviso-curso');
    if (!selectUnidade || !selectCurso) return;

    const cursoAtual = selectCurso.dataset.cursoAtual || '';
    const todasOpcoes = Array.from(selectCurso.options).slice(1);

    function filtrar() {
        const unidadeId = selectUnidade.value;
        if (!unidadeId) {
            if (aviso) { aviso.textContent = ''; aviso.className = 'text-muted d-block mt-1'; }
            return;
        }
        selectCurso.innerHTML = '<option value="">Selecione o curso</option>';
        const filtrados = todasOpcoes.filter(opt => opt.dataset.unidade === unidadeId);
        if (filtrados.length === 0) {
            if (aviso) { aviso.textContent = '❌ Esta unidade não tem cursos associados'; aviso.className = 'text-danger d-block mt-1'; }
            return;
        }
        filtrados.forEach(opt => {
            const nova = opt.cloneNode(true);
            if (nova.value === cursoAtual) nova.selected = true;
            selectCurso.appendChild(nova);
        });
        if (aviso) { aviso.textContent = filtrados.length + ' curso(s) encontrado(s) nesta unidade'; aviso.className = 'text-success d-block mt-1'; }
    }

    selectUnidade.addEventListener('change', filtrar);
    selectCurso.addEventListener('change', () => { if (selectCurso.value && aviso) aviso.textContent = ''; });
})();


/* ================================================================
   16. AVALIAÇÃO POR ESTRELAS
================================================================ */
document.addEventListener('DOMContentLoaded', function () {
    const items = document.querySelectorAll('.rating-item');
    if (!items.length) return;
    const label = document.getElementById('ratingLabel');
    const labels = { 1: 'Muito insatisfeito', 2: 'Insatisfeito', 3: 'Neutro', 4: 'Satisfeito', 5: 'Muito satisfeito' };

    function pintar(valor) {
        items.forEach(it => { const v = parseInt(it.dataset.value); it.classList.toggle('active', v <= valor); });
        if (label) {
            if (valor > 0) { label.textContent = labels[valor] || ''; label.classList.add('active'); }
            else { label.textContent = 'Selecione uma avaliação'; label.classList.remove('active'); }
        }
    }

    const checked = document.querySelector('.rating-input:checked');
    if (checked) pintar(parseInt(checked.value));

    items.forEach(item => {
        item.addEventListener('click', function () {
            const v = parseInt(this.dataset.value);
            const input = this.querySelector('.rating-input');
            if (input) input.checked = true;
            pintar(v);
        });
        item.addEventListener('mouseenter', function () { pintar(parseInt(this.dataset.value)); });
    });

    document.querySelector('.rating-wrapper')?.addEventListener('mouseleave', () => {
        const cur = document.querySelector('.rating-input:checked');
        pintar(cur ? parseInt(cur.value) : 0);
    });
});


/* ================================================================
   17. CONTADORES DE CARACTERES
================================================================ */
document.addEventListener('DOMContentLoaded', function () {
    const config = [
        { input: 'mensagem', counter: 'charCounter', max: 2000 },
        { input: 'descricao', counter: 'charCounter', max: 500 },
    ];
    config.forEach(({ input, counter, max }) => {
        const i = document.getElementById(input);
        const c = document.getElementById(counter);
        if (!i || !c) return;
        const update = () => { c.textContent = i.value.length + ' / ' + max; c.classList.toggle('text-danger', i.value.length > max * 0.9); };
        update();
        i.addEventListener('input', update);
    });
});


/* ================================================================
   18. SCROLL ANIMATIONS + COUNTERS
================================================================ */
(function () {
    if (!document.querySelector('.animate-on-scroll')) return;

    function animarContadores(container) {
        container.querySelectorAll('.counter-number').forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            if (isNaN(target) || counter.dataset.animated === '1') return;
            counter.dataset.animated = '1';
            let atual = 0;
            const passo = Math.max(1, Math.ceil(target / 60));
            const interval = setInterval(() => {
                atual += passo;
                if (atual >= target) { atual = target; clearInterval(interval); }
                const span = counter.querySelector('span');
                if (span) counter.childNodes[0].nodeValue = atual;
                else counter.textContent = atual;
            }, 2000 / 60);
        });
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('visible');
            if (entry.target.querySelector('.counter-number')) animarContadores(entry.target);
            entry.target.querySelectorAll('.progress-animated').forEach(bar => {
                const p = bar.getAttribute('data-progress');
                setTimeout(() => { bar.style.width = p + '%'; }, 300);
            });
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
})();


/* ================================================================
   19. MODAL DE EGRESSOS PÚBLICOS
================================================================ */
(function () {
    const modalEl = document.getElementById('modalEgressosPublicos');
    if (!modalEl) return;

    const body = document.body;
    const ROTA_PESQUISA = body.dataset.rotaPesquisaEgressos;
    const ROTA_FILTROS = body.dataset.rotaFiltrosEgressos;
    const ROTA_LOGIN = body.dataset.rotaLogin;
    if (!ROTA_PESQUISA || !ROTA_FILTROS) return;

    let loaded = false, filtrosLoaded = false;

    window.abrirModalEgressos = function () {
        new bootstrap.Modal(modalEl).show();
        if (!filtrosLoaded) { carregarFiltros(); filtrosLoaded = true; }
        if (!loaded) { carregarLista(); loaded = true; }
    };

    function carregarFiltros() {
        fetch(ROTA_FILTROS, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const selCurso = document.getElementById('filtroCurso');
                const selUnidade = document.getElementById('filtroUnidade');
                data.cursos?.forEach(c => selCurso?.insertAdjacentHTML('beforeend', `<option value="${c.id}">${c.nome}</option>`));
                data.unidades?.forEach(u => selUnidade?.insertAdjacentHTML('beforeend', `<option value="${u.id}">${u.nome}</option>`));
            })
            .catch(() => {});
    }

    function carregarLista() {
        const loading = document.getElementById('egressosLoading');
        const lista = document.getElementById('egressosLista');
        const vazio = document.getElementById('egressosVazio');
        const total = document.getElementById('modalTotalEgressos');
        if (!loading || !lista || !vazio) return;

        const termo = document.getElementById('filtroTexto')?.value.trim() || '';
        const cursoId = document.getElementById('filtroCurso')?.value || '';
        const unidadeId = document.getElementById('filtroUnidade')?.value || '';
        const ordem = document.getElementById('filtroOrdem')?.value || 'az';
        const params = new URLSearchParams();
        if (termo) params.append('q', termo);
        if (cursoId) params.append('curso_id', cursoId);
        if (unidadeId) params.append('unidade_id', unidadeId);
        if (ordem) params.append('ordenacao', ordem);

        loading.style.display = 'block';
        lista.style.display = 'none';
        vazio.style.display = 'none';

        fetch(`${ROTA_PESQUISA}?${params}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                loading.style.display = 'none';
                if (total) total.textContent = data.total === 1 ? '1 egresso encontrado' : data.total + ' egressos encontrados';
                if (!data.items || data.items.length === 0) {
                    vazio.style.display = 'block';
                    vazio.innerHTML = `<div class="mb-3" style="font-size:3rem;color:#cbd5e1;"><i class="fas fa-search"></i></div><h6 class="fw-bold mb-1">Sem resultados</h6><p class="text-muted small mb-0">${termo ? `Nenhum egresso corresponde a "<strong>${termo}</strong>".` : 'Nenhum egresso encontrado com os filtros aplicados.'}</p>`;
                    return;
                }
                lista.innerHTML = data.items.map(item => {
                    const avatar = item.foto_url ? `<img src="${item.foto_url}" alt="${item.nome}" class="egresso-modal-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><div class="egresso-modal-avatar-fallback" style="display:none;">${item.iniciais}</div>` : `<div class="egresso-modal-avatar-fallback">${item.iniciais}</div>`;
                    return `<div class="egresso-modal-item"><div class="d-flex align-items-center gap-3">${avatar}<div class="flex-grow-1 min-width-0"><div class="fw-bold text-dark text-truncate">${item.nome}</div><div class="text-muted small text-truncate"><i class="fas fa-graduation-cap me-1"></i>${item.curso}</div><div class="text-primary small text-truncate"><i class="fas fa-university me-1"></i>${item.unidade}</div></div><a href="${ROTA_LOGIN}" class="btn btn-sm btn-outline-primary rounded-pill" title="Entrar para ver perfil"><i class="fas fa-lock"></i></a></div></div>`;
                }).join('');
                lista.style.display = 'block';
            })
            .catch(() => {
                loading.style.display = 'none';
                vazio.style.display = 'block';
                vazio.innerHTML = `<div class="mb-3" style="font-size:3rem;color:#dc2626;"><i class="fas fa-exclamation-triangle"></i></div><h6 class="fw-bold mb-1">Erro ao carregar</h6><p class="text-muted small mb-0">Tenta novamente.</p>`;
            });
    }

    window.limparFiltrosEgressos = function () {
        const t = document.getElementById('filtroTexto');
        const c = document.getElementById('filtroCurso');
        const u = document.getElementById('filtroUnidade');
        const o = document.getElementById('filtroOrdem');
        if (t) t.value = '';
        if (c) c.value = '';
        if (u) u.value = '';
        if (o) o.value = 'az';
        carregarLista();
    };

    document.addEventListener('DOMContentLoaded', () => {
        const t = document.getElementById('filtroTexto');
        const c = document.getElementById('filtroCurso');
        const u = document.getElementById('filtroUnidade');
        const o = document.getElementById('filtroOrdem');
        t?.addEventListener('input', UniLuanda.debounce(() => carregarLista(), 350));
        [c, u, o].forEach(sel => sel?.addEventListener('change', carregarLista));
    });
})();


/* ================================================================
   20. NAVBAR — SCROLL / SPY / SMOOTH
================================================================ */
(function () {
    const nav = document.getElementById('navbar');
    if (!nav) return;

    window.addEventListener('scroll', function () { nav.classList.toggle('scrolled', window.scrollY > 50); });
    window.toggleMobileMenu = function () { document.getElementById('navLinks')?.classList.toggle('active'); };

    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
            const id = this.getAttribute('href');
            if (id === '#') return;
            const target = document.querySelector(id);
            if (!target) return;
            e.preventDefault();
            const offset = nav.offsetHeight;
            const pos = target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({ top: pos, behavior: 'smooth' });
            document.getElementById('navLinks')?.classList.remove('active');
            document.querySelectorAll('.nav-links a').forEach(l => l.classList.remove('active'));
            if (this.closest('.nav-links')) this.classList.add('active');
        });
    });

    const sections = document.querySelectorAll('section[id]');
    function atualizar() {
        const offset = nav.offsetHeight + 40;
        let atual = '';
        sections.forEach(s => { if (window.pageYOffset >= s.offsetTop - offset) atual = s.id; });
        document.querySelectorAll('.nav-links a').forEach(l => {
            l.classList.remove('active');
            if (l.getAttribute('href') === '#' + atual) l.classList.add('active');
        });
    }
    window.addEventListener('scroll', atualizar);
    atualizar();
})();


/* ================================================================
   21. CONFIRMAÇÕES
================================================================ */
function confirmarInscricao(form) {
    if (!confirm('Deseja inscrever-se neste evento?')) return false;
    const btn = form.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> A inscrever...'; }
    return true;
}

function confirmarCandidatura(form) {
    if (!confirm('Deseja realmente candidatar-se a esta oportunidade?')) return false;
    const btn = form.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> A enviar...'; }
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-cancelar-candidatura').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!confirm('Tem certeza que deseja cancelar esta candidatura?')) { e.preventDefault(); return; }
            const btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
        });
    });
    document.querySelectorAll('.form-cancelar-inscricao').forEach(form => {
        form.addEventListener('submit', function () {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> A cancelar...'; }
        });
    });
    document.querySelectorAll('.form-confirmar-presenca').forEach(form => {
        form.addEventListener('submit', function () {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> A confirmar...'; }
        });
    });
});


/* ================================================================
   22. SELECIONAR SERVIÇO
================================================================ */
function selecionarServico(servico, cardEl) {
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    cardEl?.classList.add('selected');
    const select = document.getElementById('servicoSelect');
    if (select) { select.value = servico; select.dispatchEvent(new Event('change', { bubbles: true })); }
}
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('servicoSelect');
    select?.addEventListener('change', function () { document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected')); });
});


/* ================================================================
   23. PESQUISA EM TEMPO REAL
================================================================ */
(function () {
    const input = document.getElementById('pesquisaEgressos');
    if (!input) return;
    let timer;

    function executar() {
        const termo = input.value.toLowerCase().trim();
        const itens = document.querySelectorAll('#listaEgressos .network-item');
        const sem = document.getElementById('semResultados');
        let visiveis = 0;
        itens.forEach(item => {
            const busca = (item.dataset.search || '').toLowerCase();
            if (!termo || busca.includes(termo)) { item.style.display = ''; visiveis++; }
            else { item.style.display = 'none'; }
        });
        if (sem) sem.classList.toggle('d-none', visiveis !== 0 || termo === '');
    }

    input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); executar(); } });
    input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(executar, 250); });
})();

window.limparPesquisa = function () {
    const input = document.getElementById('pesquisaEgressos');
    if (input) input.value = '';
    input?.focus();
    document.querySelectorAll('#listaEgressos .network-item').forEach(i => i.style.display = '');
    document.getElementById('semResultados')?.classList.add('d-none');
};


/* ================================================================
   24. FORÇAR MAIÚSCULAS
================================================================ */
(function () {
    const codigo = document.getElementById('codigo');
    codigo?.addEventListener('input', function () { this.value = this.value.toUpperCase(); });
})();


/* ================================================================
   25. MODAL APOIO APÓS ERRO/SUCESSO
================================================================ */
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    if (body.dataset.abrirModalApoio === '1') {
        const modalEl = document.getElementById('modalApoio');
        if (modalEl) new bootstrap.Modal(modalEl).show();
    }
});

/* ================================================================
   26. GRÁFICOS DO DASHBOARD (Chart.js)
================================================================ */
(function () {
    const canvasUnidades = document.getElementById('egressosUnidadeChart');
    if (canvasUnidades && typeof Chart !== 'undefined') {
        const dados = JSON.parse(canvasUnidades.dataset.unidades || '[]');
        const labels = dados.map(u => u.sigla || 'N/D');
        const valores = dados.map(u => u.total || 0);
        const cores = ['#c9a227', '#a67c00', '#16a34a', '#b45309', '#dc2626', '#0a0a0a', '#525252', '#8a8a8a'];
        new Chart(canvasUnidades, {
            type: 'bar',
            data: { labels, datasets: [{ label: 'Egressos', data: valores, backgroundColor: cores.map(c => c + 'DD'), borderColor: cores, borderWidth: 2, borderRadius: 8, maxBarThickness: 48 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(10, 10, 10, 0.92)', titleColor: '#fff', bodyColor: '#e8e6df', cornerRadius: 10, padding: 14, callbacks: { label: ctx => ctx.parsed.y + ' egressos' } } }, scales: { x: { grid: { display: false }, ticks: { font: { weight: '600', size: 11 }, color: '#525252' } }, y: { beginAtZero: true, grid: { color: 'rgba(10, 10, 10, 0.05)', drawBorder: false }, ticks: { font: { size: 11 }, color: '#8a8a8a', callback: v => v >= 1000 ? (v / 1000) + 'k' : v } } }, animation: { duration: 1200, easing: 'easeOutQuart' } },
        });
    }

    const canvasDistribuicao = document.getElementById('distribuicaoChart');
    if (canvasDistribuicao && typeof Chart !== 'undefined') {
        const stats = JSON.parse(canvasDistribuicao.dataset.stats || '{}');
        const ativos = stats.egressos_activos || 0;
        const inativos = stats.egressos_inativos || 0;
        const pendentes = stats.egressos_pendentes || 0;
        const semContacto = stats.egressos_lost_contact || 0;
        const total = stats.total_egressos || 0;
        new Chart(canvasDistribuicao, {
            type: 'doughnut',
            data: { labels: ['Ativos', 'Inativos', 'Pendentes', 'Sem Contacto'], datasets: [{ data: [ativos, inativos, pendentes, semContacto], backgroundColor: ['#c9a227', '#b45309', '#dc2626', '#525252'], borderWidth: 3, borderColor: '#ffffff', hoverOffset: 12 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(10, 10, 10, 0.92)', titleColor: '#fff', bodyColor: '#e8e6df', cornerRadius: 10, padding: 14, callbacks: { label: ctx => { const t = ctx.dataset.data.reduce((a, b) => a + b, 0); const p = t > 0 ? ((ctx.parsed / t) * 100).toFixed(1) : 0; return ctx.parsed + ' egressos (' + p + '%)'; } } } }, animation: { animateRotate: true, duration: 1500, easing: 'easeOutQuart' } },
            plugins: [{ id: 'centroTexto', afterDraw(chart) { const { width, height, ctx } = chart; ctx.save(); ctx.font = 'bold 28px -apple-system, system-ui, sans-serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.fillStyle = '#0a0a0a'; ctx.fillText(String(total), width / 2, height / 2 - 8); ctx.font = '12px -apple-system, system-ui, sans-serif'; ctx.fillStyle = '#8a8a8a'; ctx.fillText('Total', width / 2, height / 2 + 22); ctx.restore(); } }],
        });
    }
})();

/* ================================================================
   27. GRÁFICOS DA EMPRESA (Chart.js)
================================================================ */
(function () {
    const canvasStatus = document.getElementById('graficoStatus');
    if (canvasStatus && typeof Chart !== 'undefined') {
        let dados = {};
        try { dados = JSON.parse(canvasStatus.dataset.dados || '{}'); } catch (err) {}
        new Chart(canvasStatus, {
            type: 'doughnut',
            data: { labels: ['Pendente', 'Em Análise', 'Entrevista', 'Aprovado', 'Rejeitado'], datasets: [{ data: [dados.pendente || 0, dados.em_analise || 0, dados.entrevista || 0, dados.aprovado || 0, dados.rejeitado || 0], backgroundColor: ['#c9a227', '#0ea5e9', '#a67c00', '#16a34a', '#dc2626'], borderWidth: 3, borderColor: '#ffffff', hoverOffset: 10 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyle: 'circle', boxWidth: 10, boxHeight: 10, font: { size: 11, weight: '600' } } }, tooltip: { backgroundColor: 'rgba(10, 10, 10, 0.92)', padding: 12, cornerRadius: 8, callbacks: { label: ctx => { const total = ctx.dataset.data.reduce((a, b) => a + b, 0); const p = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0; return `${ctx.label}: ${ctx.parsed} (${p}%)`; } } } } },
        });
    }

    const canvasTipos = document.getElementById('graficoTipos');
    if (canvasTipos && typeof Chart !== 'undefined') {
        let dados = {};
        try { dados = JSON.parse(canvasTipos.dataset.dados || '{}'); } catch (err) {}
        new Chart(canvasTipos, {
            type: 'pie',
            data: { labels: ['Emprego', 'Estágio', 'Bolsa', 'Curso', 'Evento'], datasets: [{ data: [dados.emprego || 0, dados.estagio || 0, dados.bolsa || 0, dados.curso || 0, dados.evento || 0], backgroundColor: ['#c9a227', '#0ea5e9', '#16a34a', '#d97706', '#ec4899'], borderWidth: 3, borderColor: '#ffffff', hoverOffset: 12 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyle: 'circle', boxWidth: 10, boxHeight: 10, font: { size: 11, weight: '600' } } }, tooltip: { backgroundColor: 'rgba(10, 10, 10, 0.92)', padding: 12, cornerRadius: 8 } } },
        });
    }

    const canvasAprovacao = document.getElementById('graficoAprovacao');
    if (canvasAprovacao && typeof Chart !== 'undefined') {
        let dados = {};
        try { dados = JSON.parse(canvasAprovacao.dataset.dados || '{}'); } catch (err) {}
        const aprovadas = dados.aprovadas || 0;
        const total = dados.total || 0;
        const taxa = dados.taxa || 0;
        const restantes = Math.max(0, total - aprovadas);
        new Chart(canvasAprovacao, {
            type: 'doughnut',
            data: { labels: ['Aprovadas', 'Outras'], datasets: [{ data: [aprovadas, restantes], backgroundColor: ['#16a34a', '#e6e2d3'], borderWidth: 3, borderColor: '#ffffff', hoverOffset: 10 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyle: 'circle', boxWidth: 10, boxHeight: 10, font: { size: 11, weight: '600' } } }, tooltip: { backgroundColor: 'rgba(10, 10, 10, 0.92)', padding: 12, cornerRadius: 8 } } },
            plugins: [{ id: 'centroTexto', afterDraw(chart) { const { width, height, ctx } = chart; ctx.save(); ctx.font = 'bold 28px -apple-system, system-ui, sans-serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.fillStyle = '#0a0a0a'; ctx.fillText(String(taxa) + '%', width / 2, height / 2 - 8); ctx.font = '12px -apple-system, system-ui, sans-serif'; ctx.fillStyle = '#8a8a8a'; ctx.fillText('Aprovação', width / 2, height / 2 + 22); ctx.restore(); } }],
        });
    }
})();

/* ================================================================
   28. FORÇA DA SENHA
================================================================ */
(function () {
    const passwordInput = document.getElementById('password');
    const strengthWrapper = document.getElementById('passwordStrength');
    const strengthLabel = document.getElementById('passwordStrengthLabel');
    if (!passwordInput || !strengthWrapper) return;

    passwordInput.addEventListener('input', function () {
        const valor = this.value;
        if (valor.length === 0) {
            strengthWrapper.style.display = 'none';
            strengthLabel.textContent = '';
            strengthLabel.className = 'password-strength-label';
            return;
        }
        strengthWrapper.style.display = 'flex';
        let pontos = 0;
        if (valor.length >= 6) pontos++;
        if (valor.length >= 10) pontos++;
        if (/[A-Z]/.test(valor)) pontos++;
        if (/[0-9]/.test(valor)) pontos++;
        if (/[^A-Za-z0-9]/.test(valor)) pontos++;
        const barras = strengthWrapper.querySelectorAll('.password-strength-bar');
        const niveis = ['weak', 'weak', 'medium', 'medium', 'strong', 'strong'];
        const nivel = niveis[Math.min(pontos, 5)];
        const textos = { weak: 'Senha fraca', medium: 'Senha média', strong: 'Senha forte' };
        barras.forEach((barra, i) => {
            barra.className = 'password-strength-bar';
            if (i < pontos) barra.classList.add(nivel);
        });
        strengthLabel.textContent = textos[nivel];
        strengthLabel.className = 'password-strength-label ' + nivel;
    });
})();

/* ================================================================
   29. MODAIS — CORRIGIR EMPILHAMENTO
================================================================ */
(function () {
    function moverModaisParaBody() {
        document.querySelectorAll('.modal').forEach(function (modal) {
            if (modal.parentElement !== document.body) document.body.appendChild(modal);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', moverModaisParaBody);
    } else {
        moverModaisParaBody();
    }

    document.addEventListener('show.bs.modal', function (e) {
        const modal = e.target;
        if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);
        if (modal) modal.style.zIndex = '1065';
    });

    document.addEventListener('hidden.bs.modal', function () {
        document.querySelectorAll('.modal-backdrop').forEach(function (bd) { bd.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    });

    console.log('🎯 Modais movidos para o <body>');
})();


/* ================================================================
  PERFIL DO ADMIN
================================================================ */


 // ============================================================
    // PRÉ-VISUALIZAÇÃO DA FOTO
    // ============================================================
    document.getElementById('foto').addEventListener('change', function(e) {
        const preview = document.getElementById('fotoPreview');
        const placeholder = document.getElementById('fotoPreviewPlaceholder');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            if (placeholder) placeholder.style.display = 'flex';
        }
    });

    // ============================================================
    // TOGGLE SENHA (Mostrar/Ocultar)
    // ============================================================
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const iconId = inputId === 'password' ? 'password-icon' : 'password-confirm-icon';
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    // ============================================================
    // VALIDAÇÃO DA SENHA EM TEMPO REAL
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirmation');

        if (password && passwordConfirm) {
            passwordConfirm.addEventListener('input', function() {
                if (password.value && passwordConfirm.value) {
                    if (password.value === passwordConfirm.value) {
                        passwordConfirm.style.borderColor = '#28a745';
                        passwordConfirm.style.boxShadow = '0 0 0 4px rgba(40, 167, 69, 0.1)';
                    } else {
                        passwordConfirm.style.borderColor = '#dc3545';
                        passwordConfirm.style.boxShadow = '0 0 0 4px rgba(220, 53, 69, 0.1)';
                    }
                } else {
                    passwordConfirm.style.borderColor = '#e9ecef';
                    passwordConfirm.style.boxShadow = 'none';
                }
            });

            password.addEventListener('input', function() {
                if (passwordConfirm.value) {
                    if (password.value === passwordConfirm.value) {
                        passwordConfirm.style.borderColor = '#28a745';
                        passwordConfirm.style.boxShadow = '0 0 0 4px rgba(40, 167, 69, 0.1)';
                    } else {
                        passwordConfirm.style.borderColor = '#dc3545';
                        passwordConfirm.style.boxShadow = '0 0 0 4px rgba(220, 53, 69, 0.1)';
                    }
                }
            });
        }
    });



/* ================================================================
   FIM — App.js UniLuanda Alumni
================================================================ */
console.log('%c🎓 UniLuanda Alumni — app.js carregado', 'color:#c9a227;font-weight:bold;');