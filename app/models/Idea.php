<?php
/**
 * Kubica Hub — Model: Idea
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Idea extends Model
{
    protected string $table = 'ideas';

    public const STATUS_DRAFT    = 'draft';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_MATCHED  = 'matched';
    public const STATUS_INCUBATING = 'incubating';
    public const STATUS_GRADUATED  = 'graduated';

    public const SECTORS = [
        'Agritech', 'Fintech', 'Healthtech', 'Edtech',
        'Energia', 'Logística', 'Turismo', 'Segurança',
        'E-Commerce', 'Outro'
    ];

    public const PDN_AXES = [
        'Eixo 1 — Soberania e Segurança Nacional',
        'Eixo 2 — Soberania Alimentar',
        'Eixo 3 — Transformação Digital',
        'Eixo 4 — Capital Humano',
        'Eixo 5 — Diversificação da Economia',
    ];

    /**
     * Submeter nova ideia.
     * @param array<string, mixed> $data
     */
    public function submit(array $data): int
    {
        return $this->create([
            'inventor_id'    => $data['inventor_id'],
            'title'          => $data['title'],
            'sector'         => $data['sector'],
            'pdn_axis'       => $data['pdn_axis'],
            'problem'        => $data['problem'],
            'solution'       => $data['solution'],
            'target_market'  => $data['target_market'] ?? null,
            'roles_needed'   => json_encode($data['roles_needed'] ?? []),
            'status'         => self::STATUS_PENDING,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Feed público de ideias aprovadas.
     * @return array<int, array<string, mixed>>
     */
    public function publicFeed(array $filters = []): array
    {
        $where  = ["i.status = 'approved'"];
        $params = [];

        if (!empty($filters['sector'])) {
            $where[]  = 'i.sector = ?';
            $params[] = $filters['sector'];
        }
        if (!empty($filters['pdn_axis'])) {
            $where[]  = 'i.pdn_axis = ?';
            $params[] = $filters['pdn_axis'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(i.title LIKE ? OR i.problem LIKE ? OR i.solution LIKE ?)';
            $term     = '%' . $filters['search'] . '%';
            $params   = array_merge($params, [$term, $term, $term]);
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);
        return $this->db->query(
            "SELECT i.*, u.name AS inventor_name, u.university AS inventor_university, u.faculty AS inventor_faculty
             FROM ideas i
             JOIN users u ON u.id = i.inventor_id
             {$whereClause}
             ORDER BY i.created_at DESC
             LIMIT 50",
            $params
        );
    }

    /**
     * Ideias pendentes de triagem (para admin).
     * @return array<int, array<string, mixed>>
     */
    public function pendingTriage(): array
    {
        return $this->db->query(
            "SELECT i.*, u.name AS inventor_name, u.university, u.faculty
             FROM ideas i
             JOIN users u ON u.id = i.inventor_id
             WHERE i.status = 'pending'
             ORDER BY i.created_at ASC"
        );
    }

    /**
     * Actualizar estado da ideia com feedback opcional.
     */
    public function updateStatus(int $id, string $status, ?string $feedback = null): int
    {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($feedback !== null) {
            $data['screening_feedback'] = $feedback;
        }
        return $this->db->execute(
            'UPDATE ideas SET status = ?, screening_feedback = ?, updated_at = NOW() WHERE id = ?',
            [$status, $feedback, $id]
        );
    }

    /**
     * Ideia com info completa do inventor e equipa.
     * @return array<string, mixed>|null
     */
    public function detail(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT i.*, u.name AS inventor_name, u.university, u.faculty, u.bio AS inventor_bio
             FROM ideas i
             JOIN users u ON u.id = i.inventor_id
             WHERE i.id = ? LIMIT 1",
            [$id]
        );
    }
}
