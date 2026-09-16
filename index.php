<?php

require_once "vendor/autoload.php";

use Model\FilmeModel;
use Controller\FilmeController;
use Model\UsuarioModel;
use Controller\UsuarioController;
use Model\CategoriaModel;
use Controller\CategoriaController;
use OpenApi\Attributes as OA;
use OpenApi\Generator;

#[OA\Info(title: "API Catálogo de Filmes", version: "1.0.0", description: "API REST para gerenciamento de um catálogo de filmes.")]
#[OA\Server(url: "http://localhost:3000", description: "Servidor Local")]
class ApiDocs {}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false;
}

$parts = array_values(array_filter(explode('/', $path)));
$resource = !empty($parts) ? strtolower($parts[0]) : "";

if ($resource === "" || $resource === "index.php") {
    header("Content-Type: text/html; charset=UTF-8");
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Catálogo de Filmes</title>
        <link rel="stylesheet" href="/style.css">
    </head>
    <body>

        <section id="auth-screen" class="screen">
            <div class="auth-card">
                <h2 id="auth-title">Entrar na Conta</h2>
                
                <form id="auth-form" onsubmit="handleAuthSubmit(event)">
                    <div class="form-group">
                        <label for="auth-email">E-mail</label>
                        <input type="email" id="auth-email" required placeholder="seu@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="auth-senha">Senha</label>
                        <input type="password" id="auth-senha" required placeholder="Digite sua senha">
                    </div>

                    <button type="submit" id="auth-btn">Entrar</button>
                </form>

                <div class="auth-toggle">
                    <span id="auth-toggle-text">Não tem uma conta?</span>
                    <a onclick="toggleAuthMode()" id="auth-toggle-btn">Cadastre-se</a>
                </div>
            </div>
        </section>

        <section id="main-screen" class="screen hidden">
            <header>
                <h1>Catálogo de Filmes</h1>
                <div class="user-bar">
                    <span>Logado como: <strong id="user-display"></strong></span>
                    <button class="btn-logout" onclick="logout()">Sair</button>
                </div>
            </header>

            <div class="search-container">
                <input type="text" id="search-input" placeholder="Buscar por título ou descrição..." oninput="filtrarFilmes()">
                <select id="categoria-select" onchange="filtrarFilmes()">
                    <option value="">Todas as Categorias</option>
                </select>
            </div>

            <main>
                <div class="grid" id="filmes-container">
                    <p>Carregando filmes...</p>
                </div>
            </main>
        </section>

        <script src="/script.js"></script>
    </body>
    </html>
    <?php
    exit;
}

header("Content-Type: application/json; charset=UTF-8");
$id = $parts[1] ?? null;

try {
    switch ($resource) {
        case "docs":
            $openapi = Generator::scan([__DIR__ . '/Controller', __DIR__ . '/Model', __DIR__]);
            header("Content-Type: application/json; charset=UTF-8");
            echo $openapi->toJson();
            exit;

        case "filmes":
            $filmeModel = new FilmeModel();
            $filmeController = new FilmeController($filmeModel);
            $filmeController->ProcessRequest($_SERVER['REQUEST_METHOD'], $id);
            break;

        case "categorias":
            $categoriaModel = new CategoriaModel();
            $categoriaController = new CategoriaController($categoriaModel);
            $categoriaController->ProcessRequest($_SERVER['REQUEST_METHOD'], $id);
            break;

        case "login":
            $usuarioModel = new UsuarioModel();
            $usuarioController = new UsuarioController($usuarioModel);
            $usuarioController->login();
            break;

        case "usuarios":
            $usuarioModel = new UsuarioModel();
            $usuarioController = new UsuarioController($usuarioModel);
            $usuarioController->ProcessRequest($_SERVER['REQUEST_METHOD'], $id);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                "error" => "Rota não encontrada!",
                "rotas_disponiveis" => ["/", "/filmes", "/categorias", "/login", "/usuarios", "/docs"]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;
    }
} catch (\Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Erro interno no servidor: " . $error->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}