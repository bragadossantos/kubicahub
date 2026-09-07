<?php
/**
 * Kubica Hub — Model: Mensagem
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Mensagem extends Model
{
    protected string $table = 'mensagens';

    /**
     * Enviar nova mensagem.
     */
    public function enviar(int $remetente, int $destinatario, int $ideaId, string $conteudo): int
    {
        // Gerar ID de conversa único e determinístico baseado nos IDs de user
        $menor = min($remetente, $destinatario);
        $maior = max($remetente, $destinatario);
        $conversaId = "{$ideaId}_{$menor}_{$maior}";

        $id = $this->create([
            'conversa_id'     => $conversaId,
            'remetente_id'    => $remetente,
            'destinatario_id' => $destinatario,
            'idea_id'         => $ideaId,
            'conteudo'        => $conteudo,
            'lida'            => 0,
            'created_at'      => date('Y-m-d H:i:s')
        ]);

        // Gerar notificação
        $this->db->execute(
            "INSERT INTO notifications (user_id, type, title, message, action_url) VALUES (?, 'mensagem', 'Nova Mensagem', 'Recebeu uma nova mensagem no projeto FKCU.', ?)",
            [$destinatario, '/mensagens']
        );

        return $id;
    }

    /**
     * Obter lista de conversas de um utilizador.
     */
    public function obterConversas(int $userId): array
    {
        // Query para obter a última mensagem de cada conversa
        return $this->db->query(
            "SELECT m.conversa_id, m.idea_id, i.title as idea_title,
                    m.conteudo as ultima_mensagem, m.created_at, m.lida,
                    u.id as outro_user_id, u.name as outro_user_nome, u.profile_photo,
                    (SELECT COUNT(*) FROM mensagens WHERE conversa_id = m.conversa_id AND destinatario_id = ? AND lida = 0) as nao_lidas
             FROM mensagens m
             JOIN (
                SELECT conversa_id, MAX(created_at) as max_data
                FROM mensagens
                GROUP BY conversa_id
             ) m2 ON m.conversa_id = m2.conversa_id AND m.created_at = m2.max_data
             JOIN ideas i ON m.idea_id = i.id
             JOIN users u ON u.id = IF(m.remetente_id = ?, m.destinatario_id, m.remetente_id)
             WHERE m.remetente_id = ? OR m.destinatario_id = ?
             ORDER BY m.created_at DESC",
            [$userId, $userId, $userId, $userId]
        );
    }

    /**
     * Obter mensagens de uma conversa.
     */
    public function obterMensagens(string $conversaId): array
    {
        return $this->db->query(
            "SELECT m.*, r.name as remetente_nome, r.profile_photo as remetente_foto
             FROM mensagens m
             JOIN users r ON m.remetente_id = r.id
             WHERE m.conversa_id = ?
             ORDER BY m.created_at ASC",
            [$conversaId]
        );
    }

    /**
     * Marcar mensagens de uma conversa como lidas.
     */
    public function marcarLidas(string $conversaId, int $destinatarioId): void
    {
        $this->db->execute(
            "UPDATE mensagens SET lida = 1 WHERE conversa_id = ? AND destinatario_id = ? AND lida = 0",
            [$conversaId, $destinatarioId]
        );
    }
}
