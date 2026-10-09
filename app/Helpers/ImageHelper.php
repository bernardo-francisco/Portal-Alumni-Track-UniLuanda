<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ImageHelper
{
    /**
     * Converte uma imagem em base64, testando múltiplos formatos de URL.
     *
     * @param  string|null  $fotoUrl
     * @return string|null  Data URI ou null
     */
    public static function toBase64(?string $fotoUrl): ?string
    {
        if (empty($fotoUrl)) {
            return null;
        }

        // Já é data URI → devolve como está
        if (Str::startsWith($fotoUrl, 'data:image')) {
            return $fotoUrl;
        }

        // ============================================================
        // ✅ 1. Extrair o caminho relativo (remover domínio/URL)
        // ============================================================
        $path = parse_url($fotoUrl, PHP_URL_PATH);

        if (!$path) {
            $path = $fotoUrl;
        }

        // Remove a barra inicial
        $path = ltrim($path, '/');

        // Remove "storage/" e "public/" se existirem
        $path = preg_replace('#^(storage|public)/#', '', $path);

        // ============================================================
        // ✅ 2. Testar vários caminhos possíveis
        // ============================================================
        $caminhosPossiveis = [
            public_path($path),                                          // public/uploads/...
            public_path('uploads/' . basename($path)),                   // public/uploads/foto.jpg
            public_path('uploads/egressos/' . basename($path)),          // public/uploads/egressos/foto.jpg
            storage_path('app/public/' . $path),                         // storage/app/public/uploads/...
            storage_path('app/public/uploads/' . basename($path)),       // storage/app/public/uploads/foto.jpg
            storage_path('app/public/uploads/egressos/' . basename($path)), // storage/.../egressos/foto.jpg
        ];

        foreach ($caminhosPossiveis as $caminho) {
            if (file_exists($caminho) && is_file($caminho) && filesize($caminho) > 0) {
                return self::encodeImage($caminho);
            }
        }

        return null;
    }


    /**
     * Codifica um ficheiro de imagem em data URI base64.
     */
    private static function encodeImage(string $caminho): string
    {
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