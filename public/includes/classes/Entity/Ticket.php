<?php

class Ticket
{
    public const STATUS_OUVERT   = 1;
    public const STATUS_EN_COURS = 2;
    public const STATUS_CLOTURE  = 3;
    public const STATUS_REFUSE   = 4;

    private int    $id;
    private string $returnNumber;
    private string $comment;
    private string $createdAt;
    private int    $orderId;
    private int    $returnTypeId;
    private int    $statusId;
    private int    $userId;

    public function __construct(array $data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data): void
    {
        if (isset($data['ticket_id']))            $this->id           = (int) $data['ticket_id'];
        if (isset($data['ticket_return_number'])) $this->returnNumber = $data['ticket_return_number'];
        if (isset($data['ticket_comment']))       $this->comment      = $data['ticket_comment'];
        if (isset($data['ticket_created_at']))    $this->createdAt    = $data['ticket_created_at'];
        if (isset($data['order_id']))             $this->orderId      = (int) $data['order_id'];
        if (isset($data['return_type_id']))       $this->returnTypeId = (int) $data['return_type_id'];
        if (isset($data['ticket_status_id']))     $this->statusId     = (int) $data['ticket_status_id'];
        if (isset($data['user_id']))              $this->userId       = (int) $data['user_id'];
    }

    public function getId(): int             { return $this->id; }
    public function getReturnNumber(): string{ return $this->returnNumber; }
    public function getComment(): string     { return $this->comment; }
    public function getCreatedAt(): string   { return $this->createdAt; }
    public function getOrderId(): int        { return $this->orderId; }
    public function getReturnTypeId(): int   { return $this->returnTypeId; }
    public function getStatusId(): int       { return $this->statusId; }
    public function getUserId(): int         { return $this->userId; }

    public function getCreatedAtFormatted(): string
    {
        return date('d/m/Y à H:i', strtotime($this->createdAt));
    }

    public function isCloture(): bool
    {
        return $this->statusId === self::STATUS_CLOTURE;
    }
}

?>