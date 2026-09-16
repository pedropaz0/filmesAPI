<?php

namespace Model;

use Exception;
use Model\Connection;
use PDO;
use PDOException;

class FilmeModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function readAll(): array
    {
        try {
            
            $sql = "SELECT filmes.id, filmes.titulo, filmes.descricao, categorias.nome AS categoria 
                    FROM filmes 
                    INNER JOIN categorias ON filmes.id_categoria = categorias.id 
                    ORDER BY filmes.id";

            $stmt = $this->db->query($sql);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao listar filmes");
        }
    }

    public function readById(int $id): ?array
    {
        try {
            //aqu ivai trazer o filme com o nome da categoria usando o join
            $sql = "SELECT filmes.id, filmes.titulo, filmes.descricao, categorias.nome AS categoria 
                    FROM filmes 
                    INNER JOIN categorias ON filmes.id_categoria = categorias.id 
                    WHERE filmes.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;

        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao ler informações do filme");
        }
    }

    public function create(string $titulo, string $descricao, int $idCategoria): int
    {
        try {
            $sql = "INSERT INTO filmes (titulo, descricao, id_categoria) VALUES (:titulo, :descricao, :id_categoria)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":titulo", $titulo, PDO::PARAM_STR);
            $stmt->bindParam(":descricao", $descricao, PDO::PARAM_STR);
            $stmt->bindParam(":id_categoria", $idCategoria, PDO::PARAM_INT);

            $stmt->execute();

            return (int) $this->db->lastInsertId();

        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao cadastrar filme");
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM filmes WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (PDOException $error) {
            error_log($error->getMessage());
            throw new Exception("Erro ao excluir filme");
        }
    }
}