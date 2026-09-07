<?php
/**
 * Kubica Hub — Controller: Mediation
 * Pedidos de mediação e governança de conflitos para equipas FKCU.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class MediationController extends Controller
{
    /**
     * GET /api/v1/mediation/{teamId}
     */
    public function list(array $params, array $body): void
    {
        $this->requireAuth();
        $teamId = (int) $params['teamId'];
        
        $db = Database::getInstance();
        $requests = $db->query(
            "SELECT * FROM mediation_requests WHERE team_id = ? ORDER BY created_at DESC", 
            [$teamId]
        );
        
        $this->json(['requests' => $requests]);
    }

    /**
     * POST /api/v1/mediation
     */
    public function create(array $params, array $body): void
    {
        $this->requireAuth();

        $missing = $this->validarObrigatorios(['team_id', 'type', 'description'], $body);
        if ($missing) {
            $this->erro('Campos obrigatórios: team_id, type, description', 422);
        }

        $user = $this->utilizadorActual();
        $db = Database::getInstance();
        
        $id = $db->execute(
            "INSERT INTO mediation_requests (team_id, type, description, urgency, requested_by, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [
                (int) $body['team_id'], 
                $body['type'], 
                $body['description'], 
                $body['urgency'] ?? 'medium', 
                (int) $user['id']
            ]
        );

        $this->json([
            'mediation_id' => $id,
            'message'      => 'Pedido de mediação enviado. A comissão de governança FKCU entrará em contacto nas próximas 48h.'
        ], 201);
    }
}
