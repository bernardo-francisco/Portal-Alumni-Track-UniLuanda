/* ================================================================
   AUTH.JS — Login / Registo / Recuperação de Senha
   Ficheiro isolado para a área de autenticação.
================================================================ */

document.addEventListener('DOMContentLoaded', function () {
    console.log('🟢 AUTH.JS carregado');

    /* ============================================================
       TOGGLE DE SENHA (Mostrar/Ocultar)
    ============================================================ */
    const toggleButtons = document.querySelectorAll('.toggle-password');

    console.log('🟢 Botões toggle encontrados:', toggleButtons.length);

    toggleButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            const field = btn.closest('.field');
            const input = field ? field.querySelector('input') : null;
            const icon = btn.querySelector('i');

            if (!input) {
                console.warn('⚠️ Input não encontrado para o botão');
                return;
            }

            const isPass = input.type === 'password';

            input.type = isPass ? 'text' : 'password';

            if (icon) {
                icon.classList.toggle('fa-eye', !isPass);
                icon.classList.toggle('fa-eye-slash', isPass);
            }

            btn.setAttribute('aria-label', isPass ? 'Ocultar senha' : 'Mostrar senha');

            input.focus();
        });
    });

    /* ============================================================
       MODAL "ESQUECI MINHA SENHA"
    ============================================================ */
    const forgotBtn = document.getElementById('forgotPasswordBtn');
    const forgotModal = document.getElementById('forgotModal');
    const modalClose = document.getElementById('modalClose');
    const modalSuccessBtn = document.getElementById('modalSuccessBtn');

    if (forgotBtn && forgotModal) {
        forgotBtn.addEventListener('click', function (e) {
            e.preventDefault();
            forgotModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    if (modalClose) {
        modalClose.addEventListener('click', function () {
            forgotModal?.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    if (modalSuccessBtn) {
        modalSuccessBtn.addEventListener('click', function () {
            forgotModal?.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    if (forgotModal) {
        forgotModal.addEventListener('click', function (e) {
            if (e.target === forgotModal) {
                forgotModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    /* ============================================================
       FORÇA DA SENHA (só no registo)
    ============================================================ */
    const passwordStrength = document.getElementById('passwordStrength');
    const passwordStrengthLabel = document.getElementById('passwordStrengthLabel');
    const registerPassword = document.getElementById('password');

    if (passwordStrength && registerPassword) {
        registerPassword.addEventListener('input', function () {
            const valor = this.value;

            if (valor.length === 0) {
                passwordStrength.style.display = 'none';
                if (passwordStrengthLabel) passwordStrengthLabel.textContent = '';
                return;
            }

            passwordStrength.style.display = 'flex';

            let pontos = 0;
            if (valor.length >= 6) pontos++;
            if (valor.length >= 10) pontos++;
            if (/[A-Z]/.test(valor)) pontos++;
            if (/[0-9]/.test(valor)) pontos++;
            if (/[^A-Za-z0-9]/.test(valor)) pontos++;

            const barras = passwordStrength.querySelectorAll('.password-strength-bar');
            const niveis = ['weak', 'weak', 'medium', 'medium', 'strong', 'strong'];
            const nivel = niveis[Math.min(pontos, 5)];

            const textos = { weak: 'Senha fraca', medium: 'Senha média', strong: 'Senha forte' };

            barras.forEach((barra, i) => {
                barra.className = 'password-strength-bar';
                if (i < pontos) barra.classList.add(nivel);
            });

            if (passwordStrengthLabel) {
                passwordStrengthLabel.textContent = textos[nivel];
                passwordStrengthLabel.className = 'password-strength-label ' + nivel;
            }
        });
    }

    /* ============================================================
       ✅ FASE 3 — AUTO-PREENCHER + REIVINDICAÇÃO DE CONTA
    ============================================================ */

    const numeroProcessoInput = document.getElementById('numero_processo');
    const validationStatus    = document.getElementById('validationStatus');
    const reivindicacaoPanel  = document.getElementById('reivindicacaoPanel');

    if (numeroProcessoInput && validationStatus) {
        let debounceTimer = null;

        numeroProcessoInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const valor = this.value.trim();

            if (valor.length < 3) {
                validationStatus.innerHTML = '';
                esconderPainelReivindicacao();
                return;
            }

            validationStatus.innerHTML = '<span style="color:#888;font-size:11px;">🔍 A verificar...</span>';

            debounceTimer = setTimeout(() => {
                verificarNumeroProcesso(valor);
            }, 500);
        });
    }

    function verificarNumeroProcesso(numero) {
        const url   = window.APP_ROUTES?.verificarProcesso || '/verificar-processo';
        const token = window.APP_ROUTES?.csrfToken
                   || document.querySelector('meta[name="csrf-token"]')?.content
                   || document.querySelector('input[name="_token"]')?.value;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ numero_processo: numero }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.reivindicavel) {
                validationStatus.innerHTML =
                    '<span style="color:#16a34a;font-size:11px;font-weight:600;">✅ ' +
                    data.message + '</span>';

                mostrarPainelReivindicacao(data);

            } else if (data.sugerir_pendente) {
                validationStatus.innerHTML =
                    '<span style="color:#d97706;font-size:11px;">⚠️ ' +
                    data.message + '</span>';

                esconderPainelReivindicacao();

            } else {
                validationStatus.innerHTML =
                    '<span style="color:#dc2626;font-size:11px;">❌ ' +
                    data.message + '</span>';

                esconderPainelReivindicacao();
            }
        })
        .catch(err => {
            console.error('Erro na verificação:', err);
            validationStatus.innerHTML = '';
        });
    }

    function mostrarPainelReivindicacao(data) {
        if (!reivindicacaoPanel) return;

        const info = data.pre_preenchido || {};

        const infoHtml = `
            <div style="display:grid;gap:6px;font-size:12px;color:#166534;margin:12px 0;">
                <div><strong>Nome:</strong> ${info.nome_mascarado || '—'}</div>
                <div><strong>Curso:</strong> ${info.curso || '—'}</div>
                <div><strong>Unidade:</strong> ${info.unidade || '—'}</div>
                <div><strong>Ano de Formatura:</strong> ${info.ano_formatura || '—'}</div>
            </div>
        `;

        document.getElementById('reivindicacaoInfo').innerHTML = infoHtml;
        reivindicacaoPanel.style.display = 'block';

        window.__dadosReivindicacao = data.auto_preencher || {};
    }

    function esconderPainelReivindicacao() {
        if (reivindicacaoPanel) {
            reivindicacaoPanel.style.display = 'none';
        }
        window.__dadosReivindicacao = null;
    }

    const btnSimSouEu = document.getElementById('btnSimSouEu');
    if (btnSimSouEu) {
        btnSimSouEu.addEventListener('click', function () {
            const dados = window.__dadosReivindicacao;
            if (!dados) return;

            preencherCampos(dados);

            if (reivindicacaoPanel) {
                reivindicacaoPanel.style.display = 'none';
            }

            validationStatus.innerHTML =
                '<span style="color:#16a34a;font-size:11px;font-weight:600;">✅ Confirmação aceite! Preencha os campos em falta e clique em "Criar Conta".</span>';

            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });
    }

    const btnNaoSouEu = document.getElementById('btnNaoSouEu');
    if (btnNaoSouEu) {
        btnNaoSouEu.addEventListener('click', function () {
            esconderPainelReivindicacao();
            limparCamposReivindicados();

            const inputNum = document.getElementById('numero_processo');
            if (inputNum) inputNum.value = '';

            validationStatus.innerHTML = '';
        });
    }

    function preencherCampos(dados) {
        const camposTexto = {
            'nome_completo':   dados.nome_completo,
            'email':           dados.email,
            'telefone':        dados.telefone,
            'data_nascimento': dados.data_nascimento,
            'ano_formatura':   dados.ano_formatura,
        };

        for (const [id, valor] of Object.entries(camposTexto)) {
            const input = document.getElementById(id);
            if (input && valor) {
                input.value = valor;
            }
        }

        if (dados.genero) {
            const generoSel = document.getElementById('genero');
            if (generoSel) generoSel.value = dados.genero;
        }

        if (dados.curso_id) {
            const cursoSel = document.getElementById('curso_id');
            if (cursoSel) cursoSel.value = dados.curso_id;
        }

        bloquearCamposReivindicados();
    }

    function bloquearCamposReivindicados() {
        const readonlyIds = [
            'numero_processo',
            'nome_completo',
            'data_nascimento',
            'ano_formatura',
        ];

        readonlyIds.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.readOnly = true;
                input.style.background = '#f0f0f0';
                input.style.cursor = 'not-allowed';
            }
        });

        const disabledIds = ['genero', 'curso_id'];
        disabledIds.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.disabled = true;
                input.style.background = '#f0f0f0';
                input.style.cursor = 'not-allowed';
            }
        });
    }

    function limparCamposReivindicados() {
        const campos = [
            'nome_completo',
            'email',
            'telefone',
            'data_nascimento',
            'ano_formatura',
        ];

        campos.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.value = '';
                input.readOnly = false;
                input.style.background = '';
                input.style.cursor = '';
            }
        });

        const selects = ['genero', 'curso_id'];
        selects.forEach(id => {
            const sel = document.getElementById(id);
            if (sel) {
                sel.value = '';
                sel.disabled = false;
                sel.style.background = '';
                sel.style.cursor = '';
            }
        });
    }
});