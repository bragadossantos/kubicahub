<?php
/**
 * Kubica Hub — Middleware: Protecção CSRF
 * Valida o token CSRF em requisições de mutação de estado (POST, PUT, PATCH, DELETE).
 */

declare(strict_types=1);

namespace App\Middleware;

class CsrfMiddleware
{
    /**
     * Métodos HTTP seguros que não requerem validação CSRF.
     * @var array<string>
     */
    private const METODOS_SEGUROS = ['GET', 'HEAD', 'OPTIONS'];

    /**
     * Tratar o pedido. Devolve true para continuar, false para interromper.
     */
    public function tratar(): bool
    {
        $metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Métodos seguros não necessitam validação
        if (in_array($metodo, self::METODOS_SEGUROS, true)) {
            return true;
        }

        // Assegurar que a sessão tem um token gerado
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Tentar obter token do cabeçalho X-CSRF-Token
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        // Se não veio no cabeçalho, tentar ler do corpo (JSON ou form POST)
        if ($token === null) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (str_contains($contentType, 'application/json')) {
                $raw = file_get_contents('php://input');
                $corpo = json_decode($raw ?: '{}', true) ?? [];
                $token = $corpo['_csrf'] ?? null;
            } else {
                $token = $_POST['_csrf'] ?? null;
            }
        }

        if (empty($token) || !hash_equals((string) $_SESSION['csrf_token'], (string) $token)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'data'    => null,
                'message' => 'Token CSRF inválido ou ausente. Recarrega a página ou envia o cabeçalho X-CSRF-Token.',
                'errors'  => ['csrf' => 'Token CSRF inválido.'],
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }

        return true;
    }
}
