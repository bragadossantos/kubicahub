<?php
/**
 * Kubica Hub — Model: AuditLog
 * Registo de acções críticas (compliance FKCU)
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';

    /**
     * Regista uma acção.
     */
    public function log(string $acao, ?string $recurso = null, ?int $recursoId = null, ?array $antes = null, ?array $depois = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $this->create([
            'user_id'      => $userId,
            'acao'         => $acao,
            'recurso'      => $recurso,
            'recurso_id'   => $recursoId,
            'dados_antes'  => $antes ? json_encode($antes, JSON_UNESCAPED_UNICODE) : null,
            'dados_depois' => $depois ? json_encode($depois, JSON_UNESCAPED_UNICODE) : null,
            'ip'           => $ip,
            'user_agent'   => $userAgent,
            'created_at'   => date('Y-m-d H:i:s')
        ]);
    }
}
