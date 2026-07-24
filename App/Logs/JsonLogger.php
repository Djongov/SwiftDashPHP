<?php
declare(strict_types=1);

namespace App\Logs;

use DateTimeImmutable;

final class JsonLogger
{
    private static ?string $runId = null;

    public static function setRunId(string $runId): void
    {
        self::$runId = $runId;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $entry = [
            'ts'      => (new DateTimeImmutable())->format(DATE_ATOM),
            'level'   => $level,
            'message' => $message,
            'context' => $context,
            'pid'     => getmypid(),
            'run_id'  => self::$runId,
        ];

        $line    = json_encode($entry, JSON_UNESCAPED_SLASHES);
        $isError = in_array($level, ['error', 'warning'], true);

        // STDOUT/STDERR are only defined in the CLI SAPI. Under a web SAPI (e.g. an admin
        // "Run now" request) they don't exist, so fall back to error_log() there.
        if (defined('STDOUT') && defined('STDERR')) {
            fwrite($isError ? STDERR : STDOUT, $line . PHP_EOL);
        } else {
            error_log($line);
        }
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }
}
