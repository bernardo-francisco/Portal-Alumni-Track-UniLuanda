<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Relatório')</title>

    {{-- ============================================================
         CSS DO PDF
         DomPDF não suporta CSS externo, variáveis CSS, flex ou grid.
         Todo o CSS vive aqui, em modo "primitivo".
    ============================================================ --}}
    <style>
        /* ============================================================
           1. CONFIGURAÇÃO DA PÁGINA
        ============================================================ */
        @page {
            margin: 110px 35px 70px 35px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            margin: 0;
            line-height: 1.4;
            background: #ffffff;
        }

        /* ============================================================
           2. CABEÇALHO FIXO
        ============================================================ */
        .doc-header {
            position: fixed;
            top: -95px;
            left: 0;
            right: 0;
            height: 90px;
            padding: 0;
            border-bottom: 3px solid #c9a227;
        }

        .doc-header-table { width: 100%; border-collapse: collapse; }
        .doc-header-table td { vertical-align: middle; padding: 0; }

        .doc-header-logo { width: 55px; }
        .doc-header-logo img { width: 50px; height: 50px; object-fit: contain; }

        .doc-header-logo .logo-fallback {
            width: 50px; height: 50px;
            border-radius: 50%;
            background: #0a0a0a; color: #c9a227;
            font-size: 20px; font-weight: bold;
            text-align: center; line-height: 50px;
            border: 2px solid #c9a227;
        }

        .doc-header-text { padding-left: 12px !important; }

        .doc-header-text .brand {
            font-size: 13px; font-weight: bold;
            color: #0a0a0a; margin: 0; line-height: 1.2;
        }

        .doc-header-text .subtitle {
            font-size: 8.5px; color: #525252;
            margin: 1px 0 0 0; line-height: 1.2;
        }

        .doc-header-meta {
            text-align: right; font-size: 8px;
            color: #525252; line-height: 1.3;
        }
        .doc-header-meta strong { color: #c9a227; }

        /* ============================================================
           3. RODAPÉ FIXO
        ============================================================ */
        .doc-footer {
            position: fixed;
            bottom: -55px; left: 0; right: 0;
            height: 50px; padding-top: 8px;
            border-top: 1px solid #e6e2d3;
            font-size: 8px; color: #8a8a8a;
        }

        .doc-footer-table {
            width: 100%; border-collapse: collapse;
            table-layout: fixed;
        }
        .doc-footer-table td { vertical-align: middle; padding: 0; }

        .doc-footer-left { text-align: left; width: 33%; }
        .doc-footer-left strong { color: #c9a227; }

        .doc-footer-center {
            text-align: center; width: 34%; color: #525252;
        }

        .doc-footer-right { text-align: right; width: 33%; }

        .doc-footer .page-number::after { content: counter(page); }
        .doc-footer .page-total::after  { content: counter(pages); }

        /* ============================================================
           4. TÍTULO DO DOCUMENTO
        ============================================================ */
        .doc-title {
            text-align: center;
            font-size: 15px; font-weight: bold;
            color: #0a0a0a;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 9.5px; color: #525252;
            font-style: italic;
            margin: 0 0 20px 0;
        }

        /* ============================================================
           5. CARTÕES DE ESTATÍSTICA
        ============================================================ */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .stats-table td.stat-box {
            background: #faf9f5;
            border: 1px solid #e6e2d3;
            border-left: 4px solid #c9a227;
            border-radius: 6px;
            padding: 10px 12px;
            vertical-align: middle;
        }

        .stats-table td.stat-box-success { border-left-color: #16a34a; }
        .stats-table td.stat-box-danger  { border-left-color: #dc2626; }
        .stats-table td.stat-box-warning { border-left-color: #d97706; }
        .stats-table td.stat-box-info    { border-left-color: #0a0a0a; }

        .stat-value {
            display: block;
            font-size: 16px; font-weight: bold;
            color: #c9a227;
            line-height: 1; margin-bottom: 4px;
        }

        .stat-box-success .stat-value { color: #16a34a; }
        .stat-box-danger  .stat-value { color: #dc2626; }
        .stat-box-warning .stat-value { color: #d97706; }
        .stat-box-info    .stat-value { color: #0a0a0a; }

        .stat-label {
            display: block;
            font-size: 7.5px; color: #525252;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
        }

        /* ============================================================
           6. TÍTULO DE SECÇÃO
        ============================================================ */
        .section-title {
            font-size: 11px; font-weight: bold;
            color: #0a0a0a;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 7px 12px;
            background: #fbf6e3;
            border-left: 4px solid #c9a227;
            border-radius: 4px;
            margin: 15px 0 10px 0;
        }

        /* ============================================================
           7. TABELAS DE RELATÓRIO
        ============================================================ */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }

        table.report-table thead { display: table-header-group; }

        table.report-table thead th {
            background: #0a0a0a;
            color: #c9a227;
            font-size: 7.5px; font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 7px 5px;
            text-align: left;
            border: 1px solid #0a0a0a;
            vertical-align: middle;
        }

        table.report-table thead th.text-center { text-align: center; }

        table.report-table tbody td {
            padding: 6px 5px;
            font-size: 8px; color: #1a1a1a;
            border: 1px solid #e6e2d3;
            vertical-align: middle;
            word-wrap: break-word;
        }

        table.report-table tbody tr:nth-child(even) td { background: #faf9f5; }

        /* ============================================================
           8. AVATARES
        ============================================================ */
        .foto-wrap {
            display: inline-block;
            width: 24px; height: 24px;
            border-radius: 50%;
            overflow: hidden;
            background: #fbf6e3; color: #a67c00;
            text-align: center; line-height: 24px;
            font-weight: bold; font-size: 9px;
            border: 1px solid #e6e2d3;
        }

        .foto-img {
            width: 24px; height: 24px;
            object-fit: cover; display: block;
        }

        /* ============================================================
           9. BADGES DE ESTADO
        ============================================================ */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 6.5px; font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .badge-success, .badge-active, .badge-full_time, .badge-approved {
            background: #d1e7dd; color: #0f5132;
        }
        .badge-warning, .badge-part_time, .badge-pendente, .badge-pending {
            background: #fff3cd; color: #664d03;
        }
        .badge-danger, .badge-inactive, .badge-rejected, .badge-rejeitado {
            background: #f8d7da; color: #842029;
        }
        .badge-primary, .badge-freelance, .badge-entrevista {
            background: #fbf6e3; color: #a67c00;
        }
        .badge-secondary, .badge-student, .badge-unknown {
            background: #e2e3e5; color: #41464b;
        }
        .badge-info, .badge-self_employed, .badge-em_analise {
            background: #f5f4ef; color: #1a1a1a;
        }

        /* ============================================================
           10. UTILITÁRIOS
        ============================================================ */
        .text-muted  { color: #525252 !important; }
        .text-center { text-align: center !important; }
        .text-end    { text-align: right !important; }
        .fw-bold     { font-weight: bold !important; }
        .nowrap      { white-space: nowrap; }
        .page-break  { page-break-after: always; }
        .mt-3        { margin-top: 15px; }
        .mb-3        { margin-bottom: 15px; }

        /* ============================================================
           11. COMPROVATIVO DE INSCRIÇÃO
        ============================================================ */

        /* --- Caixa de estado (confirmada / rejeitada / pendente) --- */
        .status-box {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .status-box.confirmed { background: #d1e7dd; border: 2px dashed #198754; }
        .status-box.confirmed .status-icon { color: #198754; }
        .status-box.confirmed .status-text { color: #0f5132; }

        .status-box.rejected { background: #f8d7da; border: 2px dashed #dc3545; }
        .status-box.rejected .status-icon { color: #dc3545; }
        .status-box.rejected .status-text { color: #842029; }

        .status-box.pending { background: #fff3cd; border: 2px dashed #ffc107; }
        .status-box.pending .status-icon { color: #ffc107; }
        .status-box.pending .status-text { color: #664d03; }

        .status-icon {
            font-size: 28px;
            margin-bottom: 6px;
        }

        .status-text {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* --- Cartão de informação --- */
        .info-card {
            background: #faf9f5;
            border-left: 5px solid #c9a227;
            border-radius: 8px;
            padding: 20px 25px;
            margin-bottom: 25px;
        }

        .info-card .label {
            font-size: 8.5px;
            color: #525252;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .info-card .value {
            font-size: 12px;
            color: #1a1a1a;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .info-card .value:last-child { margin-bottom: 0; }

        /* --- Tabela de detalhes --- */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .details-table tr td {
            padding: 10px 12px;
            border-bottom: 1px solid #e6e2d3;
            font-size: 11px;
        }

        .details-table tr td:first-child {
            width: 35%;
            color: #525252;
            font-weight: 600;
        }

        .details-table tr td:last-child {
            color: #1a1a1a;
            font-weight: bold;
        }

        .details-table tr:last-child td { border-bottom: none; }

        /* --- Caixa do código + QR Code --- */
        .codigo-box {
            background: #fbf6e3;
            border: 1px solid #e6e2d3;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 25px 0;
        }

        .codigo-box table { width: 100%; border-collapse: collapse; }

        .codigo-box .codigo-left {
            vertical-align: middle;
            text-align: left;
            padding-right: 15px;
        }

        .codigo-box .codigo-right {
            vertical-align: middle;
            text-align: right;
            width: 130px;
        }

        .codigo-box .codigo-label {
            font-size: 9px;
            color: #525252;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .codigo-box .codigo-value {
            font-size: 16px;
            font-weight: bold;
            color: #c9a227;
            letter-spacing: 3px;
            font-family: 'DejaVu Sans Mono', monospace;
            margin-bottom: 6px;
        }

        .codigo-box .codigo-help {
            font-size: 8px;
            color: #525252;
            line-height: 1.4;
        }

        .codigo-box .codigo-help strong {
            color: #a67c00;
            font-family: 'DejaVu Sans Mono', monospace;
            word-break: break-all;
        }

        .codigo-box img.qr-code {
            width: 110px;
            height: 110px;
            display: block;
            margin-left: auto;
        }

        /* --- Aviso --- */
        .aviso {
            margin-top: 30px;
            padding: 12px 16px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 6px;
            font-size: 9.5px;
            color: #664d03;
            line-height: 1.6;
        }

        .aviso strong {
            display: block;
            margin-bottom: 4px;
            font-size: 10px;
        }

        /* ============================================================
           12. CERTIFICADO DE PARTICIPAÇÃO
        ============================================================ */

        /* --- QR Code + código de verificação --- */
        .qr-code {
            text-align: center;
            margin-bottom: 15px;
        }

        .qr-code img {
            width: 85px; height: 85px;
            display: block;
            margin: 0 auto 6px;
        }

        .qr-code-label {
            font-size: 8px; color: #8a8a8a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }

        .qr-code-value {
            display: block;
            font-size: 12px; color: #c9a227;
            letter-spacing: 3px;
            font-family: 'DejaVu Sans Mono', monospace;
            margin-bottom: 4px;
        }

        .qr-code-url {
            display: block;
            font-size: 7px; color: #8a8a8a;
            word-break: break-all;
            max-width: 180px;
            margin: 0 auto;
        }

        /* --- Corpo do certificado --- */
        .certificado-wrapper {
            padding: 30px 20px;
            text-align: center;
        }

        .certificado-selo {
            display: inline-block;
            width: 80px; height: 80px;
            border-radius: 50%;
            background: #0a0a0a; color: #c9a227;
            font-size: 2rem; line-height: 80px;
            text-align: center;
            margin-bottom: 25px;
            border: 3px solid #c9a227;
            box-shadow: 0 6px 20px rgba(201,162,39,.35);
        }

        .certificado-intro {
            font-size: 13px; color: #525252;
            font-style: italic;
            margin-bottom: 25px;
            letter-spacing: .5px;
        }

        .certificado-nome {
            display: inline-block;
            font-size: 28px; font-weight: bold;
            color: #0a0a0a;
            padding: 8px 30px;
            border-bottom: 3px solid #c9a227;
            margin: 15px 0 30px 0;
            letter-spacing: .5px;
        }

        .certificado-corpo {
            font-size: 13px; color: #404040;
            line-height: 1.9;
            margin-bottom: 25px;
        }

        .certificado-evento {
            display: inline-block;
            padding: 15px 35px;
            background: #fbf6e3;
            border-left: 5px solid #c9a227;
            border-radius: 6px;
            margin: 15px 0;
        }

        .certificado-evento-titulo {
            font-size: 20px; font-weight: bold;
            color: #0a0a0a; margin: 0;
        }

        .certificado-info {
            font-size: 11px; color: #525252;
            margin-top: 20px;
        }

        .certificado-info strong { color: #c9a227; }

        .certificado-assinaturas {
            margin-top: 70px;
            padding-top: 20px;
        }

        .certificado-assinatura {
            display: inline-block;
            width: 42%;
            margin: 0 3%;
            vertical-align: top;
            text-align: center;
        }

        .certificado-assinatura-linha {
            border-top: 1px solid #a3a3a3;
            padding-top: 8px;
            margin-top: 40px;
        }

        .certificado-assinatura-nome {
            font-size: 11px; font-weight: bold;
            color: #0a0a0a;
            margin-bottom: 3px;
        }

        .certificado-assinatura-cargo {
            font-size: 9px; color: #525252;
            margin: 0;
        }

        .certificado-codigo {
            margin-top: 45px;
            padding: 8px 15px;
            background: #faf9f5;
            border: 1px solid #e6e2d3;
            border-radius: 6px;
            display: inline-block;
            font-size: 9px; color: #525252;
        }

        .certificado-codigo strong {
            color: #c9a227;
            letter-spacing: 1.5px;
            font-size: 11px;
        }
    </style>

    {{-- Estilos específicos por PDF (uso pontual) --}}
    @stack('styles')
</head>
<body>

{{-- ============================================================
     CABEÇALHO FIXO
============================================================ --}}
<div class="doc-header">
    <table class="doc-header-table">
        <tr>
            <td class="doc-header-logo">
                @php
                    $logoPath = public_path('uploads/home/uniluanda.webp');
                    $logoBase64 = file_exists($logoPath)
                        ? 'data:image/webp;base64,' . base64_encode(file_get_contents($logoPath))
                        : null;
                @endphp

                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="UniLuanda">
                @else
                    <div class="logo-fallback">UL</div>
                @endif
            </td>

            <td class="doc-header-text">
                <p class="brand">Universidade de Luanda</p>
                <p class="subtitle">Sistema de Controlo e Localização de Ex-Estudantes</p>
            </td>

            <td class="doc-header-meta">
                <strong>@yield('report_name', 'Relatório')</strong><br>
                Gerado em {{ now()->format('d/m/Y H:i') }}<br>
                @hasSection('header_meta')
                    @yield('header_meta')
                @else
                    Documento oficial
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- ============================================================
     RODAPÉ FIXO
============================================================ --}}
<div class="doc-footer">
    <table class="doc-footer-table">
        <tr>
            <td class="doc-footer-left">
                <strong>UniLuanda Alumni Track</strong>
            </td>
            <td class="doc-footer-center">
                Página <span class="page-number"></span> de <span class="page-total"></span>
            </td>
            <td class="doc-footer-right">
                @yield('footer_right', 'Documento gerado automaticamente')
            </td>
        </tr>
    </table>
</div>

{{-- ============================================================
     CONTEÚDO
============================================================ --}}
<main>
    @yield('content')
</main>

{{-- ============================================================
     PAGINAÇÃO NATIVA DOMPDF
============================================================ --}}
<script type="text/php">
    if (isset($pdf)) {
        $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
        $size = 8;
        $font = $fontMetrics->getFont("DejaVu Sans", "bold");
        $width = $fontMetrics->getTextWidth($text, $font, $size);

        $x = $pdf->get_width() - 35 - $width - 10;
        $y = $pdf->get_height() - 42;

        $pdf->filled_rectangle($x - 6, $y - 3, $width + 12, 14, [0.97, 0.97, 0.98]);
        $pdf->rectangle($x - 6, $y - 3, $width + 12, 14, [0.87, 0.88, 0.90], 0.5);
        $pdf->page_text($x, $y, $text, $font, $size, [0.79, 0.63, 0.15]); // dourado UniLuanda
    }
</script>

</body>
</html>