<?php

//Penser à rajouter la clause WHERE

abstract class DAO {
    protected PDO       $pdo;
    protected string    $table;
    protected string    $primaryKey = "id";
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    abstract protected function hydrate(array $row): object;
    abstract protected function dehydrate(object $entite): array;


    // ── READ : un seul enregistrement ──
    public function find(int $id): ?object {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :pk"
        );
        $stmt->execute([":pk" => $id]);
        $row = $stmt->fetch();

        return ($row !== false) ? $this->hydrate($row) : null;
    }

    // ── READ : tous ──
    public function findAll(): array {
        $stmt = $this->pdo->query(
            "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC"
        );
        $resultats = [];
        foreach ($stmt->fetchAll() as $row) {
            $resultats[] = $this->hydrate($row);
        }
        return $resultats;
    }

        // ── CREATE ──
        public function create(object $entite): int {
            $donnees        = $this->dehydrate($entite);
            $colonnes       = implode(", ", array_keys($donnees));
            $placeholders   = ":" . implode(", :", array_keys($donnees));

            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->table} ($colonnes) VALUES ($placeholders)"
            );

            $params = [];
            foreach ($donnees as $col => $val) {
                $params[":" . $col] = $val;
            }
            $stmt->execute($params);

            return (int) $this->pdo->lastInsertId();
        }

        // ── UPDATE ──
        public function update(int $id, object $entite): bool {
            $donnees = $this->dehydrate($entite);

            $affectations = [];
            foreach (array_keys($donnees) as $col) {
                $affectations [] = "$col = :$col";
            }

            $stmt = $this->pdo->prepare(
                "UPDATE {$this->table} SET "
                . implode(", ", $affectations)
                . " WHERE {$this->primaryKey} = :pk"
            );

            $params = [":pk" => $id];
            foreach ($donnees as $col => $val) {
                $params[":" . $col] = $val;
            }
            return $stmt->execute($params);
        }

        // ── DELETE ──
        public function delete(int $id): bool {
            $stmt = $this->pdo->prepare(
                "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :pk"
            );
            return $stmt->execute([":pk" => $id]);
        }

}

?>