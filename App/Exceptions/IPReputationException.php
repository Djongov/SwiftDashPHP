<?php

declare(strict_types=1);

namespace App\Exceptions;

class IPReputationException extends ExceptionsTemplate
{
    public function __construct(string $message = 'IP Reputation Error', int $code = 500)
    {
        parent::__construct($message, $code);
    }

    public function notFound(): static
    {
        return new static('IP address not found', 404);
    }

    public function invalidIP(): static
    {
        return new static('Invalid IP address provided', 400);
    }
}
