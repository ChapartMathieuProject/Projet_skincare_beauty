<?php

class TicketHistory
{
    private int    $id;
    private string $action;
    private string $createdAt;
    private int    $ticketId;
    private int    $userId;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data): void
    {
        if (isset($data['ticket_history_id']))         $this->id        = (int) $data['ticket_history_id'];
        if (isset($data['ticket_history_action']))     $this->action    = $data['ticket_history_action'];
        if (isset($data['ticket_history_created_at'])) $this->createdAt = $data['ticket_history_created_at'];
        if (isset($data['ticket_id']))                 $this->ticketId  = (int) $data['ticket_id'];
        if (isset($data['user_id']))                   $this->userId    = (int) $data['user_id'];
    }

    public function getId(): int           { return $this->id; }
    public function getAction(): string    { return $this->action; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getTicketId(): int     { return $this->ticketId; }
    public function getUserId(): int       { return $this->userId; }

    public function getCreatedAtFormatted(): string
    {
        return date('d/m/Y à H:i', strtotime($this->createdAt));
    }
}

?>