<?php

class ProductTypeDAO extends DAO
{
    protected string $table      = 'product_types';
    protected string $primaryKey = 'product_type_id';

    protected function hydrate(array $row): object
    {
        return new ProductType($row);
    }

    protected function dehydrate(object $entite): array
    {
        // product_type_slug exclu : généré par le trigger before_insert_product_types
        return [
            'product_type_name' => $entite->getName(),
        ];
    }

    public function findAllKeyedById(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM product_types ORDER BY product_type_name");
        $types = [];
        foreach ($stmt->fetchAll() as $row) {
            $types[(int) $row['product_type_id']] = new ProductType($row);
        }
        return $types;
    }
}