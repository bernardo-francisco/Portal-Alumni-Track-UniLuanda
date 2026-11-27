CREATE DATABASE IF NOT EXISTS ex_estudantes_uniluanda
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE ex_estudantes_uniluanda;

-- ============================================================================
-- 1. AUTENTICAÇÃO & UTILIZADORES
-- ============================================================================

CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(255) NULL,
  photo_url VARCHAR(255) NULL,
  role ENUM('admin','egresso','coordenador','empresa') NOT NULL DEFAULT 'egresso',
  tipo ENUM('admin','egresso','empresa') NOT NULL DEFAULT 'egresso',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  registration_ip VARCHAR(45) NULL,
  registration_user_agent VARCHAR(500) NULL,
  email_verified_at TIMESTAMP NULL,
  email_verificado_at TIMESTAMP NULL,
  last_login TIMESTAMP NULL,
  session_id VARCHAR(255) NULL,
  last_login_at TIMESTAMP NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache (
  key VARCHAR(255) NOT NULL,
  value MEDIUMTEXT NOT NULL,
  expiration INT NOT NULL,
  PRIMARY KEY (key),
  KEY cache_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
  key VARCHAR(255) NOT NULL,
  owner VARCHAR(255) NOT NULL,
  expiration INT NOT NULL,
  PRIMARY KEY (key),
  KEY cache_locks_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
  id VARCHAR(255) NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  payload LONGTEXT NOT NULL,
  last_activity INT NOT NULL,
  PRIMARY KEY (id),
  KEY sessions_user_id_index (user_id),
  KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
  email VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL,
  PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE otp_codes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  code VARCHAR(6) NOT NULL,
  purpose VARCHAR(50) NOT NULL DEFAULT 'reclamar_conta',
  payload JSON NULL,
  expires_at TIMESTAMP NOT NULL,
  used_at TIMESTAMP NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(500) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY otp_codes_email_index (email),
  KEY otp_codes_email_code_used_at_index (email, code, used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE email_verification_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  token VARCHAR(64) NOT NULL,
  purpose VARCHAR(50) NOT NULL DEFAULT 'verificar_registo',
  payload JSON NULL,
  expires_at TIMESTAMP NOT NULL,
  used_at TIMESTAMP NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(500) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email_verification_tokens_token_unique (token),
  KEY email_verification_tokens_email_index (email),
  KEY email_verification_tokens_email_purpose_used_at_index (email, purpose, used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. ESTRUTURA ACADÉMICA
-- ============================================================================

CREATE TABLE unidades_organicas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  sigla VARCHAR(20) NOT NULL,
  descricao TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY unidades_organicas_sigla_unique (sigla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO unidades_organicas (nome, sigla, created_at, updated_at) VALUES
  ('Instituto Politécnico de Gestão e Tecnologias', 'IPGEST', NOW(), NOW()),
  ('Instituto Superior Técnico de Ciências', 'INSTIC', NOW(), NOW()),
  ('Faculdade de Ciências Sociais', 'FSS', NOW(), NOW()),
  ('Faculdade de Administração e Auditoria', 'FAA', NOW(), NOW());

CREATE TABLE cursos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  unidade_id BIGINT UNSIGNED NOT NULL,
  nome VARCHAR(150) NOT NULL,
  codigo VARCHAR(20) NOT NULL,
  departamento VARCHAR(100) NULL,
  duracao TINYINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY cursos_codigo_unique (codigo),
  KEY cursos_unidade_id_foreign (unidade_id),
  CONSTRAINT cursos_unidade_id_foreign
    FOREIGN KEY (unidade_id) REFERENCES unidades_organicas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admins (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  nome_completo VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  telefone VARCHAR(255) NULL,
  foto_url VARCHAR(255) NULL,
  unidade_id BIGINT UNSIGNED NULL,
  cargo VARCHAR(255) NULL,
  nivel ENUM('master','geral','unidade','suporte') NOT NULL DEFAULT 'geral',
  pode_gerenciar_admins TINYINT(1) NOT NULL DEFAULT 0,
  pode_gerenciar_egressos TINYINT(1) NOT NULL DEFAULT 1,
  pode_gerenciar_oportunidades TINYINT(1) NOT NULL DEFAULT 1,
  pode_gerenciar_eventos TINYINT(1) NOT NULL DEFAULT 1,
  pode_gerenciar_configuracoes TINYINT(1) NOT NULL DEFAULT 0,
  pode_visualizar_relatorios TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_acesso TIMESTAMP NULL,
  data_verificacao TIMESTAMP NULL,
  primeiro_acesso TINYINT(1) NOT NULL DEFAULT 1,
  data_expiracao_senha TIMESTAMP NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY admins_email_unique (email),
  KEY admins_user_id_index (user_id),
  KEY admins_email_index (email),
  KEY admins_unidade_id_index (unidade_id),
  KEY admins_nivel_index (nivel),
  KEY admins_ativo_index (ativo),
  CONSTRAINT admins_user_id_foreign
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT admins_unidade_id_foreign
    FOREIGN KEY (unidade_id) REFERENCES unidades_organicas (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. EGRESSOS
-- ============================================================================

CREATE TABLE egressos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  is_admin_profile TINYINT(1) NOT NULL DEFAULT 0,
  curso_id BIGINT UNSIGNED NULL,
  numero_processo VARCHAR(60) NOT NULL,
  nome_completo VARCHAR(200) NOT NULL,
  genero ENUM('M','F','O') NOT NULL DEFAULT 'M',
  data_nascimento DATE NULL,
  email VARCHAR(180) NULL,
  telefone VARCHAR(25) NULL,
  foto_url VARCHAR(255) NULL,
  ano_formatura YEAR NULL,
  nota_final DECIMAL(4,2) NULL,
  status_validacao ENUM('aprovado','pendente','reprovado') NOT NULL DEFAULT 'pendente',
  motivo_reprovacao TEXT NULL,
  observacoes_validacao TEXT NULL,
  validado_por BIGINT UNSIGNED NULL,
  data_validacao TIMESTAMP NULL,
  status ENUM('active','inactive','blocked','lost_contact') NOT NULL DEFAULT 'inactive',
  observacoes TEXT NULL,
  data_verificacao TIMESTAMP NULL,
  verificado TINYINT(1) NOT NULL DEFAULT 0,
  claimed_at TIMESTAMP NULL,
  claimed_ip VARCHAR(45) NULL,
  claimed_user_agent VARCHAR(500) NULL,
  empregado TINYINT(1) NOT NULL DEFAULT 0,
  area_atuacao VARCHAR(100) NULL,
  salario_medio DECIMAL(10,2) NULL,
  tempo_insercao INT NULL,
  satisfacao_emprego TINYINT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY egressos_numero_processo_unique (numero_processo),
  KEY egressos_numero_processo_index (numero_processo),
  KEY egressos_status_index (status),
  KEY egressos_status_validacao_index (status_validacao),
  KEY egressos_user_id_index (user_id),
  KEY egressos_curso_id_index (curso_id),
  KEY egressos_validado_por_index (validado_por),
  CONSTRAINT egressos_user_id_foreign
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT egressos_curso_id_foreign
    FOREIGN KEY (curso_id) REFERENCES cursos (id) ON DELETE SET NULL,
  CONSTRAINT egressos_validado_por_foreign
    FOREIGN KEY (validado_por) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. PERFIL PROFISSIONAL
-- ============================================================================

CREATE TABLE localizacoes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  egresso_id BIGINT UNSIGNED NOT NULL,
  pais VARCHAR(100) NOT NULL,
  provincia VARCHAR(100) NULL,
  cidade VARCHAR(100) NULL,
  endereco VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 1,
  data_desde DATE NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY localizacoes_egresso_id_foreign (egresso_id),
  KEY localizacoes_is_current_index (is_current),
  CONSTRAINT localizacoes_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE profissionais (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  egresso_id BIGINT UNSIGNED NOT NULL,
  empregador VARCHAR(200) NULL,
  cargo VARCHAR(150) NULL,
  sector VARCHAR(100) NULL,
  tipo_emprego ENUM('full_time','part_time','freelance','self_employed','unemployed','student','unknown') NOT NULL DEFAULT 'unknown',
  faixa_salarial ENUM('sem_rendimento','<50k','50k-100k','100k-200k','>200k','nao_informado') NOT NULL DEFAULT 'nao_informado',
  data_inicio DATE NULL,
  data_fim DATE NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 1,
  linkedin_url VARCHAR(255) NULL,
  observacoes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY profissionais_egresso_id_foreign (egresso_id),
  KEY profissionais_is_current_index (is_current),
  KEY profissionais_tipo_emprego_index (tipo_emprego),
  CONSTRAINT profissionais_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. EMPRESAS
-- ============================================================================

CREATE TABLE empresas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  nome VARCHAR(200) NOT NULL,
  nif VARCHAR(20) NULL,
  email VARCHAR(180) NOT NULL,
  telefone VARCHAR(25) NULL,
  website VARCHAR(255) NULL,
  sector VARCHAR(100) NULL,
  descricao TEXT NULL,
  localizacao VARCHAR(200) NULL,
  provincia VARCHAR(100) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  logo_url VARCHAR(255) NULL,
  tipo ENUM('parceira','nao_parceira','potencial') NOT NULL DEFAULT 'potencial',
  status_validacao ENUM('pendente','aprovado','reprovado') NOT NULL DEFAULT 'pendente',
  motivo_reprovacao TEXT NULL,
  validado_por BIGINT UNSIGNED NULL,
  data_validacao TIMESTAMP NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY empresas_nif_unique (nif),
  UNIQUE KEY empresas_email_unique (email),
  KEY empresas_user_id_foreign (user_id),
  KEY empresas_validado_por_foreign (validado_por),
  KEY empresas_sector_index (sector),
  KEY empresas_tipo_index (tipo),
  KEY empresas_status_validacao_index (status_validacao),
  KEY empresas_ativo_index (ativo),
  CONSTRAINT empresas_user_id_foreign
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT empresas_validado_por_foreign
    FOREIGN KEY (validado_por) REFERENCES admins (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. OPORTUNIDADES
-- ============================================================================

CREATE TABLE oportunidades (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  unidade_id BIGINT UNSIGNED NULL,
  created_by BIGINT UNSIGNED NULL,
  empresa_id BIGINT UNSIGNED NULL,
  titulo VARCHAR(200) NOT NULL,
  descricao TEXT NOT NULL,
  tipo ENUM('emprego','estagio','bolsa','curso','evento') NOT NULL DEFAULT 'emprego',
  empresa VARCHAR(200) NULL,
  localizacao VARCHAR(200) NULL,
  salario VARCHAR(100) NULL,
  requisitos TEXT NULL,
  data_limite DATE NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY oportunidades_unidade_id_foreign (unidade_id),
  KEY oportunidades_created_by_foreign (created_by),
  KEY oportunidades_empresa_id_foreign (empresa_id),
  KEY oportunidades_tipo_index (tipo),
  KEY oportunidades_data_limite_index (data_limite),
  CONSTRAINT oportunidades_unidade_id_foreign
    FOREIGN KEY (unidade_id) REFERENCES unidades_organicas (id) ON DELETE SET NULL,
  CONSTRAINT oportunidades_created_by_foreign
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT oportunidades_empresa_id_foreign
    FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE candidaturas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  oportunidade_id BIGINT UNSIGNED NOT NULL,
  egresso_id BIGINT UNSIGNED NOT NULL,
  mensagem_motivacional TEXT NULL,
  cv_anexo VARCHAR(255) NULL,
  status ENUM('pendente','em_analise','entrevista','aprovado','aceite','rejeitado') NOT NULL DEFAULT 'pendente',
  data_entrevista TIMESTAMP NULL,
  local_entrevista VARCHAR(255) NULL,
  confirmado_pelo_egresso TINYINT(1) NOT NULL DEFAULT 0,
  confirmado_em TIMESTAMP NULL,
  motivo_rejeicao TEXT NULL,
  observacoes_admin TEXT NULL,
  avaliado_em TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY candidaturas_oportunidade_egresso_unique (oportunidade_id, egresso_id),
  KEY candidaturas_status_index (status),
  KEY candidaturas_egresso_id_foreign (egresso_id),
  CONSTRAINT candidaturas_oportunidade_id_foreign
    FOREIGN KEY (oportunidade_id) REFERENCES oportunidades (id) ON DELETE CASCADE,
  CONSTRAINT candidaturas_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. EVENTOS
-- ============================================================================

CREATE TABLE eventos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  unidade_id BIGINT UNSIGNED NULL,
  created_by BIGINT UNSIGNED NULL,
  titulo VARCHAR(200) NOT NULL,
  descricao TEXT NULL,
  tipo ENUM('presencial','online','hibrido') NOT NULL DEFAULT 'presencial',
  categoria ENUM('workshop','palestra','networking','job_fair','curso','outro') NOT NULL DEFAULT 'outro',
  data_inicio DATETIME NOT NULL,
  data_fim DATETIME NULL,
  local VARCHAR(200) NULL,
  link_reuniao VARCHAR(255) NULL,
  max_participantes INT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  vagas_totais INT NULL,
  permite_cancelamento TINYINT(1) NOT NULL DEFAULT 1,
  data_limite_inscricao DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY eventos_unidade_id_foreign (unidade_id),
  KEY eventos_created_by_foreign (created_by),
  KEY eventos_data_inicio_index (data_inicio),
  CONSTRAINT eventos_unidade_id_foreign
    FOREIGN KEY (unidade_id) REFERENCES unidades_organicas (id) ON DELETE SET NULL,
  CONSTRAINT eventos_created_by_foreign
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inscricoes_eventos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  evento_id BIGINT UNSIGNED NOT NULL,
  egresso_id BIGINT UNSIGNED NOT NULL,
  status ENUM('pendente','confirmada','rejeitada','cancelada') NOT NULL DEFAULT 'pendente',
  motivo_rejeicao VARCHAR(500) NULL,
  presente TINYINT(1) NOT NULL DEFAULT 0,
  checkin_em TIMESTAMP NULL,
  certificado_emitido TINYINT(1) NOT NULL DEFAULT 0,
  certificado_url VARCHAR(255) NULL,
  codigo_comprovativo VARCHAR(50) NULL,
  certificado_codigo VARCHAR(50) NULL,
  certificado_emitido_em TIMESTAMP NULL,
  feedback_enviado TINYINT(1) NOT NULL DEFAULT 0,
  lembrete_24h_enviado TINYINT(1) NOT NULL DEFAULT 0,
  lembrete_1h_enviado TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY inscricoes_eventos_evento_egresso_unique (evento_id, egresso_id),
  UNIQUE KEY inscricoes_eventos_codigo_comprovativo_unique (codigo_comprovativo),
  UNIQUE KEY inscricoes_eventos_certificado_codigo_unique (certificado_codigo),
  KEY inscricoes_eventos_egresso_id_foreign (egresso_id),
  CONSTRAINT inscricoes_eventos_evento_id_foreign
    FOREIGN KEY (evento_id) REFERENCES eventos (id) ON DELETE CASCADE,
  CONSTRAINT inscricoes_eventos_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. REDE SOCIAL
-- ============================================================================

CREATE TABLE conexoes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  solicitante_id BIGINT UNSIGNED NOT NULL,
  solicitante_type VARCHAR(255) NOT NULL,
  destinatario_id BIGINT UNSIGNED NOT NULL,
  destinatario_type VARCHAR(255) NOT NULL,
  status ENUM('pendente','aceito','recusado','bloqueado') NOT NULL DEFAULT 'pendente',
  notificacao_lida TINYINT(1) NOT NULL DEFAULT 0,
  data_notificacao TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY conexoes_solicitante_destinatario_unique (solicitante_id, destinatario_id),
  KEY conexoes_solicitante_id_index (solicitante_id),
  KEY conexoes_destinatario_id_index (destinatario_id),
  KEY conexoes_status_index (status),
  CONSTRAINT conexoes_solicitante_id_foreign
    FOREIGN KEY (solicitante_id) REFERENCES egressos (id) ON DELETE CASCADE,
  CONSTRAINT conexoes_destinatario_id_foreign
    FOREIGN KEY (destinatario_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mensagens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  remetente_id BIGINT UNSIGNED NOT NULL,
  destinatario_id BIGINT UNSIGNED NOT NULL,
  mensagem TEXT NULL,
  tipo VARCHAR(255) NOT NULL DEFAULT 'texto',
  audio_url VARCHAR(255) NULL,
  audio_duracao INT NULL,
  ficheiro_url VARCHAR(255) NULL,
  ficheiro_nome VARCHAR(255) NULL,
  ficheiro_tamanho INT NULL,
  ficheiro_tipo VARCHAR(255) NULL,
  lida TINYINT(1) NOT NULL DEFAULT 0,
  lida_em TIMESTAMP NULL,
  editado TINYINT(1) NOT NULL DEFAULT 0,
  editado_em TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY mensagens_remetente_id_index (remetente_id),
  KEY mensagens_destinatario_id_index (destinatario_id),
  KEY mensagens_lida_index (lida),
  CONSTRAINT mensagens_remetente_id_foreign
    FOREIGN KEY (remetente_id) REFERENCES egressos (id) ON DELETE CASCADE,
  CONSTRAINT mensagens_destinatario_id_foreign
    FOREIGN KEY (destinatario_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE publicacoes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  egresso_id BIGINT UNSIGNED NOT NULL,
  conteudo TEXT NOT NULL,
  tipo ENUM('texto','imagem','video','link') NOT NULL DEFAULT 'texto',
  imagem_url VARCHAR(255) NULL,
  link_url VARCHAR(255) NULL,
  curtidas INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY publicacoes_egresso_id_foreign (egresso_id),
  CONSTRAINT publicacoes_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comentarios (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  publicacao_id BIGINT UNSIGNED NOT NULL,
  egresso_id BIGINT UNSIGNED NOT NULL,
  conteudo TEXT NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY comentarios_publicacao_id_foreign (publicacao_id),
  KEY comentarios_egresso_id_foreign (egresso_id),
  CONSTRAINT comentarios_publicacao_id_foreign
    FOREIGN KEY (publicacao_id) REFERENCES publicacoes (id) ON DELETE CASCADE,
  CONSTRAINT comentarios_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE curtidas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  publicacao_id BIGINT UNSIGNED NOT NULL,
  egresso_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY curtidas_publicacao_egresso_unique (publicacao_id, egresso_id),
  KEY curtidas_egresso_id_foreign (egresso_id),
  CONSTRAINT curtidas_publicacao_id_foreign
    FOREIGN KEY (publicacao_id) REFERENCES publicacoes (id) ON DELETE CASCADE,
  CONSTRAINT curtidas_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. NOTIFICAÇÕES
-- ============================================================================

CREATE TABLE notificacoes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  egresso_id BIGINT UNSIGNED NULL,
  admin_id BIGINT UNSIGNED NULL,
  empresa_id BIGINT UNSIGNED NULL,
  tipo ENUM(
    'curtida','comentario','conexao','oportunidade','mensagem',
    'sistema','servico','feedback','chamada_video','chamada_voz',
    'audio','ficheiro','evento','contacto'
  ) NOT NULL DEFAULT 'sistema',
  titulo VARCHAR(100) NOT NULL,
  mensagem TEXT NOT NULL,
  link VARCHAR(255) NULL,
  lida TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY notificacoes_egresso_id_index (egresso_id),
  KEY notificacoes_admin_id_index (admin_id),
  KEY notificacoes_empresa_id_index (empresa_id),
  KEY notificacoes_lida_index (lida),
  CONSTRAINT notificacoes_admin_id_foreign
    FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE CASCADE,
  CONSTRAINT notificacoes_empresa_id_foreign
    FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. MURAL DE NOTÍCIAS
-- ============================================================================

CREATE TABLE mural_noticias (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id BIGINT UNSIGNED NULL,
  egresso_id BIGINT UNSIGNED NULL,
  titulo VARCHAR(255) NOT NULL,
  conteudo TEXT NOT NULL,
  tipo ENUM('noticia','evento','edital') NOT NULL DEFAULT 'noticia',
  imagem_url VARCHAR(255) NULL,
  data_evento DATE NULL,
  local VARCHAR(255) NULL,
  destaque TINYINT(1) NOT NULL DEFAULT 0,
  publicado TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY mural_noticias_admin_id_foreign (admin_id),
  KEY mural_noticias_egresso_id_index (egresso_id),
  CONSTRAINT mural_noticias_admin_id_foreign
    FOREIGN KEY (admin_id) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT mural_noticias_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mural_curtidas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  mural_noticia_id BIGINT UNSIGNED NOT NULL,
  egresso_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY mural_curtidas_mural_egresso_unique (mural_noticia_id, egresso_id),
  KEY mural_curtidas_egresso_id_foreign (egresso_id),
  CONSTRAINT mural_curtidas_mural_noticia_id_foreign
    FOREIGN KEY (mural_noticia_id) REFERENCES mural_noticias (id) ON DELETE CASCADE,
  CONSTRAINT mural_curtidas_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mural_comentarios (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  mural_noticia_id BIGINT UNSIGNED NOT NULL,
  egresso_id BIGINT UNSIGNED NOT NULL,
  conteudo TEXT NOT NULL,
  editado TINYINT(1) NOT NULL DEFAULT 0,
  editado_em TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY mural_comentarios_mural_noticia_id_foreign (mural_noticia_id),
  KEY mural_comentarios_egresso_id_foreign (egresso_id),
  CONSTRAINT mural_comentarios_mural_noticia_id_foreign
    FOREIGN KEY (mural_noticia_id) REFERENCES mural_noticias (id) ON DELETE CASCADE,
  CONSTRAINT mural_comentarios_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. PESQUISAS
-- ============================================================================

CREATE TABLE pesquisas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id BIGINT UNSIGNED NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  descricao TEXT NULL,
  data_inicio DATE NOT NULL,
  data_fim DATE NOT NULL,
  ativa TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY pesquisas_admin_id_foreign (admin_id),
  CONSTRAINT pesquisas_admin_id_foreign
    FOREIGN KEY (admin_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pesquisas_perguntas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  pesquisa_id BIGINT UNSIGNED NOT NULL,
  pergunta VARCHAR(255) NOT NULL,
  tipo ENUM('texto','multipla_escolha','sim_nao','selecao_multipla') NOT NULL,
  opcoes JSON NULL,
  obrigatoria TINYINT(1) NOT NULL DEFAULT 1,
  ordem INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY pesquisas_perguntas_pesquisa_id_foreign (pesquisa_id),
  CONSTRAINT pesquisas_perguntas_pesquisa_id_foreign
    FOREIGN KEY (pesquisa_id) REFERENCES pesquisas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pesquisas_respostas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  pesquisa_id BIGINT UNSIGNED NOT NULL,
  egresso_id BIGINT UNSIGNED NOT NULL,
  pergunta_id BIGINT UNSIGNED NOT NULL,
  resposta TEXT NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY pesquisas_respostas_pesquisa_id_foreign (pesquisa_id),
  KEY pesquisas_respostas_egresso_id_foreign (egresso_id),
  KEY pesquisas_respostas_pergunta_id_foreign (pergunta_id),
  CONSTRAINT pesquisas_respostas_pesquisa_id_foreign
    FOREIGN KEY (pesquisa_id) REFERENCES pesquisas (id) ON DELETE CASCADE,
  CONSTRAINT pesquisas_respostas_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE,
  CONSTRAINT pesquisas_respostas_pergunta_id_foreign
    FOREIGN KEY (pergunta_id) REFERENCES pesquisas_perguntas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. FEEDBACK, SERVIÇOS, CONTACTOS, CHATBOT, ATIVIDADES
-- ============================================================================

CREATE TABLE feedbacks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  egresso_id BIGINT UNSIGNED NOT NULL,
  curso_id BIGINT UNSIGNED NULL,
  titulo VARCHAR(255) NOT NULL,
  mensagem TEXT NOT NULL,
  nota INT NULL,
  status ENUM('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY feedbacks_egresso_id_foreign (egresso_id),
  KEY feedbacks_curso_id_foreign (curso_id),
  CONSTRAINT feedbacks_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE,
  CONSTRAINT feedbacks_curso_id_foreign
    FOREIGN KEY (curso_id) REFERENCES cursos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE servicos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  egresso_id BIGINT UNSIGNED NOT NULL,
  servico VARCHAR(255) NOT NULL,
  descricao TEXT NOT NULL,
  status ENUM('pendente','andamento','atendido','cancelado') NOT NULL DEFAULT 'pendente',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY servicos_egresso_id_foreign (egresso_id),
  CONSTRAINT servicos_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contactos_alumni (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  assunto VARCHAR(255) NOT NULL,
  mensagem TEXT NOT NULL,
  ip VARCHAR(255) NULL,
  status ENUM('novo','lido','respondido','arquivado') NOT NULL DEFAULT 'novo',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chatbots (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  egresso_id BIGINT UNSIGNED NULL,
  admin_id BIGINT UNSIGNED NULL,
  mensagem TEXT NOT NULL,
  tipo ENUM('pergunta','resposta') NOT NULL DEFAULT 'pergunta',
  origem ENUM('egresso','admin') NOT NULL DEFAULT 'egresso',
  lida TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY chatbots_egresso_id_index (egresso_id),
  KEY chatbots_admin_id_index (admin_id),
  KEY chatbots_origem_index (origem),
  KEY chatbots_lida_index (lida),
  CONSTRAINT chatbots_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE,
  CONSTRAINT chatbots_admin_id_foreign
    FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE atividades (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  egresso_id BIGINT UNSIGNED NOT NULL,
  tipo VARCHAR(255) NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  descricao TEXT NULL,
  icone VARCHAR(255) NULL,
  cor VARCHAR(255) NOT NULL DEFAULT 'primary',
  link VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY atividades_egresso_id_foreign (egresso_id),
  CONSTRAINT atividades_egresso_id_foreign
    FOREIGN KEY (egresso_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. CHAMADAS (WebRTC)
-- ============================================================================

CREATE TABLE chamadas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  room_id VARCHAR(255) NOT NULL,
  peer_id_chamador VARCHAR(100) NULL,
  peer_id_recetor VARCHAR(100) NULL,
  chamador_id BIGINT UNSIGNED NOT NULL,
  recetor_id BIGINT UNSIGNED NOT NULL,
  tipo ENUM('audio','video') NOT NULL DEFAULT 'video',
  status ENUM('pendente','aceite','recusada','terminada','perdida') NOT NULL DEFAULT 'pendente',
  sinal_oferta JSON NULL,
  sinal_resposta JSON NULL,
  ice_candidates_chamador JSON NULL,
  ice_candidates_recetor JSON NULL,
  aceite_em TIMESTAMP NULL,
  terminada_em TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY chamadas_room_id_unique (room_id),
  KEY chamadas_recetor_status_index (recetor_id, status),
  KEY chamadas_chamador_status_index (chamador_id, status),
  CONSTRAINT chamadas_chamador_id_foreign
    FOREIGN KEY (chamador_id) REFERENCES egressos (id) ON DELETE CASCADE,
  CONSTRAINT chamadas_recetor_id_foreign
    FOREIGN KEY (recetor_id) REFERENCES egressos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. AUDITORIA
-- ============================================================================

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id BIGINT UNSIGNED NULL,
  admin_email VARCHAR(255) NULL,
  admin_nome VARCHAR(255) NULL,
  admin_unidade_id BIGINT UNSIGNED NULL,
  admin_unidade_sigla VARCHAR(20) NULL,
  admin_unidade_nome VARCHAR(255) NULL,
  acao VARCHAR(100) NOT NULL,
  alvo_tipo VARCHAR(100) NULL,
  alvo_id BIGINT UNSIGNED NULL,
  descricao VARCHAR(500) NULL,
  dados_antes JSON NULL,
  dados_depois JSON NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(500) NULL,
  severidade ENUM('info','aviso','critico') NOT NULL DEFAULT 'info',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY audit_logs_admin_id_created_at_index (admin_id, created_at),
  KEY audit_logs_severidade_index (severidade),
  KEY audit_logs_acao_index (acao),
  CONSTRAINT audit_logs_admin_id_foreign
    FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- FIM
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;