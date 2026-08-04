<?php

class LoyaltyPointDAO extends DAO
{
    protected string $table      = 'loyalty_points';
    protected string $primaryKey = 'loyalty_point_id';

    protected function hydrate(array $row): object
    {
        return new LoyaltyPoint($row);
    }

    protected function dehydrate(object $entite): array
    {
        return [
            'customer_id_account'      => $entite->getCustomerId(),
            'order_id'                 => $entite->getOrderId(),
            'loyalty_point_amount'     => $entite->getAmount(),
            'loyalty_point_type'       => $entite->getType(),
            'loyalty_point_label'      => $entite->getLabel(),
            'loyalty_point_expires_at' => $entite->getExpiresAt(),
        ];
    }

    public function getBalanceByCustomer(int $customerId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(loyalty_point_amount), 0)
             FROM loyalty_points
             WHERE customer_id_account = :customerId'
        );
        $stmt->execute([':customerId' => $customerId]);

        return (int) $stmt->fetchColumn();
    }

    public function getLifetimeEarnedByCustomer(int $customerId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(loyalty_point_amount), 0)
             FROM loyalty_points
             WHERE customer_id_account = :customerId
               AND loyalty_point_amount > 0'
        );
        $stmt->execute([':customerId' => $customerId]);

        return (int) $stmt->fetchColumn();
    }

    public function findByCustomer(int $customerId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        $stmt = $this->pdo->prepare(
            'SELECT * FROM loyalty_points
             WHERE customer_id_account = :customerId
             ORDER BY loyalty_point_created_at DESC, loyalty_point_id DESC
             LIMIT ' . $limit
        );
        $stmt->execute([':customerId' => $customerId]);

        $mouvements = [];
        foreach ($stmt->fetchAll() as $row) {
            $mouvements[] = new LoyaltyPoint($row);
        }

        return $mouvements;
    }

    public function hasPointsForOrder(int $orderId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM loyalty_points
             WHERE order_id = :orderId
               AND loyalty_point_type = :type'
        );
        $stmt->execute([
            ':orderId' => $orderId,
            ':type'    => LoyaltyPoint::TYPE_EARN,
        ]);

        return $stmt->fetchColumn() > 0;
    }
}