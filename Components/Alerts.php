<?php

declare(strict_types=1);

namespace Components;

class Alerts
{
    private static $_svgInfo = '<svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>';
    public static function info($message): string
    {
        return self::template('blue', $message);
    }
    public static function danger($message): string
    {
        return self::template('red', $message);
    }
    public static function success($message): string
    {
        return self::template('green', $message);
    }
    public static function warning($message): string
    {
        return self::template('yellow', $message);
    }
    public static function template($color, $message): string
    {
        return '
        <div class="w-max-full mx-4 flex items-center p-4 my-4 text-sm text-' . $color . '-800 border border-' . $color . '-300 rounded-lg bg-' . $color . '-50 ' . DARK_COLOR_SCHEME_CLASS . ' dark:text-' . $color . '-400 dark:border-' . $color . '-800" role="alert">
            ' . self::$_svgInfo . '
            <span class="sr-only">Info</span>
        <div>' . $message . '</div>
        </div>
        ';
    }
}
