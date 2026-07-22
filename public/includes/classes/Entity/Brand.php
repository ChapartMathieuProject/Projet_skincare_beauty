<?php

class Brand {
    private int $id;
    private string $name;
    private int $producerId;

    public function __construct(array $data = []) 
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data): void {
        if (isset($data['brand_id']))    $this->id         = (int) $data['brand_id'];
        if (isset($data['brand_name']))  $this->name       = $data['brand_name'];
        if (isset($data['producer_id'])) $this->producerId = (int) $data['producer_id'];
    }

    public function getId(): int            {return $this->id;}
    public function getName(): string       {return $this->name;}
    public function getProducerId(): int    {return $this->producerId;}
    
}

?>
