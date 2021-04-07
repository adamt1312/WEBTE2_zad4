<?php

require_once("Database.php");

class EtagController
{
    private ?PDO $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function insertEtag(string $etag)
    {
        $stmt = $this->conn->prepare("INSERT INTO etag (id, etag)
                                            VALUES (:id, :etag)
                                            ON DUPLICATE KEY UPDATE id=:id, etag=:etag");
        $id = 1;
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":etag", $etag);

        try
        {
            $stmt->execute();
        }
        catch (Exception $e)
        {
            echo "Couldn't insert new etag in DB.\n";
        }
    }

    public function getEtag(): ?string
    {
        $conn = (new Database())->getConnection();
        $stmt = $conn->prepare("SELECT etag FROM etag WHERE id = 1");

        try
        {
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $etag = $stmt->fetch();
            if ($etag != false)
            {
                return $etag["etag"];
            }
            else
            {
                return "";
            }
        }
        catch (Exception $e)
        {
            return null;
        }
    }
}