<?php

class TicketDAO extends DAO
{
    protected string $table      = 'tickets';
    protected string $primaryKey = 'ticket_id';

    protected function hydrate(array $row): object
    {
        return new Ticket($row);
    }

    protected function dehydrate(object $entite): array
    {
        return [
            'ticket_return_number' => $entite->getReturnNumber(),
            'ticket_comment'       => $entite->getComment(),
            'order_id'             => $entite->getOrderId(),
            'return_type_id'       => $entite->getReturnTypeId(),
            'ticket_status_id'     => $entite->getStatusId(),
            'user_id'              => $entite->getUserId(),
        ];
    }

    public function getNextSequence(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn() + 1;
    }

    public function generateReturnNumber(int $sequence, ?int $year = null): string
    {
        if ($sequence < 1 || $sequence > 9999) {
            throw new InvalidArgumentException(
                "Séquence de retour invalide : $sequence (attendu : 1 à 9999)."
            );
        }
        $year = $year ?? (int) date('Y');
        return sprintf('RET-%d-%04d', $year, $sequence);
    }

    public function updateStatus(int $ticketId, int $statusId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tickets SET ticket_status_id = :statut WHERE ticket_id = :pk"
        );
        return $stmt->execute([':statut' => $statusId, ':pk' => $ticketId]);
    }

    public function findByReturnNumber(string $returnNumber): ?Ticket
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM tickets WHERE ticket_return_number = :num"
        );
        $stmt->execute([':num' => $returnNumber]);
        $row = $stmt->fetch();
        return $row !== false ? new Ticket($row) : null;
    }
}