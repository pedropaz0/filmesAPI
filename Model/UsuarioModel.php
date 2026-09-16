<?php

namespace Model;

use Exception;
use Model\Connection;
use PDO;
use PDOException;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Usuario",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "email", type: "string", example: "admin@email.com")
    ]
)]
class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT id, email, senha FROM usuarios WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":email", $email, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao buscar usuário por e-mail");
        }
    }

    public function create(string $email, string $senha): int
    {
        try {
            $sql = "INSERT INTO usuarios (email, senha) VALUES (:email, :senha)";
            $stmt = $this->db->prepare($sql);

            // Criptografia segura com Argon2id
            $senhaHash = password_hash($senha, PASSWORD_ARGON2ID);

            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":senha", $senhaHash, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $this->db->lastInsertId();
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao cadastrar usuário");
        }
    }

    public function readById(int $id): ?array
    {
        try {
            $sql = "SELECT id, email FROM usuarios WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao ler informações do usuário");
        }
    }

    public function emailExists(string $email): bool
    {
        try {
            $sql = "SELECT id FROM usuarios WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":email", $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch() !== false;
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao verificar e-mail");
        }
    }
}