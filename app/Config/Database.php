<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            // Load environment variables if not already loaded
            if (empty($_ENV['DB_HOST'])) {
                $envPath = __DIR__ . '/../../.env';
                EnvLoader::load($envPath);
            }

            try {
                $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'];
                self::$pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
