<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações do Sistema Alumni - UniLuanda
    |--------------------------------------------------------------------------
    */

    // =============================================
    // UNIDADES ORGÂNICAS
    // =============================================
    'unidades_organicas' => [
        'ipgest' => [
            'nome' => 'Instituto Politécnico de Gestão e Tecnologias',
            'sigla' => 'IPGEST',
            'descricao' => 'Gestão, Tecnologias e Inovação',
        ],
        'instic' => [
            'nome' => 'Instituto Superior Técnico de Ciências',
            'sigla' => 'INSTIC',
            'descricao' => 'Ciências e Tecnologia',
        ],
        'fss' => [
            'nome' => 'Faculdade de Ciências Sociais',
            'sigla' => 'FSS',
            'descricao' => 'Ciências Sociais e Humanas',
        ],
        'faa' => [
            'nome' => 'Faculdade de Administração e Auditoria',
            'sigla' => 'FAA',
            'descricao' => 'Administração e Auditoria',
        ],
    ],

    // =============================================
    // STATUS DO EGRESSO
    // =============================================
    'status_egresso' => [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'lost_contact' => 'Sem Contacto',
    ],

    // =============================================
    // TIPOS DE EMPREGO
    // =============================================
    'tipos_emprego' => [
        'full_time' => 'Tempo Inteiro',
        'part_time' => 'Tempo Parcial',
        'freelance' => 'Freelance',
        'self_employed' => 'Autónomo',
        'unemployed' => 'Desempregado',
        'student' => 'A Estudar',
        'unknown' => 'Desconhecido',
    ],

    // =============================================
    // TIPOS DE OPORTUNIDADES
    // =============================================
    'tipos_oportunidades' => [
        'emprego' => 'Emprego',
        'estagio' => 'Estágio',
        'bolsa' => 'Bolsa',
        'curso' => 'Curso',
        'evento' => 'Evento',
    ],

    // =============================================
    // CATEGORIAS DE EVENTOS
    // =============================================
    'categorias_eventos' => [
        'workshop' => 'Workshop',
        'palestra' => 'Palestra',
        'networking' => 'Networking',
        'job_fair' => 'Job Fair',
        'curso' => 'Curso',
        'outro' => 'Outro',
    ],

    // =============================================
    // TIPOS DE EVENTOS
    // =============================================
    'tipos_eventos' => [
        'presencial' => 'Presencial',
        'online' => 'Online',
        'hibrido' => 'Híbrido',
    ],

    // =============================================
    // SEGURANÇA
    // =============================================
    'security' => [
        'admin_code' => env('ADMIN_SECRET_CODE', 'UNILUNDA2024'),
        'max_login_attempts' => 5,
        'block_minutes' => 15,
        'password_min_length' => 8,
        'session_timeout' => 60, // minutos
    ],

    // =============================================
    // UPLOAD
    // =============================================
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'],
        'paths' => [
            'egressos' => 'uploads/egressos/',
            'users' => 'uploads/users/',
            'documentos' => 'uploads/documentos/',
        ],
    ],

    // =============================================
    // RELATÓRIOS
    // =============================================
    'relatorios' => [
        'itens_por_pagina' => 20,
        'formatos' => ['pdf', 'csv', 'excel'],
    ],

    // =============================================
    // PAGINAÇÃO
    // =============================================
    'paginacao' => [
        'itens_por_pagina' => 15,
        'itens_por_pagina_admin' => 25,
    ],
];