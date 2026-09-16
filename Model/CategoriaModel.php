<?php

namespace Model;

use Exception;
use Model\Connection;
use PDO;
use PDOException;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Categoria",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nome", type: "string", example: "Ação")
    ]
)]
class CategoriaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function readAll(): array
    {
        try {
            $sql = "SELECT id, nome FROM categorias ORDER BY id";
            $stmt = $this->db->query($sql);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao listar categorias");
        }
    }

    public function readById(int $id): ?array
    {
        try {
            $sql = "SELECT id, nome FROM categorias WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao ler categoria");
        }
    }

    public function create(string $nome): int
    {
        try {
            $sql = "INSERT INTO categorias (nome) VALUES (:nome)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $this->db->lastInsertId();
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao cadastrar categoria");
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM categorias WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao excluir categoria");
        }
    }
}