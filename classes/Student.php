<?php

use JetBrains\PhpStorm\Pure;
require_once "LectureController.php";

class Student
{
    private int $id;
    private string $name;
    private string $surname;
    private string $lecture_times;
    private string $lecture_lefts;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $full_name
     */
    public function setSurname(string $full_name): void
    {
        $name = str_replace("\\", "", $full_name);
        $name = str_replace("/", "", $name);
        $name = str_replace("\"", "", $name);
        $name = str_replace("(", "", $name);
        $name = str_replace("[", "", $name);
        $name = str_replace("]", "", $name);
        $name = str_replace(")", "", $name);
        $name = str_replace("{", "", $name);
        $name = str_replace("}", "", $name);
        $pieces = explode(' ', $name);

        $priezvisko = array_pop($pieces);
        if ($priezvisko == "Guest")
        {
            $this->surname = array_pop($pieces);
        }
        else
        {
            $this->surname = $priezvisko;
        }
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $full_name
     */
    public function setName(string $full_name): void
    {
        $name = str_replace("\\", "", $full_name);
        $name = str_replace("/", "", $name);
        $name = str_replace("\"", "", $name);
        $name = str_replace("(", "", $name);
        $name = str_replace("[", "", $name);
        $name = str_replace("]", "", $name);
        $name = str_replace(")", "", $name);
        $name = str_replace("{", "", $name);
        $name = str_replace("}", "", $name);
        $pieces = explode(' ', $name);

        $priezvisko = array_pop($pieces);
        if ($priezvisko == "Guest")
        {
            array_pop($pieces);
        }

        $numPieces = count($pieces);
        $i = 0;
        $name = "";
        foreach ($pieces as $piece)
        {
            if (++$i === $numPieces)
            {
                $name .= $piece;
            }
            else
            {
                $name .= $piece." ";
            }
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    #[Pure] public function getFullName(): string
    {
        return $this->getName()." ".$this->getSurname();
    }

    /**
     * @return string
     */
    public function getLectureTimes(): string
    {
        return $this->lecture_times;
    }

    /**
     * @param string $lecture_times
     */
    public function setLectureTimes(string $lecture_times): void
    {
        $this->lecture_times = $lecture_times;
    }

    /**
     * @return string
     */
    public function getLectureLefts(): string
    {
        return json_encode($this->lecture_lefts);
    }

    /**
     * @param string $lecture_left
     */
    public function setLectureLefts(string $lecture_left): void
    {
        $this->lecture_left = $lecture_left;
    }

    public function getNumberOfLecturesVisited()
    {
        $lectures = explode(',', $this->getLectureTimes());
        $i = 0;
        foreach ($lectures as $lecture)
        {
            $lecture = str_replace("[", "", $lecture);
            $lecture = str_replace("]", "", $lecture);
            if ($lecture != 0)
            {
                ++$i;
            }
        }
        return $i;
    }

    public function getTotalTime()
    {
        $lectures = explode(',', $this->getLectureTimes());
        $totalTime = 0;
        foreach ($lectures as $lecture)
        {
            $lecture = str_replace("[", "", $lecture);
            $lecture = str_replace("]", "", $lecture);
            if ($lecture != 0)
            {
                $totalTime += floatval($lecture);
            }
        }
        return $totalTime;
    }

    public function getRow(): string
    {
        $LC = new LectureController();
        $LC = $LC->getNumberOfLectures();

        $output = "<tr>
                   <th scope='row'>".$this->getName()."</th>
                   <th scope='row'>".$this->getSurname()."</th>";

        $lectures = explode(',', $this->getLectureTimes());
        $lecturesLeft = explode(',', $this->getLectureLefts());
        for ($j = 0; $j < $LC; $j++)
        {
            $lecture = str_replace("[", "", $lectures[$j]);
            $lecture = str_replace("]", "", $lecture);
            if (str_contains($lecturesLeft[$j],"false")) {
                $output .= "<td style='color: red'>".$lecture."</td>";
            }
            else {
                $output .= "<td>".$lecture."</td>";
            }

        }

        $output .= "<td>".$this->getNumberOfLecturesVisited()."</td>
                <td>".$this->getTotalTime()."</td>
                </tr>";

        return $output;
    }

}