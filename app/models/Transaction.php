<?php
/**
 * Kubica Hub — Model: Transaction (Sandbox Funding)
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Transaction extends Model
{
    protected string $table = 'sandbox_transactions';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const ELIGIBLE_CATEGORIES = [
        'cloud'        => 'Infraestrutura Cloud / SaaS',
        'research'     => 'Pesquisa de Mercado',
        'iapi'         => 'Registo IAPI / Propriedade Industrial',
        'prototype'    => 'Prototipagem e Design',
        'events'       => 'Eventos e Demonstrações',
        'legal'        => 'Serviços Jurídicos',
        'mentoring'    => 'Mentoria Especializada',
        'travel'       => 'Deslocações (negócio comprovado)',
    ];

    // Limite máximo por equipa por temporada
    public const MAX_BUDGET_USD = 25000;

    /**
     * Pedido de disbursement pela equipa.
     * @param array<string, mixed> $data
     */
    public function request(array $data): int
    {
        return $this->create([
            'team_id'     => $data['team_id'],
            'amount_usd'  => $data['amount_usd'],
            'category'    => $data['category'],
            'description' => $data['description'],
            'tranche'     => $data['tranche'] ?? 1,
            'status'      => self::STATUS_PENDING,
            'requested_by' => $data['requested_by'],
            'requested_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Aprovar disbursement (admin).
     */
    public function approve(int $id, int $approvedBy): int
    {
        return $this->db->execute(
            'UPDATE sandbox_transactions SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?',
            [self::STATUS_APPROVED, $approvedBy, $id]
        );
    }

    /**
     * Rejeitar disbursement com motivo.
     */
    public function reject(int $id, int $rejectedBy, string $reason): int
    {
        return $this->db->execute(
            'UPDATE sandbox_transactions SET status = ?, rejected_by = ?, reject_reason = ?, approved_at = NOW() WHERE id = ?',
            [self::STATUS_REJECTED, $rejectedBy, $reason, $id]
        );
    }

    /**
     * Saldo disponível de uma equipa.
     */
    public function balance(int $teamId): array
    {
        $spent = $this->db->queryOne(
            "SELECT COALESCE(SUM(amount_usd), 0) AS total
             FROM sandbox_transactions
             WHERE team_id = ? AND status = 'approved'",
            [$teamId]
        );
        $pending = $this->db->queryOne(
            "SELECT COALESCE(SUM(amount_usd), 0) AS total
             FROM sandbox_transactions
             WHERE team_id = ? AND status = 'pending'",
            [$teamId]
        );

        $spentTotal   = (float) ($spent['total']   ?? 0);
        $pendingTotal = (float) ($pending['total']  ?? 0);

        return [
            'limit_usd'     => self::MAX_BUDGET_USD,
            'spent_usd'     => $spentTotal,
            'pending_usd'   => $pendingTotal,
            'available_usd' => self::MAX_BUDGET_USD - $spentTotal - $pendingTotal,
            'pct_used'      => round($spentTotal / self::MAX_BUDGET_USD * 100, 1),
        ];
    }

    /**
     * Histórico de transacções de uma equipa.
     * @return array<int, array<string, mixed>>
     */
    public function history(int $teamId): array
    {
        return $this->db->query(
            "SELECT st.*, u.name AS requested_by_name, a.name AS approved_by_name
             FROM sandbox_transactions st
             LEFT JOIN users u ON u.id = st.requested_by
             LEFT JOIN users a ON a.id = st.approved_by
             WHERE st.team_id = ?
             ORDER BY st.requested_at DESC",
            [$teamId]
        );
    }

    /**
     * Todos os pedidos pendentes (para o Funding Board — admin).
     * @return array<int, array<string, mixed>>
     */
    public function allPending(): array
    {
        return $this->db->query(
            "SELECT st.*, t.name AS team_name, u.name AS requested_by_name
             FROM sandbox_transactions st
             JOIN teams t ON t.id = st.team_id
             JOIN users u ON u.id = st.requested_by
             WHERE st.status = 'pending'
             ORDER BY st.requested_at ASC"
        );
    }
}
