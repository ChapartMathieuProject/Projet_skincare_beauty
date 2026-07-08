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
}
