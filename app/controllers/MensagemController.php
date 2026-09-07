<?php
/**
 * Kubica Hub — Controller: Mensagem
 * Comunicação interna entre co-fundadores e equipas FKCU.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Mensagem;

class MensagemController extends Controller
{
    private Mensagem $msg;

    public function __construct()
    {
        $this->msg = new Mensagem();
    }

    /**
     * GET /api/v1/mensagens
     */
    public function list(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        
        $conversas = $this->msg->obterConversas($user['id']);
        $this->json(['conversas' => $conversas]);
    }

    /**
     * GET /api/v1/mensagens/{conversaId}
     */
    public function show(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        $conversaId = $this->sanitizar($params['conversaId']);
        
        // Verificar se tem acesso a esta conversa
        if (!str_contains($conversaId, '_' . $user['id'] . '_') && !str_ends_with($conversaId, '_' . $user['id'])) {
             $partes = explode('_', $conversaId);
             if (count($partes) === 3 && ((int)$partes[1] !== (int)$user['id'] && (int)$partes[2] !== (int)$user['id'])) {
                 $this->forbidden('Acesso não autorizado a esta conversa.');
             }
        }

        $mensagens = $this->msg->obterMensagens($conversaId);
        
        // Marcar como lidas
        $this->msg->marcarLidas($conversaId, $user['id']);
        
        $this->json(['mensagens' => $mensagens]);
    }

    /**
     * POST /api/v1/mensagens
     */
    public function send(array $params, array $body): void
    {
        $this->requireAuth();

        $missing = $this->validarObrigatorios(['destinatario_id', 'idea_id', 'conteudo'], $body);
        if ($missing) {
            $this->erro('Campos obrigatórios: destinatario_id, idea_id, conteudo', 422);
        }

        $user = $this->utilizadorActual();
        $destinatarioId = (int) $body['destinatario_id'];
        $ideaId         = (int) $body['idea_id'];
        
        if ((int)$user['id'] === $destinatarioId) {
            $this->erro('Não podes enviar mensagens para ti próprio.', 422);
        }

        $msgId = $this->msg->enviar(
            (int) $user['id'],
            $destinatarioId,
            $ideaId,
            $this->sanitizar((string) $body['conteudo'])
        );

        $this->json([
            'mensagem_id' => $msgId,
            'message'     => 'Mensagem enviada com sucesso.'
        ], 201);
    }
}
