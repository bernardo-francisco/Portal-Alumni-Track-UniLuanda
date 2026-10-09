<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


/* ============================================================
   FORMATAÇÃO
   ============================================================ */

if (!function_exists('formatarData')) {
    function formatarData($data, $formato = 'd/m/Y H:i'): string
    {
        if (!$data) return '-';
        try {
            $date = $data instanceof \DateTime ? $data : new \DateTime($data);
            return $date->format($formato);
        } catch (\Exception $e) {
            return '-';
        }
    }
}

if (!function_exists('formatarMoeda')) {
    function formatarMoeda($valor): string
    {
        if (!$valor) return '0.00 Kz';
        return number_format((float) $valor, 2, ',', '.') . ' Kz';
    }
}

if (!function_exists('gerarSlug')) {
    function gerarSlug(string $texto): string
    {
        $texto = preg_replace('/[^a-zA-Z0-9\s]/', '', $texto);
        $texto = strtolower(trim($texto));
        return preg_replace('/\s+/', '-', $texto);
    }
}

if (!function_exists('truncarTexto')) {
    function truncarTexto(string $texto, int $limite = 100, string $sufixo = '...'): string
    {
        if (strlen($texto) <= $limite) return $texto;
        return substr($texto, 0, $limite) . $sufixo;
    }
}

if (!function_exists('obterIniciais')) {
    function obterIniciais(string $nome): string
    {
        $partes = explode(' ', $nome);
        $iniciais = '';
        foreach (array_slice($partes, 0, 2) as $parte) {
            $iniciais .= strtoupper(substr($parte, 0, 1));
        }
        return $iniciais;
    }
}


/* ============================================================
   STATUS
   ============================================================ */

if (!function_exists('getStatusColor')) {
    function getStatusColor(string $status): string
    {
        $cores = [
            'active' => 'success',
            'inactive' => 'danger',
            'lost_contact' => 'secondary',
            'pendente' => 'warning',
            'aceito' => 'success',
            'recusado' => 'danger',
            'confirmada' => 'success',
            'cancelada' => 'danger',
            'emprego' => 'primary',
            'estagio' => 'info',
            'bolsa' => 'success',
            'curso' => 'warning',
            'evento' => 'secondary',
        ];
        return $cores[$status] ?? 'secondary';
    }
}

if (!function_exists('getStatusLabel')) {
    function getStatusLabel(string $status): string
    {
        $labels = [
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'lost_contact' => 'Sem Contacto',
            'pendente' => 'Pendente',
            'aceito' => 'Aceito',
            'recusado' => 'Recusado',
            'confirmada' => 'Confirmada',
            'cancelada' => 'Cancelada',
            'full_time' => 'Tempo Inteiro',
            'part_time' => 'Tempo Parcial',
            'freelance' => 'Freelance',
            'self_employed' => 'Autónomo',
            'unemployed' => 'Desempregado',
            'student' => 'A Estudar',
            'unknown' => 'Desconhecido',
            'emprego' => 'Emprego',
            'estagio' => 'Estágio',
            'bolsa' => 'Bolsa',
            'curso' => 'Curso',
            'evento' => 'Evento',
        ];
        return $labels[$status] ?? $status;
    }
}


/* ============================================================
   DATAS
   ============================================================ */

if (!function_exists('isValidDate')) {
    function isValidDate($data): bool
    {
        if (!$data) return false;
        try {
            new \DateTime($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('diffForHumans')) {
    function diffForHumans($data): string
    {
        if (!$data) return '-';
        try {
            $date = $data instanceof \DateTime ? $data : new \DateTime($data);
            return $date->diff(now())->format('%d dias, %h horas');
        } catch (\Exception $e) {
            return '-';
        }
    }
}


/* ============================================================
   FICHEIROS
   ============================================================ */

if (!function_exists('uploadFile')) {
    function uploadFile($file, string $pasta, array $permissoes = ['jpg', 'jpeg', 'png', 'pdf']): ?string
    {
        if (!$file || !$file->isValid()) return null;

        $extensao = strtolower($file->getClientOriginalExtension());
        if (!in_array($extensao, $permissoes)) return null;

        $tamanhoMaximo = 5 * 1024 * 1024;
        if ($file->getSize() > $tamanhoMaximo) return null;

        $nome = uniqid() . '_' . time() . '.' . $extensao;
        $path = public_path('uploads/' . $pasta);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $nome);
        return 'uploads/' . $pasta . '/' . $nome;
    }
}

if (!function_exists('deleteFile')) {
    function deleteFile(string $caminho): bool
    {
        if (!$caminho) return false;
        $fullPath = public_path($caminho);
        if (file_exists($fullPath)) return unlink($fullPath);
        return false;
    }
}


/* ============================================================
   SEGURANÇA
   ============================================================ */

if (!function_exists('senhaForte')) {
    function senhaForte(int $comprimento = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@$!%*?&';
        $senha = '';
        for ($i = 0; $i < $comprimento; $i++) {
            $senha .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $senha;
    }
}

if (!function_exists('validarEmail')) {
    function validarEmail(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
}

if (!function_exists('sanitizarInput')) {
    function sanitizarInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}


/* ============================================================
   EMPREGO / GÉNERO
   ============================================================ */

if (!function_exists('getTipoEmpregoLabel')) {
    function getTipoEmpregoLabel(string $tipo): string
    {
        $labels = [
            'full_time' => 'Tempo Inteiro',
            'part_time' => 'Tempo Parcial',
            'freelance' => 'Freelance',
            'self_employed' => 'Autónomo',
            'unemployed' => 'Desempregado',
            'student' => 'A Estudar',
            'unknown' => 'Desconhecido',
        ];
        return $labels[$tipo] ?? $tipo;
    }
}

if (!function_exists('getGeneroLabel')) {
    function getGeneroLabel(string $genero): string
    {
        $labels = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro',
        ];
        return $labels[$genero] ?? $genero;
    }
}


/* ============================================================
   AUTENTICAÇÃO
   ============================================================ */

if (!function_exists('isAdmin')) {
    function isAdmin(): bool
    {
        if (!Auth::check()) return false;
        $user = Auth::user();
        if (method_exists($user, 'isAdmin')) return $user->isAdmin();
        return ($user->role === 'admin' || $user->tipo === 'admin');
    }
}

if (!function_exists('isEgresso')) {
    function isEgresso(): bool
    {
        if (!Auth::check()) return false;
        $user = Auth::user();
        if (method_exists($user, 'isEgresso')) return $user->isEgresso();
        return ($user->role === 'egresso' || $user->tipo === 'egresso');
    }
}


/* ============================================================
   FOTOS DE EGRESSO
   ============================================================ */

if (!function_exists('getFotoEgresso')) {
    function getFotoEgresso($user = null)
    {
        if (!$user) $user = Auth::user();
        if (!$user) return null;

        $egresso = \App\Models\Egresso::where('user_id', $user->id)->first();
        if ($egresso && $egresso->foto_url) return asset($egresso->foto_url);
        if ($user->photo_url) return asset($user->photo_url);

        return null;
    }
}

if (!function_exists('getFotoEgressoById')) {
    function getFotoEgressoById($egressoId)
    {
        if (!$egressoId) return null;
        $egresso = \App\Models\Egresso::find($egressoId);
        if ($egresso && $egresso->foto_url) return asset($egresso->foto_url);
        return null;
    }
}

if (!function_exists('getAvatarEgresso')) {
    function getAvatarEgresso($user = null, $size = 40, $class = '')
    {
        if (!$user) $user = Auth::user();
        if (!$user) {
            return '<div class="avatar-placeholder" style="width: ' . $size . 'px; height: ' . $size . 'px;">?</div>';
        }

        $foto = getFotoEgresso($user);
        $nome = $user->name ?? 'Egresso';
        $iniciais = obterIniciais($nome);

        if ($foto) {
            return '<img src="' . $foto . '" alt="' . $nome . '" class="rounded-circle ' . $class . '" style="width: ' . $size . 'px; height: ' . $size . 'px; object-fit: cover;">';
        }

        return '<div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center ' . $class . '" style="width: ' . $size . 'px; height: ' . $size . 'px; background: #1a56db; color: #fff; font-weight: bold; font-size: ' . ($size / 2) . 'px;">' . $iniciais . '</div>';
    }
}


/* ============================================================
   IMAGEM → BASE64 (genérica)
   ============================================================ */

if (!function_exists('imagemParaBase64')) {
    function imagemParaBase64($caminho)
    {
        if (empty($caminho)) return null;

        $caminho = str_replace('http://localhost:8000/', '', $caminho);
        $caminho = str_replace('https://localhost:8000/', '', $caminho);
        $caminho = str_replace('public/', '', $caminho);
        $caminho = str_replace('storage/', '', $caminho);

        $caminho = str_replace('\\', '/', $caminho);
        $caminho = preg_replace('#/+#', '/', $caminho);
        $caminho = trim($caminho);

        if (!str_starts_with($caminho, 'uploads/')) {
            $nomeArquivo = basename($caminho);
            if (!empty($nomeArquivo) && str_contains($nomeArquivo, 'egresso_')) {
                $caminho = 'uploads/egressos/' . $nomeArquivo;
            } else {
                return null;
            }
        }

        $caminhoCompleto = public_path($caminho);

        if (!file_exists($caminhoCompleto)) {
            $caminhoCompleto = storage_path('app/public/' . $caminho);
            if (!file_exists($caminhoCompleto)) {
                Log::warning('Imagem não encontrada: ' . $caminho);
                return null;
            }
        }

        $tipo = mime_content_type($caminhoCompleto);
        if (!$tipo) $tipo = 'image/jpeg';

        $conteudo = file_get_contents($caminhoCompleto);
        if ($conteudo === false) {
            Log::error('Erro ao ler imagem: ' . $caminhoCompleto);
            return null;
        }

        return 'data:' . $tipo . ';base64,' . base64_encode($conteudo);
    }
}


/* ============================================================
   ✅ FOTO → BASE64 (para DOMPDF)
   ============================================================
   Esta função TEM DE ESTAR no top-level do ficheiro.
   NÃO pode estar dentro de outra função.
   ============================================================ */

if (!function_exists('fotoBase64')) {
    function fotoBase64(?string $fotoUrl): ?string
    {
        if (empty($fotoUrl)) {
            return null;
        }

        if (str_starts_with($fotoUrl, 'data:image')) {
            return $fotoUrl;
        }

        $path = parse_url($fotoUrl, PHP_URL_PATH) ?: $fotoUrl;
        $path = ltrim($path, '/');
        $path = preg_replace('#^(storage|public)/#', '', $path);

        $caminhosPossiveis = [
            public_path($path),
            public_path('uploads/' . basename($path)),
            public_path('uploads/egressos/' . basename($path)),
            storage_path('app/public/' . $path),
            storage_path('app/public/uploads/' . basename($path)),
            storage_path('app/public/uploads/egressos/' . basename($path)),
        ];

        foreach ($caminhosPossiveis as $caminho) {
            if (file_exists($caminho) && is_file($caminho) && filesize($caminho) > 0) {
                $ext = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));

                $mime = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png'         => 'image/png',
                    'webp'        => 'image/webp',
                    'gif'         => 'image/gif',
                    'bmp'         => 'image/bmp',
                    'svg'         => 'image/svg+xml',
                    default       => 'image/jpeg',
                };

                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($caminho));
            }
        }

        Log::warning('fotoBase64: imagem não encontrada', ['url' => $fotoUrl]);

        return null;
    }
}