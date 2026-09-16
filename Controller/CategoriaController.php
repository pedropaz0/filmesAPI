<?php

namespace Controller;

use Model\CategoriaModel;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/categorias",
    summary: "Lista todas as categorias",
    responses: [
        new OA\Response(
            response: 200,
            description: "Sucesso",
            content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/Categoria"))
        )
    ]
)]
class CategoriaController
{
    public function __construct(private CategoriaModel $categoriaModel)
    {
    }

    public function ProcessRequest(string $method, ?string $id): void
    {
        header("Content-Type: application/json; charset=UTF-8");

        if ($id === null) {
            match ($method) {
                "GET" => $this->index(),
                "POST" => $this->create(),
                default => $this->methodNotAllowed(["GET", "POST"])
            };
            return;
        }

        match ($method) {
            "GET" => $this->show((int) $id),
            "DELETE" => $this->delete((int) $id),
            default => $this->methodNotAllowed(["GET", "DELETE"])
        };
    }

    private function index(): void
    {
        try {
            $categorias = $this->categoriaModel->readAll();

            http_response_code(200);
            echo json_encode($categorias, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    private function show(int $id): void
    {
        try {
            $categoria = $this->categoriaModel->readById($id);

            if ($categoria === null) {
                http_response_code(404);
                echo json_encode(["error" => "Categoria não encontrada!"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                return;
            }

            http_response_code(200);
            echo json_encode($categoria, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    private function create(): void
    {
        $data = $this->readInput();

        if (empty($data["nome"])) {
            http_response_code(422);
            echo json_encode(["errors" => ["O campo 'nome' é obrigatório."]], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        try {
            $id = $this->categoriaModel->create($data["nome"]);
            $categoria = $this->categoriaModel->readById($id);

            http_response_code(201);
            echo json_encode($categoria, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    private function delete(int $id): void
    {
        try {
            $categoria = $this->categoriaModel->readById($id);

            if ($categoria === null) {
                http_response_code(404);
                echo json_encode(["error" => "Categoria não encontrada!"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                return;
            }

            $this->categoriaModel->delete($id);
            http_response_code(204);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    private function readInput(): array
    {
        $body = file_get_contents("php://input");
        $data = json_decode($body, true);

        return is_array($data) ? $data : [];
    }

    private function methodNotAllowed(array $allowed): void
    {
        header("Allow: " . implode(", ", $allowed));
        http_response_code(405);
        echo json_encode(["error" => "Método não permitido"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}