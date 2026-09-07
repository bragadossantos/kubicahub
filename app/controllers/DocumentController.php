<?php
/**
 * Kubica Hub — Controller: Document
 * CORRIGIDO: generateDocument() → generate(), list() usa forTeam()
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Document;
use App\Core\Database;

class DocumentController extends Controller
{
    private Document $doc;

    public function __construct()
    {
        $this->doc = new Document();
    }

    /**
     * GET /api/v1/documents/{teamId}
     */
    public function list(array $params, array $body): void
    {
        $this->requireAuth();
        $teamId = (int) $params['teamId'];
        
        $docs = $this->doc->forTeam($teamId);
        $this->json(['documents' => $docs]);
    }

    /**
     * POST /api/v1/documents/generate
     */
    public function generate(array $params, array $body): void
    {
        $this->requireAuth();

        $missing = $this->validarObrigatorios(['team_id', 'type'], $body);
        if ($missing) {
            $this->erro('Campos obrigatórios: team_id, type', 422);
        }

        $tiposValidos = ['mou', 'cofounders_agreement', 'nda', 'vesting_schedule', 'iapi_request'];
        if (!in_array($body['type'], $tiposValidos, true)) {
            $this->erro('Tipo de documento inválido.', 422);
        }

        $user = $this->utilizadorActual();
        
        // Buscar dados da equipa para preencher o template
        $db = Database::getInstance();
        $team = $db->queryOne(
            "SELECT t.*, i.title AS idea_title FROM teams t 
             JOIN ideas i ON i.id = t.idea_id 
             WHERE t.id = ?",
            [(int) $body['team_id']]
        );

        if (!$team) {
            $this->notFound('Equipa não encontrada.');
        }

        $docId = $this->doc->generate([
            'team_id'    => (int) $body['team_id'],
            'type'       => $body['type'],
            'created_by' => $user['id'],
            'team_name'  => $team['name'],
            'idea_title' => $team['idea_title'],
        ]);

        $this->json([
            'document_id' => $docId,
            'message'     => 'Documento gerado com sucesso. A aguardar assinaturas.'
        ], 201);
    }

    /**
     * PATCH /api/v1/documents/{id}/sign
     */
    public function sign(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        $this->doc->sign((int) $params['id'], $user['id']);
        $this->json(['message' => 'Documento assinado com sucesso.']);
    }
}
