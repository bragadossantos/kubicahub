<?php
/**
 * Kubica Hub — Model: Match (Matchmaking entre ideias e builders)
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Match extends Model
{
    protected string $table = 'matches';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_EXPIRED  = 'expired';

    /**
     * Enviar proposta societária (inventor → builder).
     * @param array<string, mixed> $data
     */
    public function propose(array $data): int
    {
        return $this->create([
            'idea_id'        => (int) $data['idea_id'],
            'builder_id'     => (int) $data['builder_id'],
            'proposed_role'  => $data['proposed_role'] ?? ($data['role'] ?? 'CTO'),
            'equity_pct'     => (float) $data['equity_pct'],
            'hours_per_week' => (int) $data['hours_per_week'],
            'vesting_months' => (int) ($data['vesting_months'] ?? 36),
            'cliff_months'   => (int) ($data['cliff_months']   ?? 12),
            'message'        => $data['message'] ?? null,
            'status'         => self::STATUS_PENDING,
            'expires_at'     => date('Y-m-d H:i:s', strtotime('+7 days')),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Responder a proposta (builder aceita ou recusa).
     */
    public function respond(int $id, string $status, ?string $reason = null): int
    {
        return $this->db->execute(
            'UPDATE matches SET status = ?, decline_reason = ?, responded_at = NOW() WHERE id = ?',
            [$status, $reason, $id]
        );
    }

    /**
     * Propostas pendentes recebidas por um builder.
     * @return array<int, array<string, mixed>>
     */
    public function pendingForBuilder(int $builderId): array
    {
        return $this->db->query(
            "SELECT m.*, i.title AS idea_title, i.sector, u.name AS inventor_name, u.university
             FROM matches m
             JOIN ideas i ON i.id = m.idea_id
             JOIN users u ON u.id = i.inventor_id
             WHERE m.builder_id = ? AND m.status = 'pending'
             ORDER BY m.created_at DESC",
            [$builderId]
        );
    }

    /**
     * Propostas enviadas por um inventor (para uma ideia).
     * @return array<int, array<string, mixed>>
     */
    public function sentForIdea(int $ideaId): array
    {
        return $this->db->query(
            "SELECT m.*, u.name AS builder_name, u.university, u.faculty
             FROM matches m
             JOIN users u ON u.id = m.builder_id
             WHERE m.idea_id = ?
             ORDER BY m.created_at DESC",
            [$ideaId]
        );
    }

    /**
     * Algoritmo de afinidade operacional — pontuação de compatibilidade.
     * @return array<int, array<string, mixed>>
     */
    public function affinityScore(int $ideaId, string $roleNeeded): array
    {
        $idea = $this->db->queryOne('SELECT * FROM ideas WHERE id = ? LIMIT 1', [$ideaId]);
        if (!$idea) {
            return [];
        }

        $builders = $this->db->query(
            "SELECT u.*, 
                    (SELECT COUNT(*) FROM matches m WHERE m.builder_id = u.id AND m.status = 'accepted') AS active_teams
             FROM users u
             WHERE u.role = 'builder' AND u.faculty LIKE ? AND u.is_verified = 1
             ORDER BY u.availability_hours DESC
             LIMIT 20",
            ['%' . $this->facultyForRole($roleNeeded) . '%']
        );

        $scored = [];
        foreach ($builders as $builder) {
            $score = $this->calculateScore($idea, $builder, $roleNeeded);
            if ($score >= 50) {
                $builder['match_score'] = $score;
                $scored[] = $builder;
            }
        }

        usort($scored, fn($a, $b) => $b['match_score'] <=> $a['match_score']);
        return $scored;
    }

    private function calculateScore(array $idea, array $builder, string $role): int
    {
        $score = 0;

        $hours = (int) ($builder['availability_hours'] ?? 0);
        $score += min(25, (int) ($hours / 20 * 25));

        $expectedFaculty = $this->facultyForRole($role);
        if (str_contains(strtolower($builder['faculty'] ?? ''), strtolower($expectedFaculty))) {
            $score += 30;
        }

        if ((int) ($builder['active_teams'] ?? 0) === 0) {
            $score += 20;
        }

        if (!empty($builder['bio'])) {
            $score += 10;
        }

        if (!empty($builder['skills'])) {
            $score += 15;
        }

        return min(100, $score);
    }

    private function facultyForRole(string $role): string
    {
        return match (strtoupper($role)) {
            'CTO' => 'Engenharia',
            'CFO' => 'Gestão',
            'CLO' => 'Direito',
            'CMO' => 'Marketing',
            default => ''
        };
    }
}
