/* ================================================================
   FILTROS EM TEMPO REAL (GENÉRICO)
   ---------------------------------------------------------------
   Para usar em qualquer página:
   1. O formulário deve ter a classe: .form-filtros-ajax
   2. O contentor da tabela deve ter a classe: .container-tabela-ajax
   O script trata do resto (debounce, fetch, URL, etc.).
================================================================ */
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.form-filtros-ajax');

    forms.forEach(form => {
        // Encontra o contentor da tabela associado a este formulário
        const container = document.querySelector('.container-tabela-ajax');
        if (!container) return;

        let timeout = null;
        let controller = null;

        function executarPesquisa() {
            if (controller) controller.abort();
            controller = new AbortController();

            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            const url = form.action + (form.action.includes('?') ? '&' : '?') + params;

            // Efeito visual
            container.style.opacity = '0.5';
            container.style.transition = 'opacity 0.2s';

            fetch(url, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Erro na rede');
                return response.text();
            })
            .then(html => {
                container.innerHTML = html;
                container.style.opacity = '1';
                window.history.pushState({}, '', url);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                console.error('Erro na pesquisa:', err);
                container.style.opacity = '1';
            });
        }

        // 1. Bloqueia o submit padrão (botão "Aplicar filtros" ou Enter)
        form.addEventListener('submit', function (e) {
            e.preventDefault(); 
            executarPesquisa();
        });

        // 2. Input de texto (pesquisa com debounce)
        const textInputs = form.querySelectorAll('input[type="text"], input[type="search"]');
        textInputs.forEach(input => {
            input.addEventListener('input', function () {
                clearTimeout(timeout);
                timeout = setTimeout(executarPesquisa, 350);
            });
        });

        // 3. Selects e Dropdowns (envia imediatamente)
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function () {
                clearTimeout(timeout);
                executarPesquisa();
            });
        });

        // 4. Botão "Limpar filtros" (opcional)
        const btnLimpar = form.querySelector('.btn-limpar-filtros');
        if (btnLimpar) {
            btnLimpar.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                executarPesquisa();
            });
        }
    });
});