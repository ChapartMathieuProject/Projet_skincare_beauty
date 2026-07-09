<?php

class BrandDAO extends DAO {
    protected string $table     = "brands";
    protected string $primaryKey = "brand_id";

    protected function hydrate(array $row): object {
        return new Brand($row);
    }

    #[Override]
    protected function dehydrate(object $entite): array
    {
        return [
            "brand_name"    => $entite->getName(),
            "producer_id"   => $entite->getProducerId(),
        ];
    }

    public function findAllKeyedById(): array {
        $stmt = $this->pdo->query("SELECT * FROM brands ORDER BY brand_name");
        $brands = [];
        foreach ($stmt->fetchAll() as $row) {
            $brands[(int) $row["brand_id"]] = new Brand($row);
        }
        return $brands;
    }
}

?>