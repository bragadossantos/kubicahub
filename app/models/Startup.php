<?php
/**
 * Kubica Hub — Model: Startup (Equipa + Incubação)
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Startup extends Model
{
    protected string $table = 'teams';

    public const FKCU_PHASES = ['captacao', 'matchmaking', 'cocriacao', 'graduacao'];

    /**
     * Criar equipa (team) após match aceite.
     * @param array<string, mixed> $data
     */
    public function createTeam(array $data): int
    {
        return $this->create([
            'idea_id'       => $data['idea_id'],
            'name'          => $data['name'],
            'fkcu_phase'    => 'cocriacao',
            'mou_signed'    => 0,
            'sandbox_balance_usd' => 0,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Dashboard completo da startup — dados agregados.
     * @return array<string, mixed>|null
     */
    public function dashboard(int $teamId): ?array
    {
        $team = $this->db->queryOne(
            "SELECT t.*, i.title AS idea_title, i.sector, i.pdn_axis
             FROM teams t JOIN ideas i ON i.id = t.idea_id
             WHERE t.id = ? LIMIT 1",
            [$teamId]
        );
        if (!$team) {
            return null;
        }

        // Membros da equipa
        $team['members'] = $this->db->query(
            "SELECT tm.*, u.name, u.university, u.faculty, u.profile_photo
             FROM team_members tm
             JOIN users u ON u.id = tm.user_id
             WHERE tm.team_id = ?",
            [$teamId]
        );

        // Entregáveis por role
        $team['deliverables'] = $this->db->query(
            'SELECT * FROM deliverables WHERE team_id = ? ORDER BY submitted_at DESC',
            [$teamId]
        );

        // Saldo Sandbox
        $balance = $this->db->queryOne(
            "SELECT COALESCE(SUM(CASE WHEN status='approved' THEN amount_usd ELSE 0 END), 0) AS spent
             FROM sandbox_transactions WHERE team_id = ?",
            [$teamId]
        );
        $team['sandbox_spent'] = (float) ($balance['spent'] ?? 0);

        return $team;
    }

    /**
     * Métricas de tração (KPIs).
     * @return array<int, array<string, mixed>>
     */
    public function tractionMetrics(int $teamId): array
    {
        return $this->db->query(
            'SELECT * FROM traction_metrics WHERE team_id = ? ORDER BY recorded_at DESC LIMIT 20',
            [$teamId]
        );
    }

    /**
     * Adicionar métrica de tração.
     * @param array<string, mixed> $data
     */
    public function addTraction(array $data): int
    {
        return $this->db->execute(
            'INSERT INTO traction_metrics (team_id, metric_name, value, unit, recorded_at)
             VALUES (?, ?, ?, ?, NOW())',
            [$data['team_id'], $data['metric_name'], $data['value'], $data['unit'] ?? '']
        );
    }

    /**
     * Entregáveis da equipa filtrados por papel e estado.
     * @return array<int, array<string, mixed>>
     */
    public function deliverables(int $teamId, ?string $role = null): array
    {
        if ($role) {
            return $this->db->query(
                "SELECT d.*, u.name AS submitted_by_name
                 FROM deliverables d LEFT JOIN users u ON u.id = d.submitted_by
                 WHERE d.team_id = ? AND d.role = ? ORDER BY d.created_at DESC",
                [$teamId, $role]
            );
        }
        return $this->db->query(
            "SELECT d.*, u.name AS submitted_by_name
             FROM deliverables d LEFT JOIN users u ON u.id = d.submitted_by
             WHERE d.team_id = ? ORDER BY d.role, d.created_at DESC",
            [$teamId]
        );
    }

    /**
     * Submeter entregável.
     * @param array<string, mixed> $data
     */
    public function submitDeliverable(array $data): int
    {
        return $this->db->execute(
            'INSERT INTO deliverables (team_id, role, title, description, file_url, submitted_by, status, submitted_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $data['team_id'],
                $data['role'],
                $data['title'],
                $data['description'] ?? null,
                $data['file_url']    ?? null,
                $data['submitted_by'],
                'pending_review',
            ]
        );
    }

    /**
     * Actualizar fase FKCU da equipa.
     */
    public function advancePhase(int $teamId, string $phase): int
    {
        return $this->db->execute(
            'UPDATE teams SET fkcu_phase = ?, updated_at = NOW() WHERE id = ?',
            [$phase, $teamId]
        );
    }
}
