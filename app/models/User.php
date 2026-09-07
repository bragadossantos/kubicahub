<?php
/**
 * Kubica Hub — Model: User v2.0
 * Adicionado: tracking de login_attempts, bloqueio temporário (brute force) e last_login_at
 */

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    // Roles válidos no FKCU
    public const ROLE_INVENTOR = 'inventor';
    public const ROLE_BUILDER  = 'builder';
    public const ROLE_ADMIN    = 'admin';
    public const ROLE_MENTOR   = 'mentor';

    public const UNIVERSITIES = [
        'UAN', 'UCAN', 'ISAF', 'UGS', 'ISPTEC', 'UniPiaget', 
        'UNIA', 'UTANGA', 'UJES', 'UKB', 'UMN', 'UNILUANDA', 
        'ISUTIC', 'IMETRO', 'Outra'
    ];
    public const FACULTIES    = [
        'Engenharia', 'Gestão', 'Direito', 'Marketing',
        'Ciências Agrárias', 'Saúde & Biologia', 'Design & Multimédia', 'Outra'
    ];

    /**
     * Encontrar utilizador por email.
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->queryOne(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    /**
     * Verificar credenciais de login com protecção contra brute force.
     * @return array<string, mixed>|null|false (false significa conta bloqueada temporariamente)
     */
    public function authenticate(string $email, string $password): array|null|bool
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            $this->registarTentativaFalhadaIp();
            return null;
        }

        // 1. Verificar se a conta está bloqueada (locked_until)
        if ($user['locked_until'] !== null) {
            if (strtotime($user['locked_until']) > time()) {
                return false; // Conta ainda bloqueada
            }
            // Bloqueio expirou, limpar tentativas
            $this->resetarTentativas($user['id']);
        }

        // 2. Verificar password
        if (!password_verify($password, $user['password_hash'])) {
            $this->incrementarTentativas($user['id'], (int)$user['login_attempts']);
            return null;
        }

        // 3. Login com sucesso
        $this->resetarTentativas($user['id']);
        
        // Actualizar last_login_at
        $this->db->execute(
            'UPDATE users SET last_login_at = NOW() WHERE id = ?',
            [$user['id']]
        );

        // Limpar dados sensíveis antes de devolver
        unset($user['password_hash'], $user['otp_code'], $user['otp_expires_at'], $user['password_reset_token']);
        
        return $user;
    }

    /**
     * Incrementa contador de tentativas falhadas e bloqueia se exceder limite (RATE_LIMIT_MAX_TENTATIVAS).
     */
    private function incrementarTentativas(int $userId, int $tentativasActuais): void
    {
        $maxTentativas = defined('RATE_LIMIT_MAX_TENTATIVAS') ? RATE_LIMIT_MAX_TENTATIVAS : 5;
        $minBloqueio = defined('RATE_LIMIT_BLOQUEIO_MIN') ? RATE_LIMIT_BLOQUEIO_MIN : 15;
        
        $novasTentativas = $tentativasActuais + 1;
        
        if ($novasTentativas >= $maxTentativas) {
            $this->db->execute(
                "UPDATE users SET login_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?",
                [$novasTentativas, $minBloqueio, $userId]
            );
        } else {
            $this->db->execute(
                'UPDATE users SET login_attempts = ? WHERE id = ?',
                [$novasTentativas, $userId]
            );
        }
        
        $this->registarTentativaFalhadaIp();
    }

    /**
     * Reseta as tentativas falhadas e remove o bloqueio.
     */
    private function resetarTentativas(int $userId): void
    {
        $this->db->execute(
            'UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?',
            [$userId]
        );
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->db->execute("DELETE FROM rate_limit WHERE chave = ? AND acao = 'login'", [$ip]);
    }

    /**
     * Regista tentativa falhada por IP na tabela rate_limit genérica
     */
    private function registarTentativaFalhadaIp(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->db->execute(
            "INSERT INTO rate_limit (chave, acao, tentativas, bloqueado_ate) 
             VALUES (?, 'login', 1, NULL) 
             ON DUPLICATE KEY UPDATE 
             tentativas = tentativas + 1, 
             bloqueado_ate = IF(tentativas + 1 >= ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), NULL)",
            [$ip, defined('RATE_LIMIT_MAX_TENTATIVAS') ? RATE_LIMIT_MAX_TENTATIVAS : 5, defined('RATE_LIMIT_BLOQUEIO_MIN') ? RATE_LIMIT_BLOQUEIO_MIN : 15]
        );
    }
    
    /**
     * Verifica se o IP actual está bloqueado na tabela rate_limit genérica
     */
    public function isIpBlocked(string $acao = 'login'): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $registo = $this->db->queryOne(
            "SELECT bloqueado_ate FROM rate_limit WHERE chave = ? AND acao = ? LIMIT 1",
            [$ip, $acao]
        );
        
        if ($registo && $registo['bloqueado_ate'] !== null) {
            return strtotime($registo['bloqueado_ate']) > time();
        }
        
        return false;
    }

    /**
     * Registar novo utilizador.
     * @param array<string, mixed> $data
     */
    public function register(array $data): int
    {
        return $this->create([
            'role'          => $data['role'],
            'name'          => $data['name'],
            'email'         => $data['email'],
            'university'    => $data['university'],
            'faculty'       => $data['faculty'],
            'password_hash' => password_hash($data['password'], PASSWORD_ARGON2ID),
            'profile_type'  => $data['profile_type'] ?? $data['role'],
            'availability_hours' => $data['availability_hours'] ?? null,
            'skills'        => isset($data['skills']) ? json_encode($data['skills']) : null,
            'bio'           => $data['bio'] ?? null,
            'is_verified'   => 0,
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Gerar e guardar código OTP (6 dígitos, válido 15 minutos).
     */
    public function generateOtp(int $userId): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->db->execute(
            'UPDATE users SET otp_code = ?, otp_expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?',
            [$otp, $userId]
        );
        return $otp;
    }

    /**
     * Verificar OTP e marcar como verificado.
     */
    public function verifyOtp(int $userId, string $otp): bool
    {
        $user = $this->db->queryOne(
            'SELECT otp_code, otp_expires_at FROM users WHERE id = ? LIMIT 1',
            [$userId]
        );
        if (!$user) {
            return false;
        }
        if ($user['otp_code'] !== $otp) {
            return false;
        }
        if (strtotime($user['otp_expires_at']) < time()) {
            return false;
        }
        $this->db->execute(
            'UPDATE users SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = ?',
            [$userId]
        );
        return true;
    }

    /**
     * Pool de builders disponíveis para matchmaking.
     * @return array<int, array<string, mixed>>
     */
    public function builderPool(array $filters = []): array
    {
        $where  = ["role = 'builder'", 'is_verified = 1', 'is_active = 1'];
        $params = [];

        if (!empty($filters['faculty'])) {
            $where[]  = 'faculty = ?';
            $params[] = $filters['faculty'];
        }
        if (!empty($filters['university'])) {
            $where[]  = 'university = ?';
            $params[] = $filters['university'];
        }
        if (!empty($filters['min_hours'])) {
            $where[]  = 'availability_hours >= ?';
            $params[] = (int) $filters['min_hours'];
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);
        return $this->db->query(
            "SELECT id, name, university, faculty, availability_hours, skills, bio, profile_photo, created_at
             FROM users {$whereClause} ORDER BY created_at DESC LIMIT 100",
            $params
        );
    }

    /**
     * Perfil público de um utilizador (sem dados sensíveis).
     * @return array<string, mixed>|null
     */
    public function publicProfile(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT id, name, role, university, faculty, availability_hours, skills, bio, profile_photo, created_at
             FROM users WHERE id = ? LIMIT 1',
            [$id]
        );
    }
}
