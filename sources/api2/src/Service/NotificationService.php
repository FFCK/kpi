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
        private readonly string $mailAdminTo
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
}
