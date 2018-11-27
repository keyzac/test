<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 25/11/18
 * Time: 07:53 PM
 */

namespace app\domain\service\coverage;
require __DIR__ . '/../../../infrastructure/repository/colors.php';

use app\infrastructure\repository\colors;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


class createStructure
{
    private $directory = 'tests';
    private $pathProject;
    private $colors;

    /**
     * createStructure constructor.
     */
    public function __construct()
    {
        $this->pathProject = dirname(__DIR__, 1);
        $this->colors = new colors;
    }

    /**
     * @return bool
     */
    public function analyzeStructure()
    {
        try {
            $dir = new RecursiveDirectoryIterator($this->pathProject, FilesystemIterator::SKIP_DOTS);

            $it  = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);

            $it->setMaxDepth(3);
            foreach ($it as $fileInfo) {
                if ($fileInfo->isFile()) {
                    var_dump($it->getSubPath());die;
                    switch ($it->getSubPath()) {
                        case 'application/service':
                            $this->createEnvironment($fileInfo);
                            break;
                        case 'domain/service':
                            $this->createEnvironment($fileInfo);
                            break;
                        case 'domain/exception':
                            $this->createEnvironment($fileInfo);
                            break;
                        default:
                            break;
                    }
                }
            }
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param $fileInfo
     */
    public function createEnvironment($fileInfo)
    {
        chdir(dirname($this->pathProject, 1));

        $this->showMessage(1, "");
        $this->showMessage(0, "Verificando que exista la carpeta 'tests' en el proyecto.", "dark_gray", "light_gray");

        $this->showMessage(".","dark_gray", "light_gray");
        $this->showMessage(".","dark_gray", "light_gray");

        if (!file_exists($this->directory)) {
            $this->createDirectory($this->directory);
            $this->showMessage(2, "Carpeta 'tests' creado satisfactoriamente.", "yellow", "green");
        } else {
            $this->showMessage(2, "La carpeta 'tests' ya existe.", "yellow");
        }

        $pathTests = getcwd() . '/' . $this->directory;

        if ($fileInfo->getExtension() == 'php') {
            $fileTest = $fileInfo->getBasename('.php') . 'Test.' . $fileInfo->getExtension();
            $fileTest = $pathTests . '/domain/service/' . $fileTest;

            if (!file_exists(dirname($fileTest))) {
                $this->createDirectory(dirname($fileTest));
                $this->showMessage(1, "Carpeta de destino '" . str_replace(getcwd(), '', dirname($fileTest)) . "' creada.", "green");
            } else {
                $this->showMessage(1, "Carpeta de destino '" . str_replace(getcwd(), '', dirname($fileTest)) . "' ya existe.", "light_red");
            }

            if (!file_exists($fileTest)) {
                $this->createFile($fileTest);
                $this->showMessage(1, "Archivo '" . str_replace(getcwd(), '', $fileTest) . "' creado correctamente.", "green");
            } else {
                $this->showMessage(1, "Archivo para pruebas unitarias '" . str_replace(getcwd(), '', $fileTest) . "' ya existe.", "light_red");
            }

            $this->messageSuccess();
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
    private function messageSuccess()
    {
        echo $this->colors->getColoredString("__________________", "light_green") . "\n\n";
        echo $this->colors->getColoredString("****SUCCESSFUL****", "light_green") . "\n";
        echo $this->colors->getColoredString("__________________", "light_green") . "\n\n";
    }
}