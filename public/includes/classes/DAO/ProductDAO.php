<?php

class ProductDAO extends DAO
{
    protected string $table      = 'products';
    protected string $primaryKey = 'product_id';

    protected function hydrate(array $row): object
    {
        return new Product($row);
    }

    protected function dehydrate(object $entite): array
    {
        return [
            'product_name'        => $entite->getName(),
            'product_ean'         => $entite->getEan(),
            'product_composition' => $entite->getComposition(),
            'product_description' => $entite->getDescription(),
            'product_is_status'   => $entite->isStatus() ? 1 : 0,
            'product_buy_price'   => $entite->getBuyPrice(),
            'product_margin'      => $entite->getMargin(),
            'product_quantity'    => $entite->getQuantity(),
            'product_alert'       => $entite->getAlert(),
            'producer_id'         => $entite->getProducerId(),
            'brand_id'            => $entite->getBrandId(),
            'company_id_account'  => $entite->getCompanyId(),
        ];
    }

    public function findBySlug(string $slug): ?Product
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE product_slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row !== false ? new Product($row) : null;
    }
    // --- Tous les produits actifs uniquement ---
    public function findAllActive(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM products WHERE product_is_status = 1 ORDER BY product_id DESC"
        );
        $produits = [];
        foreach ($stmt->fetchAll() as $row) {
            $produits[] = new Product($row);
        }
        return $produits;
    }

    // --- Produits actifs, triés par nom (pour le catalogue) ---
    public function findAllActiveOrderedByName(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM products WHERE product_is_status = 1 ORDER BY product_name"
        );
        $produits = [];
        foreach ($stmt->fetchAll() as $row) {
            $produits[] = new Product($row);
        }
        return $produits;
    }

    // --- Produits actifs correspondant à une liste d'ids ---
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $this->pdo->prepare(
            "SELECT * FROM products
             WHERE product_id IN ($placeholders) AND product_is_status = 1
             ORDER BY product_name"
        );
        $stmt->execute(array_values($ids));

        $produits = [];
        foreach ($stmt->fetchAll() as $row) {
            $produits[] = new Product($row);
        }
        return $produits;
    }

    // --- Les N derniers produits actifs (nouveautés) ---
    public function findNewest(int $limit = 8): array
    {
        // $limit est casté en int : pas d'injection possible
        $limit = max(1, $limit);
        $stmt = $this->pdo->query(
            "SELECT * FROM products WHERE product_is_status = 1
             ORDER BY product_id DESC LIMIT $limit"
        );
        $produits = [];
        foreach ($stmt->fetchAll() as $row) {
            $produits[] = new Product($row);
        }
        return $produits;
    }

    // --- Recherche de produits actifs par nom (sous-chaîne, insensible à la casse et aux accents via la collation) ---
    public function searchActiveByName(string $term, int $limit = 50): array
    {
        // On échappe les jokers LIKE pour que « 50% » cherche littéralement « 50% »
        $escaped = addcslashes($term, '\\%_');
        // $limit est casté en int : pas d'injection possible
        $limit = max(1, $limit);

        $stmt = $this->pdo->prepare(
            "SELECT * FROM products
             WHERE product_is_status = 1 AND product_name LIKE :term ESCAPE '\\\\'
             ORDER BY product_name
             LIMIT $limit"
        );
        $stmt->execute([':term' => '%' . $escaped . '%']);

        $produits = [];
        foreach ($stmt->fetchAll() as $row) {
            $produits[] = new Product($row);
        }
        return $produits;
    }

    /**
     * Supprime un produit jamais commandé, sinon le désactive (product_is_status = 0)
     * pour garder l'historique des commandes et factures intact.
     * Les chemins d'images renvoyés ne concernent que la suppression physique : l'appelant
     * efface les fichiers après validation de la transaction.
     *
     * @return array{status: 'deleted'|'deactivated'|'not_found', pictures: string[]}
     */
    public function deleteOrDeactivate(int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT product_id FROM products WHERE product_id = :id");
        $stmt->execute([':id' => $id]);
        if ($stmt->fetchColumn() === false) {
            return ['status' => 'not_found', 'pictures' => []];
        }

        if ($this->isOrdered($id)) {
            $this->deactivate($id);
            return ['status' => 'deactivated', 'pictures' => []];
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT picture_path FROM pictures WHERE product_id = :id");
            $stmt->execute([':id' => $id]);
            $pictures = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // promotions est supprimée en cascade ; les autres tables liées n'ont pas de cascade.
            foreach (['cart_items', 'pictures', 'lien_product_type', 'products'] as $table) {
                $this->pdo->prepare("DELETE FROM $table WHERE product_id = :id")->execute([':id' => $id]);
            }

            $this->pdo->commit();
            return ['status' => 'deleted', 'pictures' => $pictures];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // 23000 : contrainte FK, le produit a été commandé entre-temps → on le désactive.
            if ($e->getCode() === '23000') {
                $this->deactivate($id);
                return ['status' => 'deactivated', 'pictures' => []];
            }
            throw $e;
        }
    }

    private function isOrdered(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT EXISTS (SELECT 1 FROM contains WHERE product_id = :id_contains)
                 OR EXISTS (SELECT 1 FROM `do` WHERE product_id = :id_do)"
        );
        $stmt->execute([':id_contains' => $id, ':id_do' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    private function deactivate(int $id): void
    {
        $this->pdo->prepare("UPDATE products SET product_is_status = 0 WHERE product_id = :id")
            ->execute([':id' => $id]);
    }
}
