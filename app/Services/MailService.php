<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Logger;

/**
 * Mail sender. Uses PHP's built-in mail() by default (same as the original
 * mail.php) but through a single, reusable, injectable class instead of a
 * one-off script — so swapping in PHPMailer/SMTP later (recommended for
 * production, since mail() is often unreliable/blocked by hosts) means
 * changing the body of send() in exactly one place.
 *
 * The original mail.php also had no CSRF protection and echoed validation
 * errors with no escaping; the contact form now goes through Validator +
 * Csrf like every other form.
 */
final class MailService
{
    public function send(string $to, string $subject, string $body): bool
    {
        $config = Config::get('mail');
        $headers = "From: {$config['from_name']} <{$config['from_address']}>\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $sent = @mail($to, $subject, $body, $headers);
        if (!$sent) {
            Logger::warning("MailService: failed to send '{$subject}' to {$to} (mail() unavailable in this environment — configure SMTP in production).");
        }
        return $sent;
    }
}
