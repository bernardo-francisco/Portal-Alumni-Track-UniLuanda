# Portal Alumni Track — Universidade de Luanda

Portal Alumni Track é uma plataforma que conecta ex-estudantes da Universidade de Luanda, promovendo networking, oportunidades profissionais e fortalecendo a comunidade académica.

---

## Funcionalidades

- Rede de Egressos — Encontre antigos colegas de turma
- Portal de Carreiras — Vagas de emprego e estágios
- Mapa de Egressos — Localização interativa por cidade/país
- Eventos — Workshops, palestras e feiras de emprego
- Feed — Publicações e interação entre egressos
- Mensagens — Comunicação direta entre membros
- Verificação de Certificados — Validação pública
- Chatbot com IA — Assistente inteligente
- Painel Administrativo — Gestão completa da plataforma
- Videochamadas — Chamadas entre egressos ou administrador

---

## Tecnologias

| Camada | Tecnologia |
|--------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Blade, Bootstrap 5|
| Base de Dados | MySQL |
| Tempo Real | Laravel Reverb + WebRTC (PeerJS) |
| Build | Vite |
| Autenticação | Laravel Auth + Sanctum |

---

## Instalação

### Pré-requisitos

- PHP 8.2
- Composer
- Node.js 18+
- MySQL

### Passos

```bash
# 1. Clonar o repositório
git clone git@github.com:bernardo-francisco/Portal-Alumni-Track-UniLuanda.git
cd Portal-Alumni-Track-UniLuanda

# 2. Instalar dependências PHP
composer install

# 3. Instalar dependências JavaScript
npm install

# 4. Copiar ficheiro de ambiente
cp .env.example .env

# 5. Gerar chave da aplicação
php artisan key:generate

# 6. Configurar base de dados no .env
# DB_DATABASE=portal_alumni
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Migrar e popular base de dados
php artisan migrate --seed

# 8. Compilar assets
npm run build

# 9. Iniciar servidor
php artisan serve