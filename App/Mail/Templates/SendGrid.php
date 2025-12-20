<?php

declare(strict_types=1);

namespace App\Mail\Templates;

use App\Mail\SendGridWrapper;
use App\Logs\SystemLog;

class SendGrid
{
    public static function informAdministrator(string $subject, string $message): void
    {
        $html = '<p><b>Hello ' . ADMINISTRATOR_EMAIL . ',</b></p>';
        $html .= '<p>' . $message . '</p>';
        $html .= '<p>Best regards,</p>';
        $html .= '<p>' . translate('site_title') . '</p>';
        $html .= '<p><small>This is an automated message. Please do not reply.</small></p>';
        $html .= '<p><small>Version: ' . VERSION . '</small></p>';
        try {
            SendGridWrapper::send(
                [
                [
                    'email' => ADMINISTRATOR_EMAIL
                ]
                ],
                $subject,
                $html,
                FROM,
                FROM_NAME
            );
        } catch (\Exception $e) {
            SystemLog::write('Mail', 'Error sending email to ' . ADMINISTRATOR_EMAIL . ': ' . $e->getMessage(), 'informAdministrator');
        }
    }
}
