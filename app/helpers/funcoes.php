<?php
/**
 * Kubica Hub — Helpers Genéricos
 * CORRIGIDO: removido namespace (funções globais são incluídas via require_once)
 */

declare(strict_types=1);

if (!function_exists('dd')) {
    /**
     * Dump e Die — apenas em modo debug.
     */
    function dd(mixed ...$vars): void
    {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo '<pre style="background:#111;color:#0f0;padding:16px;border-radius:6px;font-size:13px;">';
            foreach ($vars as $var) {
                var_dump($var);
            }
            echo '</pre>';
            exit;
        }
    }
}

if (!function_exists('gerarUUID')) {
    /**
     * Gera um UUID v4 seguro.
     */
    function gerarUUID(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('formatarMoeda')) {
    /**
     * Formata valor em USD.
     */
    function formatarMoeda(float $valor): string
    {
        return 'USD ' . number_format($valor, 2, '.', ',');
    }
}

if (!function_exists('sanitizar')) {
    /**
     * Sanitizar string para output HTML.
     */
    function sanitizar(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('timeAgo')) {
    /**
     * Formata data como "há X minutos/horas/dias".
     */
    function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        return match (true) {
            $diff < 60     => 'agora mesmo',
            $diff < 3600   => floor($diff / 60) . ' min atrás',
            $diff < 86400  => floor($diff / 3600) . 'h atrás',
            $diff < 604800 => floor($diff / 86400) . ' dias atrás',
            default        => date('d/m/Y', strtotime($datetime)),
        };
    }
}
