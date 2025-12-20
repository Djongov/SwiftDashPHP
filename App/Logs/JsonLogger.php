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

        $output = in_array($level, ['error', 'warning'], true)
            ? STDERR
            : STDOUT;

        fwrite($output, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL);
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
