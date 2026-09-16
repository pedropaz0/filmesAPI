<?php

namespace Controller;

use Model\UsuarioModel;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: "/login",
    summary: "Realiza login do usuário",
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", example: "admin@email.com"),
                new OA\Property(property: "senha", type: "string", example: "123456")
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "Login efetuado com sucesso"),
        new OA\Response(response: 401, description: "Credenciais inválidas")
    ]
)]
class UsuarioController
{
    public function __construct(private UsuarioModel $usuarioModel)
    {
    }

    public function login(): void
    {
        header("Content-Type: application/json; charset=UTF-8");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Allow: POST");
            http_response_code(405);
            echo json_encode(["error" => "Método não permitido. Use POST para login."], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        $data = $this->readInput();

        if (empty($data["email"]) || empty($data["senha"])) {
            http_response_code(422);
            echo json_encode(["error" => "E-mail e senha são obrigatórios."], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        try {
            $usuario = $this->usuarioModel->findByEmail($data["email"]);

            if (!$usuario || !password_verify($data["senha"], $usuario["senha"])) {
                http_response_code(401);
                echo json_encode(["error" => "E-mail ou senha inválidos."], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                return;
            }

            http_response_code(200);
            echo json_encode([
                "message" => "Login realizado com sucesso!",
                "usuario" => [
                    "id" => $usuario["id"],
                    "email" => $usuario["email"]
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    public function ProcessRequest(string $method, ?string $id): void
    {
        header("Content-Type: application/json; charset=UTF-8");

        if ($id === null) {
            match ($method) {
                "POST" => $this->create(),
                default => $this->methodNotAllowed(["POST"])
            };
            return;
        }

        match ($method) {
            "GET" => $this->show((int) $id),
            default => $this->methodNotAllowed(["GET"])
        };
    }

    private function create(): void
    {
        $data = $this->readInput();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(["errors" => $errors], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        try {
            if ($this->usuarioModel->emailExists($data["email"])) {
                http_response_code(409);
                echo json_encode(["error" => "Já existe um usuário com esse e-mail."], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                return;
            }

            $id = $this->usuarioModel->create($data["email"], $data["senha"]);
            $usuario = $this->usuarioModel->readById($id);

            http_response_code(201);
            echo json_encode([
                "message" => "Usuário criado com sucesso!",
                "usuario" => $usuario
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(["error" => $error->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    private function show(int $id): void
    {
        try {
            $usuario = $this->usuarioModel->readById($id);

            if ($usuario === null) {
                http_response_code(404);
                echo json_encode(["error" => "Usuário não encontrado!"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                return;
            }

            http_response_code(200);
            echo json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data["email"])) {
            $errors[] = "O campo 'email' é obrigatório.";
        } elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "O campo 'email' precisa ser um e-mail válido.";
        }

        if (empty($data["senha"])) {
            $errors[] = "O campo 'senha' é obrigatório.";
        } elseif (strlen($data["senha"]) < 6) {
            $errors[] = "A 'senha' precisa ter no mínimo 6 caracteres.";
        }

        return $errors;
    }

    private function methodNotAllowed(array $allowed): void
    {
        header("Allow: " . implode(", ", $allowed));
        http_response_code(405);
        echo json_encode(["error" => "Método não permitido"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}