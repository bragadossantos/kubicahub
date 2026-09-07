<?php
/**
 * Kubica Hub — Controller: Match
 * Pool de builders, propostas societárias, aceitação e formalização de equipas.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Match;
use App\Models\User;

class MatchController extends Controller
{
    private Match $match;
    private User  $user;
    private Database $db;

    public function __construct()
    {
        $this->match = new Match();
        $this->user  = new User();
        $this->db    = Database::getInstance();
    }

    /**
     * GET /api/v1/builders — Pool de builders disponíveis com filtros.
     */
    public function builderPool(array $params, array $body): void
    {
        $filters = [
            'faculty'    => $_GET['faculty']    ?? null,
            'university' => $_GET['university'] ?? null,
            'min_hours'  => $_GET['min_hours']  ?? null,
        ];

        $builders = $this->user->builderPool(array_filter($filters));

        foreach ($builders as &$b) {
            if (!empty($b['skills']) && is_string($b['skills'])) {
                $b['skills'] = json_decode($b['skills'], true) ?? [];
            }
        }

        $this->json(['builders' => $builders, 'total' => count($builders)]);
    }

    /**
     * GET /api/v1/builders/{id} — Dossier público de um builder.
     */
    public function builderProfile(array $params, array $body): void
    {
        $profile = $this->user->publicProfile((int) $params['id']);
        if (!$profile) {
            $this->notFound('Builder não encontrado.');
        }

        if (!empty($profile['skills']) && is_string($profile['skills'])) {
            $profile['skills'] = json_decode($profile['skills'], true) ?? [];
        }

        $this->json($profile);
    }

    /**
     * POST /api/v1/proposals — Inventor envia proposta a um builder.
     */
    public function propose(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();

        if ($user['role'] !== 'inventor' && $user['role'] !== 'admin') {
            $this->forbidden('Apenas inventores podem enviar propostas societárias.');
        }

        $body['proposed_role'] = $body['proposed_role'] ?? ($body['role'] ?? null);

        $required = ['idea_id', 'builder_id', 'proposed_role', 'equity_pct', 'hours_per_week'];
        $missing  = $this->validarObrigatorios($required, $body);
        if ($missing) {
            $this->erro('Campos obrigatórios: ' . implode(', ', $missing), 422);
        }

        $equity = (float) $body['equity_pct'];
        if ($equity <= 0 || $equity > 100) {
            $this->erro('A percentagem de equity deve estar entre 1% e 100%.', 422);
        }

        // Verificar se a ideia pertence ao inventor
        $idea = $this->db->queryOne('SELECT * FROM ideas WHERE id = ?', [(int) $body['idea_id']]);
        if (!$idea) {
            $this->notFound('Projecto não encontrado.');
        }

        if ((int)$idea['inventor_id'] !== (int)$user['id'] && $user['role'] !== 'admin') {
            $this->forbidden('Apenas o autor do projecto pode enviar propostas.');
        }

        // Verificar se já existe proposta pendente
        $existing = $this->match->whereOne([
            'idea_id'    => (int) $body['idea_id'],
            'builder_id' => (int) $body['builder_id'],
            'status'     => 'pending',
        ]);
        if ($existing) {
            $this->erro('Já existe uma proposta pendente para este builder.', 409);
        }

        $matchId = $this->match->propose($body);

        // Notificar o Builder
        $this->db->execute(
            "INSERT INTO notifications (user_id, type, title, message, action_url, created_at)
             VALUES (?, 'match', 'Nova Proposta de Co-Fundação', ?, 'propostas.html', NOW())",
            [
                (int) $body['builder_id'],
                "Recebeste uma proposta de co-fundação ({$body['proposed_role']}, {$equity}%) para o projecto '{$idea['title']}'."
            ]
        );

        $this->json([
            'match_id' => $matchId,
            'message'  => 'Proposta enviada com sucesso! O builder tem 7 dias para responder.',
        ], 201);
    }

    /**
     * PATCH /api/v1/proposals/{id} — Builder aceita ou recusa proposta.
     */
    public function respond(array $params, array $body): void
    {
        $this->requireAuth();
        $user     = $this->utilizadorActual();
        $matchId  = (int) $params['id'];
        $proposal = $this->match->find($matchId);

        if (!$proposal) {
            $this->notFound('Proposta não encontrada.');
        }

        if ((int) $proposal['builder_id'] !== (int) $user['id'] && $user['role'] !== 'admin') {
            $this->forbidden('Não tens permissão para responder a esta proposta.');
        }

        $validActions = ['accepted', 'declined'];
        if (empty($body['status']) || !in_array($body['status'], $validActions, true)) {
            $this->erro("Acção inválida. Use 'accepted' ou 'declined'.", 422);
        }

        $status = $body['status'];
        $reason = $body['reason'] ?? ($body['decline_reason'] ?? null);

        $this->match->respond($matchId, $status, $reason);

        // Buscar ideia e inventor
        $idea = $this->db->queryOne('SELECT * FROM ideas WHERE id = ?', [(int) $proposal['idea_id']]);

        if ($status === 'accepted') {
            // 1. Criar ou obter a equipa
            $team = $this->db->queryOne('SELECT * FROM teams WHERE idea_id = ?', [(int) $proposal['idea_id']]);
            $teamId = null;

            if (!$team) {
                $teamId = $this->db->execute(
                    "INSERT INTO teams (idea_id, name, fkcu_phase, mou_signed, sandbox_balance_usd, created_at)
                     VALUES (?, ?, 'cocriacao', 0, 5000.00, NOW())",
                    [(int) $proposal['idea_id'], $idea['title']]
                );
            } else {
                $teamId = (int) $team['id'];
            }

            // 2. Adicionar o inventor à equipa se ainda não estiver
            $inventorInTeam = $this->db->queryOne(
                'SELECT * FROM team_members WHERE team_id = ? AND user_id = ?',
                [$teamId, (int) $idea['inventor_id']]
            );
            if (!$inventorInTeam) {
                $this->db->execute(
                    "INSERT IGNORE INTO team_members (team_id, user_id, role, equity_pct, vesting_months, cliff_months, joined_at)
                     VALUES (?, ?, 'CEO', 50.00, 36, 12, NOW())",
                    [$teamId, (int) $idea['inventor_id']]
                );
            }

            // 3. Adicionar o builder à equipa
            $this->db->execute(
                "INSERT INTO team_members (team_id, user_id, role, equity_pct, vesting_months, cliff_months, joined_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE role = VALUES(role), equity_pct = VALUES(equity_pct)",
                [
                    $teamId,
                    (int) $user['id'],
                    $proposal['proposed_role'] ?? 'CTO',
                    (float) ($proposal['equity_pct'] ?? 20.00),
                    (int) ($proposal['vesting_months'] ?? 36),
                    (int) ($proposal['cliff_months'] ?? 12)
                ]
            );

            // 4. Actualizar estado da ideia para 'incubating'
            $this->db->execute("UPDATE ideas SET status = 'incubating', updated_at = NOW() WHERE id = ?", [(int) $proposal['idea_id']]);

            // 5. Notificar o Inventor
            $this->db->execute(
                "INSERT INTO notifications (user_id, type, title, message, action_url, created_at)
                 VALUES (?, 'match', '🎉 Proposta Aceite!', ?, 'dashboard.html', NOW())",
                [
                    (int) $idea['inventor_id'],
                    "O builder {$user['nome']} aceitou a tua proposta para {$proposal['proposed_role']}. A vossa equipa FKCU está formalizada!"
                ]
            );

            // 6. Gerar documento MoU automático
            $this->db->execute(
                "INSERT INTO documents (team_id, type, title, content, version, created_by, status, created_at)
                 VALUES (?, 'mou', 'Memorando de Entendimento (MoU) — Co-Fundadores', ?, 1, ?, 'draft', NOW())",
                [
                    $teamId,
                    "MEMORANDO DE ENTENDIMENTO — FKCU ANGOLA\n\nProjecto: {$idea['title']}\nInventor: {$idea['inventor_id']}\nBuilder: {$user['nome']} ({$proposal['proposed_role']})\nEquity: {$proposal['equity_pct']}%\nVesting: {$proposal['vesting_months']} meses com cliff de {$proposal['cliff_months']} meses.",
                    (int) $idea['inventor_id']
                ]
            );

            $this->json(['message' => 'Proposta aceite! A tua equipa FKCU foi formada com sucesso e o projecto entrou em incubação.']);
        } else {
            // Notificar o inventor da recusa
            $this->db->execute(
                "INSERT INTO notifications (user_id, type, title, message, action_url, created_at)
                 VALUES (?, 'match', 'Proposta Declinada', ?, 'colaboracoes.html', NOW())",
                [
                    (int) $idea['inventor_id'],
                    "O builder declinou a proposta para o projecto '{$idea['title']}'." . ($reason ? " Motivo: $reason" : '')
                ]
            );

            $this->json(['message' => 'Proposta declinada.']);
        }
    }

    /**
     * GET /api/v1/proposals — Propostas do utilizador actual (recebidas ou enviadas).
     */
    public function myProposals(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();

        if ($user['role'] === 'builder') {
            $proposals = $this->db->query(
                "SELECT m.*, i.title AS idea_title, i.sector, u.name AS inventor_name, u.university AS inventor_university
                 FROM matches m
                 LEFT JOIN ideas i ON i.id = m.idea_id
                 LEFT JOIN users u ON u.id = i.inventor_id
                 WHERE m.builder_id = ?
                 ORDER BY m.created_at DESC",
                [(int) $user['id']]
            );
        } else {
            // Inventor ou admin
            $proposals = $this->db->query(
                "SELECT m.*, i.title AS idea_title, u.name AS builder_name, u.university AS builder_university, u.faculty AS builder_faculty
                 FROM matches m
                 LEFT JOIN ideas i ON i.id = m.idea_id
                 LEFT JOIN users u ON u.id = m.builder_id
                 WHERE i.inventor_id = ?
                 ORDER BY m.created_at DESC",
                [(int) $user['id']]
            );
        }

        $this->json(['proposals' => $proposals, 'total' => count($proposals)]);
    }

    /**
     * POST /api/v1/matches/calculate — Calcula score de afinidade multidisciplinar FKCU.
     */
    public function calculateAffinity(array $params, array $body): void
    {
        $builderId = (int) ($body['candidate_id'] ?? ($body['builder_id'] ?? 0));
        $ideaId    = (int) ($body['idea_id'] ?? 0);

        if (!$builderId || !$ideaId) {
            $this->erro('Parâmetros candidate_id e idea_id são obrigatórios.', 422);
        }

        $builder = $this->db->queryOne('SELECT * FROM users WHERE id = ?', [$builderId]);
        $idea    = $this->db->queryOne('SELECT * FROM ideas WHERE id = ?', [$ideaId]);

        if (!$builder || !$idea) {
            $this->notFound('Builder ou Ideia não encontrados.');
        }

        // 1. C_role (0.35)
        $rolesNeeded = [];
        if (!empty($idea['roles_needed'])) {
            $rolesNeeded = is_string($idea['roles_needed']) ? json_decode($idea['roles_needed'], true) : $idea['roles_needed'];
        }
        $builderRole = $body['proposed_role'] ?? ($builder['profile_type'] ?? 'CTO');
        $cRole = (!empty($rolesNeeded) && in_array($builderRole, (array)$rolesNeeded, true)) ? 1.0 : 0.7;

        // 2. A_fac (0.25)
        $faculty = $builder['faculty'] ?? '';
        $facultyRoleMap = [
            'Engenharia' => ['CTO', 'Tech'],
            'Gestão'     => ['CFO', 'CEO', 'Operations'],
            'Direito'    => ['CLO', 'Legal'],
            'Marketing'  => ['CMO', 'Growth']
        ];
        $aFac = 0.5;
        if (isset($facultyRoleMap[$faculty]) && in_array($builderRole, $facultyRoleMap[$faculty], true)) {
            $aFac = 1.0;
        } elseif ($faculty !== $idea['pdn_axis']) {
            $aFac = 0.8;
        }

        // 3. T_avail (0.15)
        $hours = (int) ($builder['availability_hours'] ?? ($body['hours_per_week'] ?? 10));
        $tAvail = $hours >= 20 ? 1.0 : ($hours >= 10 ? 0.75 : 0.5);

        // 4. E_skill (0.15)
        $skills = is_string($builder['skills'] ?? '') ? json_decode($builder['skills'], true) : ($builder['skills'] ?? []);
        $skillCount = is_array($skills) ? count($skills) : 0;
        $eSkill = min(1.0, 0.4 + ($skillCount * 0.12));

        // 5. V_align (0.10)
        $vAlign = !empty($body['vanderbilt_aligned']) ? 1.0 : 0.85;

        // Fórmula de Afinidade FKCU
        $score = round((0.35 * $cRole + 0.25 * $aFac + 0.15 * $tAvail + 0.15 * $eSkill + 0.10 * $vAlign) * 100, 1);
        $score = min(99.4, max(45.0, $score));

        $this->json([
            'score'       => $score,
            'candidate'   => $builder['name'],
            'role'        => $builderRole,
            'idea'        => $idea['title'],
            'breakdown'   => [
                'c_role'  => round($cRole * 100),
                'a_fac'   => round($aFac * 100),
                't_avail' => round($tAvail * 100),
                'e_skill' => round($eSkill * 100),
                'v_align' => round($vAlign * 100),
            ],
            'recommendation' => $score >= 80 ? 'Altamente Recomendado para Co-Fundador FKCU' : 'Afinidade Moderada'
        ]);
    }

    /**
     * POST /api/v1/matches/invite — Alias aprimorado de proposta com termos FKCU
     */
    public function invite(array $params, array $body): void
    {
        $body['vesting_months'] = 36;
        $body['cliff_months']   = 12;
        $this->propose($params, $body);
    }

    /**
     * POST /api/v1/matches/accept — Aceitação com governança automática
     */
    public function acceptDirect(array $params, array $body): void
    {
        $matchId = (int) ($body['match_id'] ?? ($params['id'] ?? 0));
        $params['id'] = (string) $matchId;
        $body['status'] = 'accepted';
        $this->respond($params, $body);
    }

    /**
     * POST /api/v1/admin/speeddate/session — Criar sessão de Speed Dating (admin).
     */
    public function createSpeedDating(array $params, array $body): void
    {
        $this->requireAuth();
        $user = $this->utilizadorActual();
        if ($user['role'] !== 'admin') {
            $this->forbidden('Apenas administradores podem criar sessões de Speed Dating.');
        }

        $missing = $this->validarObrigatorios(['date', 'time'], $body);
        if ($missing) {
            $this->erro('Data e hora são obrigatórias.', 422);
        }

        $this->db->execute(
            'INSERT INTO speed_dating_sessions (date, time, capacity, status, notes, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [
                $body['date'],
                $body['time'],
                (int) ($body['capacity'] ?? 14),
                'scheduled',
                $body['notes'] ?? null,
            ]
        );

        $this->json(['message' => 'Sessão Speed Dating criada com sucesso.'], 201);
    }
}

