<?php

class LoyaltyService
{  

    public const POINTS_PER_EURO = 1;
    
    public const POINTS_PER_VOUCHER = 100;
    public const VOUCHER_VALUE = 5.00;
    public const POINTS_LIFETIME_MONTHS = 12;
 
    private LoyaltyPointDAO $loyaltyPointDAO;
    private LoyaltyTierDAO $loyaltyTierDAO;
    private MailerInterface $mailer;

    public function __construct(
        LoyaltyPointDAO $loyaltyPointDAO,
        LoyaltyTierDAO $loyaltyTierDAO,
        MailerInterface $mailer
    ) {
        $this->loyaltyPointDAO = $loyaltyPointDAO;
        $this->loyaltyTierDAO  = $loyaltyTierDAO;
        $this->mailer          = $mailer;
    }

    public function addPointsForOrder(
        int $customerId,
        int $orderId,
        float $orderTotal,
        ?string $customerEmail = null
    ): int {
        if ($orderTotal < 0) {
            throw new InvalidArgumentException('Le montant de la commande ne peut pas etre negatif.');
        }

        if ($this->loyaltyPointDAO->hasPointsForOrder($orderId)) {
            return 0;
        }

        $pointsEarned = (int) floor($orderTotal) * self::POINTS_PER_EURO;

        if ($pointsEarned === 0) {
            return 0;
        }

        $tierBefore = $this->getTier($customerId);

        $expiresAt = (new DateTimeImmutable())
            ->modify('+' . self::POINTS_LIFETIME_MONTHS . ' months')
            ->format('Y-m-d');

        $mouvement = new LoyaltyPoint([
            'customer_id_account'      => $customerId,
            'order_id'                 => $orderId,
            'loyalty_point_amount'     => $pointsEarned,
            'loyalty_point_type'       => LoyaltyPoint::TYPE_EARN,
            'loyalty_point_label'      => 'Points gagnes sur la commande',
            'loyalty_point_expires_at' => $expiresAt,
        ]);

        $this->loyaltyPointDAO->create($mouvement);

        $tierAfter = $this->getTier($customerId);

        if ($this->hasChangedTier($tierBefore, $tierAfter) && $customerEmail !== null) {
            $this->notifyTierUpgrade($customerEmail, $tierAfter);
        }

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

    public function getTier(int $customerId): ?LoyaltyTier
    {
        $lifetimePoints = $this->loyaltyPointDAO->getLifetimeEarnedByCustomer($customerId);

        return $this->loyaltyTierDAO->findByPoints($lifetimePoints);
    }

    public function getPointsToNextTier(int $customerId): ?int
    {
        $lifetimePoints = $this->loyaltyPointDAO->getLifetimeEarnedByCustomer($customerId);
        $nextTier = $this->loyaltyTierDAO->findNextTier($lifetimePoints);

        if ($nextTier === null) {
            return null;
        }

        return $nextTier->getMinPoints() - $lifetimePoints;
    }

    public function getConvertibleValue(int $customerId): float
    {
        $balance = $this->getBalance($customerId);

        return floor($balance / self::POINTS_PER_VOUCHER) * self::VOUCHER_VALUE;
    }

    private function hasChangedTier(?LoyaltyTier $before, ?LoyaltyTier $after): bool
    {
        if ($after === null) {
            return false;
        }
        if ($before === null) {
            return true;
        }

        return $before->getId() !== $after->getId();
    }

    private function notifyTierUpgrade(string $customerEmail, LoyaltyTier $tier): bool
    {
        $subject = 'Felicitations, vous passez au palier ' . $tier->getName();

        $body = '<h1>Bienvenue au palier ' . htmlspecialchars($tier->getName()) . '</h1>'
              . '<p>Bonjour,</p>'
              . '<p>Grace a vos achats, vous venez de debloquer le palier '
              . '<strong>' . htmlspecialchars($tier->getName()) . '</strong>.</p>'
              . '<p>Vous beneficiez desormais de ' . htmlspecialchars($tier->getAdvantagesLabel()) . '.</p>'
              . '<p>Merci de votre fidelite,<br>L equipe SkinCareBeauty</p>';

        return $this->mailer->send($customerEmail, $subject, $body);
    }
}