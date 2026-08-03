<?php

class LoyaltyService
{
    public const POINTS_PER_EURO = 1;

    public const POINTS_PER_VOUCHER = 100;
    public const VOUCHER_VALUE      = 5.00;

    public const POINTS_LIFETIME_MONTHS = 12;

    private const TIERS = [
        ['name' => 'Or',     'min' => 1500],
        ['name' => 'Argent', 'min' => 500],
        ['name' => 'Bronze', 'min' => 0],
    ];

    private LoyaltyPointDAO $loyaltyPointDAO;

    public function __construct(LoyaltyPointDAO $loyaltyPointDAO)
    {
        $this->loyaltyPointDAO = $loyaltyPointDAO;
    }

    public function addPointsForOrder(int $customerId, int $orderId, float $orderTotal): int
    {
        if ($orderTotal < 0) {
            throw new InvalidArgumentException(
                'Le montant de la commande ne peut pas être négatif.'
            );
        }

        if ($this->loyaltyPointDAO->hasPointsForOrder($orderId)) {
            return 0;
        }

        $pointsEarned = (int) floor($orderTotal) * self::POINTS_PER_EURO;

        if ($pointsEarned === 0) {
            return 0;
        }

        $expiresAt = (new DateTimeImmutable())
            ->modify('+' . self::POINTS_LIFETIME_MONTHS . ' months')
            ->format('Y-m-d');

        $mouvement = new LoyaltyPoint([
            'customer_id_account'      => $customerId,
            'order_id'                 => $orderId,
            'loyalty_point_amount'     => $pointsEarned,
            'loyalty_point_type'       => LoyaltyPoint::TYPE_EARN,
            'loyalty_point_label'      => 'Points gagnés sur la commande',
            'loyalty_point_expires_at' => $expiresAt,
        ]);

        $this->loyaltyPointDAO->create($mouvement);

        return $pointsEarned;
    }

    public function getBalance(int $customerId): int
    {
        return $this->loyaltyPointDAO->getBalanceByCustomer($customerId);
    }

    public function getHistory(int $customerId, int $limit = 50): array
    {
        return $this->loyaltyPointDAO->findByCustomer($customerId, $limit);
    }

    public function getTierName(int $customerId): string
    {
        $lifetimePoints = $this->loyaltyPointDAO->getLifetimeEarnedByCustomer($customerId);

        foreach (self::TIERS as $tier) {
            if ($lifetimePoints >= $tier['min']) {
                return $tier['name'];
            }
        }

        return 'Bronze';
    }

    public function getPointsToNextTier(int $customerId): ?int
    {
        $lifetimePoints = $this->loyaltyPointDAO->getLifetimeEarnedByCustomer($customerId);

        foreach (array_reverse(self::TIERS) as $tier) {
            if ($lifetimePoints < $tier['min']) {
                return $tier['min'] - $lifetimePoints;
            }
        }

        return null;
    }

    public function getConvertibleValue(int $customerId): float
    {
        $balance = $this->getBalance($customerId);

        return floor($balance / self::POINTS_PER_VOUCHER) * self::VOUCHER_VALUE;
    }
}