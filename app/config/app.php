<?php
/**
 * Kubica Hub — Configuração Global da Aplicação v2.0
 * Corrigido: headers de segurança, CORS restrito, rate limit config
 */

declare(strict_types=1);

// ── Modo de execução ───────────────────────────────────────────────────────
define('APP_ENV',     getenv('APP_ENV')  ?: 'development');
define('APP_DEBUG',   APP_ENV === 'development');
define('APP_NAME',    'Kubica Hub');
define('APP_VERSION', '2.0.0');

// ── URLs base ──────────────────────────────────────────────────────────────
define('APP_URL',     getenv('APP_URL')  ?: 'http://localhost');
define('BASE_PATH',   dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('APP_PATH',    BASE_PATH . '/app');
define('VIEW_PATH',   APP_PATH  . '/views');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// ── Rate Limiting ──────────────────────────────────────────────────────────
define('RATE_LIMIT_MAX_TENTATIVAS', 5);
define('RATE_LIMIT_BLOQUEIO_MIN',   15); // minutos de bloqueio

// ── Tratamento de erros ────────────────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/logs/errors.log');
}

// ── Timezone ────────────────────────────────────────────────────────────────
date_default_timezone_set('Africa/Luanda');

// ── Origens permitidas (CORS restrito) ────────────────────────────────────
$origensPermitidas = APP_DEBUG
    ? ['http://localhost', 'http://localhost:8000', 'http://127.0.0.1', 'http://localhost:80']
    : [APP_URL];

$origemPedido = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origemPedido, $origensPermitidas, true)) {
    header('Access-Control-Allow-Origin: ' . $origemPedido);
    header('Vary: Origin');
}

header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Headers de Segurança (não negociáveis) ─────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

if (!APP_DEBUG) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self'");
}

// ── Tipo de conteúdo por defeito para respostas API ────────────────────────
// Nota: pode ser sobrescrito por controllers que servem HTML
header('Content-Type: application/json; charset=UTF-8');
