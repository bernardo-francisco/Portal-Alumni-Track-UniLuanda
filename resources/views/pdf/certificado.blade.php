<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Certificado — {{ $evento->titulo }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            color: #1a1a1a;
            background: #ffffff;
        }

        .folha {
            position: relative;
            width: 297mm;
            height: 209mm;
            overflow: hidden;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        .moldura {
            position: absolute;
            top: 8mm;
            left: 8mm;
            width: 279.5mm;
            height: 191.5mm;
            border: 2.5px solid #c9a227;
        }

        /* Logotipo (topo esquerdo) */
        .logo {
            position: absolute;
            top: 10mm;
            left: 14mm;
            width: 21mm;
            height: 28mm;
        }

        .logo img {
            display: block;
            width: 21mm;
            height: 28mm;
        }

        /* QR (topo direito) */
        .qr-box {
            position: absolute;
            top: 10mm;
            right: 14mm;
            width: 55mm;
            text-align: center;
        }

        .qr-box img {
            display: block;
            width: 22mm;
            height: 22mm;
            margin: 0 auto 1mm;
        }

        .qr-label {
            display: block;
            font-size: 6pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #737373;
            line-height: 1.2;
        }

        .qr-valor {
            display: block;
            font-size: 9pt;
            letter-spacing: 2px;
            color: #1a1a1a;
            line-height: 1.3;
        }

        .qr-url {
            display: block;
            font-size: 5.5pt;
            color: #737373;
            line-height: 1.2;
        }

        /* Corpo central */
        .corpo {
            position: absolute;
            top: 42mm;
            left: 0;
            width: 100%;
            text-align: center;
        }

        .intro {
            font-size: 11pt;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #525252;
            margin: 0 0 5mm;
        }

        .nome {
            font-size: 26pt;
            font-weight: bold;
            margin: 0 0 5mm;
            line-height: 1.15;
        }

        .participou {
            font-size: 10pt;
            color: #525252;
            margin: 0 0 3mm;
        }

        .evento-titulo {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 3mm;
            line-height: 1.2;
        }

        .descricao {
            font-size: 8.5pt;
            color: #525252;
            line-height: 1.35;
            margin: 0 auto 4mm;
            padding: 0 30mm;
        }

        .info {
            font-size: 9pt;
            color: #404040;
            padding: 0 12mm;
        }

        /* Assinaturas */
        .assinatura {
            position: absolute;
            bottom: 24mm;
            width: 85mm;
            text-align: center;
        }

        .assinatura.esq { left: 35mm; }
        .assinatura.dir { right: 35mm; }

        .assinatura-linha {
            border-top: 1px solid #1a1a1a;
            margin-bottom: 2mm;
            height: 0;
        }

        .assinatura-nome {
            font-size: 10pt;
            font-weight: bold;
            line-height: 1.3;
        }

        .assinatura-cargo {
            font-size: 8pt;
            color: #737373;
            line-height: 1.3;
        }

        /* Código no rodapé */
        .codigo-rodape {
            position: absolute;
            bottom: 9mm;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #737373;
        }

        .codigo-rodape strong {
            color: #1a1a1a;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
@php
    $urlVerificacao = url("/verificar/{$codigo}");
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=' . urlencode($urlVerificacao);

    // Logotipo embutido em base64
    $logoPath = public_path('uploads/logos/Universidade-Logotipo.png');
    $logoSrc  = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;
@endphp

<div class="folha">
    <div class="moldura">

        {{-- Topo --}}
        @if($logoSrc)
            <div class="logo">
                <img src="{{ $logoSrc }}" alt="Universidade de Luanda">
            </div>
        @endif

        <div class="qr-box">
            <img src="{{ $qrCodeUrl }}" alt="QR Code" width="83" height="83">
            <span class="qr-label">Código de Verificação</span>
            {{--  <strong class="qr-valor">{{ $codigo }}</strong>
            <span class="qr-url">{{ $urlVerificacao }}</span> --}}
        </div>

        {{-- Corpo --}}
        <div class="corpo">
            <p class="intro">A Universidade de Luanda certifica que</p>

            <div class="nome">{{ $inscricao->egresso->nome_completo }}</div>

            <p class="participou">participou com êxito no evento</p>

            <div class="evento-titulo">{{ $evento->titulo }}</div>

            @if($evento->descricao)
                <div class="descricao">
                    {{ \Illuminate\Support\Str::limit($evento->descricao, 160) }}
                </div>
            @endif

            <div class="info">
                <strong>Data:</strong>
                @if($evento->data_inicio)
                    {{ $evento->data_inicio->translatedFormat('d \d\e F \d\e Y') }}
                    @if($evento->data_fim && $evento->data_fim != $evento->data_inicio)
                        · até {{ $evento->data_fim->translatedFormat('d \d\e F \d\e Y') }}
                    @endif
                @else
                    Data a definir
                @endif
                @if($evento->local)
                    &nbsp;·&nbsp;<strong>Local:</strong> {{ $evento->local }}
                @endif
                @if($evento->categoria)
                    &nbsp;·&nbsp;<strong>Categoria:</strong> {{ ucfirst(str_replace('_', ' ', $evento->categoria)) }}
                @endif
            </div>
        </div>

        {{-- Assinaturas --}}
        <div class="assinatura esq">
            <div class="assinatura-linha"></div>
            <div class="assinatura-nome">Coordenação</div>
            <div class="assinatura-cargo">Universidade de Luanda</div>
        </div>

        <div class="assinatura dir">
            <div class="assinatura-linha"></div>
            <div class="assinatura-nome">{{ now()->format('d/m/Y') }}</div>
            <div class="assinatura-cargo">Data de Emissão</div>
        </div>

        {{-- Rodapé --}}
        <div class="codigo-rodape">
            Código de verificação: <strong>{{ $codigo }}</strong>
        </div>

    </div>
</div>
</body>
</html>