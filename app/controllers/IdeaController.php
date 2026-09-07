<?php
/**
 * Kubica Hub — Controller: Idea
 * Submissão, feed público, gestão pelo inventor e triagem pela coordenação.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Idea;

class IdeaController extends Controller
{
    private Idea $idea;
    private Database $db;

    public function __construct()
    {
        $this->idea = new Idea();
        $this->db   = Database::getInstance();
    }

    /**
     * GET /api/v1/ideas — Feed de ideias (público ou filtrado pelo inventor)
     * @param array<string, string> $params
     * @param array<string, mixed> $body
     */
    public function index(array $params, array $body): void
    {
        $inventorFilter = $_GET['inventor'] ?? null;
        $statusFilter   = $_GET['status']   ?? null;
        $sectorFilter   = $_GET['sector']   ?? null;
        $pdnFilter      = $_GET['pdn_axis'] ?? null;
        $searchQuery    = $_GET['q']        ?? ($_GET['search'] ?? null);

        // 1. Filtrar pelo próprio inventor autenticado
        if ($inventorFilter === 'me') {
            $this->requireAuth();
            $user = $this->utilizadorActual();

            $sql = "SELECT i.*, u.name AS inventor_name, u.university AS inventor_university, u.faculty AS inventor_faculty
                    FROM ideas i
                    JOIN users u ON u.id = i.inventor_id
                    WHERE i.inventor_id = ?";
            $args = [$user['id']];

            if ($statusFilter) {
                $sql .= " AND i.status = ?";
                $args[] = $statusFilter;
            }

            $sql .= " ORDER BY i.created_at DESC";
            $ideas = $this->db->query($sql, $args);

            // Descodificar roles_needed
            foreach ($ideas as &$item) {
                if (!empty($item['roles_needed']) && is_string($item['roles_needed'])) {
                    $item['roles_needed'] = json_decode($item['roles_needed'], true) ?? [];
                }
            }

            $this->json(['ideas' => $ideas, 'total' => count($ideas)]);
            return;
        }

        // 2. Feed geral / público
        $where  = [];
        $args   = [];

        if ($statusFilter) {
            $where[] = "i.status = ?";
            $args[]  = $statusFilter;
        } else {
            // Por defeito no feed público, mostrar aprovadas ou em incubação
            $where[] = "i.status IN ('approved', 'matched', 'incubating')";
        }

        $universityFilter = $_GET['university']     ?? null;
        $facultyDemand    = $_GET['faculty_demand'] ?? ($_GET['role'] ?? null);

        if ($sectorFilter && $sectorFilter !== 'all') {
            $where[] = "i.sector = ?";
            $args[]  = $sectorFilter;
        }

        if ($pdnFilter) {
            $where[] = "i.pdn_axis = ?";
            $args[]  = $pdnFilter;
        }

        if ($universityFilter && $universityFilter !== 'all') {
            $where[] = "u.university = ?";
            $args[]  = $universityFilter;
        }

        if ($facultyDemand && $facultyDemand !== 'all') {
            $where[] = "i.roles_needed LIKE ?";
            $args[]  = '%' . trim($facultyDemand) . '%';
        }

        if ($searchQuery) {
            $where[] = "(i.title LIKE ? OR i.problem LIKE ? OR i.solution LIKE ?)";
            $term    = '%' . trim($searchQuery) . '%';
            $args[]  = $term;
            $args[]  = $term;
            $args[]  = $term;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT i.*, u.name AS inventor_name, u.university AS inventor_university, u.faculty AS inventor_faculty
                FROM ideas i
                JOIN users u ON u.id = i.inventor_id
                {$whereClause}
                ORDER BY i.created_at DESC LIMIT 100";

        $ideas = $this->db->query($sql, $args);

        foreach ($ideas as &$item) {
            if (!empty($item['roles_needed']) && is_string($item['roles_needed'])) {
                $item['roles_needed'] = json_decode($item['roles_needed'], true) ?? [];
            }
        }

        $this->json([
            'ideas' => $ideas,
            'total' => count($ideas),
        ]);
    }

    /**
     * GET /api/v1/ideas/{id} — Detalhe da ideia.
     */
    public function show(array $params, array $body): void
    {
        $idea = $this->idea->detail((int) $params['id']);
        if (!$idea) {
            $this->notFound('Ideia não encontrada.');
        }

        if (!empty($idea['roles_needed']) && is_string($idea['roles_needed'])) {
            $idea['roles_needed'] = json_decode($idea['roles_needed'], true) ?? [];
        }

        // Incrementar contagem de visualizações
        $this->db->execute('UPDATE ideas SET views_count = views_count + 1 WHERE id = ?', [(int) $params['id']]);

        $this->json($idea);
    }

    /**
     * POST /api/v1/ideas — Submeter nova ideia (apenas inventor).
     */
    public function store(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();

        if ($user['role'] !== 'inventor' && $user['role'] !== 'admin') {
            $this->forbidden('Apenas inventores podem submeter ideias ao FKCU.');
        }

        $required = ['title', 'sector', 'pdn_axis', 'problem', 'solution'];
        $missing  = $this->validarObrigatorios($required, $body);
        if ($missing) {
            $this->erro('Campos obrigatórios em falta: ' . implode(', ', $missing), 422);
        }

        $ideaId = $this->idea->submit(array_merge(
            $this->sanitizarArray($body),
            [
                'inventor_id'  => $user['id'],
                'roles_needed' => $body['roles_needed'] ?? []
            ]
        ));

        // Notificar o inventor
        $this->db->execute(
            "INSERT INTO notifications (user_id, type, title, message, action_url, created_at)
             VALUES (?, 'idea', 'Projecto Submetido', 'A tua ideia foi submetida com sucesso e aguarda triagem da coordenação FKCU.', 'projetos.html', NOW())",
            [$user['id']]
        );

        $this->json([
            'idea_id' => $ideaId,
            'message' => 'Ideia submetida com sucesso! A coordenação FKCU fará a triagem nas próximas 48 horas.',
        ], 201);
    }

    /**
     * PATCH /api/v1/ideas/{id} — Actualizar ideia (apenas o autor).
     */
    public function update(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        $ideaId = (int) $params['id'];

        $idea = $this->idea->find($ideaId);
        if (!$idea) {
            $this->notFound('Ideia não encontrada.');
        }

        if ((int)$idea['inventor_id'] !== (int)$user['id'] && $user['role'] !== 'admin') {
            $this->forbidden('Não podes editar uma ideia de outro inventor.');
        }

        $permitidos = ['title', 'sector', 'pdn_axis', 'problem', 'solution', 'target_market', 'roles_needed'];
        $actualizar = [];

        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $body)) {
                $val = $body[$campo];
                if ($campo === 'roles_needed' && is_array($val)) {
                    $val = json_encode($val);
                } elseif (is_string($val)) {
                    $val = $this->sanitizar($val);
                }
                $actualizar[$campo] = $val;
            }
        }

        if (!empty($actualizar)) {
            $actualizar['updated_at'] = date('Y-m-d H:i:s');
            $this->db->execute(
                'UPDATE ideas SET ' . implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($actualizar))) . ' WHERE id = ?',
                [...array_values($actualizar), $ideaId]
            );
        }

        $this->json(['message' => 'Projecto actualizado com sucesso.']);
    }

    /**
     * DELETE /api/v1/ideas/{id} — Eliminar ideia.
     */
    public function destroy(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        $ideaId = (int) $params['id'];

        $idea = $this->idea->find($ideaId);
        if (!$idea) {
            $this->notFound('Ideia não encontrada.');
        }

        if ((int)$idea['inventor_id'] !== (int)$user['id'] && $user['role'] !== 'admin') {
            $this->forbidden('Não tens permissão para eliminar esta ideia.');
        }

        $this->db->execute('DELETE FROM ideas WHERE id = ?', [$ideaId]);
        $this->json(['message' => 'Projecto eliminado com sucesso.']);
    }

    /**
     * PATCH /api/v1/admin/ideas/{id}/status ou /api/v1/ideas/{id}/status — Triagem (admin).
     */
    public function updateStatus(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        if ($user['role'] !== 'admin') {
            $this->forbidden('Apenas a coordenação FKCU / admin pode alterar o estado de triagem.');
        }

        $validStatuses = ['approved', 'rejected', 'pending', 'incubating', 'matched', 'graduated'];
        if (empty($body['status']) || !in_array($body['status'], $validStatuses, true)) {
            $this->erro('Estado inválido.', 422);
        }

        $ideaId = (int) $params['id'];
        $idea = $this->idea->find($ideaId);
        if (!$idea) {
            $this->notFound('Ideia não encontrada.');
        }

        $feedback = $body['screening_feedback'] ?? ($body['feedback'] ?? null);
        $this->idea->updateStatus($ideaId, $body['status'], $feedback);

        // Notificar o inventor sobre o resultado da triagem
        $statusMsg = match ($body['status']) {
            'approved'   => 'A tua ideia foi APROVADA pela coordenação FKCU e já está disponível para matchmaking!',
            'rejected'   => 'A tua ideia necessita de ajustes. Feedback: ' . ($feedback ?? 'Consulte a coordenação.'),
            'incubating' => 'O teu projecto avançou para a fase de Incubação FKCU!',
            default      => "O estado da tua ideia foi actualizado para: {$body['status']}",
        };

        $this->db->execute(
            "INSERT INTO notifications (user_id, type, title, message, action_url, created_at)
             VALUES (?, 'idea', 'Actualização do Projecto', ?, 'projetos.html', NOW())",
            [$idea['inventor_id'], $statusMsg]
        );

        $this->json([
            'idea_id' => $ideaId,
            'status'  => $body['status'],
            'message' => 'Estado da ideia actualizado com sucesso.',
        ]);
    }
}
