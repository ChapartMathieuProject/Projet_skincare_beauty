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
}
