<?php

declare(strict_types=1);

namespace App\Mail;

use Exception;
use SendGrid\Mail\Mail;
use SendGrid\Mail\To;
use SendGrid\Mail\From;

/*
Good to knows:
    - Array $to needs to be an indexed associative array comprised of email and name keys
    Example:
    $to = [
        [
            'email' => 'example@example.com',
            'name' => 'Example'
        ],
        [
            'email' => 'example2@example.com',
        ]
    ];
*/

class Send
{
    public static function send(array $to, string $subject, string $body, string $from = FROM, string $fromName = FROM_NAME): string
    {
        if (!SENDGRID) {
            throw new Exception('Sendgrid is not enabled');
        }
        // Check structure of the $to array
        self::validateRecipients($to);
        // Do a FROM check
        if ($fromName !== FROM_NAME) {
            throw new Exception('Currently setting a FROM is not allowed');
        }
        // Set up FROM
        $from = new From($from, $fromName);

        // Now deal with To
        $toArray = [];

        foreach ($to as $recipient) {
            $dynamicData = [
                'subject' => $subject,
                'email' => $recipient['email'],
                'body' => $body,
                'name' => $recipient['name']
            ];
            array_push($toArray, new To($recipient['email'], $recipient['name'], $dynamicData, $subject));
        }


        $email = new Mail($from, $toArray);

        if (defined('SENDGRID_TEMPLATE_ID')) {
            $email->setTemplateId(SENDGRID_TEMPLATE_ID);
        } else {
            $email->setSubject($subject);
            $email->addContent("text/html", $body);
        }

        $sendgrid = new \SendGrid(SENDGRID_API_KEY, ['curl' => [CURLOPT_CAINFO => CURL_CERT]]);

        try {
            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202) {
                return 'email sent successfully';
            } else {
                throw new Exception('mail send failed with status ' . $response->statusCode() . 'and error ' . $response->body());
            }
        } catch (Exception $e) {
            throw new Exception('Caught exception: ' .  $e->getMessage() . "\n");
        }
    }

    // This proivate method will not only check if the passed 'email' key is set but also if it is a valid email address
    private static function validateRecipients(array &$to)
    {
        /* First we need to check the integrity of the the $to array. It will be an indexed associative array comprised of email and name keys
        array(2) {
            [
                0
            ]=>
        array(1) {
                [
                    "email"
                ]=>
            string(23) "dimitar.djongov@uefa.ch"
            }
        [
                1
            ]=>
        array(1) {
                [
                    "email"
                ]=>
            string(18) "ict.secops@uefa.ch"
            }
        }

        */
        // Let's check the structure of $to and make sure it's ok. $to needs to have arrays of email and name keys
        foreach ($to as $index => $recipient) {
            if (!is_array($recipient)) {
                throw new Exception('each recipient needs to be an array');
            }
            if (!isset($recipient['email'])) {
                throw new Exception('each recipient needs to have an "email" and optional "name" key');
            }
        }
        // What we can do to minimize the effort of providing names is first check if name is set and if not set it to the email address
        foreach ($to as $index => $recipient) {
            if (!isset($recipient['name'])) {
                $to[$index]['name'] = $recipient['email'];
            }
        }
        // Now the general check
        foreach ($to as $index => $recipient) {
            if (!isset($recipient['email'], $recipient['name'])) {
                throw new Exception('each recipient needs to have an "email" and "name" key');
            }
            if (!filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('"' . $recipient['email'] . '" is not a valid email address');
            }
        }
    }
}
