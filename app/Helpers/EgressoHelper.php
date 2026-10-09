<?php

namespace App\Helpers;

use App\Models\Egresso;
use Illuminate\Support\Facades\Auth;

class EgressoHelper
{
    /**
     * Obter a foto do egresso logado
     */
    public static function getFotoEgresso()
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }

        $egresso = Egresso::where('user_id', $user->id)->first();
        
        if ($egresso && $egresso->foto_url) {
            return self::getFoto($egresso);
        }
        
        if ($user->photo_url) {
            return asset($user->photo_url);
        }
        
        return null;
    }

    /**
     * Obter a foto de um egresso específico (pelo objeto ou ID)
     */
    public static function getFoto($egresso, $size = 60)
    {
        // Se for um ID, buscar o egresso
        if (is_numeric($egresso)) {
            $egresso = Egresso::find($egresso);
        }

        // Se não tiver egresso, retorna avatar padrão
        if (!$egresso) {
            return self::getAvatarDefault($size);
        }

        // Verificar se tem foto
        if (!empty($egresso->foto_url)) {
            $fotoUrl = $egresso->foto_url;
            
            // ============================================================
            // 1. SE FOR UMA URL COMPLETA (http://...)
            // ============================================================
            if (filter_var($fotoUrl, FILTER_VALIDATE_URL)) {
                // Extrair o caminho da URL
                $caminho = parse_url($fotoUrl, PHP_URL_PATH);
                if ($caminho) {
                    $caminho = ltrim($caminho, '/');
                    // Verificar se o arquivo existe no public
                    if (file_exists(public_path($caminho))) {
                        return $fotoUrl;
                    }
                    // Se não existir, retorna a URL mesmo assim
                    // (pode ser que a imagem esteja em um servidor externo)
                    return $fotoUrl;
                }
                return $fotoUrl;
            }
            
            // ============================================================
            // 2. SE FOR UM CAMINHO RELATIVO (uploads/...)
            // ============================================================
            
            // 2.1 Tentar com o caminho direto
            $caminho = public_path($fotoUrl);
            if (file_exists($caminho)) {
                return asset($fotoUrl);
            }
            
            // 2.2 Tentar com 'storage/'
            $caminhoStorage = str_replace('uploads/', 'storage/', $fotoUrl);
            if (file_exists(public_path($caminhoStorage))) {
                return asset($caminhoStorage);
            }
            
            // 2.3 Tentar apenas com o nome do arquivo em 'uploads/egressos/'
            $nomeArquivo = basename($fotoUrl);
            $caminhoUploads = 'uploads/egressos/' . $nomeArquivo;
            if (file_exists(public_path($caminhoUploads))) {
                return asset($caminhoUploads);
            }
            
            // 2.4 Se não encontrar, retorna asset mesmo assim
            // (pode ser que a imagem exista mas em outro local)
            return asset($fotoUrl);
        }

        // Se não tiver foto, gera avatar com iniciais
        return self::getAvatarIniciais($egresso->nome_completo ?? 'E', $size);
    }

    /**
     * Gera um avatar com as iniciais (SVG em Base64)
     */
    public static function getAvatarIniciais($nome, $size = 60)
    {
        // Obter iniciais
        $iniciais = self::getIniciais($nome);
        $iniciais = $iniciais ?: 'E';

        // Cores para o avatar
        $cores = [
            '#1a56db', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#06b6d4', '#f97316', '#6366f1', '#14b8a6'
        ];
        
        // Escolher cor baseada no nome
        $indice = abs(crc32($nome)) % count($cores);
        $cor = $cores[$indice];

        // Tamanho da fonte (proporcional ao tamanho do avatar)
        $fontSize = $size * 0.45;

        // Gerar SVG em Base64
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">
                <circle cx="' . ($size/2) . '" cy="' . ($size/2) . '" r="' . ($size/2) . '" fill="' . $cor . '"/>
                <text x="' . ($size/2) . '" y="' . ($size/2 + $fontSize * 0.35) . '" 
                      font-size="' . $fontSize . '" 
                      text-anchor="middle" 
                      fill="white" 
                      font-family="Arial, sans-serif" 
                      font-weight="bold">' . $iniciais . '</text>
            </svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Avatar padrão
     */
    public static function getAvatarDefault($size = 60)
    {
        return self::getAvatarIniciais('E', $size);
    }

    /**
     * Obter as iniciais do nome
     */
    public static function getIniciais($nome)
    {
        if (empty($nome)) {
            return 'E';
        }

        $palavras = explode(' ', trim($nome));
        $iniciais = '';
        
        foreach (array_slice($palavras, 0, 2) as $palavra) {
            if (!empty($palavra)) {
                $iniciais .= strtoupper(substr($palavra, 0, 1));
            }
        }
        
        return $iniciais ?: 'E';
    }
}