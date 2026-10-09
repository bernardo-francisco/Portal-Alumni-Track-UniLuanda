@extends('layouts.app')

@section('title', 'Bem-vindo - UniLuanda Alumni Track')

@section('content')
<!-- Hero Section -->
<section class="hero-section py-5">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6 animate-fadeInUp">
                <h1 class="display-4 fw-bold mb-4">
                    Conectando <span style="color: #667eea;">Ex-Estudantes</span> da<br>
                    <span style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; background-clip: text; color: transparent;">
                        Universidade de Luanda
                    </span>
                </h1>
                <p class="lead mb-4 text-muted">
                    Plataforma inovadora para acompanhamento profissional e localização de ex-estudantes.
                    Conecte-se, compartilhe experiências e impulsione sua carreira.
                </p>
                <div class="d-flex gap-3">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i>Registar-se
                    </a>
                    <a href="#features" class="btn btn-outline-primary btn-lg px-4">
                        <i class="fas fa-play me-2"></i>Conhecer
                    </a>
                </div>
            </div>
            <div class="col-lg-6 animate-fadeInUp">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&h=500&fit=crop" 
                     alt="Alumni" class="img-fluid rounded-4 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section py-5 bg-light">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>500+</h3>
                    <p>Ex-Estudantes Registados</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>30+</h3>
                    <p>Cursos Disponíveis</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-globe-africa"></i>
                    <h3>15+</h3>
                    <p>Países Representados</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-briefcase"></i>
                    <h3>85%</h3>
                    <p>Taxa de Empregabilidade</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Funcionalidades da Plataforma</h2>
            <p class="lead text-muted">Tudo o que você precisa para acompanhar sua trajetória profissional</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="fas fa-map-marker-alt fa-3x mb-3" style="color: #667eea;"></i>
                        <h4 class="card-title">Localização Global</h4>
                        <p class="card-text text-muted">
                            Visualize onde estão os ex-estudantes no mundo através de mapas interativos com marcadores personalizados.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-3x mb-3" style="color: #764ba2;"></i>
                        <h4 class="card-title">Acompanhamento Profissional</h4>
                        <p class="card-text text-muted">
                            Registre e acompanhe sua trajetória profissional, empregos e setores de atuação.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="fas fa-network-wired fa-3x mb-3" style="color: #667eea;"></i>
                        <h4 class="card-title">Rede de Contactos</h4>
                        <p class="card-text text-muted">
                            Conecte-se com outros ex-estudantes, promovendo networking e oportunidades.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="fas fa-brain fa-3x mb-3" style="color: #764ba2;"></i>
                        <h4 class="card-title">Inteligência Artificial</h4>
                        <p class="card-text text-muted">
                            Sugestões personalizadas de carreira e matching de oportunidades com IA.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="fas fa-file-pdf fa-3x mb-3" style="color: #667eea;"></i>
                        <h4 class="card-title">Relatórios Avançados</h4>
                        <p class="card-text text-muted">
                            Exporte dados em PDF com estatísticas completas do sistema.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="fas fa-shield-alt fa-3x mb-3" style="color: #764ba2;"></i>
                        <h4 class="card-title">Segurança e Privacidade</h4>
                        <p class="card-text text-muted">
                            Dados protegidos com criptografia e controle de acesso por níveis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Institutions Section -->
<section class="institutions-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Unidades Orgânicas</h2>
            <p class="lead text-muted">A Universidade de Luanda é composta por 4 unidades de excelência</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <div class="card-body">
                        <i class="fas fa-laptop-code fa-3x mb-3" style="color: #667eea;"></i>
                        <h4>IPGEST</h4>
                        <p class="text-muted">Instituto Politécnico de Gestão e Tecnologia</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <div class="card-body">
                        <i class="fas fa-microchip fa-3x mb-3" style="color: #764ba2;"></i>
                        <h4>INSTI</h4>
                        <p class="text-muted">Instituto de Tecnologia</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <div class="card-body">
                        <i class="fas fa-gavel fa-3x mb-3" style="color: #667eea;"></i>
                        <h4>FSS</h4>
                        <p class="text-muted">Faculdade de Ciências Sociais</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <div class="card-body">
                        <i class="fas fa-palette fa-3x mb-3" style="color: #764ba2;"></i>
                        <h4>FAA</h4>
                        <p class="text-muted">Faculdade de Arquitetura e Artes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-4">Pronto para fazer parte desta rede?</h2>
                <p class="lead mb-4">
                    Junte-se a centenas de ex-estudantes que já utilizam nossa plataforma.
                </p>
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-user-plus me-2"></i>Criar Conta Gratuita
                </a>
            </div>
        </div>
    </div>
</section>
@endsection