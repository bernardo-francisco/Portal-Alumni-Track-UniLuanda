<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Egresso - UniLuanda Alumni')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- App CSS + JS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="layout-app"
      data-panel="egresso"
      data-egresso-id="{{ auth()->user()->egresso->id ?? '' }}"
      data-contato-id="{{ $contato->id ?? '' }}"
      data-abrir-modal-apoio="{{ ($errors->any() && old('_token')) || session('success') ? '1' : '0' }}">

<div id="app">

    {{-- SIDEBAR EGRESSO --}}
    @include('layouts.partials.egresso_sidebar')

    {{-- OVERLAY MOBILE --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    {{-- MAIN CONTENT --}}
    <div class="main-content">

        {{-- TOPBAR --}}
        @include('layouts.partials.egresso_topbar')

        {{-- CONTENT --}}
        <div class="content-wrapper">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')

        </div>

        {{-- FOOTER --}}
        <footer class="footer-system py-3 mt-auto" style="flex-shrink: 0;">
            <div class="container-fluid px-4">
                <div class="text-center">
                    <small class="footer-text">
                        <img src="{{ asset('uploads/logos/Universidade-Logotipo.png') }}"
                             alt="UniLuanda"
                             style="height:20px;width:20px;object-fit:contain;display:inline-block;margin-right:6px;vertical-align:middle;border-radius:4px;">
                        &copy; {{ date('Y') }} UniLuanda - Sistema de Controlo e LocalizaÃ§Ã£o de Ex-Estudantes
                        <span class="mx-2 text-muted">|</span>
                        <span class="text-muted">VersÃ£o 1.0</span>
                    </small>
                </div>
            </div>
        </footer>

    </div>

</div>

{{-- CHATBOT --}}
@include('components.chatbot-floating', ['chatbotRole' => 'egresso'])

{{-- Bootstrap 5 --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')


{{-- ============================================================
     MODAL — CHAMADA RECEBIDA
============================================================ --}}
<div class="modal fade" id="modalChamadaRecebida" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden; border: none;">

            <div class="call-incoming-header">
                <div class="call-ripple"></div>
                <div class="call-ripple call-ripple-2"></div>
                <div class="call-avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="modal-body text-center py-4">
                <h5 class="fw-bold mb-1" id="chamadaNome">A chamar…</h5>
                <p class="text-muted mb-0 small" id="chamadaTipo">Chamada de voz</p>
            </div>

            <div class="modal-footer border-0 d-flex justify-content-center gap-3 pb-4">
                <button type="button" class="btn btn-danger rounded-circle call-action-btn" id="btnRecusarChamada" title="Recusar">
                    <i class="fas fa-phone-slash"></i>
                </button>
                <button type="button" class="btn btn-success rounded-circle call-action-btn" id="btnAceitarChamada" title="Atender">
                    <i class="fas fa-phone"></i>
                </button>
            </div>

        </div>
    </div>
</div>

</body>

</html>
