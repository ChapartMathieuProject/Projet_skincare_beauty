<?php

class LoyaltyTierDAO extends DAO
{
    protected string $table      = 'loyalty_tiers';
    protected string $primaryKey = 'loyalty_tier_id';

    protected function hydrate(array $row): object
    {
        return new LoyaltyTier($row);
    }

    protected function dehydrate(object $entite): array
    {
        return [
            'loyalty_tier_name'             => $entite->getName(),
            'loyalty_tier_min_points'       => $entite->getMinPoints(),
            'loyalty_tier_discount_percent' => $entite->getDiscountPercent(),
            'loyalty_tier_is_free_shipping' => $entite->isFreeShipping() ? 1 : 0,
        ];
    }

    public function findByPoints(int $points): ?LoyaltyTier
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM loyalty_tiers
             WHERE loyalty_tier_min_points <= :points
             ORDER BY loyalty_tier_min_points DESC
             LIMIT 1'
        );
        $stmt->execute([':points' => $points]);
        $row = $stmt->fetch();

        return $row ? new LoyaltyTier($row) : null;
    }

    public function findNextTier(int $points): ?LoyaltyTier
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM loyalty_tiers
             WHERE loyalty_tier_min_points > :points
             ORDER BY loyalty_tier_min_points ASC
             LIMIT 1'
        );
        $stmt->execute([':points' => $points]);
        $row = $stmt->fetch();

        return $row ? new LoyaltyTier($row) : null;
    }
}