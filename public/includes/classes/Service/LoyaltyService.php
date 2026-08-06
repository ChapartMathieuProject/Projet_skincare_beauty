<?php

class LoyaltyService
{
    public const POINTS_PER_EURO = 1;
    public const POINTS_PER_VOUCHER = 100;
    public const VOUCHER_VALUE = 5.00;
    public const POINTS_LIFETIME_MONTHS = 12;
    public const VOUCHER_LIFETIME_MONTHS = 6;
    public const POINTS_EXPIRY_WARNING_DAYS = 3;

    private LoyaltyPointDAO $loyaltyPointDAO;
    private LoyaltyTierDAO $loyaltyTierDAO;
    private LoyaltyVoucherDAO $loyaltyVoucherDAO;
    private MailerInterface $mailer;
    private PDO $pdo;

    public function __construct(
        LoyaltyPointDAO $loyaltyPointDAO,
        LoyaltyTierDAO $loyaltyTierDAO,
        LoyaltyVoucherDAO $loyaltyVoucherDAO,
        MailerInterface $mailer,
        PDO $pdo
    ) {
        $this->loyaltyPointDAO   = $loyaltyPointDAO;
        $this->loyaltyTierDAO    = $loyaltyTierDAO;
        $this->loyaltyVoucherDAO = $loyaltyVoucherDAO;
        $this->mailer            = $mailer;
        $this->pdo               = $pdo;
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

    public function convertPointsToVoucher(int $customerId, int $pointsToConvert): LoyaltyVoucher
    {
        if ($pointsToConvert <= 0) {
            throw new InvalidArgumentException('Le nombre de points a convertir doit etre positif.');
        }

        if ($pointsToConvert % self::POINTS_PER_VOUCHER !== 0) {
            throw new InvalidArgumentException(
                'Les points doivent etre convertis par tranches de ' . self::POINTS_PER_VOUCHER . '.'
            );
        }

        $balance = $this->getBalance($customerId);

        if ($balance < $pointsToConvert) {
            throw new InvalidArgumentException(
                'Solde insuffisant : ' . $balance . ' points disponibles sur ' . $pointsToConvert . ' demandes.'
            );
        }

        $amount = ($pointsToConvert / self::POINTS_PER_VOUCHER) * self::VOUCHER_VALUE;

        $expiresAt = (new DateTimeImmutable())
            ->modify('+' . self::VOUCHER_LIFETIME_MONTHS . ' months')
            ->format('Y-m-d');

        $this->pdo->beginTransaction();

        try {
            $debit = new LoyaltyPoint([
                'customer_id_account'  => $customerId,
                'order_id'             => null,
                'loyalty_point_amount' => -$pointsToConvert,
                'loyalty_point_type'   => LoyaltyPoint::TYPE_SPEND,
                'loyalty_point_label'  => 'Conversion en bon de reduction',
            ]);
            $this->loyaltyPointDAO->create($debit);

            $voucher = new LoyaltyVoucher([
                'customer_id_account'         => $customerId,
                'loyalty_voucher_code'        => $this->generateVoucherCode(),
                'loyalty_voucher_amount'      => $amount,
                'loyalty_voucher_points_used' => $pointsToConvert,
                'loyalty_voucher_expires_at'  => $expiresAt,
            ]);
            $this->loyaltyVoucherDAO->create($voucher);

            $this->pdo->commit();

            return $voucher;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getBalance(int $customerId): int
    {
        return $this->loyaltyPointDAO->getBalanceByCustomer($customerId);
    }

    public function getHistory(int $customerId, int $limit = 50): array
    {
        return $this->loyaltyPointDAO->findByCustomer($customerId, $limit);
    }

    public function getVouchers(int $customerId): array
    {
        return $this->loyaltyVoucherDAO->findByCustomer($customerId);
    }

    public function getUsableVouchers(int $customerId): array
    {
        return $this->loyaltyVoucherDAO->findUsableByCustomer($customerId);
    }

    public function getUsableVoucherForCustomer(string $code, int $customerId): LoyaltyVoucher
    {
        $voucher = $this->loyaltyVoucherDAO->findByCode($code);

        if ($voucher === null
            || $voucher->getCustomerId() !== $customerId
            || !$voucher->isUsable()
        ) {
            throw new InvalidArgumentException(
                "Ce code n'est pas valide, a expire ou a deja ete utilise."
            );
        }

        return $voucher;
    }

    public function computeDiscount(LoyaltyVoucher $voucher, float $orderTotal): float
    {
        return min($voucher->getAmount(), max(0.0, $orderTotal));
    }

    public function computeTierDiscountAmount(?LoyaltyTier $tier, float $amount): float
    {
        if ($tier === null || $tier->getDiscountPercent() <= 0) {
            return 0.0;
        }

        return round(max(0.0, $amount) * $tier->getDiscountPercent() / 100, 2);
    }

    public function useVoucherOnOrder(LoyaltyVoucher $voucher, int $orderId, int $customerId): void
    {
        $used = $this->loyaltyVoucherDAO->markAsUsed($voucher->getId(), $orderId, $customerId);

        if (!$used) {
            throw new RuntimeException(
                "Le bon de reduction a deja ete utilise ou a expire entre-temps."
            );
        }
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

    public function notifyExpiringPoints(): int
    {
        $expiringGroups = $this->loyaltyPointDAO->findExpiringSoon(self::POINTS_EXPIRY_WARNING_DAYS);
        $sent = 0;

        foreach ($expiringGroups as $group) {
            $customerId = (int) $group['customer_id_account'];
            $expiresAt  = $group['expires_at'];
            $amount     = (int) $group['expiring_amount'];

            if (!$this->loyaltyPointDAO->claimExpiryNotification($customerId, $expiresAt)) {
                continue;
            }

            $contact = $this->getCustomerContact($customerId);

            if ($contact === null) {
                continue;
            }

            if ($this->sendExpiryWarning($contact['mail'], $contact['name'], $amount, $expiresAt)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function getCustomerContact(int $customerId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.user_mail, c.customer_firstname, c.customer_name
             FROM customers c
             JOIN users u ON u.user_id = c.user_id
             WHERE c.customer_id_account = :customerId'
        );
        $stmt->execute([':customerId' => $customerId]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return [
            'mail' => $row['user_mail'],
            'name' => trim($row['customer_firstname'] . ' ' . $row['customer_name']),
        ];
    }

    private function sendExpiryWarning(string $toMail, string $toName, int $amount, string $expiresAt): bool
    {
        $dateLabel = (new DateTimeImmutable($expiresAt))->format('d/m/Y');

        $subject = 'Vos points de fidelite expirent bientot';

        $body = '<h1>Vos points expirent le ' . htmlspecialchars($dateLabel) . '</h1>'
              . '<p>Bonjour ' . htmlspecialchars($toName) . ',</p>'
              . '<p><strong>' . $amount . ' points</strong> de votre solde de fidelite expireront le '
              . '<strong>' . htmlspecialchars($dateLabel) . '</strong>.</p>'
              . '<p>Utilisez-les avant cette date pour ne pas les perdre : convertissez-les en bon de '
              . 'reduction depuis votre espace fidelite.</p>'
              . '<p>Merci de votre fidelite,<br>L equipe SkinCareBeauty</p>';

        return $this->mailer->send($toMail, $subject, $body);
    }

    private function generateVoucherCode(): string
    {
        do {
            $code = 'FID-' . strtoupper(bin2hex(random_bytes(4)));
        } while ($this->loyaltyVoucherDAO->findByCode($code) !== null);

        return $code;
    }
}