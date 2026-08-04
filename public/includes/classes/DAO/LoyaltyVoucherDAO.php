<?php

class LoyaltyVoucherDAO extends DAO
{
    protected string $table      = 'loyalty_vouchers';
    protected string $primaryKey = 'loyalty_voucher_id';

    protected function hydrate(array $row): object
    {
        return new LoyaltyVoucher($row);
    }

    protected function dehydrate(object $entite): array
    {
        return [
            'customer_id_account'         => $entite->getCustomerId(),
            'loyalty_voucher_code'        => $entite->getCode(),
            'loyalty_voucher_amount'      => $entite->getAmount(),
            'loyalty_voucher_points_used' => $entite->getPointsUsed(),
            'loyalty_voucher_expires_at'  => $entite->getExpiresAt(),
        ];
    }

    public function findByCustomer(int $customerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM loyalty_vouchers
             WHERE customer_id_account = :customerId
             ORDER BY loyalty_voucher_created_at DESC'
        );
        $stmt->execute([':customerId' => $customerId]);

        $vouchers = [];
        foreach ($stmt->fetchAll() as $row) {
            $vouchers[] = new LoyaltyVoucher($row);
        }

        return $vouchers;
    }

    public function findByCode(string $code): ?LoyaltyVoucher
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM loyalty_vouchers WHERE loyalty_voucher_code = :code LIMIT 1'
        );
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch();

        return $row ? new LoyaltyVoucher($row) : null;
    }

    public function markAsUsed(int $voucherId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE loyalty_vouchers
             SET loyalty_voucher_is_used = 1
             WHERE loyalty_voucher_id = :id'
        );

        return $stmt->execute([':id' => $voucherId]);
    }
}