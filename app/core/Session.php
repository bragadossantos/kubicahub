<?php
/**
 * Kubica Hub — Core: Session Manager
 * Gestão segura de sessões PHP com regeneração de ID e flash messages.
 */

declare(strict_types=1);

namespace App\Core;

class Session
{
    /**
     * Guardar valor na sessão.
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Ler valor da sessão.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar se chave existe.
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remover chave da sessão.
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Autenticar utilizador — guardar dados na sessão.
     * @param array<string, mixed> $user
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true); // Prevenir session fixation

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['university'] = $user['university'] ?? null;
        $_SESSION['faculty']    = $user['faculty']    ?? null;
        $_SESSION['logged_in']  = true;
        $_SESSION['login_time'] = time();
    }

    /**
     * Terminar sessão — logout seguro.
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Verificar se o utilizador está autenticado.
     */
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    /**
     * Verificar se a sessão expirou (timeout de 2 horas).
     */
    public static function isExpired(int $timeout = 7200): bool
    {
        if (!self::has('login_time')) {
            return true;
        }
        return (time() - (int) $_SESSION['login_time']) > $timeout;
    }

    /**
     * Flash message — disponível apenas na próxima request.
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Ler e apagar flash message.
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
