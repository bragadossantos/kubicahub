<?php
/**
 * Kubica Hub — Middleware: Autenticação
 * Verifica se o utilizador tem sessão activa. Bloqueia com 401 se não.
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;

class AuthMiddleware
{
    /**
     * Tratar o pedido. Devolve true para continuar, false para interromper.
     */
    public function tratar(): bool
    {
        // Verificar sessão activa
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'data'    => null,
                'message' => 'Sessão expirada. Por favor inicia sessão novamente.',
                'errors'  => [],
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }

        // Verificar expiração de sessão (2 horas de inactividade)
        if (!empty($_SESSION['login_time']) && (time() - (int) $_SESSION['login_time']) > 7200) {
            session_destroy();
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'data'    => null,
                'message' => 'Sessão expirada por inactividade. Por favor inicia sessão novamente.',
                'errors'  => [],
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }

        // Verificar se a conta ainda está activa na BD (segurança adicional)
        try {
            $db   = Database::getInstance();
            $user = $db->queryOne('SELECT is_active FROM users WHERE id = ? LIMIT 1', [(int) $_SESSION['user_id']]);
            if (!$user || !$user['is_active']) {
                session_destroy();
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Esta conta foi suspensa. Contacta a coordenação FKCU.',
                    'errors'  => [],
                ], JSON_UNESCAPED_UNICODE);
                return false;
            }
        } catch (\Throwable $e) {
            // Se a BD não estiver disponível, deixar passar (fail open para não bloquear tudo)
        }

        return true;
    }
}
