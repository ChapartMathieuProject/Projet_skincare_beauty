<?php

use PHPUnit\Framework\TestCase;

class TicketDAOTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $stmt;
    private TicketDAO $dao;

    #[Override]
    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->dao  = new TicketDAO($this->pdo);
    }

    public function testGenerateReturnNumberFormatValide(): void
    {
        $numero = $this->dao->generateReturnNumber(7, 2026);

        $this->assertSame("RET-2026-0007", $numero);
    }

    public function testGenerateReturnNumberCompletAvecDesZeros(): void
    {
        $this->assertSame("RET-2026-0001", $this->dao->generateReturnNumber(1, 2026));
        $this->assertSame("RET-2026-9999", $this->dao->generateReturnNumber(9999, 2026));
    }

    public function testGenerateReturnNumberRefuseSequenceNulle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dao->generateReturnNumber(0, 2026);
    }

    public function testGenerateReturnNumberRefuseSequenceTropGrande(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dao->generateReturnNumber(10000, 2026);
    }

    public function testFindByReturnNumberRetourneUnTicket(): void
    {
        $ligne = [
            "ticket_id"             => 12,
            "ticket_return_number"  => "RET-2026-0012",
            "ticket_comment"        => "Colis revenu NPAI",
            "ticket_created_at"     => "2026-08-04 10:30:00",
            "order_id"              => 2,
            "return_type_id"        => 1,
            "ticket_status_id"      => 2,
            "user_id"               => 1,
        ];

        $this->pdo->method("prepare")->willReturn($this->stmt);
        $this->stmt->method("execute")->willReturn(true);
        $this->stmt->method("fetch")->willReturn($ligne);

        $ticket = $this->dao->findByReturnNumber("RET-2026-0012");

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertSame("RET-2026-0012", $ticket->getReturnNumber());
        $this->assertSame("Colis revenu NPAI", $ticket->getComment());
        $this->assertSame(2, $ticket->getOrderId());
        $this->assertSame(Ticket::STATUS_EN_COURS, $ticket->getStatusId());
        $this->assertFalse($ticket->isCloture());
    }

    public function testFindByReturnNumberRetourneNullSiIntrouvable(): void
    {
        $this->pdo->method("prepare")->willReturn($this->stmt);
        $this->stmt->method("execute")->willReturn(true);
        $this->stmt->method("fetch")->willReturn(false);

        $ticket = $this->dao->findByReturnNumber("RET-9999-0000");

        $this->assertNull($ticket);
    }

    public function testUpdateStatusExecuteLaRequetePreparee(): void
    {
        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->willReturn($this->stmt);
        $this->stmt->expects($this->once())
                   ->method('execute')
                   ->with([':statut' => Ticket::STATUS_CLOTURE, ':pk' => 12])
                   ->willReturn(true);

        $resultat = $this->dao->updateStatus(12, Ticket::STATUS_CLOTURE);

        $this->assertTrue($resultat);
    }
}
