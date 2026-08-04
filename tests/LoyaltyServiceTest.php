<?php

use PHPUnit\Framework\TestCase;

class LoyaltyServiceTest extends TestCase
{
    private $pointDAO;
    private $tierDAO;
    private $voucherDAO;
    private $mailer;
    private $pdo;
    private LoyaltyService $service;

    protected function setUp(): void
    {
        $this->pointDAO   = $this->createMock(LoyaltyPointDAO::class);
        $this->tierDAO    = $this->createMock(LoyaltyTierDAO::class);
        $this->voucherDAO = $this->createMock(LoyaltyVoucherDAO::class);
        $this->mailer     = $this->createMock(MailerInterface::class);
        $this->pdo        = $this->createMock(PDO::class);

        $this->service = new LoyaltyService(
            $this->pointDAO,
            $this->tierDAO,
            $this->voucherDAO,
            $this->mailer,
            $this->pdo
        );
    }

    private function makeTier(int $id, string $name, int $minPoints): LoyaltyTier
    {
        return new LoyaltyTier([
            'loyalty_tier_id'               => $id,
            'loyalty_tier_name'             => $name,
            'loyalty_tier_min_points'       => $minPoints,
            'loyalty_tier_discount_percent' => 0,
            'loyalty_tier_is_free_shipping' => 0,
        ]);
    }

    public function testAddPointsForOrderCrediteUnPointParEuro(): void
    {
        $bronze = $this->makeTier(1, 'Bronze', 0);

        $this->pointDAO->method('hasPointsForOrder')->willReturn(false);
        $this->pointDAO->method('getLifetimeEarnedByCustomer')->willReturn(0);
        $this->tierDAO->method('findByPoints')->willReturn($bronze);

        $this->pointDAO->expects($this->once())->method('create');

        $points = $this->service->addPointsForOrder(4, 10, 49.90);

        $this->assertSame(49, $points);
    }

    public function testAddPointsForOrderRejetteUnMontantNegatif(): void
    {
        $this->pointDAO->expects($this->never())->method('create');

        $this->expectException(InvalidArgumentException::class);

        $this->service->addPointsForOrder(4, 10, -50.00);
    }

    public function testAddPointsForOrderEnvoieUnMailAuChangementDePalier(): void
    {
        $bronze = $this->makeTier(1, 'Bronze', 0);
        $argent = $this->makeTier(2, 'Argent', 500);

        $this->pointDAO->method('hasPointsForOrder')->willReturn(false);
        $this->pointDAO->method('getLifetimeEarnedByCustomer')
            ->willReturnOnConsecutiveCalls(450, 550);
        $this->tierDAO->method('findByPoints')
            ->willReturnOnConsecutiveCalls($bronze, $argent);

        $this->mailer->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo('client@example.com'),
                $this->stringContains('Argent'),
                $this->anything()
            )
            ->willReturn(true);

        $this->service->addPointsForOrder(4, 10, 100.00, 'client@example.com');
    }
}