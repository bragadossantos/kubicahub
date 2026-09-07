<?php
/**
 * Kubica Hub — Controller: Profile
 * CORRIGIDO: uso de Database::getInstance() em vez de $this->user->db (protected)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;

class ProfileController extends Controller
{
    private User $user;
    private Database $db;

    public function __construct()
    {
        $this->user = new User();
        $this->db   = Database::getInstance();
    }

    /**
     * GET /api/v1/profile/{userId}
     */
    public function show(array $params, array $body): void
    {
        $this->requireAuth();
        $userId = (int) $params['userId'];
        
        $perfil = $this->user->publicProfile($userId);
        if (!$perfil) {
            $this->notFound('Perfil não encontrado.');
        }

        // Decode JSON skills para array
        if (!empty($perfil['skills']) && is_string($perfil['skills'])) {
            $perfil['skills'] = json_decode($perfil['skills'], true) ?? [];
        }

        // Badges do utilizador
        $badges = $this->db->query(
            "SELECT badge_type, awarded_at FROM badges WHERE user_id = ? ORDER BY awarded_at DESC",
            [$userId]
        );

        // Estatísticas
        $stats = $this->getUserStats($userId);

        $this->json([
            'profile' => $perfil,
            'badges'  => $badges,
            'stats'   => $stats
        ]);
    }

    /**
     * PATCH /api/v1/profile/{userId}
     */
    public function update(array $params, array $body): void
    {
        $this->requireAuth();
        
        $userActual = $this->utilizadorActual();
        $userId     = (int) $params['userId'];

        if ($userActual['id'] !== $userId && $userActual['role'] !== 'admin') {
            $this->forbidden('Não tem permissão para editar este perfil.');
        }

        // Campos editáveis pelo utilizador
        $permitidos = ['name', 'bio', 'availability_hours', 'skills', 'profile_photo', 'profile_type'];
        $actualizar = [];

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $body)) {
                $val = $body[$campo];
                if ($campo === 'skills' && is_array($val)) {
                    $val = json_encode($val);
                } elseif (is_string($val)) {
                    $val = $this->sanitizar($val);
                }
                $actualizar[$campo] = $val;
            }
        }

        // Processar upload de foto de perfil (multipart/form-data)
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            require_once dirname(__DIR__, 2) . '/app/helpers/upload.php';
            $upload = \App\Helpers\Upload::processar($_FILES['photo'], 'avatar', $userActual['id']);
            if ($upload['success']) {
                $actualizar['profile_photo'] = $upload['path'];
            } else {
                $this->erro($upload['message'], 422);
            }
        }

        if (!empty($actualizar)) {
            $actualizar['updated_at'] = date('Y-m-d H:i:s');
            $this->db->execute(
                'UPDATE users SET ' . implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($actualizar))) . ' WHERE id = ?',
                [...array_values($actualizar), $userId]
            );
        }

        $this->json(['message' => 'Perfil actualizado com sucesso.']);
    }

    /**
     * GET /api/v1/profile/{userId}/badges
     */
    public function badges(array $params, array $body): void
    {
        $userId = (int) $params['userId'];
        $badges = $this->db->query(
            "SELECT badge_type, awarded_at FROM badges WHERE user_id = ? ORDER BY awarded_at DESC",
            [$userId]
        );
        $this->json(['badges' => $badges]);
    }

    /**
     * Estatísticas do utilizador.
     * @return array<string, mixed>
     */
    private function getUserStats(int $userId): array
    {
        $row = $this->db->queryOne(
            "SELECT 
               (SELECT COUNT(*) FROM ideas WHERE inventor_id = ?) AS ideias_submetidas,
               (SELECT COUNT(*) FROM matches WHERE builder_id = ? AND status = 'accepted') AS propostas_aceites,
               (SELECT COUNT(*) FROM team_members tm JOIN teams t ON t.id = tm.team_id WHERE tm.user_id = ?) AS equipas_activas",
            [$userId, $userId, $userId]
        );
        return $row ?? [];
    }
}
