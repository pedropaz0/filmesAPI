<?php

require_once "vendor/autoload.php";

use Model\FilmeModel;
use Controller\FilmeController;

header("Content-Type: application/json; charset=UTF-8");

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = array_values(array_filter(explode('/', $path)));

$resource = !empty($parts) ? strtolower($parts[0]) : "filmes";
$id = $parts[1] ?? null;

if ($resource !== "filmes") {
    http_response_code(404);
    echo json_encode([
        "error" => "Rota não encontrada!",
        "rota_recebida" => "/" . implode("/", $parts),
        "dica" => "Acesse http://localhost:3000/filmes"
    ]);
    exit;
}

try {
    $filmeModel = new FilmeModel();
    $filmeController = new FilmeController($filmeModel);

    $filmeController->ProcessRequest($_SERVER['REQUEST_METHOD'], $id);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Erro interno no servidor: " . $error->getMessage()]);
}