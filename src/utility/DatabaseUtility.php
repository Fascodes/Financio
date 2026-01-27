<?php

class DatabaseUtility {
    private static $pdo = null;

    private static function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv("$name=$value");
        }
    }

    public static function getConnection() {
        if (self::$pdo === null) {
            // Załaduj .env ręcznie
            self::loadEnv(__DIR__ . '/../../.env');

            $host = 'db';  // Nazwa usługi z docker-compose
            $port = '5432';
            $dbname = getenv('POSTGRES_DB');
            $user = getenv('POSTGRES_USER');
            $password = getenv('POSTGRES_PASSWORD');

            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
                self::$pdo = new PDO($dsn, $user, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Błąd połączenia: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}