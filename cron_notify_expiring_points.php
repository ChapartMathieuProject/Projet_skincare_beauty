<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script ne s\'execute qu\'en ligne de commande.');
}

require_once __DIR__ . '/public/includes/db.php';
require_once __DIR__ . '/public/includes/Mailer.php';

$loyaltyService = new LoyaltyService(
    new LoyaltyPointDAO($pdo),
    new LoyaltyTierDAO($pdo),
    new LoyaltyVoucherDAO($pdo),
    $mailer,
    $pdo
);

$sent = $loyaltyService->notifyExpiringPoints();

echo $sent . " notification(s) d'expiration de points envoyee(s).\n";
