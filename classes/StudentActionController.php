<?php

require_once("StudentAction.php");
require_once("Database.php");

class StudentActionController
{
    private ?PDO $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function insertUserAction(StudentAction $userAction): ?string
    {
        $stmt = $this->conn->prepare("INSERT INTO students_actions (lecture_id, full_name, action, timestamp)
                                            VALUES (:lecture_id, :full_name, :action, :timestamp)
                                            ON DUPLICATE KEY UPDATE lecture_id=:lecture_id, full_name=:full_name, action=:action, timestamp=:timestamp");
        $lecture_id = $userAction->getLectureID();
        $full_name = $userAction->getFullName();
        $action = $userAction->getAction();
        $timestamp = $userAction->getTimestamp();

        $stmt->bindParam(":lecture_id", $lecture_id);
        $stmt->bindParam(":full_name", $full_name);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":timestamp", $timestamp);

        try
        {
            $stmt->execute();
            return $this->conn->lastInsertId();
        }
        catch (Exception $e)
        {
            return null;
        }
    }
}