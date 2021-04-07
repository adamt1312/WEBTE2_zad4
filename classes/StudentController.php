<?php

require_once("Student.php");

class StudentController
{
    private ?PDO $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function insertStudent(Student $student): ?string
    {
        if ($this->isInDB($student))
        {
            return null;
        }

        $stmt = $this->conn->prepare("INSERT INTO students (name, surname)
                                            VALUES (:name, :surname)
                                            ON DUPLICATE KEY UPDATE name=:name, surname=:surname");
        $name = $student->getName();
        $surname = $student->getSurname();

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":surname", $surname);

        try
        {
            $stmt->execute();
            return $this->conn->lastInsertId();
        }
        catch (Exception $e)
        {
            echo $e."<br>";
            return null;
        }
    }

    public function isInDB(Student $student): ?int
    {
        $stmt = $this->conn->prepare("SELECT id FROM students 
                                            WHERE name=:name
                                            AND surname=:surname");
        $name = $student->getName();
        $surname = $student->getSurname();

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":surname", $surname);

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
                return $result["id"];
            }
        }
        catch (Exception $e)
        {
            echo $e."<br>";
            return false;
        }
    }

    public function insertStudentsInDB()
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT(full_name)
                                            FROM students_actions");
        try
        {
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();
            foreach ($result as $one)
            {
                $S = new Student();
                $S->setName($one["full_name"]);
                $S->setSurname($one["full_name"]);
                $this->insertStudent($S);
            }
        }
        catch (Exception $e)
        {
            echo $e."<br>";
        }
    }

    public function getAllStudents(): array
    {
        $stmt = $this->conn->prepare("SELECT name, surname
                                            FROM students");
        try
        {
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            return $stmt->fetchAll();
        }
        catch (Exception $e)
        {
            echo $e."<br>";
        }
    }

    public function initStudentsLectureTimesAndLefts()
    {
        $fetchedStudentsNames = $this->getAllStudents();
        $LC = new LectureController();
        $fetchedLectures = $LC->getNumberOfLectures();

        // iterujem cez studentov
        foreach ($fetchedStudentsNames as $fetchedStudentsName)
        {
            $lecture_times = array();
            $lecture_lefts = array();
            $full_name = $fetchedStudentsName["name"]." ".$fetchedStudentsName["surname"];


            //iterujem cez prednasky
            for ($i = 1; $i <= $fetchedLectures; ++$i)
            {
                $stmt = $this->conn->prepare("SELECT action,timestamp
                                            FROM `students_actions`
                                            WHERE full_name=:full_name
                                            AND lecture_id=:lecture_id");
                $stmt->bindParam(":full_name", $full_name);
                $stmt->bindParam(":lecture_id", $i);


                try
                {
                    $stmt->execute();
                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                    $actionTimestamps = $stmt->fetchAll();

                    // last left timestamp of lecture
                    try
                    {
                        $stmt = $this->conn->prepare("SELECT MAX(timestamp)
                                                        AS timestamp
                                                        FROM students_actions
                                                        WHERE lecture_id=:lecture_id
                                                        AND action='Left'");
                        $stmt->bindParam(":lecture_id", $i);
                        $stmt->execute();
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        $lastLeftTimeStamp = $stmt->fetch();
                    }
                    catch (Exception $e)
                    {
                        echo $e;
                    }

                    if (count($actionTimestamps) % 2 != 0)
                    {
                        array_push($lecture_lefts, false);
                        array_push($actionTimestamps, ["action" => "Left", "timestamp" => $lastLeftTimeStamp["timestamp"]]);
                    }
                    else
                    {
                        array_push($lecture_lefts, true);
                    }

                    $timeSpent = 0;
                    //iterujem cez studentove timestampy na jednlotivej prednaske
                    for ($klm = 0; $klm < count($actionTimestamps); $klm+=2)
                    {
                        if ($actionTimestamps[$klm] != null)
                        {
                            $from_time = strtotime($actionTimestamps[$klm]['timestamp'] ?? null);
                            $to_time = strtotime($actionTimestamps[$klm + 1]['timestamp'] ?? null);
                            $timeSpent += round(abs($to_time - $from_time) / 60, 2);
                        }
                    }

                    $timeSpent = round($timeSpent,2);
                    array_push($lecture_times, $timeSpent);

                }
                catch (Exception $e)
                {
                    echo $e."<br>";
                }
            }

            $stmt = $this->conn->prepare("UPDATE students
                                                SET lecture_times=:lecture_times, lecture_lefts=:lecture_lefts
                                                WHERE name=:name AND surname=:surname");
            $name = $fetchedStudentsName["name"];
            $surname = $fetchedStudentsName["surname"];
            $casy = json_encode($lecture_times);
            $odchody = json_encode($lecture_lefts);

            $stmt->bindParam(":lecture_times", $casy);
            $stmt->bindParam(":lecture_lefts", $odchody);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":surname", $surname);

            try
            {
                $stmt->execute();
            }
            catch (Exception $e)
            {
                echo $e;
            }
        }
    }

    public function fetchAllStudentsIntoClass()
    {
        $stmt = $this->conn->prepare("SELECT *
                                            FROM students");
        try
        {
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_CLASS,"Student");
            return $stmt->fetchAll();
        }
        catch (Exception $e)
        {
            echo $e."<br>";
        }
    }
}