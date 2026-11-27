<?php

use PHPUnit\Framework\TestCase;

class PaymentValidationTest extends TestCase
{
    private DateTimeImmutable $today;

    protected function setUp(): void
    {
        // Horloge figée : 27 novembre 2026
        $this->today = new DateTimeImmutable('2026-11-27 12:00:00', new DateTimeZone('Europe/Paris'));
    }

    public function testMoisEnCoursAccepte(): void
    {
        $this->assertTrue(is_card_expiry_valid('11/26', $this->today), 'La carte reste valable jusqu\'à la fin du mois');
    }

    public function testMoisPasseDeLAnneeEnCoursRefuse(): void
    {
        $this->assertFalse(is_card_expiry_valid('10/26', $this->today));
    }

    public function testAnneeSuivanteAcceptee(): void
    {
        $this->assertTrue(is_card_expiry_valid('01/27', $this->today));
    }

    public function testAnneePasseeRefusee(): void
    {
        $this->assertFalse(is_card_expiry_valid('12/25', $this->today));
    }

    public function testDernierJourDuMoisAccepteEtPremierJourDuMoisSuivantRefuse(): void
    {
        $lastDay  = new DateTimeImmutable('2026-11-30 23:59:59', new DateTimeZone('Europe/Paris'));
        $nextDay  = new DateTimeImmutable('2026-12-01 00:00:00', new DateTimeZone('Europe/Paris'));

        $this->assertTrue(is_card_expiry_valid('11/26', $lastDay));
        $this->assertFalse(is_card_expiry_valid('11/26', $nextDay));
    }

    public function testFormatInvalideRefuse(): void
    {
        $this->assertFalse(is_card_expiry_valid('13/27', $this->today));
        $this->assertFalse(is_card_expiry_valid('1/27', $this->today));
        $this->assertFalse(is_card_expiry_valid('', $this->today));
    }
}
