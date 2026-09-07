<?php
/**
 * Kubica Hub — Controller: Admin
 * CORRIGIDO: requireAdmin() → requireRole, validateRequired() → validarObrigatorios,
 *            error() → erro(), acesso db via Database::getInstance()
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Idea;
use App\Models\User;

class AdminController extends Controller
{
    private Idea $idea;
    private User $user;
    private Database $db;

    public function __construct()
    {
        $this->idea = new Idea();
        $this->user = new User();
        $this->db   = Database::getInstance();
    }

    /** Helper: verificar se o utilizador é admin */
    private function checkAdmin(): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        if ($user['role'] !== 'admin') {
            $this->forbidden('Acesso restrito a administradores.');
        }
    }

    /**
     * GET /api/v1/admin/kpis — Dashboard executivo com métricas globais.
     */
    public function kpis(array $params, array $body): void
    {
        $this->checkAdmin();

        $totalUsers     = $this->user->count();
        $totalInventors = $this->user->count(['role' => 'inventor']);
        $totalBuilders  = $this->user->count(['role' => 'builder']);
        $totalIdeas     = $this->idea->count();
        $pendingIdeas   = $this->idea->count(['status' => 'pending']);
        $approvedIdeas  = $this->idea->count(['status' => 'approved']);

        $totalTeams = $this->db->queryOne("SELECT COUNT(*) AS n FROM teams")['n'] ?? 0;
        $incubating = $this->db->queryOne("SELECT COUNT(*) AS n FROM ideas WHERE status = 'incubating'")['n'] ?? 0;
        $demoDays   = $this->db->queryOne("SELECT COUNT(*) AS n FROM speed_dating_sessions WHERE status = 'scheduled'")['n'] ?? 0;

        $sandboxRow = $this->db->queryOne("SELECT COALESCE(SUM(amount_usd), 0) AS total FROM sandbox_transactions WHERE status = 'approved'");
        $sandboxTotal = (float) ($sandboxRow['total'] ?? 0);

        $this->json([
            'total_users'        => (int) $totalUsers,
            'total_inventors'    => (int) $totalInventors,
            'total_builders'     => (int) $totalBuilders,
            'total_ideas'        => (int) $totalIdeas,
            'pending_ideas'      => (int) $pendingIdeas,
            'approved_ideas'     => (int) $approvedIdeas,
            'total_teams'        => (int) $totalTeams,
            'incubating_count'   => (int) $incubating,
            'demo_day_sessions'  => (int) $demoDays,
            'sandbox_total_usd'  => $sandboxTotal,
        ]);
    }

    /**
     * GET /api/v1/admin/ideas/pending — Fila de triagem de ideias.
     */
    public function pendingIdeas(array $params, array $body): void
    {
        $this->checkAdmin();

        $ideas = $this->db->query(
            "SELECT i.*, u.name AS inventor_name, u.university AS inventor_university
             FROM ideas i LEFT JOIN users u ON u.id = i.inventor_id
             WHERE i.status = 'pending'
             ORDER BY i.created_at ASC"
        );
        $this->json(['ideas' => $ideas, 'total' => count($ideas)]);
    }

    /**
     * GET /api/v1/admin/sandbox/pending — Pedidos de financiamento pendentes.
     */
    public function pendingSandbox(array $params, array $body): void
    {
        $this->checkAdmin();

        $pending = $this->db->query(
            "SELECT st.*, t.name AS team_name, u.name AS requester_name
             FROM sandbox_transactions st
             LEFT JOIN teams t ON t.id = st.team_id
             LEFT JOIN users u ON u.id = st.requested_by
             ORDER BY st.requested_at ASC"
        );
        $this->json(['transactions' => $pending, 'total' => count($pending)]);
    }

    /**
     * POST /api/v1/admin/matching/run — Executar algoritmo de afinidade para todas as ideias aprovadas.
     */
    public function runMatchingAlgorithm(array $params, array $body): void
    {
        $this->checkAdmin();

        // Buscar todas as ideias aprovadas sem equipa completa
        $ideas = $this->db->query(
            "SELECT i.* FROM ideas i
             LEFT JOIN teams t ON t.idea_id = i.id
             WHERE i.status = 'approved' AND t.id IS NULL"
        );

        $proposalsCreated = 0;

        foreach ($ideas as $idea) {
            $rolesNeeded = json_decode($idea['roles_needed'] ?? '[]', true);
            foreach ($rolesNeeded as $role) {
                // Buscar top 3 builders compatíveis por role/faculdade
                $faculty = match ($role) {
                    'CTO' => 'Engenharia',
                    'CFO' => 'Gestão',
                    'CLO' => 'Direito',
                    'CMO' => 'Marketing',
                    default => null,
                };

                $builders = $this->db->query(
                    "SELECT u.id FROM users u
                     WHERE u.role = 'builder' AND u.is_active = 1 AND u.is_verified = 1
                     " . ($faculty ? "AND u.faculty = '$faculty'" : '') . "
                     AND u.id NOT IN (
                         SELECT builder_id FROM matches WHERE idea_id = ? AND status IN ('pending','accepted')
                     )
                     LIMIT 3",
                    [(int) $idea['id']]
                );

                foreach ($builders as $builder) {
                    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
                    $this->db->execute(
                        "INSERT IGNORE INTO matches (idea_id, builder_id, proposed_role, equity_pct, hours_per_week, vesting_months, cliff_months, status, expires_at)
                         VALUES (?, ?, ?, 20.00, 20, 36, 12, 'pending', ?)",
                        [(int) $idea['id'], (int) $builder['id'], $role, $expires]
                    );
                    $proposalsCreated++;
                }
            }
        }

        $this->json([
            'message'           => 'Algoritmo de matching executado com sucesso.',
            'proposals_created' => $proposalsCreated,
            'ideas_processed'   => count($ideas),
        ]);
    }

    /**
     * GET /api/v1/admin/mentors — Lista de mentores.
     */
    public function mentors(array $params, array $body): void
    {
        $this->checkAdmin();
        $mentors = $this->db->query("SELECT id, name, email, university, faculty, bio, availability_hours FROM users WHERE role = 'mentor' ORDER BY name");
        $this->json(['mentors' => $mentors, 'total' => count($mentors)]);
    }

    /**
     * GET /api/v1/admin/users — Lista de utilizadores.
     */
    public function users(array $params, array $body): void
    {
        $this->checkAdmin();
        $role = $_GET['role'] ?? null;

        $sql = "SELECT id, name, email, role, university, faculty, is_active, is_verified, created_at FROM users";
        $args = [];
        if ($role) {
            $sql .= " WHERE role = ?";
            $args[] = $role;
        }
        $sql .= " ORDER BY created_at DESC";

        $users = $this->db->query($sql, $args);
        $this->json(['users' => $users, 'total' => count($users)]);
    }

    /**
     * PATCH /api/v1/admin/users/{id}/status — Activar/suspender utilizador.
     */
    public function toggleUserStatus(array $params, array $body): void
    {
        $this->checkAdmin();

        $userId   = (int) $params['id'];
        $isActive = isset($body['is_active']) ? (int) $body['is_active'] : null;

        if ($isActive === null) {
            $this->erro('is_active obrigatório (0 ou 1).', 422);
        }

        $this->db->execute("UPDATE users SET is_active = ?, updated_at = NOW() WHERE id = ?", [$isActive, $userId]);
        $this->json(['message' => 'Estado do utilizador actualizado com sucesso.']);
    }
}
