<?php

use JetBrains\PhpStorm\Pure;
require_once ("EtagController.php");
require_once ("Lecture.php");
require_once ("LectureController.php");
require_once("StudentAction.php");
require_once("StudentActionController.php");
require_once ("StudentController.php");
require_once ("index.php");

class CurlController
{
    private $curl;

    public function __construct()
    {
        $this->curl = curl_init();
    }

    
    public function fetchAllFilesIntoDB($url): void
    {
        $EC = new EtagController();

        $etagFromDB = $EC->getEtag();
        $etagFromHeader = $this->getEtagFromHeader($url);

        if ($etagFromHeader == $etagFromDB)
        {
            return;
        }

        $EC->insertEtag($etagFromHeader);

        $repoUrl = $this->changeRepoUrlToApiContents($url);

        $files = $this->getRepoContent($repoUrl);
        $files = json_decode($files);

        foreach ($files as $file) {
            $this->downloadFileIntoDB($file->path);
        }
    }

    //$url is link for online repository
    private function getEtagFromHeader($url): bool|string
    {
        curl_reset($this->curl);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($this->curl, CURLOPT_NOBODY, true);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        $header = curl_exec($this->curl);

        $lines = explode(PHP_EOL, $header);
        $array = array();
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $charIndex = strpos($line, ":");
                $key = substr($line, 0, $charIndex);
                $value = substr($line, $charIndex + 1, strlen($line) - $charIndex - 1);
                $array[$key] = $value;
            }
        }

        return $array["etag"];
    }

    #[Pure] private function changeRepoUrlToApiContents($repositoryUrl): bool|string
    {
        $repoUrl = null;
        $pos1 = strpos($repositoryUrl, "//") + 2;
        $pos2 = strpos($repositoryUrl, ".com/") + 5;
        $length = abs($pos1 - $pos2);

        $repoUrl = substr($repositoryUrl, 0, $pos1);
        $repoUrl .= "api.";
        $repoUrl .= substr($repositoryUrl, $pos1, $length);
        $repoUrl .= "repos/";
        $repoUrl .= substr($repositoryUrl, $pos2);
        $repoUrl .= "/contents";

        return $repoUrl;
    }

    private function getRepoContent($url): bool|string
    {
        curl_reset($this->curl);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        return curl_exec($this->curl);
    }

    //$url is link for raw file from repository
    private function downloadFileIntoDB($fileName): void
    {
        $lecture = new Lecture();
        $lecture->setDate($this->getDateFromFile($fileName));

        $LC = new LectureController();

        if ($LC->isInDB($lecture))
        {
            return;
        }

        $id = $LC->insertLecture($lecture);
        $output = $this->getFileContent("https://raw.githubusercontent.com/apps4webte/curldata2021/main/" . $fileName);
        $lines = explode(PHP_EOL, $output);

        $UAC = new StudentActionController();

        foreach ($lines as $index => $line)
        {
            $lineArray = str_getcsv($line, "\t");
            if ($index > 0 && count($lineArray) == 3)
            {
                $uA = new StudentAction();

                $uA->setLectureId(intval($id));
                $uA->setFullName($lineArray[0]);
                $uA->setAction($lineArray[1]);
                try
                {
                    $uA->setTimestamp(date('Y-m-d H:i:s', date_create_from_format('d/m/Y, H:i:s', $lineArray[2])->getTimestamp()));
                }
                catch (Error $e)
                {
                    $uA->setTimestamp(date('Y-m-d H:i:s', date_create_from_format('m/d/Y, H:i:s A', $lineArray[2])->getTimestamp()));
                }
                $UAC->insertUserAction($uA);
            }
        }
    }

    #[Pure] protected function getDateFromFile($fileUrl): bool|string
    {
        $pos = strpos($fileUrl, "_");
        return substr($fileUrl, 0, $pos);
    }

    public function getFileContent($url): bool|string
    {
        curl_reset($this->curl);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($this->curl);
        if (!mb_detect_encoding($output, 'UTF-8', true)) {
            $output = mb_convert_encoding($output, 'UTF-8', 'UTF-16LE');
        }
        return $output;
    }

    public function closeDownloadedUrl()
    {
        curl_close($this->curl);
    }
}