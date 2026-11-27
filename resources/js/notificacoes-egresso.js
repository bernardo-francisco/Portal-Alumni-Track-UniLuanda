/* ================================================================
   NOTIFICAÇÕES — EGRESSO
   Marcar como lida (individual) + contadores + badges
================================================================ */
(function () {
    'use strict';

    // -------------------------------------------------------------
    // CSRF
    // -------------------------------------------------------------
    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // -------------------------------------------------------------
    // TOAST simples (opcional, mas dá feedback)
    // -------------------------------------------------------------
    function toast(msg, tipo) {
        const cores = { success: '#16a34a', info: '#c9a227', danger: '#dc2626' };
        const antigo = document.querySelector('.notif-toast');
        if (antigo) antigo.remove();

        const el = document.createElement('div');
        el.className = 'notif-toast';
        el.style.cssText = `
            position: fixed; top: 24px; right: 24px; z-index: 99999;
            background: ${cores[tipo] || cores.success};
            color: #fff; padding: 12px 22px; border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
            font-weight: 500; font-size: .9rem;
        `;
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 2600);
    }

    // -------------------------------------------------------------
    // MARCAR NOTIFICAÇÃO COMO LIDA (egresso)
    // -------------------------------------------------------------
    window.marcarNotifEgresso = function (id) {
        if (!id) return;

        fetch(`/egresso/notificacoes/${id}/marcar-lida`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ _token: csrf() }),
        })
        .then(async r => {
            const txt = await r.text();
            console.log('[notif-egresso]', r.status, txt);
            if (!r.ok) throw new Error('HTTP ' + r.status + ' — ' + txt.slice(0, 200));
            return JSON.parse(txt);
        })
        .then(data => {
            if (!data.success) {
                toast(data.message || 'Erro ao marcar como lida.', 'danger');
                return;
            }

            // 1) Atualiza o badge do topo (se existir)
            const badge = document.getElementById('badge-notificacoes');
            if (badge) {
                let atual = parseInt(badge.dataset.total || badge.textContent) || 0;
                atual = Math.max(0, atual - 1);
                badge.dataset.total = atual;
                badge.textContent = atual;
                if (atual === 0) badge.style.display = 'none';
            }

            // 2) Atualiza o item na lista
            const item = document.getElementById('notif-item-' + id);
            if (item) {
                item.classList.remove('notification-unread');
                item.classList.add('notification-read');
                item.setAttribute('data-status', 'lida');

                // Remove o ponto (badge-dot)
                item.querySelector('.badge-dot')?.remove();

                // Substitui o botão pelo badge "Lida"
                const btn = item.querySelector('.btn-marcar-lida');
                if (btn) {
                    const wrapper = btn.parentElement;
                    if (wrapper) {
                        const span = document.createElement('span');
                        span.className = 'badge bg-light text-muted border';
                        span.innerHTML = '<i class="fas fa-check me-1"></i> Lida';
                        wrapper.replaceChild(span, btn);
                    }
                }
            }

            // 3) Atualiza os contadores das estatísticas (cards)
            const naoLidasEl = document.querySelector('.stat-card-danger .stat-card-value');
            if (naoLidasEl) {
                const n = parseInt(naoLidasEl.textContent, 10) || 0;
                if (n > 0) naoLidasEl.textContent = n - 1;
            }
            const lidasEl = document.querySelector('.stat-card-success .stat-card-value');
            if (lidasEl) {
                const n = parseInt(lidasEl.textContent, 10) || 0;
                lidasEl.textContent = n + 1;
            }

            toast('Notificação marcada como lida.', 'success');
        })
        .catch(err => {
            console.error('[notif-egresso] ERRO:', err);
            toast('Erro ao marcar como lida: ' + err.message, 'danger');
        });
    };

    console.log('✅ Scripts de notificações do egresso carregados.');
})();