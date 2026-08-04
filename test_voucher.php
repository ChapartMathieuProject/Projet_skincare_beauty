<?php
require_once 'public/includes/db.php';
require_once 'public/includes/mailer.php';

$customerId = 4;

$service = new LoyaltyService(
    new LoyaltyPointDAO($pdo),
    new LoyaltyTierDAO($pdo),
    new LoyaltyVoucherDAO($pdo),
    $mailer,
    $pdo
);

function countRows(PDO $pdo, string $table, int $customerId): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE customer_id_account = :id");
    $stmt->execute([':id' => $customerId]);
    return (int) $stmt->fetchColumn();
}

function testConversion(LoyaltyService $service, PDO $pdo, int $customerId, int $points, string $attendu): void
{
    $pointsAvant   = countRows($pdo, 'loyalty_points', $customerId);
    $vouchersAvant = countRows($pdo, 'loyalty_vouchers', $customerId);

    echo "<hr><strong>Test : convertir $points points</strong><br>";
    echo "Attendu : $attendu<br>";

    try {
        $voucher = $service->convertPointsToVoucher($customerId, $points);
        echo "Resultat : bon cree " . $voucher->getCode() . " d une valeur de " . $voucher->getAmount() . " EUR<br>";
    } catch (InvalidArgumentException $e) {
        echo "Resultat : exception rejetee - " . $e->getMessage() . "<br>";
    }

    $pointsApres   = countRows($pdo, 'loyalty_points', $customerId);
    $vouchersApres = countRows($pdo, 'loyalty_vouchers', $customerId);

    echo "Lignes loyalty_points : $pointsAvant puis $pointsApres<br>";
    echo "Lignes loyalty_vouchers : $vouchersAvant puis $vouchersApres<br>";
}

echo "<h2>Solde initial : " . $service->getBalance($customerId) . " points</h2>";

testConversion($service, $pdo, $customerId, -100, 'exception : doit etre positif');
testConversion($service, $pdo, $customerId, 150,  'exception : tranches de 100');
testConversion($service, $pdo, $customerId, 500,  'exception : solde insuffisant');
testConversion($service, $pdo, $customerId, 100,  'succes : bon de 5 EUR');

echo "<hr><h2>Solde final : " . $service->getBalance($customerId) . " points</h2>"; 

