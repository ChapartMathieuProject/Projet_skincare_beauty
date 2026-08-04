<?php

class LoyaltyTier
{
    private int $id;
    private string $name;
    private int $minPoints;
    private int $discountPercent;
    private bool $isFreeShipping;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data): void
    {
        if (isset($data['loyalty_tier_id'])) {
            $this->id = (int) $data['loyalty_tier_id'];
        }
        if (isset($data['loyalty_tier_name'])) {
            $this->name = $data['loyalty_tier_name'];
        }
        if (isset($data['loyalty_tier_min_points'])) {
            $this->minPoints = (int) $data['loyalty_tier_min_points'];
        }
        if (isset($data['loyalty_tier_discount_percent'])) {
            $this->discountPercent = (int) $data['loyalty_tier_discount_percent'];
        }
        if (isset($data['loyalty_tier_is_free_shipping'])) {
            $this->isFreeShipping = (bool) $data['loyalty_tier_is_free_shipping'];
        }
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getMinPoints(): int { return $this->minPoints; }
    public function getDiscountPercent(): int { return $this->discountPercent; }
    public function isFreeShipping(): bool { return $this->isFreeShipping; }

    public function getAdvantagesLabel(): string
    {
        $advantages = [];

        if ($this->discountPercent > 0) {
            $advantages[] = $this->discountPercent . ' % de remise sur toutes vos commandes';
        }
        if ($this->isFreeShipping) {
            $advantages[] = 'la livraison offerte';
        }

        return empty($advantages) ? 'l acces au programme de fidelite' : implode(' et ', $advantages);
    }
}