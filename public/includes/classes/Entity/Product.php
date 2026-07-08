<?php

class Product
{
    private int    $id;
    private string $name;
    private string $ean;
    private string $composition;
    private string $description;
    private bool   $isStatus;
    private float  $buyPrice;
    private int    $margin;
    private int    $quantity;
    private ?int   $alert;  
    private string $slug;
    private int    $producerId;
    private int    $brandId;
    private int    $companyId;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data): void
    {
        if (isset($data['product_id']))          $this->id          = (int) $data['product_id'];
        if (isset($data['product_name']))        $this->name        = $data['product_name'];
        if (isset($data['product_ean']))         $this->ean         = $data['product_ean'];
        if (isset($data['product_composition'])) $this->composition = $data['product_composition'];
        if (isset($data['product_description'])) $this->description = $data['product_description'];
        if (isset($data['product_is_status']))   $this->isStatus    = (bool)  $data['product_is_status'];
        if (isset($data['product_buy_price']))   $this->buyPrice    = (float) $data['product_buy_price'];
        if (isset($data['product_margin']))      $this->margin      = (int)   $data['product_margin'];
        if (isset($data['product_quantity']))    $this->quantity    = (int)   $data['product_quantity'];
        if (array_key_exists('product_alert', $data)) {
            $this->alert = $data['product_alert'] !== null ? (int) $data['product_alert'] : null;
        }
        if (isset($data['product_slug']))          $this->slug       = $data['product_slug'];
        if (isset($data['producer_id']))           $this->producerId = (int) $data['producer_id'];
        if (isset($data['brand_id']))              $this->brandId    = (int) $data['brand_id'];
        if (isset($data['company_id_account']))    $this->companyId  = (int) $data['company_id_account'];
    }

    public function getId(): int             { return $this->id; }
    public function getName(): string        { return $this->name; }
    public function getEan(): string         { return $this->ean; }
    public function getComposition(): string { return $this->composition; }
    public function getDescription(): string { return $this->description; }
    public function isStatus(): bool         { return $this->isStatus; }
    public function getBuyPrice(): float     { return $this->buyPrice; }
    public function getMargin(): int         { return $this->margin; }
    public function getQuantity(): int       { return $this->quantity; }
    public function getAlert(): ?int         { return $this->alert; }
    public function getSlug(): string        { return $this->slug; }
    public function getProducerId(): int     { return $this->producerId; }
    public function getBrandId(): int        { return $this->brandId; }
    public function getCompanyId(): int      { return $this->companyId; }

    public function getSellPrice(): float
    {
        return round($this->buyPrice * (1 + $this->margin / 100), 2);
    }

    public function getSellPriceFormatted(): string
    {
        return number_format($this->getSellPrice(), 2, ',', ' ') . ' €';
    }
}

?>