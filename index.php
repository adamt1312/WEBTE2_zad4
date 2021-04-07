<?php

require_once("classes/CurlController.php");
require_once ("classes/StudentController.php");
require_once ("classes/LectureController.php");
$G_URL = "https://github.com/apps4webte/curldata2021";

$curlController = new CurlController();
$curlController->fetchAllFilesIntoDB($G_URL);
$curlController->closeDownloadedUrl();

$studentController = new StudentController();
$studentController->insertStudentsInDB();
$studentController->initStudentsLectureTimesAndLefts();
$students = $studentController->fetchAllStudentsIntoClass();

$lectureController = new LectureController();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>WEBTE2 - CURL</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@300&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.0.2/chart.min.js" integrity="sha512-dnUg2JxjlVoXHVdSMWDYm2Y5xcIrJg1N+juOuRi0yLVkku/g26rwHwysJDAMwahaDfRpr1AxFz43ktuMPr/l1A==" crossorigin="anonymous"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
</head>
<body>
<div class="container-fluid con">
    <div class="title">
        <h1 style="font-family: 'Saira'; font-size: 70px">WEBTE2 - CURL</h1>
    </div>
    <div class="chartWrapper">
        <canvas id="myChart" width="1600" height="900"></canvas>
    </div>
    <hr style="width: 80%; height: 2px; color: #000;">
    <div class="tableWrapper">
        <table class="table table-dark table-hover table-responsive-md" id="lectures">
            <thead>
                <th>Meno</th>
                <th>Priezvisko</th>

                <?php
                // for loop generate lectures
                for ($x = 1; $x <= $lectureController->getNumberOfLectures(); $x++) {
                    echo "<th>Prednáška $x.</th>";
                }
                ?>
                <th>Počet účastí</th>
                <th>Celkovo minút</th>
            </thead>

            <tbody>
                    <?php
                        foreach ($students as $student)
                         echo $student->getRow();
                    ?>
            </tbody>
        </table>
    </div>
</div>
<div class="footer">
    Adam Trebichalský, 98014
</div>



<input type="hidden" value="<?= implode(",", $lectureController->getNumbersOfStudents()); ?>" id="visitsOfLectures">
<script src="script.js"></script>
</body>
</html>
