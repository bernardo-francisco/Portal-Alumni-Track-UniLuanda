<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Track | Portal do Egresso</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ============================================================
         CSS DO HERO FULLWIDTH COM CARROSSEL DE FUNDO — CENTRALIZADO
    ============================================================ --}}
    <style>
        /* HERO FULLWIDTH */
        .hero-fullwidth {
            position: relative;
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        /* CARROSSEL DE FUNDO */
        .hero-bg-carousel {
            position: absolute;
            inset: 0;
            z-index: 0;
            width: 100%;
            height: 100%;
        }
        .hero-bg-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transform: scale(1.08);
            transition: opacity 1.2s ease, transform 6s ease;
            pointer-events: none;
        }
        .hero-bg-slide.active {
            opacity: 1;
            transform: scale(1);
            z-index: 1;
        }
        .hero-bg-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* OVERLAY — escurece ao centro para destacar o texto */
        .hero-bg-overlay {
            position: absolute;
            inset: 0;
            z-index: 2;
            background:
                radial-gradient(ellipse at center,
                    rgba(10, 15, 30, 0.75) 0%,
                    rgba(10, 15, 30, 0.65) 40%,
                    rgba(10, 15, 30, 0.55) 100%);
            pointer-events: none;
        }

        /* ============================================================
           CONTEÚDO CENTRALIZADO
        ============================================================ */
        .hero-fullwidth-content {
            position: relative;
            z-index: 3;
            width: 100%;
            padding: 7rem 6%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .hero-center-wrapper {
            max-width: 900px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        /* ============================================================
           BADGE
        ============================================================ */
        .glass-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        /* ============================================================
           TÍTULO
        ============================================================ */
        .hero-title {
            font-size: clamp(2.2rem, 5vw, 4rem);
            line-height: 1.12;
            color: #fff;
            margin: 0;
            font-weight: 800;
            letter-spacing: -1px;
            text-shadow:
                0 4px 20px rgba(0, 0, 0, 0.5),
                0 2px 6px rgba(0, 0, 0, 0.35);
        }
        .hero-gradient-text {
            background: linear-gradient(135deg, #facc15, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }

        /* ============================================================
           SUBTÍTULO — CENTRADO
        ============================================================ */
        .hero-subtitle-wrapper {
            max-width: 720px;
            padding: 0;
            margin: 0;
        }
        .hero-subtitle {
            font-size: clamp(1rem, 1.3vw, 1.18rem);
            color: #ffffff !important;
            line-height: 1.75;
            margin: 0;
            font-weight: 400;
            letter-spacing: 0.15px;
            text-shadow:
                0 2px 10px rgba(0, 0, 0, 0.6),
                0 1px 3px rgba(0, 0, 0, 0.4);
            opacity: 0.95;
        }

        /* ============================================================
           BOTÃO DE VIDRO
        ============================================================ */
        .explorar-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 0.5rem;
        }
        .glass-btn {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            transition: all 0.3s ease;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.2);
        }
        .glass-btn:hover {
            background: rgba(255, 255, 255, 0.22);
            transform: translateY(-3px);
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.3);
            color: #fff;
        }

        /* ============================================================
           CARDS FLUTUANTES — EM LINHA, CENTRADOS
        ============================================================ */
        .hero-floating-cards {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 1.5rem;
            width: 100%;
        }
        .floating-glass-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 22px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.28);
            box-shadow:
                0 10px 32px rgba(0, 0, 0, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.35);
            color: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            min-width: 220px;
            text-align: left;
        }
        .floating-glass-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 16px 40px rgba(0, 0, 0, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.45);
        }
        .floating-glass-card h5 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
            letter-spacing: -0.3px;
        }
        .floating-glass-card p {
            font-size: 0.75rem;
            margin: 0;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }
        .floating-glass-card .icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
            flex-shrink: 0;
        }

        /* ============================================================
           DOTS
        ============================================================ */
        .hero-dots {
            position: absolute;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            z-index: 4;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 90vw;
        }
        .hero-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            padding: 0;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        .hero-dot.active {
            width: 22px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.9);
        }

        /* ============================================================
           RESPONSIVO
        ============================================================ */
        @media (max-width: 992px) {
            .hero-fullwidth-content {
                padding: 6rem 5%;
            }
            .hero-center-wrapper {
                gap: 1.25rem;
            }
            .hero-floating-cards {
                flex-direction: column;
                align-items: center;
            }
            .floating-glass-card {
                width: 100%;
                max-width: 360px;
            }
        }

        @media (max-width: 576px) {
            .hero-fullwidth-content {
                padding: 5rem 5%;
            }
            .hero-title {
                letter-spacing: -0.5px;
            }
            .hero-subtitle {
                font-size: 0.95rem;
                line-height: 1.65;
            }
            .glass-badge {
                font-size: 0.75rem;
                padding: 6px 14px;
            }
            .hero-dot { width: 6px; height: 6px; }
            .hero-dot.active { width: 18px; }
            .hero-dots {
                gap: 4px;
                padding: 6px 10px;
                bottom: 20px;
            }
        }

        /* ============================================================
           ESTATÍSTICAS — GRID EQUILIBRADO (4 CARDS)
        ============================================================ */
        .quick-stats {
            padding: 4rem 6% !important;
            background: var(--surface);
        }

        .stats-grid-user {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            align-items: stretch;
        }

        /* Tablet: 2 por linha */
        @media (max-width: 992px) {
            .stats-grid-user {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
        }

        /* Mobile: 1 por linha */
        @media (max-width: 576px) {
            .stats-grid-user {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>
<body
    data-rota-pesquisa-egressos="{{ route('pesquisar.egressos-publicos') }}"
    data-rota-filtros-egressos="{{ route('pesquisar.egressos-filtros') }}"
    data-rota-login="{{ route('login') }}">

    {{-- ============================================================
         NAVBAR
    ============================================================ --}}
    <nav class="user-navbar" id="navbar">
        <div class="nav-container">
            <a href="{{ route('home') }}" class="brand-logo">
                <img src="{{ asset('uploads/home/uniluanda.webp') }}"
                     alt="Universidade de Luanda"
                     class="brand-logo-img">
                <span class="brand-logo-text">Universidade de Luanda</span>
            </a>

            <ul class="nav-links" id="navLinks">
                <li><a href="#inicio"        class="active nav-link" data-target="inicio"><i class="fas fa-home"></i> Início</a></li>
                <li><a href="#comunidade"    class="nav-link" data-target="comunidade"><i class="fas fa-users"></i> Comunidade</a></li>
                <li><a href="#oportunidades" class="nav-link" data-target="oportunidades"><i class="fas fa-briefcase"></i> Oportunidades</a></li>
                <li><a href="#eventos"       class="nav-link" data-target="eventos"><i class="fas fa-calendar"></i> Eventos</a></li>
                <li><a href="#sobre"         class="nav-link" data-target="sobre"><i class="fas fa-info-circle"></i> Sobre</a></li>
            </ul>

            <div class="nav-actions">
                <a href="{{ route('login') }}" class="btn-nav-outline">
                    <i class="fas fa-sign-in-alt"></i> <span class="btn-text">Entrar</span>
                </a>
                <a href="{{ route('register') }}" class="btn-nav-solid btn-pulse">
                    <i class="fas fa-user-plus"></i> <span class="btn-text">Cadastrar-se</span>
                </a>
            </div>

            <div class="mobile-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>


    {{-- ============================================================
         HERO FULLWIDTH COM CARROSSEL DE FUNDO — CENTRALIZADO
    ============================================================ --}}
    <section class="hero-section hero-fullwidth" id="inicio">

        {{-- CARROSSEL DE FUNDO --}}
        <div class="hero-bg-carousel" id="heroCarousel">
            <div class="hero-bg-slide active">
                <img src="{{ asset('uploads/home/Diplomados.png') }}" alt="Rede de Egressos UniLuanda">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img1.jpg') }}" alt="Cerimónia de Graduação">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img2.jpg') }}" alt="Comunidade Alumni">
            </div>
           
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img4.jpg') }}" alt="Entrega de Diplomas">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img5.jpg') }}" alt="Formatura UniLuanda">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img6.jpg') }}" alt="Cerimónia Oficial">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img7.jpg') }}" alt="Graduados">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img8.jpg') }}" alt="Egressos em Festa">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img9.jpg') }}" alt="Grupo de Egressos">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img11.jpg') }}" alt="Plateia de Formatura">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img12.jpg') }}" alt="Plateia de Formatura">
            </div>
            <div class="hero-bg-slide">
                <img src="{{ asset('uploads/home/img13.jpg') }}" alt="Plateia de Formatura">
            </div>
            <div class="hero-bg-overlay"></div>
        </div>

        {{-- CONTEÚDO SOBREPOSTO — CENTRALIZADO --}}
        <div class="hero-fullwidth-content">

            <div class="hero-center-wrapper">

                {{-- BADGE --}}
                <div class="badge-tag glass-badge animate-on-scroll visible">
                    <i class="fas fa-globe-africa"></i>
                    Rede Global de Egressos
                    <span class="badge bg-primary text-white ms-2" style="font-size: 0.6rem;">
                        +{{ number_format($totalEgressos, 0, ',', '.') }}
                    </span>
                </div>

                {{-- TÍTULO --}}
                <h1 class="hero-title animate-on-scroll visible">
                    A sua trajetória continua
                    <span class="hero-gradient-text">conectada aqui.</span>
                </h1>

                {{-- SUBTÍTULO --}}
                <div class="hero-subtitle-wrapper animate-on-scroll visible">
                    <p class="lead hero-subtitle">
                        Encontre antigos colegas de turma, descubra novas oportunidades
                        de carreira e mantenha o seu vínculo ativo com a sua instituição.
                    </p>
                </div>

                {{-- BOTÃO --}}
                <div class="explorar-wrapper animate-on-scroll visible">
                    <button type="button" class="btn-explorar glass-btn" onclick="abrirModalEgressos()">
                        <span class="btn-explorar-icon">
                            <i class="fas fa-users"></i>
                        </span>
                        <span class="btn-explorar-text">
                            <span class="btn-explorar-title">Explorar Egressos</span>
                            <span class="btn-explorar-subtitle">
                                <i class="fas fa-graduation-cap me-1"></i>
                                Conheça a comunidade UniLuanda
                            </span>
                        </span>
                        <span class="btn-explorar-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </button>
                </div>

            </div>

        </div>

        {{-- DOTS DE NAVEGAÇÃO (13 slides) --}}
        <div class="hero-dots" id="heroDots">
            <button class="hero-dot active" data-slide="0"  aria-label="Slide 1"></button>
            <button class="hero-dot"        data-slide="1"  aria-label="Slide 2"></button>
            <button class="hero-dot"        data-slide="2"  aria-label="Slide 3"></button>
            <button class="hero-dot"        data-slide="3"  aria-label="Slide 4"></button>
            <button class="hero-dot"        data-slide="4"  aria-label="Slide 5"></button>
            <button class="hero-dot"        data-slide="5"  aria-label="Slide 6"></button>
            <button class="hero-dot"        data-slide="6"  aria-label="Slide 7"></button>
            <button class="hero-dot"        data-slide="7"  aria-label="Slide 8"></button>
            <button class="hero-dot"        data-slide="8"  aria-label="Slide 9"></button>
            <button class="hero-dot"        data-slide="9"  aria-label="Slide 10"></button>
            <button class="hero-dot"        data-slide="10" aria-label="Slide 11"></button>
            <button class="hero-dot"        data-slide="11" aria-label="Slide 12"></button>
            <button class="hero-dot"        data-slide="12" aria-label="Slide 13"></button>
        </div>

    </section>


    {{-- ============================================================
         ESTATÍSTICAS
    ============================================================ --}}
    <section class="quick-stats" style="background: var(--surface); padding: 4rem 6%;">
        <div class="stats-grid-user" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; max-width: 1400px; margin: 0 auto;">

            <div class="stat-box-modern animate-on-scroll">
                <div class="stat-icon-wrapper stat-float-icon" style="background: rgba(26, 86, 219, 0.1);">
                    <i class="fas fa-users text-primary"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="counter-number mb-1" data-target="{{ $totalEgressos }}">0</h3>
                    <p style="color: var(--text-muted); font-weight: 600; margin-bottom: 0.5rem;">Egressos Conectados</p>
                    <div class="progress" style="height: 4px; border-radius: 4px;">
                        <div class="progress-bar bg-primary progress-animated" style="width: 0%;" data-progress="85"></div>
                    </div>
                </div>
            </div>

            <div class="stat-box-modern animate-on-scroll delay-1">
                <div class="stat-icon-wrapper stat-float-icon" style="background: rgba(14, 165, 233, 0.1);">
                    <i class="fas fa-building text-info"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="counter-number mb-1" data-target="{{ $totalEmpresas }}">0</h3>
                    <p style="color: var(--text-muted); font-weight: 600; margin-bottom: 0.5rem;">Empresas Parceiras</p>
                    <div class="progress" style="height: 4px; border-radius: 4px;">
                        <div class="progress-bar bg-info progress-animated" style="width: 0%;" data-progress="70"></div>
                    </div>
                </div>
            </div>

            <div class="stat-box-modern animate-on-scroll delay-2">
                <div class="stat-icon-wrapper stat-float-icon" style="background: rgba(245, 158, 11, 0.1);">
                    <i class="fas fa-map-marked-alt text-warning"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="counter-number mb-1" data-target="{{ $totalPaises }}">0</h3>
                    <p style="color: var(--text-muted); font-weight: 600; margin-bottom: 0.5rem;">Países Alcançados</p>
                    <div class="progress" style="height: 4px; border-radius: 4px;">
                        <div class="progress-bar bg-warning progress-animated" style="width: 0%;" data-progress="60"></div>
                    </div>
                </div>
            </div>

            <div class="stat-box-modern animate-on-scroll delay-3">
                <div class="stat-icon-wrapper stat-float-icon" style="background: rgba(34, 197, 94, 0.1);">
                    <i class="fas fa-briefcase text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <h3 class="counter-number mb-1" data-target="{{ $taxaEmpregabilidade }}">
                        0<span style="font-size: 1.25rem;">%</span>
                    </h3>
                    <p style="color: var(--text-muted); font-weight: 600; margin-bottom: 0.5rem;">Taxa de Empregabilidade</p>
                    <div class="progress" style="height: 4px; border-radius: 4px;">
                        <div class="progress-bar bg-success progress-animated"
                             style="width: 0%;"
                             data-progress="{{ $taxaEmpregabilidade }}"></div>
                    </div>
                </div>
            </div>

        </div>
    </section>


    {{-- ============================================================
         RECURSOS
    ============================================================ --}}
    <section class="user-features" id="comunidade">
        <div class="section-head animate-on-scroll">
            <span class="badge-tag" style="margin: 0 auto 16px;">
                <i class="fas fa-rocket"></i> Ferramentas Exclusivas
            </span>
            <h2>O que o Portal oferece para você?</h2>
            <p>Acesse ferramentas exclusivas desenhadas para impulsionar a sua carreira e fortalecer o seu networking.</p>
        </div>

        <div class="cards-interactive-grid">

            <div class="user-card card-3d animate-on-scroll delay-1">
                <div class="card-icon-box"><i class="fas fa-search-location"></i></div>
                <h3>Localizador de Alumni</h3>
                <p>Descubra onde estão trabalhando os seus ex-colegas de turma no mapa interativo por cidade ou país.</p>
                <a href="{{ route('egresso.mapa') }}" class="card-link">
                    Explorar mapa <i class="fas fa-arrow-right"></i>
                </a>
                <div class="mt-3">
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                        <i class="fas fa-users me-1"></i> +{{ number_format($totalEgressos, 0, ',', '.') }} localizados
                    </span>
                </div>
            </div>

            <div class="user-card card-3d animate-on-scroll delay-2">
                <div class="card-icon-box"><i class="fas fa-briefcase"></i></div>
                <h3>Portal de Carreiras</h3>
                <p>Acesse vagas de emprego e estágios disponibilizados diretamente por empresas ou recomendadas por outros egressos.</p>
                <a href="{{ route('egresso.oportunidades') }}" class="card-link">
                    Ver oportunidades <i class="fas fa-arrow-right"></i>
                </a>
                <div class="mt-3">
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                        <i class="fas fa-briefcase me-1"></i> {{ $totalOportunidades }} vagas ativas
                    </span>
                </div>
            </div>

            <div class="user-card card-3d animate-on-scroll delay-3">
                <div class="card-icon-box"><i class="fas fa-user-graduate"></i></div>
                <h3>Atualização de Perfil</h3>
                <p>Mantenha o seu histórico profissional atualizado para ser encontrado por recrutadores e parceiros de negócios.</p>
                <a href="{{ route('egresso.perfil.editar') }}" class="card-link">
                    Atualizar dados <i class="fas fa-arrow-right"></i>
                </a>
                <div class="mt-3">
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                        <i class="fas fa-check-circle me-1"></i> Perfil verificado
                    </span>
                </div>
            </div>

        </div>
    </section>


    {{-- ============================================================
         EGRESSOS EM DESTAQUE
    ============================================================ --}}
    @if(isset($ultimosEgressos) && $ultimosEgressos->count() > 0)

        <section style="padding: 5rem 6%; background: var(--surface-alt);" id="egressos">
            <div class="section-head animate-on-scroll" style="text-align: center;">
                <span class="badge-tag" style="margin: 0 auto 16px;">
                    <i class="fas fa-star"></i> Egressos em Destaque
                </span>
                <h2>Conheça quem está a brilhar</h2>
                <p>Graduados que se destacam no mercado e na comunidade.</p>
            </div>

            <div class="alumni-grid-3">

                @foreach($ultimosEgressos as $index => $egresso)
                    @php
                        $iniciais = collect(explode(' ', trim($egresso->nome_completo ?? 'E')))
                            ->filter()
                            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                            ->take(2)
                            ->implode('');

                        $temFoto = !empty($egresso->foto_url);
                        $fotoUrl = $temFoto
                            ? (filter_var($egresso->foto_url, FILTER_VALIDATE_URL)
                                ? $egresso->foto_url
                                : asset($egresso->foto_url))
                            : null;
                    @endphp

                    <div class="alumni-card-modern animate-on-scroll delay-{{ $index + 1 }}">

                        @if($temFoto)
                            <img src="{{ $fotoUrl }}"
                                 alt="{{ $egresso->nome_completo }}"
                                 class="alumni-avatar-modern"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="alumni-avatar-fallback" style="display: none;">
                                {{ $iniciais }}
                            </div>
                        @else
                            <div class="alumni-avatar-fallback">
                                {{ $iniciais }}
                            </div>
                        @endif

                        <h4 class="alumni-name-modern">
                            {{ $egresso->nome_completo }}
                        </h4>

                        @if($egresso->profissao_atual)
                            <p class="alumni-role-modern">
                                <i class="fas fa-briefcase me-1"></i>
                                {{ $egresso->profissao_atual }}
                            </p>
                        @endif

                        <div class="alumni-info-modern">
                            @if($egresso->curso)
                                <span>
                                    <i class="fas fa-graduation-cap text-primary"></i>
                                    {{ $egresso->curso->nome }}
                                </span>
                            @endif

                            @if($egresso->ano_formatura)
                                <span>
                                    <i class="fas fa-calendar text-info"></i>
                                    Formatura {{ $egresso->ano_formatura }}
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('egresso.egressos.show', $egresso->id) }}"
                           class="alumni-btn-modern">
                            <i class="fas fa-user-plus"></i> Ver Perfil
                        </a>

                    </div>
                @endforeach

            </div>
        </section>

    @endif


    {{-- ============================================================
         OPORTUNIDADES RECENTES
    ============================================================ --}}
    @if(isset($oportunidadesRecentes) && $oportunidadesRecentes->count() > 0)

        <section style="padding: 5rem 6%; background: var(--surface);" id="oportunidades">
            <div class="section-head animate-on-scroll" style="text-align: center;">
                <span class="badge-tag" style="margin: 0 auto 16px;">
                    <i class="fas fa-briefcase"></i> Oportunidades
                </span>
                <h2>Últimas oportunidades de carreira</h2>
                <p>Vagas recomendadas para o seu perfil de formação.</p>
            </div>

            <div class="row g-4" style="max-width: 1300px; margin: 0 auto;">
                @foreach($oportunidadesRecentes as $index => $op)
                    <div class="col-lg-6 animate-on-scroll delay-{{ min($index, 3) }}">
                        <div class="job-card" style="cursor: pointer;" onclick="window.location='{{ route('egresso.oportunidades') }}'">
                            <div class="company-logo">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="job-details">
                                <h4>{{ $op->titulo }}</h4>
                                <div class="company-name">{{ $op->empresa ?? 'Empresa Confidencial' }}</div>
                                <div class="job-tags">
                                    <span class="tag">{{ ucfirst($op->tipo ?? 'Tempo Inteiro') }}</span>
                                    <span class="tag">
                                        <i class="fas fa-map-marker-alt"></i> {{ $op->localizacao ?? 'Luanda' }}
                                    </span>
                                    <span class="tag bg-primary text-white">
                                        <i class="fas fa-clock"></i> {{ $op->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <div class="job-footer">
                                    <span>
                                        <i class="fas fa-users"></i>
                                        {{ $op->candidaturas_count ?? 0 }} candidaturas
                                    </span>
                                    <a href="{{ route('egresso.oportunidades') }}" class="btn btn-primary btn-sm">
                                        Candidatar-se <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

    @endif


    {{-- ============================================================
         EVENTOS
    ============================================================ --}}
    @if(isset($proximosEventos) && $proximosEventos->count() > 0)

        <section class="events-section" id="eventos" style="padding: 5rem 6%; background: var(--surface-alt);">
            <div class="section-head animate-on-scroll" style="text-align: center;">
                <span class="badge-tag" style="margin: 0 auto 16px;">
                    <i class="fas fa-calendar-alt"></i> Agenda
                </span>
                <h2>Próximos Eventos & Workshops</h2>
                <p>Participe em encontros de networking, palestras e feiras de emprego exclusivas.</p>
            </div>

            <div class="events-grid">
                @foreach($proximosEventos as $index => $evento)
                    <div class="event-card animate-on-scroll delay-{{ min($index, 3) }}"
                         style="cursor: pointer;"
                         onclick="window.location='{{ route('egresso.eventos') }}'">

                        <div class="event-date-badge">
                            <span class="day">{{ \Carbon\Carbon::parse($evento->data_inicio)->format('d') }}</span>
                            <span class="month">{{ \Carbon\Carbon::parse($evento->data_inicio)->translatedFormat('M') }}</span>
                        </div>

                        <div class="event-content">
                            <div class="event-type-tag">{{ $evento->tipo ?? 'Webinar' }}</div>
                            <h3>{{ $evento->titulo }}</h3>
                            <p class="event-location">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $evento->local ?? 'Auditório Principal / Online' }}
                            </p>
                            <a href="{{ route('egresso.eventos') }}" class="btn-event-ticket">
                                Garantir Vaga <i class="fas fa-ticket-alt"></i>
                            </a>
                            <div class="mt-2">
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-users me-1"></i>
                                    {{ $evento->inscricoes_count ?? 0 }} inscritos
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

    @endif


    {{-- ============================================================
         TESTEMUNHOS DINÂMICOS
    ============================================================ --}}
    @if(isset($testemunhos) && $testemunhos->count() > 0)

        <section style="padding: 80px 6%; background: var(--surface);">
            <div class="section-head animate-on-scroll" style="text-align: center;">
                <span class="badge-tag" style="margin: 0 auto 16px;">
                    <i class="fas fa-quote-left"></i> Depoimentos
                </span>
                <h2>O que dizem os nossos egressos</h2>
                <p>Histórias reais de quem faz parte da nossa comunidade.</p>
            </div>

            <div class="row g-4" style="max-width: 1300px; margin: 0 auto;">

               @foreach($testemunhos as $index => $testemunho)
    @php
        $egresso = $testemunho->egresso;
        $nomeEgresso = $egresso->nome_completo ?? 'Egresso';
        $iniciais = collect(explode(' ', trim($nomeEgresso)))
            ->filter()
            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
            ->take(2)
            ->implode('');

        $temFoto = !empty($egresso->foto_url);
        $fotoUrl = $temFoto
            ? (filter_var($egresso->foto_url, FILTER_VALIDATE_URL)
                ? $egresso->foto_url
                : asset($egresso->foto_url))
            : null;

        $nomeCurso = $testemunho->curso->nome
            ?? $egresso->curso->nome
            ?? 'Estudante';

        $anoFormatura = $egresso->ano_formatura ?? null;
        $profissao = $egresso->profissao_atual ?? null;

        $coresAvatar = ['primary', 'success', 'warning', 'info', 'danger'];
        $corAvatar = $coresAvatar[$index % count($coresAvatar)];

        $nota = max(1, min(5, (int) ($testemunho->nota ?? 5)));
    @endphp

    <div class="col-md-4 animate-on-scroll delay-{{ $index + 1 }}">
        <div class="testimonial-card p-4 bg-white rounded-4 shadow-sm h-100">

            <div class="d-flex align-items-center gap-3 mb-3">

                @if($temFoto)
                    <img src="{{ $fotoUrl }}"
                         alt="{{ $nomeEgresso }}"
                         class="rounded-circle border border-2 border-white shadow-sm flex-shrink-0"
                         style="width: 50px; height: 50px; object-fit: cover; display: block;"
                         onerror="this.style.display='none'; document.getElementById('testemunho-fallback-{{ $testemunho->id }}').style.display='flex';">

                    <div id="testemunho-fallback-{{ $testemunho->id }}"
                         class="rounded-circle bg-{{ $corAvatar }} align-items-center justify-content-center text-white flex-shrink-0"
                         style="width: 50px; height: 50px; font-weight: bold; font-size: 1.1rem; display: none;">
                        {{ $iniciais }}
                    </div>
                @else
                    <div class="rounded-circle bg-{{ $corAvatar }} d-flex align-items-center justify-content-center text-white flex-shrink-0"
                         style="width: 50px; height: 50px; font-weight: bold; font-size: 1.1rem;">
                        {{ $iniciais }}
                    </div>
                @endif

                <div class="min-width-0">
                    <h6 class="mb-0 fw-bold text-truncate">{{ $nomeEgresso }}</h6>
                    <small class="text-muted d-block text-truncate">
                        {{ $profissao ?? $nomeCurso }}
                        @if($anoFormatura)
                            · {{ $anoFormatura }}
                        @endif
                    </small>
                </div>
            </div>

            @if($testemunho->titulo)
                <h6 class="fw-bold text-dark mb-2" style="font-size: 0.95rem;">
                    {{ $testemunho->titulo }}
                </h6>
            @endif

            <p class="text-muted mb-3" style="font-style: italic; line-height: 1.7;">
                "{{ Str::limit($testemunho->mensagem, 180) }}"
            </p>

            <div class="text-warning">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= $nota ? '' : 'opacity-25' }}"></i>
                @endfor
            </div>

        </div>
    </div>
@endforeach

            </div>
        </section>

    @else

        <section style="padding: 80px 6%; background: var(--surface);">
            <div class="section-head animate-on-scroll" style="text-align: center;">
                <span class="badge-tag" style="margin: 0 auto 16px;">
                    <i class="fas fa-quote-left"></i> Depoimentos
                </span>
                <h2>O que dizem os nossos egressos</h2>
                <p>Histórias reais de quem faz parte da nossa comunidade.</p>
            </div>

            <div class="text-center py-4">
                <div class="empty-state-icon mb-3" style="font-size: 3rem; color: #cbd5e1;">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <h5 class="fw-bold mb-2">Ainda sem depoimentos</h5>
                <p class="text-muted mb-0" style="max-width: 400px; margin: 0 auto;">
                    Em breve teremos histórias de sucesso dos nossos egressos para partilhar contigo.
                </p>
            </div>
        </section>

    @endif


    {{-- ============================================================
         SOBRE NÓS
    ============================================================ --}}
    <section style="padding: 80px 6%; background: var(--surface-alt);" id="sobre">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <div class="row align-items-center g-5">

                <div class="col-lg-6 animate-on-scroll">
                    <span class="badge-tag" style="margin-bottom: 16px;">
                        <i class="fas fa-info-circle"></i> Sobre Nós
                    </span>
                    <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 20px; line-height: 1.2;">
                        Conectando <span style="color: var(--primary);">graduados</span> ao futuro
                    </h2>
                    <p style="font-size: 1.05rem; color: var(--text-muted); line-height: 1.8; margin-bottom: 20px;">
                        O Alumni Track é uma plataforma inovadora que visa conectar ex-estudantes da Universidade de Luanda,
                        promovendo networking, oportunidades de carreira e fortalecendo os laços com a instituição.
                    </p>

                    <div class="row g-3 mt-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>{{ number_format($totalEgressos, 0, ',', '.') }} Egressos conectados</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>{{ $totalEmpresas }} Empresas parceiras</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>{{ $totalPaises }} Países alcançados</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>{{ $taxaEmpregabilidade }}% Taxa de sucesso</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 animate-on-scroll delay-2">
                    <div style="background: var(--surface); border-radius: 20px; padding: 40px; border: 1px solid var(--border-color);">
                        <div class="row g-3">
                            <div class="col-6">
                                <div style="background: var(--surface-alt); padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color);">
                                    <i class="fas fa-users" style="font-size: 2rem; color: var(--primary);"></i>
                                    <h4 style="margin-top: 8px; font-size: 1.1rem;">Comunidade</h4>
                                    <small style="color: var(--text-muted);">Ativa e engajada</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div style="background: var(--surface-alt); padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color);">
                                    <i class="fas fa-briefcase" style="font-size: 2rem; color: var(--success);"></i>
                                    <h4 style="margin-top: 8px; font-size: 1.1rem;">Carreiras</h4>
                                    <small style="color: var(--text-muted);">Oportunidades reais</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div style="background: var(--surface-alt); padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color);">
                                    <i class="fas fa-graduation-cap" style="font-size: 2rem; color: var(--warning);"></i>
                                    <h4 style="margin-top: 8px; font-size: 1.1rem;">Formação</h4>
                                    <small style="color: var(--text-muted);">Contínua e relevante</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div style="background: var(--surface-alt); padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color);">
                                    <i class="fas fa-handshake" style="font-size: 2rem; color: var(--info);"></i>
                                    <h4 style="margin-top: 8px; font-size: 1.1rem;">Networking</h4>
                                    <small style="color: var(--text-muted);">Conexões valiosas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


    {{-- ============================================================
         FOOTER
    ============================================================ --}}
    <footer class="user-footer">
        <div class="footer-main-grid">
            <div class="footer-brand">
                <a href="{{ route('home') }}" class="brand-logo">
                    <img src="{{ asset('uploads/home/uniluanda.webp') }}"
                         alt="UniLuanda"
                         class="brand-logo-img">
                    Uni<span>Luanda</span>
                </a>
                <p>Plataforma para acompanhamento contínuo, integração e desenvolvimento de carreira dos graduados.</p>
            </div>

            <div class="footer-col">
                <h4>Navegação</h4>
                <ul>
                    <li><a href="#inicio">Início</a></li>
                    <li><a href="#comunidade">Comunidade</a></li>
                    <li><a href="#oportunidades">Vagas & Carreiras</a></li>
                    <li><a href="#eventos">Eventos</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Suporte</h4>
                <ul>
                    <li><a href="#" data-bs-toggle="modal" data-bs-target="#modalFaq">Perguntas Frequentes</a></li>
                    <li><a href="#" data-bs-toggle="modal" data-bs-target="#modalApoio">Apoio ao Alumni</a></li>
                    <li><a href="#" data-bs-toggle="modal" data-bs-target="#modalTermos">Termos de Uso</a></li>
                    <li><a href="#" data-bs-toggle="modal" data-bs-target="#modalPrivacidade">Privacidade</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Siga-nos</h4>
                <div class="footer-socials">
                    <a href="https://www.linkedin.com/company/uniluanda" target="_blank" rel="noopener" aria-label="LinkedIn" class="social-link social-linkedin">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://www.facebook.com/uniluanda" target="_blank" rel="noopener" aria-label="Facebook" class="social-link social-facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/uniluanda" target="_blank" rel="noopener" aria-label="Instagram" class="social-link social-instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://twitter.com/uniluanda" target="_blank" rel="noopener" aria-label="Twitter" class="social-link social-twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.youtube.com/@uniluanda" target="_blank" rel="noopener" aria-label="YouTube" class="social-link social-youtube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom-bar">
            <p>&copy; {{ date('Y') }} Alumni Track. Todos os direitos reservados.</p>
            <p>Feito para conectar graduados ao futuro.</p>
        </div>
    </footer>


    {{-- ============================================================
         MODAL 1: PERGUNTAS FREQUENTES
    ============================================================ --}}
    <div class="modal fade" id="modalFaq" tabindex="-1" aria-labelledby="modalFaqLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-primary text-white rounded-top-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h5 class="modal-title mb-0" id="modalFaqLabel">
                            Perguntas Frequentes
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 shadow-sm rounded-3 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="fas fa-user-plus text-primary me-2"></i>
                                    Como faço para me registar na plataforma?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Para se registar, clique em <strong>"Cadastrar-se"</strong> no topo da página. Preencha os seus dados pessoais e académicos e aguarde a validação por parte da administração. Após a aprovação, receberá um email de confirmação.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 shadow-sm rounded-3 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="fas fa-id-card text-primary me-2"></i>
                                    Como atualizo o meu perfil profissional?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Aceda ao seu perfil através do menu lateral, clique em <strong>"Editar Perfil"</strong> e atualize as suas informações profissionais como cargo, empregador e sector de atividade. Não se esqueça de guardar as alterações.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 shadow-sm rounded-3 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="fas fa-briefcase text-primary me-2"></i>
                                    Como me candidato a uma oportunidade?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Aceda à secção <strong>"Oportunidades"</strong>, encontre a vaga pretendida e clique em <strong>"Candidatar-se"</strong>. Pode acompanhar o estado das suas candidaturas na secção <strong>"Minhas Candidaturas"</strong>.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 shadow-sm rounded-3 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    Como entro em contacto com outros egressos?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Utilize a secção <strong>"Rede de Contactos"</strong> para enviar pedidos de conexão a outros egressos. Após a aceitação, poderá trocar mensagens privadas através da secção <strong>"Mensagens"</strong>.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 shadow-sm rounded-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <i class="fas fa-map-marked-alt text-primary me-2"></i>
                                    Como funciona o mapa de egressos?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    O mapa mostra a localização dos egressos que partilharam as suas coordenadas. Pode filtrar por curso, unidade orgânica e país, e identificar as suas conexões através das cores dos marcadores.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <p class="text-muted small mb-0 me-auto">
                        <i class="fas fa-info-circle me-1"></i>
                        Não encontrou a resposta? Contacte o suporte.
                    </p>
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Fechar
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalApoio">
                        <i class="fas fa-headset me-1"></i> Contactar Suporte
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ============================================================
         MODAL 2: APOIO AO ALUMNI
    ============================================================ --}}
    <div class="modal fade" id="modalApoio" tabindex="-1" aria-labelledby="modalApoioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-success text-white rounded-top-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5 class="modal-title mb-0" id="modalApoioLabel">
                            Apoio ao Alumni
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 mb-3" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 mb-3" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 bg-primary bg-opacity-10 rounded-4 h-100 text-center p-3">
                                <div class="bg-primary text-white rounded-circle d-inline-flex p-3 mb-2 mx-auto" style="width: 56px; height: 56px; align-items: center; justify-content: center;">
                                    <i class="fas fa-envelope fa-lg"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Email</h6>
                                <p class="text-muted small mb-2">Resposta em até 24h</p>
                                <a href="mailto:alumni@uniluanda.ao" class="text-primary text-decoration-none small fw-semibold">
                                    bernardofrancisco9991@gmail.com
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0 bg-success bg-opacity-10 rounded-4 h-100 text-center p-3">
                                <div class="bg-success text-white rounded-circle d-inline-flex p-3 mb-2 mx-auto" style="width: 56px; height: 56px; align-items: center; justify-content: center;">
                                    <i class="fas fa-phone fa-lg"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Telefone</h6>
                                <p class="text-muted small mb-2">Seg-Sex, 8h-15h</p>
                                <a href="tel:+244923000000" class="text-success text-decoration-none small fw-semibold">
                                    +244 923 688049
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0 bg-info bg-opacity-10 rounded-4 h-100 text-center p-3">
                                <div class="bg-info text-white rounded-circle d-inline-flex p-3 mb-2 mx-auto" style="width: 56px; height: 56px; align-items: center; justify-content: center;">
                                    <i class="fas fa-map-marker-alt fa-lg"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Localização</h6>
                                <p class="text-muted small mb-2">Universidade de Luanda</p>
                                <span class="text-info small fw-semibold">Luanda, Angola</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light rounded-4 p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-paper-plane text-primary me-2"></i>
                            Envie-nos uma mensagem
                        </h6>

                        <form action="{{ route('apoio.alumni.contacto') }}" method="POST" novalidate>
                            @csrf

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="apoioNome" class="form-label fw-semibold">
                                        Nome completo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('nome') is-invalid @enderror"
                                           id="apoioNome" name="nome"
                                           value="{{ old('nome') }}"
                                           placeholder="Digite o seu nome completo"
                                           maxlength="200" required>
                                    @error('nome')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="apoioEmail" class="form-label fw-semibold">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="apoioEmail" name="email"
                                           value="{{ old('email') }}"
                                           placeholder="exemplo@email.com"
                                           maxlength="200" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="apoioAssunto" class="form-label fw-semibold">
                                        Assunto <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('assunto') is-invalid @enderror"
                                            id="apoioAssunto" name="assunto" required>
                                        <option value="">Selecione um assunto</option>
                                        <option value="suporte" {{ old('assunto') === 'suporte' ? 'selected' : '' }}>Suporte à plataforma</option>
                                        <option value="conta" {{ old('assunto') === 'conta' ? 'selected' : '' }}>Problema com a conta</option>
                                        <option value="perfil" {{ old('assunto') === 'perfil' ? 'selected' : '' }}>Problema com o perfil</option>
                                        <option value="oportunidade" {{ old('assunto') === 'oportunidade' ? 'selected' : '' }}>Oportunidades</option>
                                        <option value="outro" {{ old('assunto') === 'outro' ? 'selected' : '' }}>Outro assunto</option>
                                    </select>
                                    @error('assunto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="apoioMensagem" class="form-label fw-semibold">
                                        Mensagem <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('mensagem') is-invalid @enderror"
                                              id="apoioMensagem" name="mensagem"
                                              rows="5" maxlength="2000"
                                              placeholder="Descreva detalhadamente o seu pedido..."
                                              required>{{ old('mensagem') }}</textarea>
                                    @error('mensagem')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Máximo de 2000 caracteres.</div>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar mensagem
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ============================================================
         MODAL 3: TERMOS DE USO
    ============================================================ --}}
    <div class="modal fade" id="modalTermos" tabindex="-1" aria-labelledby="modalTermosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-warning text-dark rounded-top-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-dark bg-opacity-25 rounded-circle p-2">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h5 class="modal-title mb-0" id="modalTermosLabel">
                            Termos de Uso
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center mb-4">
                        <i class="fas fa-calendar-alt fa-lg me-3"></i>
                        <div>
                            <strong>Última atualização:</strong>
                            <span class="ms-1">{{ date('d/m/Y') }}</span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-2">1. Aceitação dos Termos</h6>
                    <p class="text-muted small mb-4">
                        Ao aceder e utilizar a plataforma Alumni Track, concorda em cumprir e estar vinculado a estes Termos de Uso. Se não concordar com alguma parte destes termos, não deverá utilizar a plataforma.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">2. Descrição do Serviço</h6>
                    <p class="text-muted small mb-4">
                        A plataforma Alumni Track é um serviço que visa conectar ex-estudantes da Universidade de Luanda, facilitando o networking, acesso a oportunidades de carreira, eventos e serviços institucionais.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">3. Registo e Conta</h6>
                    <p class="text-muted small mb-4">
                        Para utilizar a plataforma, deve criar uma conta fornecendo informações precisas e completas. É responsável por manter a confidencialidade da sua password e por todas as atividades realizadas na sua conta.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">4. Conduta do Utilizador</h6>
                    <p class="text-muted small mb-4">
                        Compromete-se a não utilizar a plataforma para fins ilegais, a não publicar conteúdo ofensivo, discriminatório ou que viole direitos de terceiros, e a respeitar os outros membros da comunidade.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">5. Propriedade Intelectual</h6>
                    <p class="text-muted small mb-4">
                        Todo o conteúdo da plataforma, incluindo textos, gráficos, logótipos e software, é propriedade da Universidade de Luanda e está protegido por leis de propriedade intelectual.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">6. Alterações aos Termos</h6>
                    <p class="text-muted small mb-4">
                        Reservamo-nos o direito de modificar estes termos a qualquer momento. As alterações entram em vigor imediatamente após a sua publicação na plataforma.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">7. Limitação de Responsabilidade</h6>
                    <p class="text-muted small mb-4">
                        A Universidade de Luanda não se responsabiliza por danos diretos ou indiretos resultantes da utilização da plataforma ou da impossibilidade de a utilizar.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">8. Lei Aplicável</h6>
                    <p class="text-muted small mb-4">
                        Estes termos são regidos pelas leis. Qualquer litígio será resolvido nos tribunais competentes de Luanda.
                    </p>

                    <div class="alert alert-info border-0 rounded-4 d-flex align-items-center">
                        <i class="fas fa-info-circle fa-lg me-3"></i>
                        <div class="small">
                            Para mais informações sobre os nossos termos, contacte-nos através do email
                            <a href="mailto:bernardofrancisco9991@gmail.com" class="fw-semibold text-decoration-none">bernardofrancisco9991@gmail.com</a>.
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Fechar
                    </button>
                    <button type="button" class="btn btn-warning rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-check me-2"></i> Entendi
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ============================================================
         MODAL 4: PRIVACIDADE
    ============================================================ --}}
    <div class="modal fade" id="modalPrivacidade" tabindex="-1" aria-labelledby="modalPrivacidadeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-danger text-white rounded-top-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="modal-title mb-0" id="modalPrivacidadeLabel">
                            Política de Privacidade
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4">
                        <i class="fas fa-shield-alt fa-lg me-3"></i>
                        <div>
                            <strong>Última atualização:</strong>
                            <span class="ms-1">{{ date('d/m/Y') }}</span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-2">1. Recolha de Dados</h6>
                    <p class="text-muted small mb-4">
                        Recolhemos informações que fornece diretamente ao criar a sua conta, como nome, email, telefone, dados académicos e profissionais. Recolhemos também dados de utilização para melhorar os nossos serviços.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">2. Utilização dos Dados</h6>
                    <p class="text-muted small mb-4">
                        Os seus dados são utilizados para: gerir a sua conta, facilitar o networking entre egressos, enviar notificações sobre oportunidades e eventos, e melhorar a experiência na plataforma.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">3. Partilha de Dados</h6>
                    <p class="text-muted small mb-4">
                        Não vendemos os seus dados pessoais. As informações do seu perfil podem ser visíveis para outros egressos registados na plataforma, conforme as suas configurações de privacidade.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">4. Segurança dos Dados</h6>
                    <p class="text-muted small mb-4">
                        Implementamos medidas técnicas e organizacionais para proteger os seus dados contra acesso não autorizado, alteração, divulgação ou destruição.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">5. Os Seus Direitos</h6>
                    <p class="text-muted small mb-2">Tem o direito de:</p>
                    <ul class="text-muted small mb-4">
                        <li><i class="fas fa-check text-success me-2"></i> Aceder aos seus dados pessoais</li>
                        <li><i class="fas fa-check text-success me-2"></i> Corrigir dados incorretos ou incompletos</li>
                        <li><i class="fas fa-check text-success me-2"></i> Eliminar a sua conta e dados associados</li>
                        <li><i class="fas fa-check text-success me-2"></i> Opor-se ao tratamento dos seus dados</li>
                        <li><i class="fas fa-check text-success me-2"></i> Solicitar a portabilidade dos dados</li>
                    </ul>

                    <h6 class="fw-bold text-dark mb-2">6. Cookies</h6>
                    <p class="text-muted small mb-4">
                        Utilizamos cookies para melhorar a sua experiência na plataforma. Pode configurar o seu navegador para recusar cookies, mas isso pode afetar algumas funcionalidades.
                    </p>

                    <h6 class="fw-bold text-dark mb-2">7. Retenção de Dados</h6>
                    <p class="text-muted small mb-4">
                        Os seus dados são mantidos enquanto a sua conta estiver ativa. Após a eliminação da conta, os dados são removidos dos nossos servidores num prazo de 30 dias.
                    </p>

                    <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center">
                        <i class="fas fa-user-shield fa-lg me-3"></i>
                        <div class="small">
                            Para exercer os seus direitos ou esclarecer dúvidas sobre privacidade, contacte o nosso Encarregado de Proteção de Dados através do email
                            <a href="mailto:privacidade@uniluanda.ao" class="fw-semibold text-decoration-none">privacidade@uniluanda.ao</a>.
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Fechar
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-check me-2"></i> Entendi
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ============================================================
         MODAL — LISTA DE EGRESSOS COM FILTROS
    ============================================================ --}}
    <div class="modal fade" id="modalEgressosPublicos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow-lg">

                <div class="modal-header bg-primary text-white rounded-top-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0">Egressos UniLuanda</h5>
                            <small class="text-white-50" id="modalTotalEgressos">A carregar...</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body p-0">

                    <div class="modal-filtros-wrap">

                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text"
                                   id="filtroTexto"
                                   class="form-control border-start-0 border-end-0"
                                   placeholder="Pesquise por nome, curso ou unidade..."
                                   autocomplete="off">
                            <button type="button"
                                    class="btn btn-outline-secondary border-start-0"
                                    onclick="limparFiltrosEgressos()"
                                    title="Limpar pesquisa">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="row g-2">
                            <div class="col-6 col-md-4">
                                <select id="filtroCurso" class="form-select form-select-sm">
                                    <option value="">Todos os cursos</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <select id="filtroUnidade" class="form-select form-select-sm">
                                    <option value="">Todas as unidades</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <select id="filtroOrdem" class="form-select form-select-sm">
                                    <option value="az">Nome (A → Z)</option>
                                    <option value="za">Nome (Z → A)</option>
                                    <option value="recentes">Mais recentes</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div id="egressosLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">A carregar...</span>
                        </div>
                        <p class="text-muted small mt-3 mb-0">A carregar lista...</p>
                    </div>

                    <div id="egressosLista" style="display: none;"></div>

                    <div id="egressosVazio" class="text-center py-5" style="display: none;"></div>

                </div>

                <div class="modal-footer border-0 bg-light rounded-bottom-4 d-flex justify-content-between">
                    <small class="text-muted">
                        <i class="fas fa-lock me-1"></i> Dados sensíveis protegidos
                    </small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                data-bs-dismiss="modal">
                            Fechar
                        </button>
                        <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-sign-in-alt me-1"></i> Entrar
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- ============================================================
         JS DO CARROSSEL DE FUNDO (HERO) — LOOP INFINITO
         4 SEGUNDOS POR IMAGEM
    ============================================================ --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const slides = document.querySelectorAll('.hero-bg-slide');
        const dots   = document.querySelectorAll('.hero-dot');

        if (!slides.length) return;

        let current = 0;
        let autoplay;

        function goTo(index) {
            slides[current]?.classList.remove('active');
            dots[current]?.classList.remove('active');

            current = (index + slides.length) % slides.length;

            slides[current]?.classList.add('active');
            dots[current]?.classList.add('active');
        }

        function next() { goTo(current + 1); }
        function prev() { goTo(current - 1); }

        function startAutoplay() {
            autoplay = setInterval(next, 5000);   // ⏱️ 4 SEGUNDOS
        }

        function stopAutoplay() {
            clearInterval(autoplay);
        }

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                goTo(i);
                stopAutoplay();
                startAutoplay();
            });
        });

        // ⚠️ Pausa ao passar o rato — REMOVIDO para que troque SEMPRE
        // Se quiseres que pause ao passar o rato, basta descomentar as 2 linhas abaixo:
        // const hero = document.querySelector('.hero-fullwidth');
        // hero?.addEventListener('mouseenter', stopAutoplay);
        // hero?.addEventListener('mouseleave', startAutoplay);

        startAutoplay();
    });
    </script>

</body>
</html>