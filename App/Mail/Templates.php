<?php

declare(strict_types=1);

namespace App\Mail;

use App\Mail\Send;
use App\Logs\SystemLog;

class Templates
{
    public static function informAdministrator(string $subject, string $message) : void
    {
        $html = '<p><b>Hello ' . ADMINISTRATOR_EMAIL . ',</b></p>';
        $html .= '<p>' . $message . '</p>';
        $html .= '<p>Best regards,</p>';
        $html .= '<p>' . SITE_TITLE . '</p>';
        $html .= '<p><small>This is an automated message. Please do not reply.</small></p>';
        $html .= '<p><small>Version: ' . VERSION . '</small></p>';
        $send = Send::send([
            [
                'email' => ADMINISTRATOR_EMAIL
            ]
        ], $subject, $html);
        SystemLog::write('Mail', 'informAdministrator has sent email to ' . ADMINISTRATOR_EMAIL . ' with subject: ' . $subject . ' and result is ' . json_encode($send), 'informAdministrator');
    }
}
