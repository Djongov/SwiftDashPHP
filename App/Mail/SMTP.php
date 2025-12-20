<?php

declare(strict_types=1);

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Models\AppSettings;
use RuntimeException;

class SMTP
{
    private PHPMailer $mailer;

    public function __construct(array $config = [])
    {
        $this->mailer = new PHPMailer(true);

        // If config is provided, initialize with it
        if (!empty($config)) {
            $this->mailer->isSMTP();
            $this->mailer->Host       = $config['host'];
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $config['username'];
            $this->mailer->Password   = $config['password'];
            $this->mailer->Port       = (int) $config['port'];

            $this->mailer->SMTPSecure = $config['ssl'] === true
                ? PHPMailer::ENCRYPTION_SMTPS   // port 465
                : PHPMailer::ENCRYPTION_STARTTLS;
            
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => $config['verify_peer'] ?? false,
                    'verify_peer_name'  => $config['verify_peer_name'] ?? false,
                    'allow_self_signed' => $config['allow_self_signed'] ?? true,
                ],
            ];

            $this->mailer->setFrom(
                $config['from_address'],
                $config['from_name']
            );
        }
    }

    public function setConfig() : array
    {
        $appSettings = new AppSettings();
        $smtpSettingsRaw = $appSettings->getAllByOwner('smtp');

        // Transform the numeric array into an associative array keyed by setting name
        $smtpSettings = [];
        foreach ($smtpSettingsRaw as $setting) {
            $smtpSettings[$setting['name']] = $setting;
        }

        $expectedSettings = [
            'email_smtp_host',
            'email_smtp_port',
            'email_smtp_ssl',
            'email_smtp_verify_peer',
            'email_smtp_verify_peer_name',
            'email_smtp_allow_self_signed',
            'email_smtp_username',
            'email_smtp_password',
            'email_smtp_from_address',
            'email_smtp_from_name',
        ];

        $configArray = [];

        foreach ($expectedSettings as $setting) {
            if (array_key_exists($setting, $smtpSettings) === false) {
                throw new RuntimeException("Missing SMTP setting: $setting");
            }
            
            // Strip the 'email_smtp_' prefix from the key
            $key = str_replace('email_smtp_', '', $setting);
            $value = $smtpSettings[$setting]['value'];
            
            // Decode HTML entities (especially important for passwords)
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            // Convert boolean values to actual booleans
            if (isset($smtpSettings[$setting]['type']) && $smtpSettings[$setting]['type'] === 'bool') {
                $value = (bool)($value == "1" || $value === "true" || $value === true);
            }
            
            // Convert port to integer
            if ($key === 'port') {
                $value = (int)$value;
            }
            
            $configArray[$key] = $value;
        }

        return $configArray;
    }

    public function sendEmail(string $to, string $subject, string $body): bool
    {
        try {
            // Recipients
            $this->mailer->addAddress($to);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function send(string $to, string $subject, string $body, null|array $config = null ) : bool
    {
        if ($config === null) {
            $config = $this->setConfig();
        }
        
        $mailer = new SMTP($config);

        try {
            return $mailer->sendEmail(
                $to,
                $subject,
                $body
                );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}