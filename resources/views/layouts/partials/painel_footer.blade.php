{{-- ============================================================
     FOOTER UNIFICADO DOS PAINÉIS
     Uso: @include('layouts.partials.painel_footer', ['contexto' => 'admin'])
     Valores: 'admin', 'egresso', 'empresa'
============================================================ --}}

@php
    $contexto = $contexto ?? 'admin';

    $descricoes = [
        'admin'   => 'Painel Administrativo',
        'egresso' => 'Portal do Egresso',
        'empresa' => 'Painel da Empresa',
    ];

    $descricao = $descricoes[$contexto] ?? 'Painel';
@endphp

<footer class="painel-footer">
    <div class="container-fluid px-4">
        <div class="painel-footer-content">

            {{-- Lado esquerdo: logo + copyright --}}
            <small class="painel-footer-left">
                <img src="{{ asset('uploads/logos/Universidade-Logotipo.png') }}"
                     alt="UniLuanda"
                     class="painel-footer-logo">
                <span class="painel-footer-text">
                    &copy; {{ date('Y') }}
                    <strong>UniLuanda</strong>
                    <span class="painel-footer-sep">—</span>
                    {{ $descricao }}
                </span>
            </small>

            {{-- Lado direito: versão + links --}}
            <small class="painel-footer-right">
                <span class="painel-footer-version">
                
                Versão 1.0
                </span> 
                
            </small>

        </div>
    </div>
</footer>