<?php
/**
 * Kubica Hub — Helper para Upload de Ficheiros
 */

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Database;

class Upload
{
    private const MAX_SIZE_MB = 10;
    private const ALLOWED_MIMES = [
        'avatar'     => ['image/jpeg', 'image/png', 'image/webp'],
        'entregavel' => ['application/pdf', 'application/zip', 'image/png', 'image/jpeg'],
        'documento'  => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    ];

    /**
     * Tenta guardar o ficheiro e regista-o na base de dados.
     * @return array<string, mixed> ['success' => bool, 'message' => string, 'file_id' => int|null, 'path' => string|null]
     */
    public static function processar(array $ficheiro, string $tipo, int $userId, ?int $refId = null): array
    {
        if ($ficheiro['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erro no envio do ficheiro.'];
        }

        // Validar Tamanho
        $tamanhoMaximo = self::MAX_SIZE_MB * 1024 * 1024;
        if ($ficheiro['size'] > $tamanhoMaximo) {
            return ['success' => false, 'message' => 'Ficheiro demasiado grande. Máximo ' . self::MAX_SIZE_MB . 'MB.'];
        }

        // Validar MIME
        $mimeTipesPermitidos = self::ALLOWED_MIMES[$tipo] ?? [];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $ficheiro['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $mimeTipesPermitidos, true)) {
            return ['success' => false, 'message' => 'Tipo de ficheiro não permitido para esta categoria.'];
        }

        // Extensão segura e Nome Único
        $extensao = pathinfo($ficheiro['name'], PATHINFO_EXTENSION);
        $novoNome = gerarUUID() . '.' . strtolower($extensao);
        
        $pastaDestino = defined('UPLOAD_PATH') ? UPLOAD_PATH . '/' . $tipo : dirname(__DIR__, 2) . '/public/uploads/' . $tipo;
        
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }
        
        $caminhoFinal = $pastaDestino . '/' . $novoNome;
        $caminhoRelativo = '/uploads/' . $tipo . '/' . $novoNome;

        // Mover ficheiro
        if (!move_uploaded_file($ficheiro['tmp_name'], $caminhoFinal)) {
            return ['success' => false, 'message' => 'Falha ao guardar o ficheiro no servidor.'];
        }

        // Registar na Base de Dados
        $db = Database::getInstance();
        $fileId = $db->execute(
            "INSERT INTO ficheiros (user_id, tipo, nome_original, nome_ficheiro, caminho, mime_type, tamanho_bytes, referencia_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$userId, $tipo, $ficheiro['name'], $novoNome, $caminhoRelativo, $mimeType, $ficheiro['size'], $refId]
        );

        return [
            'success' => true,
            'message' => 'Ficheiro enviado com sucesso.',
            'file_id' => $fileId,
            'path'    => $caminhoRelativo
        ];
    }
}
