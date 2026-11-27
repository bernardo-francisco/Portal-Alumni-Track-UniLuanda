
//============================================================
//     SCRIPTS DO FEED — curtir / comentar / preview / modais
//     (inline, independente do app.js)
//============================================================

(function () {
    'use strict';

    // ---------------------------------------------------------
    // CSRF
    // ---------------------------------------------------------
    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // ---------------------------------------------------------
    // TOAST
    // ---------------------------------------------------------
    function toast(msg, tipo) {
        const cores = { success: '#16a34a', info: '#c9a227', danger: '#dc2626' };
        const antigo = document.querySelector('.feed-toast');
        if (antigo) antigo.remove();

        const el = document.createElement('div');
        el.className = 'feed-toast';
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

    // ---------------------------------------------------------
    // ESTADO DO MODAL DE COMENTÁRIOS
    // ---------------------------------------------------------
    let publicacaoComentarioId = null;

    // ---------------------------------------------------------
    // CURTIR
    // ---------------------------------------------------------
    window.curtir = function (id) {
        const card = document.querySelector(`[data-publicacao-id="${id}"]`);
        if (!card) { console.warn('Card não encontrado:', id); return; }

        const btn  = card.querySelector('.btn-curtir');
        const span = card.querySelector('.curtidas-count');
        if (!btn || !span) { console.warn('btn-curtir/curtidas-count não encontrados'); return; }

        if (btn.dataset.loading === '1') return;
        btn.dataset.loading = '1';
        btn.disabled = true;

        fetch(`/egresso/feed/${id}/curtir`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(async r => {
            const txt = await r.text();
            console.log('[curtir]', r.status, txt);
            if (!r.ok) throw new Error('HTTP ' + r.status + ' — ' + txt.slice(0, 200));
            return JSON.parse(txt);
        })
        .then(data => {
            if (data.success) {
                span.textContent = data.total;
                btn.classList.toggle('text-danger', data.curtido);
                btn.classList.toggle('text-muted', !data.curtido);
            } else {
                toast(data.message || 'Erro ao curtir.', 'danger');
            }
        })
        .catch(err => {
            console.error('[curtir] ERRO:', err);
            toast('Erro ao curtir: ' + err.message, 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.dataset.loading = '0';
        });
    };

    // ---------------------------------------------------------
    // COMENTAR — abre o modal Bootstrap
    // ---------------------------------------------------------
    window.comentar = function (id) {
        const modalEl = document.getElementById('modalComentar');
        if (!modalEl || typeof bootstrap === 'undefined') {
            toast('Modal de comentários indisponível.', 'danger');
            return;
        }

        publicacaoComentarioId = id;

        const card = document.querySelector(`[data-publicacao-id="${id}"]`);
        const nomeAutor = card?.querySelector('.card-header strong')?.textContent?.trim() || 'Publicação';
        const resumo    = card?.dataset.conteudo || '';

        const elNome   = document.getElementById('comentarNomeAutor');
        const elResumo = document.getElementById('comentarResumo');
        const elLista  = document.getElementById('comentariosLista');
        const elVazio  = document.getElementById('comentariosVazio');
        const elTexto  = document.getElementById('comentarioTexto');
        const elCont   = document.getElementById('comentarioContador');

        if (elNome)   elNome.textContent = nomeAutor;
        if (elResumo) elResumo.textContent = resumo.length > 90 ? resumo.slice(0, 90) + '…' : resumo;
        if (elTexto)  elTexto.value = '';
        if (elCont)   elCont.textContent = '0';

        if (elLista) {
            elLista.innerHTML = `
                <div class="text-center text-muted py-3" id="comentariosVazio">
                    <i class="far fa-comment-dots fa-2x mb-2 d-block"></i>
                    <small>Ainda não há comentários. Sê o primeiro!</small>
                </div>`;
        }

        carregarComentarios(id);

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    // ---------------------------------------------------------
    // CARREGAR COMENTÁRIOS
    // ---------------------------------------------------------
    function carregarComentarios(id) {
        const lista = document.getElementById('comentariosLista');
        if (!lista) return;

        fetch(`/egresso/feed/${id}/comentarios`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(async r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            const comentarios = data.comentarios || data.data || [];
            if (!comentarios.length) {
                lista.innerHTML = `
                    <div class="text-center text-muted py-3" id="comentariosVazio">
                        <i class="far fa-comment-dots fa-2x mb-2 d-block"></i>
                        <small>Ainda não há comentários. Sê o primeiro!</small>
                    </div>`;
                return;
            }

            lista.innerHTML = comentarios.map(c => {
                const nome    = c.autor?.nome || c.nome || 'Egresso';
                const foto    = c.autor?.foto || c.foto_url || '';
                const iniciais = nome.charAt(0).toUpperCase();
                const texto   = c.conteudo || c.texto || '';
                const tempo   = c.tempo_decorrido || '';

                const avatar = foto
                    ? `<img src="${foto}" class="rounded-circle border flex-shrink-0" style="width:36px;height:36px;object-fit:cover;">`
                    : `<div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                            style="width:36px;height:36px;background:linear-gradient(135deg,#1a56db,#3b82f6);font-size:.85rem;">${iniciais}</div>`;

                return `
                    <div class="d-flex align-items-start gap-2 mb-3">
                        ${avatar}
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="small">${nome}</strong>
                                <small class="text-muted">${tempo}</small>
                            </div>
                            <div class="text-muted small" style="white-space: pre-wrap;">${texto}</div>
                        </div>
                    </div>`;
            }).join('');
        })
        .catch(err => {
            console.error('[comentar] erro ao carregar:', err);
            lista.innerHTML = `
                <div class="text-center text-danger py-3">
                    <small><i class="fas fa-exclamation-circle me-1"></i> Não foi possível carregar os comentários.</small>
                </div>`;
        });
    }

    // ---------------------------------------------------------
    // ENVIAR COMENTÁRIO (submissão do form do modal)
    // ---------------------------------------------------------
    window.enviarComentario = function (event) {
    event.preventDefault();
    if (!publicacaoComentarioId) return false;

    const elTexto = document.getElementById('comentarioTexto');
    const btn     = document.getElementById('btnEnviarComentario');
    const texto   = (elTexto?.value || '').trim();

    if (!texto) {
        toast('Escreve um comentário antes de enviar.', 'info');
        return false;
    }

    if (btn) { btn.disabled = true; btn.dataset.loading = '1'; }

    fetch(`/egresso/feed/${publicacaoComentarioId}/comentar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ conteudo: texto }),
    })
    .then(async r => {
        const txt = await r.text();
        console.log('[comentar]', r.status, txt);
        if (!r.ok) throw new Error('HTTP ' + r.status + ' — ' + txt.slice(0, 200));
        return JSON.parse(txt);
    })
    .then(data => {
        if (data.success) {
            // Atualiza contador no card
            const card = document.querySelector(`[data-publicacao-id="${publicacaoComentarioId}"]`);
            const span = card?.querySelector('.comentarios-count');
            if (span) span.textContent = data.total;

            // Limpa o textarea e contador
            if (elTexto) elTexto.value = '';
            const cont = document.getElementById('comentarioContador');
            if (cont) cont.textContent = '0';

            // ✅ Fecha o modal
            const modalEl = document.getElementById('modalComentar');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }

            toast('Comentário adicionado.', 'success');
        } else {
            toast(data.message || 'Erro ao comentar.', 'danger');
        }
    })
    .catch(err => {
        console.error('[comentar] ERRO:', err);
        toast('Erro ao comentar: ' + err.message, 'danger');
    })
    .finally(() => {
        if (btn) { btn.disabled = false; btn.dataset.loading = '0'; }
    });

    return false;
};

    // ---------------------------------------------------------
    // CONTADOR DO TEXTAREA DE COMENTÁRIO
    // ---------------------------------------------------------
    document.addEventListener('input', function (e) {
        if (e.target && e.target.id === 'comentarioTexto') {
            const cont = document.getElementById('comentarioContador');
            if (cont) cont.textContent = e.target.value.length;
        }
    });

    // ---------------------------------------------------------
    // PREVIEW DE IMAGEM (nova publicação)
    // ---------------------------------------------------------
    window.previewImagem = function (input) {
        const preview = document.getElementById('previewImagem');
        const img     = document.getElementById('previewImg');
        if (!input.files || !input.files[0]) return;

        const reader = new FileReader();
        reader.onload = e => {
            if (img) img.src = e.target.result;
            if (preview) preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    };

    window.removerImagem = function () {
        const input   = document.getElementById('imagem');
        const preview = document.getElementById('previewImagem');
        const img     = document.getElementById('previewImg');
        if (input) input.value = '';
        if (img) img.src = '';
        if (preview) preview.classList.add('d-none');
    };

    // ---------------------------------------------------------
    // MODAL EDITAR
    // ---------------------------------------------------------
    window.abrirModalEditar = function (id) {
        const modalEl = document.getElementById('modalEditar');
        const card    = document.querySelector(`[data-publicacao-id="${id}"]`);
        if (!modalEl || !card || typeof bootstrap === 'undefined') return;

        const form       = document.getElementById('formEditar');
        const conteudo   = document.getElementById('editConteudo');
        const imagemBox  = document.getElementById('imagemAtualBox');
        const imagemPrev = document.getElementById('imagemAtualPreview');
        const removerChk = document.getElementById('removerImagemCheck');
        const novaBox    = document.getElementById('previewNovaImagemBox');
        const novaImg    = document.getElementById('previewNovaImagemImg');
        const inputImg   = document.getElementById('editImagem');

        if (form) form.action = `/egresso/feed/${id}`;
        if (conteudo) conteudo.value = card.dataset.conteudo || '';
        if (inputImg) inputImg.value = '';
        if (novaBox) novaBox.classList.add('d-none');
        if (novaImg) novaImg.src = '';
        if (removerChk) removerChk.checked = false;

        const imgAtual = card.dataset.imagem || '';
        if (imgAtual && imagemPrev && imagemBox) {
            imagemPrev.src = imgAtual;
            imagemBox.style.display = 'block';
        } else if (imagemBox) {
            imagemBox.style.display = 'none';
        }

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    window.previewNovaImagem = function (input) {
        const box = document.getElementById('previewNovaImagemBox');
        const img = document.getElementById('previewNovaImagemImg');
        if (!input.files || !input.files[0]) {
            if (box) box.classList.add('d-none');
            if (img) img.src = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            if (img) img.src = e.target.result;
            if (box) box.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    };

    // ---------------------------------------------------------
    // MODAL IMAGEM AMPLIADA
    // ---------------------------------------------------------
    window.abrirImagem = function (src) {
        const modalEl = document.getElementById('modalImagem');
        const img     = document.getElementById('imagemAmpliada');
        if (!modalEl || !img || typeof bootstrap === 'undefined') return;
        img.src = src;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    console.log('✅ Scripts do feed carregados (inline).');
})();
