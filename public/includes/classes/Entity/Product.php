<?php

class Product {
    private int $id;
    private string $name;
    private float $price;
    private int $brandId;
    private int $typeId;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data): void {
        if (isset($data["product_id"])) $this->id       = (int) $data["product_id"];
        if (isset($data["name"]))       $this->name     = $data["name"];
        if (isset($data["price"]))      $this->price    = (float) $data["price"];
        if (isset($data["brand_id"]))   $this->brandId  = (int) $data["brand_id"];
        if (isset($data["type_id"]))    $this->typeId   = (int) $data["tpe_id"];
    }
    public function getId(): int      {return $this->id;}
    public function getName(): string {return $this->name;}
    public function getPrice(): float {return $this->price;}
    public function getBrandId(): int {return $this->brandId;}
    public function getTypeId(): int  {return $this->typeId;}
    
}

?>