<?php

namespace App\Config;

use Exception;

class EnvLoader
{
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exception(".env file not found at $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);

            $name  = trim($name);
            $value = trim($value ?? '');

            if ($name === '') continue;

            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}
