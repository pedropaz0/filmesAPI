<?php

namespace Controller;

use Model\FilmeModel;
use Exception;

class FilmeController
{
    public function __construct(private FilmeModel $filmeModel)
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
            $filmes = $this->filmeModel->readAll();

            http_response_code(200);
            echo json_encode($filmes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()]);
        }
    }

    private function show(int $id): void
    {
        try {
            $filme = $this->filmeModel->readById($id);

            if ($filme === null) {
                http_response_code(404);
                echo json_encode(["error" => "Filme não encontrado."]);
                return;
            }

            http_response_code(200);
            echo json_encode($filme, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()]);
        }
    }

    private function create(): void
    {
        $data = $this->readInput();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(["errors" => $errors]);
            return;
        }

        try {
            $id = $this->filmeModel->create(
                $data["titulo"],
                $data["descricao"] ?? "",
                (int)$data["id_categoria"]
            );

            $filme = $this->filmeModel->readById($id);

            http_response_code(201);
            echo json_encode($filme, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()]);
        }
    }

    private function delete(int $id): void
    {
        try {
            $filme = $this->filmeModel->readById($id);

            if ($filme === null) {
                http_response_code(404);
                echo json_encode(["error" => "Filme não encontrado."]);
                return;
            }

            $this->filmeModel->delete($id);

            http_response_code(204);
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()]);
        }
    }

    private function readInput(): array
    {
        $body = file_get_contents("php://input");
        $data = json_decode($body, true);

        return is_array($data) ? $data : [];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data["titulo"])) {
            $errors[] = "O campo 'titulo' é obrigatório.";
        }

        if (empty($data["id_categoria"])) {
            $errors[] = "O campo 'id_categoria' é obrigatório.";
        }

        return $errors;
    }

    private function methodNotAllowed(array $allowed): void
    {
        header("Allow: " . implode(", ", $allowed));
        http_response_code(405);
        echo json_encode(["error" => "Método não permitido"]);
    }
}