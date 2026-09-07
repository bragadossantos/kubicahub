<?php
/**
 * Kubica Hub — Controller: Startup (Incubação)
 * Dashboard da equipa, entregáveis, tração e marcos FKCU.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Startup;

class StartupController extends Controller
{
    private Startup $startup;
    private Database $db;

    public function __construct()
    {
        $this->startup = new Startup();
        $this->db      = Database::getInstance();
    }

    /**
     * Helper para obter teamId a partir de um ID (que pode ser team_id ou idea_id)
     */
    private function resolveTeamId(int $id): ?int
    {
        // Verificar se é team_id directo
        $team = $this->db->queryOne('SELECT id FROM teams WHERE id = ?', [$id]);
        if ($team) return (int) $team['id'];

        // Verificar se foi passado idea_id
        $teamByIdea = $this->db->queryOne('SELECT id FROM teams WHERE idea_id = ?', [$id]);
        if ($teamByIdea) return (int) $teamByIdea['id'];

        return null;
    }

    /**
     * GET /api/v1/startups/{id}/dashboard
     */
    public function dashboard(array $params, array $body): void
    {
        $this->requireAuth();
        $id = (int) $params['id'];
        $teamId = $this->resolveTeamId($id);

        if (!$teamId) {
            $this->notFound('Equipa de startup não encontrada.');
        }

        $dashboard = $this->startup->dashboard($teamId);
        if (!$dashboard) {
            $this->notFound('Dados da startup não encontrados.');
        }

        $this->json($dashboard);
    }

    /**
     * GET /api/v1/startups/{id}/deliverables
     */
    public function deliverables(array $params, array $body): void
    {
        $this->requireAuth();
        $id = (int) $params['id'];
        $teamId = $this->resolveTeamId($id);

        if (!$teamId) {
            $this->json(['deliverables' => [], 'total' => 0]);
            return;
        }

        $role = $_GET['role'] ?? null;
        $deliverables = $this->startup->deliverables($teamId, $role);
        $this->json(['deliverables' => $deliverables, 'total' => count($deliverables)]);
    }

    /**
     * POST /api/v1/startups/{id}/deliverables — Submeter entregável.
     */
    public function submitDeliverable(array $params, array $body): void
    {
        $this->requireAuth();
        $id = (int) $params['id'];
        $teamId = $this->resolveTeamId($id);

        if (!$teamId) {
            $this->notFound('Equipa de startup não encontrada.');
        }

        $required = ['role', 'title'];
        $missing  = $this->validarObrigatorios($required, $body);
        if ($missing) {
            $this->erro('Campos obrigatórios: ' . implode(', ', $missing), 422);
        }

        $user = $this->utilizadorActual();
        $this->startup->submitDeliverable(array_merge($body, [
            'team_id'      => $teamId,
            'submitted_by' => $user['id'],
        ]));

        $this->json(['message' => 'Entregável submetido com sucesso! A coordenação FKCU irá rever o documento.'], 201);
    }

    /**
     * GET /api/v1/startups/{id}/traction — Métricas de tração.
     */
    public function traction(array $params, array $body): void
    {
        $this->requireAuth();
        $id = (int) $params['id'];
        $teamId = $this->resolveTeamId($id);

        if (!$teamId) {
            $this->json(['metrics' => []]);
            return;
        }

        $metrics = $this->startup->tractionMetrics($teamId);
        $this->json(['metrics' => $metrics]);
    }

    /**
     * POST /api/v1/startups/{id}/traction — Adicionar métrica.
     */
    public function addTraction(array $params, array $body): void
    {
        $this->requireAuth();
        $id = (int) $params['id'];
        $teamId = $this->resolveTeamId($id);

        if (!$teamId) {
            $this->notFound('Equipa de startup não encontrada.');
        }

        $required = ['metric_name', 'value'];
        $missing  = $this->validarObrigatorios($required, $body);
        if ($missing) {
            $this->erro('Campos obrigatórios: metric_name e value.', 422);
        }

        $this->startup->addTraction(array_merge($body, [
            'team_id' => $teamId,
        ]));

        $this->json(['message' => 'Métrica de tração registada com sucesso!'], 201);
    }
}
