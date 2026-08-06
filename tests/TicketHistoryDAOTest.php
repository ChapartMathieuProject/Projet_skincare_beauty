<?php

use PHPUnit\Framework\TestCase;

class TicketHistoryDAOTest extends TestCase
{
    private PDO $pdo;
    private PDOStatement $stmt;
    private TicketHistoryDAO $dao;

    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->dao  = new TicketHistoryDAO($this->pdo);
    }

    public function testFindByTicketIdRetourneLesLignesHydratees(): void
    {
        $lignes = [
            [
                'ticket_history_id'         => 1,
                'ticket_history_action'     => 'Demande de retour créée par Sophie Martin',
                'ticket_history_created_at' => '2026-08-05 09:00:00',
                'ticket_id'                 => 12,
                'user_id'                   => 2,
            ],
            [
                'ticket_history_id'         => 2,
                'ticket_history_action'     => 'Demande de retour validée par Agent SAV',
                'ticket_history_created_at' => '2026-08-05 10:30:00',
                'ticket_id'                 => 12,
                'user_id'                   => 6,
            ],
        ];

        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetchAll')->willReturn($lignes);

        $historique = $this->dao->findByTicketId(12);

        $this->assertCount(2, $historique);
        $this->assertInstanceOf(TicketHistory::class, $historique[0]);
        $this->assertSame('Demande de retour créée par Sophie Martin', $historique[0]->getAction());
        $this->assertSame(12, $historique[0]->getTicketId());
        $this->assertSame(6, $historique[1]->getUserId());
    }

    public function testFindByTicketIdRetourneUnTableauVideSiAucuneLigne(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetchAll')->willReturn([]);

        $this->assertSame([], $this->dao->findByTicketId(999));
    }

    public function testLogEnregistreUneLigneEtRetourneSonId(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->pdo->method('lastInsertId')->willReturn('7');

        $id = $this->dao->log(12, 6, 'Demande de retour validée par Agent SAV');

        $this->assertSame(7, $id);
    }

    public function testUpdateActionExecuteLaRequeteAvecLesBonsParametres(): void
    {
        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->willReturn($this->stmt);
        $this->stmt->expects($this->once())
                   ->method('execute')
                   ->with([':action' => 'Libellé corrigé', ':pk' => 3])
                   ->willReturn(true);

        $this->assertTrue($this->dao->updateAction(3, 'Libellé corrigé'));
    }

    public function testDeleteSupprimeLaLigneParSaClePrimaire(): void
    {
        $this->pdo->expects($this->once())
                  ->method('prepare')
                  ->willReturn($this->stmt);
        $this->stmt->expects($this->once())
                   ->method('execute')
                   ->with([':pk' => 3])
                   ->willReturn(true);

        $this->assertTrue($this->dao->delete(3));
    }
}