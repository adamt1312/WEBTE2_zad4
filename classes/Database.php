<?php

require_once("config.php");

class Database {

    protected ?PDO $conn;

    public function getConnection(): ?PDO
    {
        $this->conn = null;
        try
        {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $exception)
        {
            echo "Database could not be connected: " . $exception->getMessage();
        }
        return $this->conn;
    }
}