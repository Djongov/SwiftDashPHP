<?php

declare(strict_types=1);

use \App\Mail\SMTP;
use App\Api\Checks;
use App\Api\Response;

$checks = new Checks($loginInfo, $_POST);

$checks->checkParams(['recipients', 'subject', 'body'], $_POST);

// We need the most strict checks for this endpoint
$checks->apiAdminChecks();

// First let's check the recipients, they should be comma separated
$recipients = explode(';', $_POST['recipients']);

foreach ($recipients as $recipient) {
    $smtp = new SMTP();
    $smtpAppSettings = new \Models\AppSettings();
    try {
        $send = $smtp->send($recipient, $_POST['subject'], $_POST['body']);
        if ($send) {
            Response::output('Email sent successfully to ' . $recipient, 200);
        } else {
            Response::output('Failed to send email to ' . $recipient, 500);
        }
    } catch (RuntimeException $e) {
        Response::output($e->getMessage(), $e->getCode() ?? 500);
    }
}
