<?php

require_once("Lecture.php");

class LectureController
{
    private ?PDO $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function insertLecture(Lecture $lecture): ?string
    {
        $stmt = $this->conn->prepare("INSERT INTO lectures (date)
                                            VALUES (DATE(:date))
                                            ON DUPLICATE KEY UPDATE date=:date");

        $date = $lecture->getDate();
        $stmt->bindParam(":date", $date);
        try
        {
            $stmt->execute();
            return $this->conn->lastInsertId();
        }
        catch (Exception $e)
        {
            $stmt = $this->conn->prepare("SELECT id FROM lectures
                                                WHERE date=DATE(:date)");
            $stmt->bindParam(":date", $date);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetch();
            return $result["id"] ?? "";
        }
    }

    public function isInDB(Lecture $lecture): bool
    {
        $stmt = $this->conn->prepare("SELECT id FROM lectures 
                                            WHERE date=DATE(:date)");
        $date = $lecture->getDate();
        $stmt->bindParam(":date", $date);
        try
        {
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetch();
            if ($result == false)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    public function getNumberOfLectures(): ?int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT(id))
                                            AS lectures
                                            FROM lectures");
        try
        {
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $lectures = $stmt->fetch()["lectures"];
        }
        catch (Exception $e)
        {
            $lectures = null;
        }
        return $lectures;
    }

    public function getNumbersOfStudents(): ?array
    {
        $lectures = $this->getNumberOfLectures();

        $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT(full_name))
                                            AS number
                                            FROM students_actions
                                            WHERE lecture_id=:lecture_id
                                            GROUP BY lecture_id");

        $arr = array();

        for ($i = 1; $i <= $lectures; ++$i)
        {
            $stmt->bindParam(":lecture_id", $i);
            try
            {
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                array_push($arr, $stmt->fetch()["number"]-1);
            }
            catch (Exception $e)
            {
                return null;
            }
        }
        return $arr;
    }
}