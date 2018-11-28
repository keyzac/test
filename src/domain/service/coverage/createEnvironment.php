<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 27/11/18
 * Time: 02:05 AM
 */

namespace app\domain\service\coverage;
require __DIR__ . '/../../../infrastructure/repository/colors.php';

use app\infrastructure\repository\colors;

class createEnvironment
{
    private $directory = 'tests';
    private $pathProject;
    private $colors;

    /**
     * createEnvironment constructor.
     */
    public function __construct()
    {
        $this->pathProject = "/app/app/Search";
        $this->colors = new colors;
    }

    public function createFolder()
    {
        chdir(dirname($this->pathProject, 2));

        $this->showMessage(1, "");
        $this->showMessage(0, "Verificando que exista la carpeta 'tests' en el proyecto.", "dark_gray", "light_gray");

        $this->showMessage(0, ".","dark_gray", "light_gray");
        $this->showMessage(1, ".","dark_gray", "light_gray");

        if (!file_exists($this->directory)) {
            $this->createDirectory($this->directory);
            $this->showMessage(2, "Carpeta 'tests' creado satisfactoriamente.", "yellow", "green");
        } else {
            $this->showMessage(2, "La carpeta 'tests' ya existe.", "yellow");
        }
    }

    /**
     * @param $fileInfo
     * @param string $folder
     */
    public function createEnvironment($fileInfo, $folder = "")
    {
        $pathTests = getcwd() . '/' . $this->directory;

        if ($fileInfo->getExtension() == 'php') {
            $fileTest = $fileInfo->getBasename('.php') . 'Test.' . $fileInfo->getExtension();
            $fileTest = $pathTests . $folder . $fileTest;

            if (!file_exists(dirname($fileTest))) {
                $this->createDirectory(dirname($fileTest));
                $this->showMessage(1, "Carpeta de destino '" . str_replace(getcwd().'/', '', dirname($fileTest)) . "' creada.", "green");
            } else {
                $this->showMessage(1, "Carpeta de destino '" . str_replace(getcwd().'/', '', dirname($fileTest)) . "' ya existe.", "light_red");
            }

            if (!file_exists($fileTest)) {
                $this->createFile($fileTest);
                $this->showMessage(1, "Archivo '" . str_replace(getcwd().'/', '', $fileTest) . "' creado correctamente.", "green");
            } else {
                $this->showMessage(1, "Archivo para pruebas unitarias '" . str_replace(getcwd().'/', '', $fileTest) . "' ya existe.", "light_red");
            }
        }
    }

    /**
     * @param $directory
     */
    private function createDirectory($directory)
    {
        mkdir($directory, 0777, true);
        chmod($directory, 0777);
    }

    /**
     * @param $file
     */
    private function createFile($file)
    {
        touch($file);
        chmod($file, 0777);
    }

    /**
     * @param $numberLine
     * @param $text
     * @param null $foreground
     * @param null $background
     */
    private function showMessage($numberLine, $text, $foreground = null, $background = null)
    {
        echo $this->colors->getColoredString($text, $foreground, $background);
        for ($i = 0; $i < $numberLine; $i++) {
            echo "\n";
        }
        sleep(1);
    }

    /**
     *
     */
    public function messageSuccess()
    {
        echo $this->colors->getColoredString("__________________", "light_green") . "\n\n";
        echo $this->colors->getColoredString("****SUCCESSFUL****", "light_green") . "\n";
        echo $this->colors->getColoredString("__________________", "light_green") . "\n\n";
    }
}