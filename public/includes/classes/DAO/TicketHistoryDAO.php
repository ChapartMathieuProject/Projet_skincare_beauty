<?php

class TicketHistoryDAO extends DAO
{
    protected string $table      = 'ticket_history';
    protected string $primaryKey = 'ticket_history_id';

    protected function hydrate(array $row): object
    {
        return new TicketHistory($row);
    }

    protected function dehydrate(object $entite): array
    {
        return [
            'ticket_history_action' => $entite->getAction(),
            'ticket_id'             => $entite->getTicketId(),
            'user_id'               => $entite->getUserId(),
        ];
    }

    public function findByTicketId(int $ticketId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM ticket_history
             WHERE ticket_id = :tid
             ORDER BY ticket_history_created_at ASC, ticket_history_id ASC"
        );
        $stmt->execute([':tid' => $ticketId]);

        $lignes = [];
        foreach ($stmt->fetchAll() as $row) {
            $lignes[] = new TicketHistory($row);
        }
        return $lignes;
    }

    public function log(int $ticketId, int $userId, string $action): int
    {
        return $this->create(new TicketHistory([
            'ticket_history_action' => $action,
            'ticket_id'             => $ticketId,
            'user_id'               => $userId,
        ]));
    }
}