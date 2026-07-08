<?php

class ProductDAO extends DAO {
    protected string $table     = "products";
    protected string $primaryKey = "product_id";

    protected function hydrate(array $row): object {
        return new Product($row);
    }

    protected function dehydrate(object $entite): array {
        return [
            "name"      => $entite->getName(),
            "price"     => $entite->getPrice(),
            "brand_id"  => $entite->getBrandId(),
            "type_id"   => $entite->getTypeId(),
        ];
    }
}

?>