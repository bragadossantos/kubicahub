<?php
/**
 * Kubica Hub — Middleware: Controlo de Papel (Role)
 * Uso: new RoleMiddleware('admin') ou new RoleMiddleware(['inventor','admin'])
 */

declare(strict_types=1);

namespace App\Middleware;

class RoleMiddleware
{
    /** @var array<string> */
    private array $rolesPermitidos;

    /** @param string|array<string> $roles */
    public function __construct(string|array $roles)
    {
        $this->rolesPermitidos = is_array($roles) ? $roles : [$roles];
    }

    /**
     * Tratar o pedido. Devolve true para continuar, false para interromper.
     */
    public function tratar(): bool
    {
        $roleActual = $_SESSION['role'] ?? '';

        if (!in_array($roleActual, $this->rolesPermitidos, true)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'data'    => null,
                'message' => 'Acesso proibido. Papel requerido: ' . implode(' ou ', $this->rolesPermitidos) . '.',
                'errors'  => [],
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }

        return true;
    }
}
