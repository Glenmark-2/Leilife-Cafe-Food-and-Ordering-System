<?php
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            throw new Exception(".env file not found at $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines or comments
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Skip lines that don't contain '='
            if (strpos($line, '=') === false) {
                continue;
            }

            // Split key=value safely
            list($name, $value) = explode('=', $line, 2);

            $name  = trim($name);
            $value = trim($value ?? ''); // ensure $value is not null

            if ($name === '') continue; // skip if no key

            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}