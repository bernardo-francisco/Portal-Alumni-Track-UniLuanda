@php
    /*
    |--------------------------------------------------------------------------
    | CONFIGURAÇÃO DO CHATBOT
    |--------------------------------------------------------------------------
    */

    $chatbotRole = $chatbotRole ?? 'egresso';

    $isAdmin = $chatbotRole === 'admin';


    /*
    |--------------------------------------------------------------------------
    | ROTAS
    |--------------------------------------------------------------------------
    */

    if ($isAdmin) {

        $sendUrl = url('/admin/chatbot/enviar');

        $verifyUrl = url('/admin/chatbot/verificar');

    } else {

        $sendUrl = url('/egresso/chatbot/enviar');

        $verifyUrl = url('/egresso/chatbot/verificar');

    }


    /*
    |--------------------------------------------------------------------------
    | TEXTOS
    |--------------------------------------------------------------------------
    */

    $chatbotTitle = $isAdmin
        ? 'Assistente Administrativo'
        : 'Assistente do Egresso';

    $chatbotSubtitle = 'Estou aqui para ajudar-te';


    /*
    |--------------------------------------------------------------------------
    | MENSAGEM INICIAL
    |--------------------------------------------------------------------------
    */

    $welcomeMessage = $isAdmin

        ? 'Olá, Administrador! 👋<br><br>
           Sou o teu assistente virtual da área administrativa.
           Posso orientar-te nas principais funcionalidades da plataforma UniLuanda.'

        : 'Olá! 👋<br><br>
           Sou o teu assistente virtual.
           Posso ajudar-te a utilizar a plataforma UniLuanda.';


    /*
    |--------------------------------------------------------------------------
    | SUGESTÕES
    |--------------------------------------------------------------------------
    */

    if ($isAdmin) {

        $suggestions = [
            [
                'icon' => 'fa-users',
                'text' => 'Egressos',
                'message' => 'Como posso gerir os egressos?'
            ],
            [
                'icon' => 'fa-user-check',
                'text' => 'Validação',
                'message' => 'Como funciona a validação dos egressos?'
            ],
            [
                'icon' => 'fa-graduation-cap',
                'text' => 'Cursos',
                'message' => 'Como posso gerir os cursos?'
            ],
            [
                'icon' => 'fa-building',
                'text' => 'Unidades',
                'message' => 'Como posso gerir as unidades orgânicas?'
            ],
            [
                'icon' => 'fa-briefcase',
                'text' => 'Oportunidades',
                'message' => 'Como posso gerir as oportunidades?'
            ],
            [
                'icon' => 'fa-chart-column',
                'text' => 'Relatórios',
                'message' => 'Como posso consultar os relatórios?'
            ],
        ];

    } else {

        $suggestions = [
            [
                'icon' => 'fa-circle-question',
                'text' => 'Ajuda',
                'message' => 'Preciso de ajuda para utilizar o sistema.'
            ],
            [
                'icon' => 'fa-briefcase',
                'text' => 'Oportunidades',
                'message' => 'Como vejo as oportunidades?'
            ],
            [
                'icon' => 'fa-paper-plane',
                'text' => 'Candidaturas',
                'message' => 'Como vejo as minhas candidaturas?'
            ],
            [
                'icon' => 'fa-user',
                'text' => 'Meu perfil',
                'message' => 'Como posso atualizar o meu perfil?'
            ],
        ];
    }
@endphp


<!-- ================================================================
     CHATBOT FLUTUANTE
================================================================ -->

<div
    id="uniluanda-chatbot"
    class="uniluanda-chatbot"
    data-role="{{ $chatbotRole }}"
    data-send-url="{{ $sendUrl }}"
    data-verify-url="{{ $verifyUrl }}"
>


    <!-- ============================================================
         BOTÃO FLUTUANTE
    ============================================================= -->

    <button
        type="button"
        id="chatbotToggle"
        class="chatbot-toggle"
        aria-label="Abrir assistente virtual"
    >

        <i class="fas fa-robot"></i>

        <span
            id="chatbotBadge"
            class="chatbot-badge d-none"
        >
            0
        </span>

    </button>



    <!-- ============================================================
         JANELA DO CHAT
    ============================================================= -->

    <div
        id="chatbotWindow"
        class="chatbot-window"
        aria-hidden="true"
    >


        <!-- ========================================================
             CABEÇALHO
        ========================================================= -->

        <div class="chatbot-header">

            <div class="chatbot-header-info">

                <div class="chatbot-avatar">

                    <i class="fas fa-robot"></i>

                </div>


                <div>

                    <h6 class="chatbot-title mb-0">
                        {{ $chatbotTitle }}
                    </h6>

                    <small class="chatbot-subtitle">
                        {{ $chatbotSubtitle }}
                    </small>

                </div>

            </div>


            <button
                type="button"
                id="chatbotClose"
                class="chatbot-close"
                aria-label="Fechar chatbot"
            >

                <i class="fas fa-times"></i>

            </button>

        </div>



        <!-- ========================================================
             CORPO
        ========================================================= -->

        <div
            id="chatbotMessages"
            class="chatbot-messages"
        >


            <!-- ====================================================
                 MENSAGEM INICIAL
            ===================================================== -->

            <div class="chatbot-message bot-message">

                <div class="message-avatar">

                    <i class="fas fa-robot"></i>

                </div>


                <div class="message-content">

                    <div class="message-bubble">

                        {!! $welcomeMessage !!}

                    </div>


                    <div class="message-time">

                        Agora

                    </div>

                </div>

            </div>



            <!-- ====================================================
                 SUGESTÕES
            ===================================================== -->

            <div class="chatbot-suggestions">

                @foreach ($suggestions as $suggestion)

                    <button
                        type="button"
                        class="chatbot-suggestion"
                        data-message="{{ $suggestion['message'] }}"
                    >

                        <i class="fas {{ $suggestion['icon'] }}"></i>

                        <span>
                            {{ $suggestion['text'] }}
                        </span>

                    </button>

                @endforeach

            </div>



            <!-- ====================================================
                 RODAPÉ DA MENSAGEM
            ===================================================== -->

            <div class="chatbot-help-footer">

                <i class="fas fa-lightbulb"></i>

                <span>
                    Pergunta-me o que precisas e tentarei orientar-te.
                </span>

            </div>


        </div>



        <!-- ========================================================
             ÁREA DE DIGITAÇÃO
        ========================================================= -->

        <form
            id="chatbotForm"
            class="chatbot-form"
        >

            <div class="chatbot-input-wrapper">

                <input
                    type="text"
                    id="chatbotInput"
                    class="chatbot-input"
                    placeholder="Escreve a tua pergunta..."
                    autocomplete="off"
                    maxlength="1000"
                >


                <button
                    type="submit"
                    id="chatbotSend"
                    class="chatbot-send"
                    aria-label="Enviar mensagem"
                >

                    <i class="fas fa-paper-plane"></i>

                </button>

            </div>

        </form>

    </div>

</div>



<!-- ================================================================
     CSS
================================================================ -->

<style>

.uniluanda-chatbot {

    position: fixed;

    right: 25px;

    bottom: 25px;

    z-index: 9999;

    font-family:
        'Inter',
        Arial,
        sans-serif;

}


/*
|--------------------------------------------------------------------------
| BOTÃO FLUTUANTE
|--------------------------------------------------------------------------
*/

.chatbot-toggle {

    width: 60px;

    height: 60px;

    border: none;

    border-radius: 50%;

    background: #0d6efd;

    color: #ffffff;

    font-size: 23px;

    display: flex;

    align-items: center;

    justify-content: center;

    cursor: pointer;

    box-shadow:
        0 8px 25px rgba(0, 0, 0, 0.20);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;

}


.chatbot-toggle:hover {

    transform: translateY(-3px);

    box-shadow:
        0 12px 30px rgba(0, 0, 0, 0.25);

}


.chatbot-toggle:active {

    transform: scale(0.95);

}



/*
|--------------------------------------------------------------------------
| BADGE
|--------------------------------------------------------------------------
*/

.chatbot-badge {

    position: absolute;

    top: -3px;

    right: -3px;

    min-width: 20px;

    height: 20px;

    padding: 0 5px;

    border-radius: 50px;

    background: #dc3545;

    color: #ffffff;

    font-size: 11px;

    font-weight: 700;

    display: flex;

    align-items: center;

    justify-content: center;

    border: 2px solid #ffffff;

}



/*
|--------------------------------------------------------------------------
| JANELA
|--------------------------------------------------------------------------
*/

.chatbot-window {

    position: absolute;

    right: 0;

    bottom: 75px;

    width: 390px;

    max-width: calc(100vw - 30px);

    height: 570px;

    max-height: calc(100vh - 110px);

    background: #ffffff;

    border-radius: 18px;

    overflow: hidden;

    display: none;

    flex-direction: column;

    box-shadow:
        0 15px 50px rgba(0, 0, 0, 0.22);

    border:
        1px solid #e9ecef;

}


.chatbot-window.open {

    display: flex;

}



/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.chatbot-header {

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #0b5ed7
        );

    color: #ffffff;

    padding: 15px 17px;

    display: flex;

    align-items: center;

    justify-content: space-between;

}


.chatbot-header-info {

    display: flex;

    align-items: center;

    gap: 11px;

}


.chatbot-avatar {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.18);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

}


.chatbot-title {

    font-size: 15px;

    font-weight: 700;

}


.chatbot-subtitle {

    opacity: 0.85;

    font-size: 11px;

}


.chatbot-close {

    width: 34px;

    height: 34px;

    border: none;

    background: transparent;

    color: #ffffff;

    border-radius: 50%;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 16px;

}


.chatbot-close:hover {

    background:
        rgba(255, 255, 255, 0.15);

}



/*
|--------------------------------------------------------------------------
| MENSAGENS
|--------------------------------------------------------------------------
*/

.chatbot-messages {

    flex: 1;

    overflow-y: auto;

    padding: 18px;

    background: #f8f9fa;

}


.chatbot-message {

    display: flex;

    gap: 9px;

    margin-bottom: 15px;

}


.message-avatar {

    width: 32px;

    height: 32px;

    min-width: 32px;

    border-radius: 50%;

    background: #e7f1ff;

    color: #0d6efd;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 13px;

}


.message-content {

    max-width: 82%;

}


.message-bubble {

    background: #ffffff;

    border:
        1px solid #e9ecef;

    border-radius:
        4px 14px 14px 14px;

    padding: 11px 13px;

    color: #343a40;

    font-size: 13px;

    line-height: 1.55;

    box-shadow:
        0 2px 5px rgba(0, 0, 0, 0.04);

}


.message-time {

    margin-top: 4px;

    color: #8a8f98;

    font-size: 10px;

}



/*
|--------------------------------------------------------------------------
| MENSAGEM DO UTILIZADOR
|--------------------------------------------------------------------------
*/

.user-message {

    justify-content: flex-end;

}


.user-message .message-content {

    display: flex;

    flex-direction: column;

    align-items: flex-end;

}


.user-message .message-bubble {

    background: #0d6efd;

    color: #ffffff;

    border-radius:
        14px 4px 14px 14px;

    border: none;

}


.user-message .message-time {

    text-align: right;

}



/*
|--------------------------------------------------------------------------
| SUGESTÕES
|--------------------------------------------------------------------------
*/

.chatbot-suggestions {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 8px;

    margin-top: 5px;

}


.chatbot-suggestion {

    border:
        1px solid #dce6f5;

    background: #ffffff;

    border-radius: 10px;

    padding: 9px 8px;

    color: #0d6efd;

    font-size: 11px;

    font-weight: 600;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    cursor: pointer;

    transition:
        all 0.2s ease;

}


.chatbot-suggestion:hover {

    background: #eaf3ff;

    border-color: #0d6efd;

    transform: translateY(-1px);

}


.chatbot-suggestion i {

    font-size: 12px;

}



/*
|--------------------------------------------------------------------------
| FOOTER DE AJUDA
|--------------------------------------------------------------------------
*/

.chatbot-help-footer {

    margin-top: 15px;

    padding:
        10px 12px;

    border-radius: 10px;

    background: #eef5ff;

    color: #5c6775;

    font-size: 11px;

    display: flex;

    align-items: flex-start;

    gap: 7px;

}


.chatbot-help-footer i {

    color: #0d6efd;

    margin-top: 2px;

}



/*
|--------------------------------------------------------------------------
| FORMULÁRIO
|--------------------------------------------------------------------------
*/

.chatbot-form {

    background: #ffffff;

    border-top:
        1px solid #e9ecef;

    padding: 11px;

}


.chatbot-input-wrapper {

    display: flex;

    align-items: center;

    gap: 7px;

    background: #f5f6f8;

    border:
        1px solid #e1e4e8;

    border-radius: 12px;

    padding:
        4px 5px 4px 12px;

}


.chatbot-input {

    flex: 1;

    border: none;

    outline: none;

    background: transparent;

    font-size: 13px;

    color: #343a40;

    min-width: 0;

}


.chatbot-input::placeholder {

    color: #969ba3;

}


.chatbot-send {

    width: 38px;

    height: 38px;

    border: none;

    border-radius: 10px;

    background: #0d6efd;

    color: #ffffff;

    display: flex;

    align-items: center;

    justify-content: center;

    cursor: pointer;

}


.chatbot-send:hover {

    background: #0b5ed7;

}


.chatbot-send:disabled {

    opacity: 0.6;

    cursor: not-allowed;

}



/*
|--------------------------------------------------------------------------
| INDICADOR DE DIGITAÇÃO
|--------------------------------------------------------------------------
*/

.chatbot-typing {

    display: flex;

    align-items: center;

    gap: 4px;

    padding: 10px 13px;

}


.chatbot-typing span {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background: #9aa0a6;

    animation:
        chatbotTyping 1.2s infinite ease-in-out;

}


.chatbot-typing span:nth-child(2) {

    animation-delay: 0.15s;

}


.chatbot-typing span:nth-child(3) {

    animation-delay: 0.30s;

}


@keyframes chatbotTyping {

    0%,
    60%,
    100% {

        transform: translateY(0);

    }

    30% {

        transform: translateY(-4px);

    }

}



/*
|--------------------------------------------------------------------------
| RESPONSIVO
|--------------------------------------------------------------------------
*/

@media (max-width: 576px) {

    .uniluanda-chatbot {

        right: 15px;

        bottom: 15px;

    }


    .chatbot-toggle {

        width: 55px;

        height: 55px;

        font-size: 21px;

    }


    .chatbot-window {

        right: 0;

        bottom: 65px;

        width: calc(100vw - 30px);

        height: 520px;

        max-height: calc(100vh - 90px);

        border-radius: 15px;

    }

}

</style>



<!-- ================================================================
     JAVASCRIPT
================================================================ -->

<script>

(function () {

    /*
    |--------------------------------------------------------------------------
    | EVITAR DUPLA INICIALIZAÇÃO
    |--------------------------------------------------------------------------
    */

    if (window.__uniluandaChatbotInitialized) {

        return;

    }

    window.__uniluandaChatbotInitialized = true;



    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS
    |--------------------------------------------------------------------------
    */

    const chatbot =
        document.getElementById(
            'uniluanda-chatbot'
        );


    if (!chatbot) {

        return;

    }


    const toggle =
        document.getElementById(
            'chatbotToggle'
        );


    const close =
        document.getElementById(
            'chatbotClose'
        );


    const windowChat =
        document.getElementById(
            'chatbotWindow'
        );


    const messages =
        document.getElementById(
            'chatbotMessages'
        );


    const form =
        document.getElementById(
            'chatbotForm'
        );


    const input =
        document.getElementById(
            'chatbotInput'
        );


    const sendButton =
        document.getElementById(
            'chatbotSend'
        );


    const badge =
        document.getElementById(
            'chatbotBadge'
        );


    const sendUrl =
        chatbot.dataset.sendUrl;


    const verifyUrl =
        chatbot.dataset.verifyUrl;



    /*
    |--------------------------------------------------------------------------
    | ABRIR CHAT
    |--------------------------------------------------------------------------
    */

    function abrirChat() {

        if (!windowChat) {

            return;

        }

        windowChat.classList.add('open');

        windowChat.setAttribute(
            'aria-hidden',
            'false'
        );

        if (input) {

            setTimeout(
                function () {

                    input.focus();

                },
                100
            );

        }

        scrollMessages();

        esconderBadge();

    }



    /*
    |--------------------------------------------------------------------------
    | FECHAR CHAT
    |--------------------------------------------------------------------------
    */

    function fecharChat() {

        if (!windowChat) {

            return;

        }

        windowChat.classList.remove('open');

        windowChat.setAttribute(
            'aria-hidden',
            'true'
        );

    }



    /*
    |--------------------------------------------------------------------------
    | TOGGLE
    |--------------------------------------------------------------------------
    */

    if (toggle) {

        toggle.addEventListener(
            'click',
            function () {

                if (
                    windowChat &&
                    windowChat.classList.contains('open')
                ) {

                    fecharChat();

                } else {

                    abrirChat();

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | FECHAR
    |--------------------------------------------------------------------------
    */

    if (close) {

        close.addEventListener(
            'click',
            fecharChat
        );

    }



    /*
    |--------------------------------------------------------------------------
    | SCROLL
    |--------------------------------------------------------------------------
    */

    function scrollMessages() {

        if (!messages) {

            return;

        }

        messages.scrollTop =
            messages.scrollHeight;

    }



    /*
    |--------------------------------------------------------------------------
    | ESCONDER BADGE
    |--------------------------------------------------------------------------
    */

    function esconderBadge() {

        if (!badge) {

            return;

        }

        badge.classList.add(
            'd-none'
        );

        badge.textContent = '0';

    }



    /*
    |--------------------------------------------------------------------------
    | MOSTRAR BADGE
    |--------------------------------------------------------------------------
    */

    function mostrarBadge(total) {

        if (!badge) {

            return;

        }

        if (!total || total <= 0) {

            esconderBadge();

            return;

        }

        badge.textContent =
            total > 99
                ? '99+'
                : total;

        badge.classList.remove(
            'd-none'
        );

    }



    /*
    |--------------------------------------------------------------------------
    | ESCAPAR HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(text) {

        const div =
            document.createElement(
                'div'
            );

        div.textContent =
            text ?? '';

        return div.innerHTML;

    }



    /*
    |--------------------------------------------------------------------------
    | ADICIONAR MENSAGEM DO UTILIZADOR
    |--------------------------------------------------------------------------
    */

    function adicionarMensagemUtilizador(texto) {

        if (!messages) {

            return;

        }

        const div =
            document.createElement(
                'div'
            );

        div.className =
            'chatbot-message user-message';

        div.innerHTML = `

            <div class="message-content">

                <div class="message-bubble">

                    ${escapeHtml(texto)}

                </div>

                <div class="message-time">

                    Agora

                </div>

            </div>

        `;

        messages.appendChild(div);

        scrollMessages();

    }



    /*
    |--------------------------------------------------------------------------
    | ADICIONAR MENSAGEM DO BOT
    |--------------------------------------------------------------------------
    */

    function adicionarMensagemBot(texto) {

        if (!messages) {

            return;

        }

        const div =
            document.createElement(
                'div'
            );

        div.className =
            'chatbot-message bot-message';

        div.innerHTML = `

            <div class="message-avatar">

                <i class="fas fa-robot"></i>

            </div>

            <div class="message-content">

                <div class="message-bubble">

                    ${escapeHtml(texto)}

                </div>

                <div class="message-time">

                    Agora

                </div>

            </div>

        `;

        messages.appendChild(div);

        scrollMessages();

    }



    /*
    |--------------------------------------------------------------------------
    | INDICADOR "A ESCREVER..."
    |--------------------------------------------------------------------------
    */

    function mostrarDigitando() {

        if (!messages) {

            return null;

        }

        const div =
            document.createElement(
                'div'
            );

        div.className =
            'chatbot-message bot-message chatbot-typing-container';

        div.innerHTML = `

            <div class="message-avatar">

                <i class="fas fa-robot"></i>

            </div>

            <div class="message-content">

                <div class="message-bubble">

                    <div class="chatbot-typing">

                        <span></span>
                        <span></span>
                        <span></span>

                    </div>

                </div>

            </div>

        `;

        messages.appendChild(div);

        scrollMessages();

        return div;

    }



    /*
    |--------------------------------------------------------------------------
    | ENVIAR MENSAGEM
    |--------------------------------------------------------------------------
    */

    async function enviarMensagem(texto) {

        texto =
            String(texto || '')
                .trim();


        if (!texto) {

            return;

        }


        /*
        |--------------------------------------------------------------
        | VERIFICAR URL
        |--------------------------------------------------------------
        */

        if (!sendUrl) {

            adicionarMensagemBot(
                'O assistente não está disponível neste momento. Verifica a configuração das rotas do chatbot.'
            );

            return;

        }


        /*
        |--------------------------------------------------------------
        | MOSTRAR MENSAGEM
        |--------------------------------------------------------------
        */

        adicionarMensagemUtilizador(
            texto
        );


        /*
        |--------------------------------------------------------------
        | MOSTRAR DIGITAÇÃO
        |--------------------------------------------------------------
        */

        const typing =
            mostrarDigitando();


        if (sendButton) {

            sendButton.disabled =
                true;

        }


        try {

            const csrfMeta =
                document.querySelector(
                    'meta[name="csrf-token"]'
                );


            const csrfToken =
                csrfMeta
                    ? csrfMeta.getAttribute('content')
                    : '';


            const response =
                await fetch(
                    sendUrl,
                    {
                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest'

                        },

                        body:
                            JSON.stringify({
                                mensagem: texto
                            })

                    }
                );


            /*
            |----------------------------------------------------------
            | RESPOSTA
            |----------------------------------------------------------
            */

            const data =
                await response.json();


            /*
            |----------------------------------------------------------
            | REMOVER DIGITAÇÃO
            |----------------------------------------------------------
            */

            if (typing) {

                typing.remove();

            }


            /*
            |----------------------------------------------------------
            | ERRO HTTP
            |----------------------------------------------------------
            */

            if (!response.ok) {

                adicionarMensagemBot(
                    data.message ||
                    'Ocorreu um erro ao processar a tua mensagem.'
                );

                return;

            }


            /*
            |----------------------------------------------------------
            | RESPOSTA DO CHATBOT
            |----------------------------------------------------------
            */

            if (
                data.resposta &&
                typeof data.resposta === 'object'
            ) {

                adicionarMensagemBot(
                    data.resposta.mensagem ||
                    'Não consegui encontrar uma resposta para a tua pergunta.'
                );

            }

            else if (
                typeof data.resposta === 'string'
            ) {

                adicionarMensagemBot(
                    data.resposta
                );

            }

            else if (
                data.message
            ) {

                adicionarMensagemBot(
                    data.message
                );

            }

            else {

                adicionarMensagemBot(
                    'Mensagem recebida, mas não foi possível obter uma resposta.'
                );

            }

        }

        catch (error) {

            console.error(
                'Erro no chatbot:',
                error
            );


            if (typing) {

                typing.remove();

            }


            adicionarMensagemBot(
                'Não foi possível contactar o assistente. Verifica a ligação e tenta novamente.'
            );

        }

        finally {

            if (sendButton) {

                sendButton.disabled =
                    false;

            }

            scrollMessages();

        }

    }



    /*
    |--------------------------------------------------------------------------
    | FORMULÁRIO
    |--------------------------------------------------------------------------
    */

    if (form) {

        form.addEventListener(
            'submit',
            function (event) {

                event.preventDefault();


                const texto =
                    input
                        ? input.value.trim()
                        : '';


                if (!texto) {

                    return;

                }


                if (input) {

                    input.value = '';

                }


                enviarMensagem(
                    texto
                );

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | SUGESTÕES
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.chatbot-suggestion'
        )
        .forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const texto =
                            button.dataset.message ||
                            '';


                        if (!texto) {

                            return;

                        }


                        abrirChat();

                        enviarMensagem(
                            texto
                        );

                    }
                );

            }
        );



    /*
    |--------------------------------------------------------------------------
    | VERIFICAR NOVAS MENSAGENS
    |--------------------------------------------------------------------------
    */

    async function verificarNovas() {

        if (!verifyUrl) {

            return;

        }


        try {

            const response =
                await fetch(
                    verifyUrl,
                    {
                        method: 'GET',

                        headers: {

                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'

                        }

                    }
                );


            if (!response.ok) {

                return;

            }


            const data =
                await response.json();


            if (
                data.success &&
                data.total
            ) {

                if (
                    !windowChat ||
                    !windowChat.classList.contains(
                        'open'
                    )
                ) {

                    mostrarBadge(
                        data.total
                    );

                }

            }

        }

        catch (error) {

            console.warn(
                'Não foi possível verificar novas mensagens.',
                error
            );

        }

    }



    /*
    |--------------------------------------------------------------------------
    | VERIFICAÇÃO AUTOMÁTICA
    |--------------------------------------------------------------------------
    */

    setInterval(
        verificarNovas,
        30000
    );


    /*
    |--------------------------------------------------------------------------
    | PRIMEIRA VERIFICAÇÃO
    |--------------------------------------------------------------------------
    */

    setTimeout(
        verificarNovas,
        3000
    );

})();

</script>