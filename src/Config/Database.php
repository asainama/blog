<?php

namespace App\Config;

use PDO;

class Database
{
    /**
     * Dotenv Object
     *
     * @var Dotenv\Dotenv $_dotenv Object Dotenv
     */
    private $dotenv;

    /** @var PDO */
    private static $pdo;

    /**
     * Init Database
     * @return void
     */
    public function __construct()
    {
        $this->id = uniqid();
        $this->dotenv = \Dotenv\Dotenv::createImmutable(ROOT);
        $this->dotenv->load();
    }
    /**
     * Return database connection
     * @return PDO
     */
    public static function connect(): PDO
    {
        if (is_null(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . $_ENV['DB_HOST']
                    . ";port=" . $_ENV['DB_PORT']
                    . ";dbname=" . $_ENV['DB_NAME'],
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASSWORD'],
                    [PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION]
                );
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }
        return self::$pdo;
        // try {
        //     return new PDO(
        //         "mysql:host=" . $_ENV['DB_HOST']
        //         . ";port=" . $_ENV['DB_PORT']
        //         . ";dbname=" . $_ENV['DB_NAME'],
        //         $_ENV['DB_USER'],
        //         $_ENV['DB_PASSWORD'],
        //         [PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION]
        //     );
        // } catch (\PDOException $e) {
        //     throw new \Exception($e->getMessage());
        // }
    }
}
