<?php
class Controller
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . "/../views/" . $view . ".php";
    }
}