<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailService
{
    private string $host;
    private int    $port;
    private string $fromMail;
    private string $fromName;
    private string $lastError = '';

    public function __construct(?string $host = null, ?int $port = null)
    {
        $this->host     = $host ?? (getenv('MAIL_HOST') ?: '127.0.0.1');
        $this->port     = $port ?? (int) (getenv('MAIL_PORT') ?: 1025);
        $this->fromMail = getenv('MAIL_FROM')      ?: 'sav@skincarebeauty.fr';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: 'SkinCareBeauty - Service SAV';
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function sendReturnInstructions(string $toMail, string $toName, string $returnNumber, string $orderNumber): bool
    {
        $subject = "Votre retour $returnNumber — instructions de renvoi";

        $body = "
            <h2>Bonjour " . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . ",</h2>
            <p>Un retour a été enregistré pour votre commande
               <strong>" . htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8') . "</strong>.</p>
            <p>Votre numéro de retour est :</p>
            <p style=\"font-size:1.4em;\"><strong>" . htmlspecialchars($returnNumber, ENT_QUOTES, 'UTF-8') . "</strong></p>
            <h3>Instructions de renvoi</h3>
            <ol>
                <li>Emballez soigneusement le(s) produit(s) dans leur emballage d'origine.</li>
                <li>Inscrivez lisiblement le numéro de retour <strong>" . htmlspecialchars($returnNumber, ENT_QUOTES, 'UTF-8') . "</strong> sur le colis.</li>
                <li>Renvoyez le colis sous 14 jours à :<br>
                    SkinCareBeauty — Service SAV<br>
                    123 Rue de Paris<br>
                    75001 Paris, France</li>
            </ol>
            <p>Notre service SAV reste à votre disposition en répondant à cet e-mail.</p>
            <p>L'équipe SkinCareBeauty</p>";

        return $this->send($toMail, $toName, $subject, $body);
    }

    private function send(string $toMail, string $toName, string $subject, string $bodyHtml): bool
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host     = $this->host;
            $mail->Port     = $this->port;
            $mail->SMTPAuth = false;

            $username = getenv('MAIL_USERNAME');
            if ($username !== false && $username !== '') {
                $mail->SMTPAuth   = true;
                $mail->Username   = $username;
                $mail->Password   = getenv('MAIL_PASSWORD') ?: '';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->CharSet = 'UTF-8';
            $mail->setFrom($this->fromMail, $this->fromName);
            $mail->addAddress($toMail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;
            $mail->AltBody = strip_tags(str_replace(['<br>', '</li>'], "\n", $bodyHtml));

            $mail->send();
            return true;

        } catch (PHPMailerException $e) {
            $this->lastError = $mail->ErrorInfo;
            return false;
        }
    }
}
