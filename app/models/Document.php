<?php
/**
 * Kubica Hub — Model: Document (MoU, NDA, Co-Founders Agreement)
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Document extends Model
{
    protected string $table = 'documents';

    public const TYPE_MOU               = 'mou';
    public const TYPE_COFOUNDERS        = 'cofounders_agreement';
    public const TYPE_NDA               = 'nda';
    public const TYPE_VESTING_SCHEDULE  = 'vesting_schedule';
    public const TYPE_IAPI_REQUEST      = 'iapi_request';

    /**
     * Gerar documento com base num template.
     * @param array<string, mixed> $data
     */
    public function generate(array $data): int
    {
        $content = $this->renderTemplate($data['type'], $data);

        return $this->create([
            'team_id'    => $data['team_id'],
            'type'       => $data['type'],
            'title'      => $this->titleForType($data['type']),
            'content'    => $content,
            'version'    => $this->nextVersion($data['team_id'], $data['type']),
            'created_by' => $data['created_by'],
            'status'     => 'draft',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Documentos de uma equipa.
     * @return array<int, array<string, mixed>>
     */
    public function forTeam(int $teamId): array
    {
        return $this->db->query(
            "SELECT d.*, u.name AS created_by_name
             FROM documents d LEFT JOIN users u ON u.id = d.created_by
             WHERE d.team_id = ? ORDER BY d.created_at DESC",
            [$teamId]
        );
    }

    /**
     * Marcar documento como assinado.
     */
    public function sign(int $id, int $signedBy): int
    {
        return $this->db->execute(
            'UPDATE documents SET status = ?, signed_by = ?, signed_at = NOW() WHERE id = ?',
            ['signed', $signedBy, $id]
        );
    }

    private function nextVersion(int $teamId, string $type): int
    {
        $result = $this->db->queryOne(
            'SELECT MAX(version) AS max_version FROM documents WHERE team_id = ? AND type = ?',
            [$teamId, $type]
        );
        return ((int) ($result['max_version'] ?? 0)) + 1;
    }

    private function titleForType(string $type): string
    {
        return match ($type) {
            self::TYPE_MOU              => 'Memorando de Entendimento (MoU)',
            self::TYPE_COFOUNDERS       => 'Acordo de Co-Founders FKCU',
            self::TYPE_NDA              => 'Acordo de Confidencialidade (NDA)',
            self::TYPE_VESTING_SCHEDULE => 'Calendário de Vesting',
            self::TYPE_IAPI_REQUEST     => 'Pedido de Registo IAPI',
            default                     => 'Documento FKCU',
        };
    }

    /**
     * Renderizar template de documento com dados da equipa.
     * @param array<string, mixed> $data
     */
    private function renderTemplate(string $type, array $data): string
    {
        return match ($type) {
            self::TYPE_MOU          => $this->templateMoU($data),
            self::TYPE_COFOUNDERS   => $this->templateCoFounders($data),
            self::TYPE_NDA          => $this->templateNda($data),
            default                 => 'Documento gerado pelo sistema Kubica Hub FKCU em ' . date('d/m/Y H:i') . '.',
        };
    }

    /** @param array<string, mixed> $data */
    private function templateMoU(array $data): string
    {
        $date = date('d \d\e F \d\e Y');
        return "MEMORANDO DE ENTENDIMENTO — FKCU\n\n"
             . "Data: {$date}\n"
             . "Projecto: {$data['idea_title']}\n"
             . "Equipa: {$data['team_name']}\n\n"
             . "As partes abaixo identificadas acordam colaborar no âmbito do Framework "
             . "KUBICA de Co-Criação Universitária (FKCU) para o desenvolvimento do projecto "
             . "acima indicado, respeitando os princípios de co-criação, partilha equitativa de "
             . "valor e alinhamento com o Plano de Desenvolvimento Nacional 2023-2027.\n\n"
             . "[Assinaturas das partes]\n";
    }

    /** @param array<string, mixed> $data */
    private function templateCoFounders(array $data): string
    {
        $date = date('d \d\e F \d\e Y');
        return "ACORDO DE CO-FOUNDERS — FKCU\n\n"
             . "Data: {$date}\n"
             . "Startup: {$data['team_name']}\n\n"
             . "CLÁUSULA 1 — PROPRIEDADE INTELECTUAL\n"
             . "Toda a propriedade intelectual gerada no âmbito deste projecto pertence "
             . "exclusivamente aos co-founders, conforme Lei n.º 3/92 (IAPI).\n\n"
             . "CLÁUSULA 2 — DISTRIBUIÇÃO DE EQUITY\n"
             . "A distribuição de participações societárias será definida no Vesting Schedule "
             . "anexo, com cliff de 12 meses e vesting total de 36 meses.\n\n"
             . "CLÁUSULA 3 — RESOLUÇÃO DE CONFLITOS\n"
             . "Qualquer conflito será mediado pela coordenação FKCU antes de recurso a "
             . "vias judiciais, nos termos do portal de mediação Kubica Hub.\n\n"
             . "[Assinaturas dos Co-Founders]\n";
    }

    /** @param array<string, mixed> $data */
    private function templateNda(array $data): string
    {
        $date = date('d \d\e F \d\e Y');
        return "ACORDO DE CONFIDENCIALIDADE (NDA) — FKCU\n\n"
             . "Data: {$date}\n"
             . "Projecto: {$data['idea_title']}\n\n"
             . "As partes comprometem-se a manter confidencialidade sobre todas as "
             . "informações técnicas, comerciais e estratégicas partilhadas no âmbito do "
             . "processo de matchmaking e co-criação FKCU, pelo período de 3 anos.\n\n"
             . "[Assinaturas das partes]\n";
    }
}
