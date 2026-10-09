<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UniLuanda Alumni')</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- App CSS + JS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="layout-public" data-panel="public">

    <div id="app">
        @yield('content')
    </div>

    <!-- RODAPÉ GLOBAL -->
    <footer class="global-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-left">
                    <strong>UniLuanda Alumni Track</strong>
                    <span class="footer-sep">·</span>
                    <span>© {{ date('Y') }} Universidade de Luanda</span>
                </div>

                <div class="footer-right">
                    <a href="{{ route('verificacao.index') }}" class="footer-link">
                        <i class="fas fa-shield-alt"></i>
                        Verificar Documento
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>