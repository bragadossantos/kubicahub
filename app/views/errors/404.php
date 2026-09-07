<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página Não Encontrada | Kubica Hub</title>
    <style>
        :root {
            --bg-espresso: #140b00;
            --accent-amber: #ffb442;
            --text-light: #f5f0eb;
            --text-muted: #a39589;
            --card-bg: rgba(255, 255, 255, 0.04);
            --card-border: rgba(255, 180, 66, 0.18);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-espresso);
            color: var(--text-light);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .error-card {
            max-width: 520px;
            width: 100%;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 48px 36px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }
        .badge {
            display: inline-block;
            color: var(--accent-amber);
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 72px;
            color: var(--accent-amber);
            line-height: 1;
            margin-bottom: 16px;
            font-weight: 800;
        }
        h2 {
            font-size: 22px;
            margin-bottom: 12px;
            font-weight: 600;
        }
        p {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .btn-home {
            display: inline-block;
            background: var(--accent-amber);
            color: #140b00;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-home:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="badge">Kubica Hub OS</div>
        <h1>404</h1>
        <h2>Página Não Encontrada</h2>
        <p>O recurso ou rota que estás a tentar aceder não existe ou foi movido na plataforma.</p>
        <a href="/" class="btn-home">Voltar ao Início</a>
    </div>
</body>
</html>
