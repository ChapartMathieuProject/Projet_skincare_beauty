<?php

class LoyaltyVoucher
{
    private int $id;
    private int $customerId;
    private string $code;
    private float $amount;
    private int $pointsUsed;
    private bool $isUsed = false;
    private string $expiresAt;
    private string $createdAt;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data): void
    {
        if (isset($data['loyalty_voucher_id'])) {
            $this->id = (int) $data['loyalty_voucher_id'];
        }
        if (isset($data['customer_id_account'])) {
            $this->customerId = (int) $data['customer_id_account'];
        }
        if (isset($data['loyalty_voucher_code'])) {
            $this->code = $data['loyalty_voucher_code'];
        }
        if (isset($data['loyalty_voucher_amount'])) {
            $this->amount = (float) $data['loyalty_voucher_amount'];
        }
        if (isset($data['loyalty_voucher_points_used'])) {
            $this->pointsUsed = (int) $data['loyalty_voucher_points_used'];
        }
        if (isset($data['loyalty_voucher_is_used'])) {
            $this->isUsed = (bool) $data['loyalty_voucher_is_used'];
        }
        if (isset($data['loyalty_voucher_expires_at'])) {
            $this->expiresAt = $data['loyalty_voucher_expires_at'];
        }
        if (isset($data['loyalty_voucher_created_at'])) {
            $this->createdAt = $data['loyalty_voucher_created_at'];
        }
    }

    public function getId(): int { return $this->id; }
    public function getCustomerId(): int { return $this->customerId; }
    public function getCode(): string { return $this->code; }
    public function getAmount(): float { return $this->amount; }
    public function getPointsUsed(): int { return $this->pointsUsed; }
    public function isUsed(): bool { return $this->isUsed; }
    public function getExpiresAt(): string { return $this->expiresAt; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function isExpired(): bool
    {
        return strtotime($this->expiresAt) < strtotime(date('Y-m-d'));
    }

    public function isUsable(): bool
    {
        return !$this->isUsed && !$this->isExpired();
    }
}