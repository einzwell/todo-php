<?php

namespace Todo\config;

use PDO;
use PDOException;

class Database {
    public ?PDO $connection = null;

    public function __construct() {
        // Load configuration from the environment
        $host = getenv('MARIADB_HOST');
        $port = getenv('MARIADB_PORT');
        $user = getenv('MARIADB_USER');
        $pass = getenv('MARIADB_PASSWORD');
        $db = getenv('MARIADB_DATABASE');

        try {
            $this->connection = new PDO(
                "mysql:host={$host};port={$port};dbname={$db}",
                $user,
                $pass
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $pdo_e) {
            echo("Could not connect to database {$db} through {$user}@{$host}:{$port}: {$pdo_e->getMessage()}");
        }
    }
}