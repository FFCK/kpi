<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $mailFrom,
        private readonly string $mailAdminTo,
        private readonly string $app4Url = ''
    ) {
    }

    /**
     * Send a notification email to the admin address.
     */
    public function sendAdminNotification(string $subject, string $body, ?string $to = null): void
    {
        try {
            $email = (new Email())
                ->from($this->mailFrom)
                ->to($to ?? $this->mailAdminTo)
                ->subject($subject)
                ->text($body);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to send notification email', [
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send a password reset email to a user.
     */
    public function sendPasswordReset(string $toEmail, string $token, bool $includeDocLink, string $complementaryMessage): void
    {
        $resetUrl = rtrim($this->app4Url, '/') . '/reset-password?token=' . $token;

        $body = "Bonjour,\n\n";
        $body .= "Votre compte KPI a été créé ou mis à jour.\n\n";
        $body .= "Pour définir votre mot de passe, cliquez sur le lien ci-dessous (valable 48h) :\n";
        $body .= $resetUrl . "\n\n";

        if ($includeDocLink) {
            $body .= "Documentation : " . rtrim($this->app4Url, '/') . "/doc\n\n";
        }

        if ($complementaryMessage !== '') {
            $body .= $complementaryMessage . "\n\n";
        }

        $body .= "Cordialement,\nL'équipe KPI";

        try {
            $email = (new Email())
                ->from($this->mailFrom)
                ->to($toEmail)
                ->subject('Accès KPI — Définition de votre mot de passe')
                ->text($body);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to send password reset email', [
                'to' => $toEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
