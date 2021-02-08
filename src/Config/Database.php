<?php

namespace App\Config;

use PDO;

class Database
{
    /** @var PDO */
    private static $pdo;

    /**
     * Init Database
     * @return void
     */
    public function __construct()
    {
        $this->id = uniqid();
    }
    /**
     * Return database connection
     * @return PDO
     */
    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . getenv('DB_HOST')
                    . ";port=" . getenv('DB_PORT')
                    . ";dbname=" . getenv('DB_NAME'),
                    getenv('DB_USER'),
                    getenv('DB_PASSWORD'),
                    [PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION]
                );
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }
        return self::$pdo;
    }
}
