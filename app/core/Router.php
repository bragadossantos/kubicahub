<?php
/**
 * Kubica Hub — Core: Router v2.0
 * Suporte a middleware pipeline + parâmetros dinâmicos {param}.
 */

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:string, middleware:array<string>}> */
    private array $routes = [];
    private string $controllerNamespace = 'App\\Controllers\\';
    private string $middlewareNamespace  = 'App\\Middleware\\';

    // ── Registar rotas ──────────────────────────────────────────────────────

    /** @param array<string> $middleware */
    public function get(string $caminho, string $handler, array $middleware = []): void
    {
        $this->adicionarRota('GET', $caminho, $handler, $middleware);
    }

    /** @param array<string> $middleware */
    public function post(string $caminho, string $handler, array $middleware = []): void
    {
        $this->adicionarRota('POST', $caminho, $handler, $middleware);
    }

    /** @param array<string> $middleware */
    public function patch(string $caminho, string $handler, array $middleware = []): void
    {
        $this->adicionarRota('PATCH', $caminho, $handler, $middleware);
    }

    /** @param array<string> $middleware */
    public function put(string $caminho, string $handler, array $middleware = []): void
    {
        $this->adicionarRota('PUT', $caminho, $handler, $middleware);
    }

    /** @param array<string> $middleware */
    public function delete(string $caminho, string $handler, array $middleware = []): void
    {
        $this->adicionarRota('DELETE', $caminho, $handler, $middleware);
    }

    /** @param array<string> $middleware */
    private function adicionarRota(string $metodo, string $caminho, string $handler, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $metodo,
            'pattern'    => $caminho,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    // ── Grupo de rotas com middleware partilhado ────────────────────────────

    /**
     * Agrupa rotas com prefixo e middleware comuns.
     * Exemplo: $router->group('/api/v1/inventor', ['auth', 'inventor'], function($r) { ... })
     *
     * @param array<string> $middleware
     */
    public function grupo(string $prefixo, array $middleware, callable $callback): void
    {
        // Salvar estado actual
        $rotasAntes = $this->routes;

        // Executar callback — adiciona rotas sem prefixo/middleware
        $callback($this);

        // Aplicar prefixo e middleware a todas as rotas novas
        foreach ($this->routes as $i => $rota) {
            if (!in_array($rota, $rotasAntes, true)) {
                $this->routes[$i]['pattern']    = $prefixo . $rota['pattern'];
                $this->routes[$i]['middleware'] = array_merge($middleware, $rota['middleware']);
            }
        }
    }

    // ── Despachar pedido ───────────────────────────────────────────────────

    public function despachar(): void
    {
        $metodo = $_SERVER['REQUEST_METHOD'];
        $uri    = $this->obterUri();

        foreach ($this->routes as $rota) {
            if ($rota['method'] !== $metodo) {
                continue;
            }

            $params = $this->corresponderRota($rota['pattern'], $uri);
            if ($params !== null) {
                // Executar pipeline de middleware
                if (!$this->executarMiddleware($rota['middleware'])) {
                    return; // Middleware interrompeu o pedido
                }
                // Chamar o controller
                $this->chamarHandler($rota['handler'], $params);
                return;
            }
        }

        $this->naoEncontrado();
    }

    /**
     * Executar cadeia de middleware. Devolve false se algum interromper.
     * @param array<string> $middlewares
     */
    private function executarMiddleware(array $middlewares): bool
    {
        foreach ($middlewares as $mw) {
            $partes = explode(':', $mw, 2);
            $nomeMw = $partes[0];
            $argsStr = $partes[1] ?? '';
            $args = $argsStr ? explode(',', $argsStr) : [];

            $classe = $this->middlewareNamespace . $nomeMw;
            if (!class_exists($classe)) {
                continue;
            }
            
            $instancia = !empty($args) ? new $classe($args) : new $classe();
            
            if (!$instancia->tratar()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Compara a rota com o URI. Devolve parâmetros extraídos ou null.
     * @return array<string, string>|null
     */
    private function corresponderRota(string $padrao, string $uri): ?array
    {
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $padrao);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $uri, $matches)) {
            return null;
        }

        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Instanciar o controller e chamar o método.
     * Handler no formato "NomeController@metodo"
     * @param array<string, string> $params
     */
    private function chamarHandler(string $handler, array $params): void
    {
        [$nomeController, $metodo] = explode('@', $handler, 2);
        $classe = $this->controllerNamespace . $nomeController;

        if (!class_exists($classe)) {
            $this->erroServidor("Controller '{$nomeController}' não encontrado.");
            return;
        }

        $controller = new $classe();

        if (!method_exists($controller, $metodo)) {
            $this->erroServidor("Método '{$metodo}' não existe em '{$nomeController}'.");
            return;
        }

        // Corpo do pedido (JSON ou form-data)
        $corpo = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw   = file_get_contents('php://input');
            $corpo = json_decode($raw ?: '{}', true) ?? [];
        } else {
            $corpo = $_POST;
        }

        $controller->$metodo($params, $corpo);
    }

    private function obterUri(): string
    {
        $uri = $_GET['url'] ?? $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        return '/' . trim($uri, '/');
    }

    private function querHtml(): bool
    {
        $uri = $this->obterUri();
        if (str_starts_with($uri, '/api/')) {
            return false;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'text/html');
    }

    private function naoEncontrado(): void
    {
        http_response_code(404);
        if ($this->querHtml()) {
            header('Content-Type: text/html; charset=UTF-8');
            $viewPath = defined('VIEW_PATH') ? VIEW_PATH . '/errors/404.php' : dirname(__DIR__) . '/views/errors/404.php';
            if (file_exists($viewPath)) {
                require $viewPath;
                return;
            }
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Rota não encontrada.', 'codigo' => 404], JSON_UNESCAPED_UNICODE);
    }

    private function erroServidor(string $msg): void
    {
        http_response_code(500);
        if ($this->querHtml()) {
            header('Content-Type: text/html; charset=UTF-8');
            $mensagemErro = $msg;
            $viewPath = defined('VIEW_PATH') ? VIEW_PATH . '/errors/500.php' : dirname(__DIR__) . '/views/errors/500.php';
            if (file_exists($viewPath)) {
                require $viewPath;
                return;
            }
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => APP_DEBUG ? $msg : 'Erro interno do servidor.',
            'codigo'  => 500,
        ], JSON_UNESCAPED_UNICODE);
    }

    // Alias em inglês para compatibilidade
    public function dispatch(): void { $this->despachar(); }
}
