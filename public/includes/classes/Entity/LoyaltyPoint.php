<?php


class LoyaltyPoint
{
    
    public const TYPE_EARN   = 'earn';
    public const TYPE_SPEND  = 'spend';
    public const TYPE_EXPIRE = 'expire';
    public const TYPE_ADJUST = 'adjust';

    private int     $id;
    private int     $customerId;
    private ?int    $orderId = null;
    private int     $amount;
    private string  $type;
    private string  $label;
    private ?string $expiresAt = null;
    private string  $createdAt;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data): void
    {
        if (isset($data['loyalty_point_id'])) {
            $this->id = (int) $data['loyalty_point_id'];
        }
        if (isset($data['customer_id_account'])) {
            $this->customerId = (int) $data['customer_id_account'];
        }
        if (array_key_exists('order_id', $data)) {
            $this->orderId = $data['order_id'] !== null ? (int) $data['order_id'] : null;
        }
        if (isset($data['loyalty_point_amount'])) {
            $this->amount = (int) $data['loyalty_point_amount'];
        }
        if (isset($data['loyalty_point_type'])) {
            $this->type = $data['loyalty_point_type'];
        }
        if (isset($data['loyalty_point_label'])) {
            $this->label = $data['loyalty_point_label'];
        }
        if (array_key_exists('loyalty_point_expires_at', $data)) {
            $this->expiresAt = $data['loyalty_point_expires_at'];
        }
        if (isset($data['loyalty_point_created_at'])) {
            $this->createdAt = $data['loyalty_point_created_at'];
        }
    }

    public function getId(): int          { return $this->id; }
    public function getCustomerId(): int  { return $this->customerId; }
    public function getOrderId(): ?int    { return $this->orderId; }
    public function getAmount(): int      { return $this->amount; }
    public function getType(): string     { return $this->type; }
    public function getLabel(): string    { return $this->label; }
    public function getExpiresAt(): ?string { return $this->expiresAt; }
    public function getCreatedAt(): string  { return $this->createdAt; }

    
    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

  
    public function getFormattedAmount(): string
    {
        return ($this->amount > 0 ? '+' : '') . $this->amount;
    }
}