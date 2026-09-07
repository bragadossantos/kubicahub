<?php
/**
 * Kubica Hub — Controller: Auth
 * Adicionado: AuthController@me (dados do utilizador actual para o frontend)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * POST /api/v1/auth/register
     */
    public function register(array $params, array $body): void
    {
        $required = ['name', 'email', 'password', 'role', 'university', 'faculty'];
        $missing  = $this->validarObrigatorios($required, $body);

        if ($missing) {
            $this->erro('Campos obrigatórios em falta: ' . implode(', ', $missing), 422);
        }

        // Validar role (apenas inventor ou builder no registo público)
        $validRoles = [User::ROLE_INVENTOR, User::ROLE_BUILDER];
        if (!in_array($body['role'], $validRoles, true)) {
            $this->erro("Role inválido. Use 'inventor' ou 'builder'.", 422);
        }

        // Validar email institucional
        if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            $this->erro('Email institucional inválido.', 422);
        }

        // Validar email pessoal (se fornecido)
        if (!empty($body['personal_email']) && !filter_var($body['personal_email'], FILTER_VALIDATE_EMAIL)) {
            $this->erro('Email pessoal inválido.', 422);
        }

        // Se universidade for customizada ou não listada
        if (!empty($body['university_custom'])) {
            $body['university'] = substr(trim($body['university_custom']), 0, 80);
        } elseif (!in_array($body['university'], User::UNIVERSITIES, true)) {
            $body['university'] = substr(trim((string)$body['university']), 0, 80);
        }

        // Validar faculdade
        if (!in_array($body['faculty'], User::FACULTIES, true)) {
            $body['faculty'] = 'Engenharia';
        }

        // Verificar email duplicado
        if ($this->user->findByEmail($body['email'])) {
            $this->erro('Este email já está registado.', 409);
        }

        // Validar password (mín. 8 chars, 1 maiúscula, 1 número)
        $errosPass = $this->validarPassword($body['password']);
        if (!empty($errosPass)) {
            $this->erro(implode(' ', $errosPass), 422, $errosPass);
        }

        // Criar utilizador
        $userId = $this->user->register($this->sanitizarArray($body) + ['password' => $body['password']]);

        // Gerar OTP
        $otp = $this->user->generateOtp($userId);

        // Em produção: enviar email/SMS com $otp
        $response = [
            'user_id' => $userId,
            'message' => 'Registo efectuado. Verifica o teu email para o código OTP.'
        ];
        if (APP_DEBUG) {
            $response['dev_otp'] = $otp;
        }

        $this->json($response, 201);
    }

    /**
     * POST /api/v1/auth/otp
     */
    public function verifyOtp(array $params, array $body): void
    {
        $missing = $this->validarObrigatorios(['user_id', 'otp'], $body);
        if ($missing) {
            $this->erro('Campos obrigatórios: user_id e otp.', 422);
        }

        $verified = $this->user->verifyOtp((int) $body['user_id'], (string) $body['otp']);

        if (!$verified) {
            $this->erro('OTP inválido ou expirado. Solicita um novo.', 401);
        }

        $this->json(['message' => 'Email verificado com sucesso. Podes fazer login.']);
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(array $params, array $body): void
    {
        $missing = $this->validarObrigatorios(['email', 'password'], $body);
        if ($missing) {
            $this->erro('Email e password obrigatórios.', 422);
        }

        // Verificar bloqueio por IP antes de tentar
        if ($this->user->isIpBlocked('login')) {
            $this->erro('Demasiadas tentativas. Tente novamente em 15 minutos.', 429);
        }

        $user = $this->user->authenticate($body['email'], $body['password']);

        if ($user === false) {
            $this->erro('Conta temporariamente bloqueada. Tente novamente em 15 minutos.', 429);
        }

        if ($user === null) {
            $this->erro('Credenciais inválidas.', 401);
        }

        if (empty($user['is_verified'])) {
            $this->erro('Email não verificado. Verifica o código OTP enviado.', 403);
        }

        if (empty($user['is_active'])) {
            $this->erro('Conta suspensa. Contacta a coordenação FKCU.', 403);
        }

        // Criar sessão
        Session::login($user);

        $this->json([
            'user'    => [
                'id'         => $user['id'],
                'name'       => $user['name'],
                'role'       => $user['role'],
                'university' => $user['university'],
                'faculty'    => $user['faculty'],
            ],
            'message' => 'Sessão iniciada com sucesso. Bem-vindo ao Kubica Hub!'
        ]);
    }

    /**
     * GET /api/v1/auth/logout
     */
    public function logout(array $params, array $body): void
    {
        Session::logout();
        $this->json(['message' => 'Sessão terminada com sucesso.']);
    }

    /**
     * GET /api/v1/auth/me — dados do utilizador autenticado (para o frontend verificar sessão PHP)
     */
    public function me(array $params, array $body): void
    {
        if (!Session::isLoggedIn() || Session::isExpired()) {
            $this->erro('Não autenticado.', 401);
        }

        $this->json([
            'user' => [
                'id'         => (int) ($_SESSION['user_id']   ?? 0),
                'name'       => $_SESSION['user_name']         ?? '',
                'email'      => $_SESSION['user_email']        ?? '',
                'role'       => $_SESSION['role']              ?? '',
                'university' => $_SESSION['university']        ?? '',
                'faculty'    => $_SESSION['faculty']           ?? '',
            ]
        ]);
    }
}
