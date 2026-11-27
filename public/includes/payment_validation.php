<?php

/**
 * Indique si une date d'expiration de carte « MM/AA » n'est pas encore passée.
 * Une carte reste valable jusqu'à la fin de son mois d'expiration : le mois en cours est accepté.
 * L'horloge est injectée ($today) pour pouvoir tester la règle à une date fixe.
 * Renvoie false si le format n'est pas MM/AA.
 */
function is_card_expiry_valid(string $mmYy, DateTimeImmutable $today): bool
{
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $mmYy, $matches)) {
        return false;
    }

    $expiryMonth = (int) $matches[1];
    $expiryYear  = 2000 + (int) $matches[2];
    $currentYear  = (int) $today->format('Y');
    $currentMonth = (int) $today->format('n');

    if ($expiryYear !== $currentYear) {
        return $expiryYear > $currentYear;
    }
    return $expiryMonth >= $currentMonth;
}
