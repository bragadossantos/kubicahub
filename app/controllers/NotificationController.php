<?php
/**
 * Kubica Hub — Controller: Notification
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class NotificationController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/v1/notifications
     */
    public function list(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        $pag  = $this->paginacao(20);

        $notifications = $this->db->query(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$user['id'], $pag['limite'], $pag['offset']]
        );

        $unread = $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0",
            [$user['id']]
        );

        $this->json([
            'notifications' => $notifications,
            'unread_count'  => (int) ($unread['total'] ?? 0),
        ]);
    }

    /**
     * PATCH /api/v1/notifications/{id}/read
     */
    public function markRead(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        $this->db->execute(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?",
            [(int) $params['id'], $user['id']]
        );
        $this->json(['message' => 'Notificação marcada como lida.']);
    }

    /**
     * PATCH /api/v1/notifications/read-all
     */
    public function markAllRead(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        $this->db->execute(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ?",
            [$user['id']]
        );
        $this->json(['message' => 'Todas as notificações marcadas como lidas.']);
    }
}
