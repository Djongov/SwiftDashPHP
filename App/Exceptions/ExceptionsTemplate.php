<?php

declare(strict_types=1);

namespace App\Exceptions;

abstract class ExceptionsTemplate extends \Exception implements ExceptionInterface
{
    public function genericError(string $message, int $code): static
    {
        return new static($message, $code);
    }
    public function alreadyExists(): static
    {
        return new static('resource already exists', 409);
    }
    public function notFound(): static
    {
        return new static('resource not found', 404);
    }
    public function emptyData(): static
    {
        return new static('no data provided', 400);
    }

    public function noParameter($parameter): static
    {
        return new static('no ' . $parameter . ' provided', 400);
    }

    public function emptyParameter($parameter): static
    {
        return new static($parameter . ' value is empty', 400);
    }

    public function parameterNoInt($parameter): static
    {
        return new static($parameter . ' is not an integer', 400);
    }

    public function parameterNoString($parameter): static
    {
        return new static($parameter . ' is not a string', 400);
    }

    public function parameterNoBool($parameter): static
    {
        return new static($parameter . ' is not a boolean', 400);
    }

    public function notSaved(): static
    {
        return new static('data not saved', 500);
    }
    public function dataMissmatch($errorMessage): static
    {
        return new static($errorMessage, 400);
    }
}
