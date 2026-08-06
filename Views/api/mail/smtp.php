<?php

declare(strict_types=1);

use \App\Mail\SMTP;
use App\Api\Checks;
use App\Api\Response;

$checks = new Checks($loginInfo, $_POST);

$checks->checkParams(['recipients', 'subject', 'body'], $_POST);

// We need the most strict checks for this endpoint
$checks->apiAdminChecks();

// First let's check the recipients, they should be semicolon separated; all go on one email's To: header
$recipients = array_values(array_filter(array_map('trim', explode(';', $_POST['recipients']))));

if (empty($recipients)) {
    Response::output('No valid recipients provided', 400);
}

$smtp = new SMTP();
try {
    $send = $smtp->send($recipients, $_POST['subject'], $_POST['body']);
    if ($send) {
        Response::output('Email sent successfully to ' . implode(', ', $recipients), 200);
    } else {
        Response::output('Failed to send email to ' . implode(', ', $recipients), 500);
    }
} catch (RuntimeException $e) {
    Response::output($e->getMessage(), $e->getCode() ?: 500);
}
