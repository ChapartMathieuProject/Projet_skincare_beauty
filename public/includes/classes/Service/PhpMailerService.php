<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PhpMailerException;

class PhpMailerService implements MailerInterface
{
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromAddress;
    private string $fromName;

    public function __construct(
        string $host,
        int    $port,
        string $username,
        string $password,
        string $encryption,
        string $fromAddress,
        string $fromName
    ) {
        $this->host        = $host;
        $this->port        = $port;
        $this->username    = $username;
        $this->password    = $password;
        $this->encryption  = $encryption;
        $this->fromAddress = $fromAddress;
        $this->fromName    = $fromName;
    }

    public function send(string $to, string $subject, string $body): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Adresse email invalide : ' . $to);
        }

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host    = $this->host;
            $mailer->Port    = $this->port;
            $mailer->CharSet = 'UTF-8';

            if ($this->username !== '') {
                $mailer->SMTPAuth = true;
                $mailer->Username = $this->username;
                $mailer->Password = $this->password;
            }

            if ($this->encryption !== '') {
                $mailer->SMTPSecure = $this->encryption;
            }

            $mailer->setFrom($this->fromAddress, $this->fromName);
            $mailer->addAddress($to);

            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body    = $body;
            $mailer->AltBody = strip_tags($body);

            return $mailer->send();
        } catch (PhpMailerException $e) {
            error_log('Echec envoi mail : ' . $e->getMessage());
            return false;
        }
    }
}