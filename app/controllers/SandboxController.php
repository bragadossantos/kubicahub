<?php
/**
 * Kubica Hub — Controller: Sandbox (Funding)
 * Gestão de disbursements não dilutivos.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Transaction;

class SandboxController extends Controller
{
    private Transaction $tx;

    public function __construct()
    {
        $this->tx = new Transaction();
    }

    /**
     * GET /api/sandbox/{teamId} — Saldo e histórico.
     * @param array<string, string> $params
     * @param array<string, mixed> $body
     */
    public function balance(array $params, array $body): void
    {
        $this->requireAuth();

        $teamId  = (int) $params['teamId'];
        $balance = $this->tx->balance($teamId);
        $history = $this->tx->history($teamId);

        $this->json([
            'balance'    => $balance,
            'history'    => $history,
            'categories' => Transaction::ELIGIBLE_CATEGORIES,
        ]);
    }

    /**
     * POST /api/sandbox/request — Pedido de disbursement.
     * @param array<string, string> $params
     * @param array<string, mixed> $body
     */
    public function requestDisbursement(array $params, array $body): void
    {
        $this->requireAuth();

        $required = ['team_id', 'amount_usd', 'category', 'description'];
        $missing  = $this->validarObrigatorios($required, $body);
        if ($missing) {
            $this->erro('Campos obrigatórios: ' . implode(', ', $missing), 422);
        }

        // Validar categoria elegível
        if (!array_key_exists($body['category'], Transaction::ELIGIBLE_CATEGORIES)) {
            $this->erro('Categoria não elegível para Sandbox FKCU.', 422);
        }

        // Validar montante
        $amount = (float) $body['amount_usd'];
        if ($amount <= 0 || $amount > 5000) {
            $this->erro('Montante deve ser entre USD 1 e USD 5.000 por pedido.', 422);
        }

        // Verificar saldo disponível
        $balance = $this->tx->balance((int) $body['team_id']);
        if ($amount > $balance['available_usd']) {
            $this->erro(
                "Saldo insuficiente. Disponível: USD {$balance['available_usd']}.",
                422
            );
        }

        $user = $this->utilizadorActual();
        $txId = $this->tx->request(array_merge($body, [
            'requested_by' => $user['id'],
        ]));

        $this->json([
            'transaction_id' => $txId,
            'message'        => 'Pedido de disbursement submetido. A coordenação irá rever em até 48 horas.',
        ], 201);
    }

    /**
     * PATCH /api/sandbox/{id}/approve — Aprovar (admin).
     * @param array<string, string> $params
     * @param array<string, mixed> $body
     */
    public function approve(array $params, array $body): void
    {
        $this->requireAuth();
        $u = $this->utilizadorActual();
        if ($u['role'] !== 'admin') { $this->forbidden(); }

        $tx = $this->tx->find((int) $params['id']);
        if (!$tx) { $this->notFound('Transação não encontrada.'); }

        if ($tx['status'] !== 'pending') {
            $this->erro('Esta transação já foi processada.', 409);
        }

        $this->tx->approve((int) $params['id'], (int) $u['id']);
        $this->json(['message' => "Disbursement de USD {$tx['amount_usd']} aprovado com sucesso."]);
    }

    public function reject(array $params, array $body): void
    {
        $this->requireAuth();
        $u = $this->utilizadorActual();
        if ($u['role'] !== 'admin') { $this->forbidden(); }

        $reason = $body['reject_reason'] ?? ($body['reason'] ?? null);
        if (empty($reason)) {
            $this->erro('Motivo de rejeição obrigatório.', 422);
        }

        $tx = $this->tx->find((int) $params['id']);
        if (!$tx) { $this->notFound('Transação não encontrada.'); }

        $this->tx->reject((int) $params['id'], (int) $u['id'], $reason);
        $this->json(['message' => 'Pedido rejeitado. A equipa foi notificada.']);
    }
}
