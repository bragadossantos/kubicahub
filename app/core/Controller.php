<?php
/**
 * Kubica Hub — Core: Controller base v2.0
 * Adicionado: requireRole(), paginação, respostas normalizadas /api/v1/
 */

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    // ── Respostas JSON normalizadas ────────────────────────────────────────

    /**
     * Resposta de sucesso padronizada.
     * @param mixed $dados
     */
    protected function json(mixed $dados, int $status = 200, string $mensagem = ''): void
    {
        http_response_code($status);
        echo json_encode([
            'success'  => true,
            'data'     => $dados,
            'message'  => $mensagem,
            'errors'   => [],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Resposta de erro padronizada.
     * @param array<string> $erros
     */
    protected function erro(string $mensagem, int $status = 400, array $erros = []): void
    {
        http_response_code($status);
        echo json_encode([
            'success' => false,
            'data'    => null,
            'message' => $mensagem,
            'errors'  => $erros,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Alias em inglês
    protected function error(string $msg, int $status = 400, array $erros = []): void
    {
        $this->erro($msg, $status, $erros);
    }

    protected function unauthorized(string $msg = 'Não autorizado.'): void
    {
        $this->erro($msg, 401);
    }

    protected function forbidden(string $msg = 'Acesso proibido.'): void
    {
        $this->erro($msg, 403);
    }

    protected function notFound(string $msg = 'Recurso não encontrado.'): void
    {
        $this->erro($msg, 404);
    }

    // ── Autenticação e Autorização ─────────────────────────────────────────

    /**
     * Verificar autenticação. Aborta com 401 se não autenticado.
     */
    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->unauthorized('Sessão expirada. Por favor inicia sessão novamente.');
        }

        // Verificar expiração de sessão (2 horas)
        if (!empty($_SESSION['login_time']) && (time() - (int) $_SESSION['login_time']) > 7200) {
            session_destroy();
            $this->unauthorized('Sessão expirada por inactividade. Por favor inicia sessão novamente.');
        }
    }

    /**
     * Verificar role específico. Aborta com 403 se role não corresponder.
     * @param string|array<string> $roles
     */
    protected function requireRole(string|array $roles): void
    {
        $this->requireAuth();
        $rolesPermitidos = is_array($roles) ? $roles : [$roles];
        $roleActual = $_SESSION['role'] ?? '';

        if (!in_array($roleActual, $rolesPermitidos, true)) {
            $this->forbidden(
                'Acesso restrito. Esta funcionalidade requer o papel: ' . implode(' ou ', $rolesPermitidos) . '.'
            );
        }
    }

    /**
     * Verificar se é admin.
     */
    protected function requireAdmin(): void
    {
        $this->requireRole('admin');
    }

    /**
     * Verificar se é inventor.
     */
    protected function requireInventor(): void
    {
        $this->requireRole('inventor');
    }

    /**
     * Verificar se é builder.
     */
    protected function requireBuilder(): void
    {
        $this->requireRole('builder');
    }

    /**
     * Utilizador autenticado actual.
     * @return array<string, mixed>
     */
    protected function utilizadorActual(): array
    {
        return [
            'id'         => (int) ($_SESSION['user_id']   ?? 0),
            'nome'       => $_SESSION['user_name']         ?? '',
            'email'      => $_SESSION['user_email']        ?? '',
            'role'       => $_SESSION['role']              ?? '',
            'university' => $_SESSION['university']        ?? '',
            'faculty'    => $_SESSION['faculty']           ?? '',
        ];
    }

    // Alias inglês
    protected function currentUser(): array { return $this->utilizadorActual(); }

    // ── Validação ─────────────────────────────────────────────────────────

    /**
     * Verificar campos obrigatórios.
     * @param array<string> $campos
     * @param array<string, mixed> $corpo
     * @return array<string> Campos em falta
     */
    protected function validarObrigatorios(array $campos, array $corpo): array
    {
        $emFalta = [];
        foreach ($campos as $campo) {
            if (!isset($corpo[$campo]) || $corpo[$campo] === '' || $corpo[$campo] === null) {
                $emFalta[] = $campo;
            }
        }
        return $emFalta;
    }

    // Alias inglês
    protected function validateRequired(array $fields, array $body): array
    {
        return $this->validarObrigatorios($fields, $body);
    }

    /**
     * Validar endereço de email.
     */
    protected function validarEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validar password (mínimo 8 chars, 1 maiúscula, 1 número).
     */
    protected function validarPassword(string $password): array
    {
        $erros = [];
        if (strlen($password) < 8) {
            $erros[] = 'A senha deve ter mínimo 8 caracteres.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $erros[] = 'A senha deve conter pelo menos uma letra maiúscula.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $erros[] = 'A senha deve conter pelo menos um número.';
        }
        return $erros;
    }

    // ── CSRF ──────────────────────────────────────────────────────────────

    protected function gerarCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validarCsrf(array $corpo): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $corpo['_csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $this->erro('Token CSRF inválido. Recarrega a página e tenta novamente.', 403);
        }
    }

    // Alias inglês
    protected function csrfToken(): string { return $this->gerarCsrfToken(); }
    protected function validateCsrf(array $body): void { $this->validarCsrf($body); }

    // ── Sanitização ───────────────────────────────────────────────────────

    protected function sanitizar(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    protected function sanitizarArray(array $dados): array
    {
        return array_map(
            fn($v) => is_string($v) ? $this->sanitizar($v) : $v,
            $dados
        );
    }

    // Alias inglês
    protected function sanitize(string $input): string { return $this->sanitizar($input); }
    protected function sanitizeArray(array $data): array { return $this->sanitizarArray($data); }

    // ── Paginação ─────────────────────────────────────────────────────────

    /**
     * Calcular offset de paginação a partir dos query params.
     */
    protected function paginacao(int $porPagina = 20): array
    {
        $pagina   = max(1, (int) ($_GET['pagina'] ?? 1));
        $limite   = min(100, max(1, (int) ($_GET['limite'] ?? $porPagina)));
        $offset   = ($pagina - 1) * $limite;
        return ['pagina' => $pagina, 'limite' => $limite, 'offset' => $offset];
    }
}
