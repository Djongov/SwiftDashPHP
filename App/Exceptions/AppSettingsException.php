<?php

declare(strict_types=1);

// Path: App/Exceptions/AppSettingsException.php

// Used in /Models/Api/AppSettings.php

namespace App\Exceptions;

class AppSettingsException extends ExceptionsTemplate
{
    public function settingAlreadyExists(): self
    {
        return new self('App setting already exists', 409);
    }
    public function settingDoesNotExist(): self
    {
        return new self('App setting does not exist', 404);
    }
    public function settingNotUpdated(): self
    {
        return new self('App setting not updated', 500);
    }
    public function invalidSetting(): self
    {
        return new self('invalid App setting', 400);
    }
}
