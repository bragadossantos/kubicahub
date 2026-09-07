<?php
/**
 * Kubica Hub — Front Controller API REST v1 (Vercel Serverless & Local)
 * Autoloader completo, rotas API REST v1, protecção CSRF e tratamento de sessão.
 */

declare(strict_types=1);

// ── Configuração base (define constantes da plataforma) ───────────────────
require dirname(__DIR__) . '/app/config/app.php';

// ── Helpers de funções globais ────────────────────────────────────────────
require_once dirname(__DIR__) . '/app/helpers/funcoes.php';

// ── Auto-loader (Composer com fallback PSR-4 nativo) ────────────────────────
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    spl_autoload_register(function (string $class): void {
        $mapa = [
            'App\\Core\\'        => dirname(__DIR__) . '/app/core/',
            'App\\Controllers\\' => dirname(__DIR__) . '/app/controllers/',
            'App\\Models\\'      => dirname(__DIR__) . '/app/models/',
            'App\\Middleware\\'  => dirname(__DIR__) . '/app/middleware/',
            'App\\Helpers\\'     => dirname(__DIR__) . '/app/helpers/',
            'App\\'              => dirname(__DIR__) . '/app/',
        ];

        foreach ($mapa as $prefixo => $dir) {
            if (str_starts_with($class, $prefixo)) {
                $relativo = substr($class, strlen($prefixo));
                $ficheiro = $dir . str_replace('\\', '/', $relativo) . '.php';
                if (file_exists($ficheiro)) {
                    require $ficheiro;
                }
                return;
            }
        }
    });
}

// ── Session global ─────────────────────────────────────────────────────────
session_name('KUBICA_SESSION');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
if (!APP_DEBUG) {
    ini_set('session.cookie_secure', '1');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Iniciar Router ─────────────────────────────────────────────────────────
use App\Core\Router;

$router = new Router();

// ===========================================================================
// ROTAS API v1
// ===========================================================================

$router->grupo('/api/v1', [], function (Router $api): void {

    // ── Públicas (Sem Autenticação) ────────────────────────────────────────
    $api->get('/auth/csrf-token', 'AuthController@csrfToken');
    $api->post('/auth/register',  'AuthController@register');
    $api->post('/auth/otp',       'AuthController@verifyOtp');
    $api->post('/auth/login',     'AuthController@login');
    $api->get('/auth/logout',     'AuthController@logout');
    $api->get('/auth/me',         'AuthController@me');

    // Ideias (Feed público e detalhe)
    $api->get('/ideas',           'IdeaController@index');
    $api->get('/ideas/{id}',      'IdeaController@show');
    $api->post('/matches/calculate', 'MatchController@calculateAffinity');

    // ── Protegidas (Requer Autenticação + Protecção CSRF) ─────────────────
    $api->grupo('', ['AuthMiddleware', 'CsrfMiddleware'], function (Router $auth): void {

        // Perfis
        $auth->get('/profile/{userId}',        'ProfileController@show');
        $auth->patch('/profile/{userId}',      'ProfileController@update');
        $auth->get('/profile/{userId}/badges', 'ProfileController@badges');

        // Ideias do Inventor
        $auth->post('/ideas',                  'IdeaController@store');
        $auth->patch('/ideas/{id}',            'IdeaController@update');
        $auth->delete('/ideas/{id}',           'IdeaController@destroy');

        // Builders / Matchmaking
        $auth->get('/builders',                'MatchController@builderPool');
        $auth->get('/builders/{id}',           'MatchController@builderProfile');
        $auth->post('/matches/calculate',      'MatchController@calculateAffinity');
        $auth->post('/matches/invite',         'MatchController@invite');
        $auth->post('/matches/accept',         'MatchController@acceptDirect');
        $auth->post('/proposals',              'MatchController@propose');
        $auth->patch('/proposals/{id}',        'MatchController@respond');
        $auth->get('/proposals',               'MatchController@myProposals');

        // Startups / Incubação FKCU
        $auth->get('/startups/{id}/dashboard',     'StartupController@dashboard');
        $auth->get('/startups/{id}/deliverables',  'StartupController@deliverables');
        $auth->post('/startups/{id}/deliverables', 'StartupController@submitDeliverable');
        $auth->get('/startups/{id}/traction',      'StartupController@traction');
        $auth->post('/startups/{id}/traction',     'StartupController@addTraction');

        // Notificações
        $auth->get('/notifications',              'NotificationController@list');
        $auth->patch('/notifications/{id}/read',  'NotificationController@markRead');
        $auth->patch('/notifications/read-all',   'NotificationController@markAllRead');

        // Mensagens Internas
        $auth->get('/mensagens',                  'MensagemController@list');
        $auth->get('/mensagens/{conversaId}',     'MensagemController@show');
        $auth->post('/mensagens',                 'MensagemController@send');

        // Governança / Documentos
        $auth->get('/documents/{teamId}',        'DocumentController@list');
        $auth->post('/documents/generate',       'DocumentController@generate');
        $auth->patch('/documents/{id}/sign',     'DocumentController@sign');

        // Sandbox / Financiamento
        $auth->get('/sandbox/{teamId}',          'SandboxController@balance');
        $auth->post('/sandbox/request',          'SandboxController@requestDisbursement');

        // Mediação
        $auth->get('/mediation/{teamId}',        'MediationController@list');
        $auth->post('/mediation',                'MediationController@create');
    });

    // ── Protegidas: Apenas Admin ───────────────────────────────────────────
    $api->grupo('/admin', ['AuthMiddleware', 'RoleMiddleware:admin'], function (Router $adm): void {
        $adm->get('/kpis',                    'AdminController@kpis');
        $adm->get('/ideas/pending',           'AdminController@pendingIdeas');
        $adm->patch('/ideas/{id}/status',     'IdeaController@updateStatus');
        $adm->post('/matching/run',           'AdminController@runMatchingAlgorithm');
        $adm->get('/sandbox/pending',         'AdminController@pendingSandbox');
        $adm->patch('/sandbox/{id}/approve',  'SandboxController@approve');
        $adm->patch('/sandbox/{id}/reject',   'SandboxController@reject');
        $adm->get('/mentors',                 'AdminController@mentors');
        $adm->get('/users',                   'AdminController@users');
        $adm->patch('/users/{id}/status',     'AdminController@toggleUserStatus');
        $adm->post('/speeddate/session',      'MatchController@createSpeedDating');
    });
});

// ── Despachar pedido ───────────────────────────────────────────────────────
$router->dispatch();
