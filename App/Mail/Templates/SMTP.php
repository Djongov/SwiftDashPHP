<?php

declare(strict_types=1);

namespace App\Mail\Templates;

use App\Mail\SMTP as SMTPMail;
use Models\AppSettings;

class SMTP
{
    /**
     * Quickly send an email to the administrator
     * Usage: SMTPTemplates::informAdministrator('Subject', 'Message');
     */
    public static function informAdministrator(string $subject, string $message): bool
    {
        try {
            // Get administrator email from settings
            $appSettings = new AppSettings();
            $smtpSettingsRaw = $appSettings->getAllByOwner('smtp');
            
            // Transform the numeric array into an associative array keyed by setting name
            $smtpSettings = [];
            foreach ($smtpSettingsRaw as $setting) {
                $smtpSettings[$setting['name']] = $setting;
            }
            
            // Email address is email_smtp_from_address
            if (!isset($smtpSettings['email_smtp_administrator_email']) || empty($smtpSettings['email_smtp_administrator_email']['value'])) {
                throw new \RuntimeException('SMTP administrator email is not configured.');
            }
            
            $adminEmail = $smtpSettings['email_smtp_administrator_email']['value'];
            
            // Build HTML email
            $html = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
            $html .= '<p><b>Hello Administrator,</b></p>';
            $html .= '<p>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>';
            $html .= '<p>Best regards,<br>';
            $html .= defined('SITE_TITLE') ? SITE_TITLE : 'Azure WAF Manager' . '</p>';
            $html .= '<hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">';
            $html .= '<p style="font-size: 12px; color: #666;"><i>This is an automated message. Please do not reply.</i></p>';
            if (defined('VERSION')) {
                $html .= '<p style="font-size: 11px; color: #999;">Version: ' . VERSION . '</p>';
            }
            $html .= '</div>';

            // Use the SMTP class with its built-in config retrieval
            $smtp = new SMTPMail();
            return $smtp->send($adminEmail, $subject, $html);
            
        } catch (\Exception $e) {
            // Log the error and output for debugging
            $errorMsg = 'Error sending email to administrator: ' . $e->getMessage();
            if (class_exists('\App\Logs\SystemLog')) {
                \App\Logs\SystemLog::write('Mail', $errorMsg, 'informAdministrator');
            }
            // Also output to help with debugging
            error_log($errorMsg);
            throw $e; // Re-throw to see the actual error during testing
        }
    }
}
