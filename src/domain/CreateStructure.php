<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 11/11/18
 * Time: 01:53 PM
 */

    $pathProject = dirname(__DIR__, 1);
    $colors = new colors();

    $dir = new RecursiveDirectoryIterator($pathProject, FilesystemIterator::SKIP_DOTS);

    $it  = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);

	$it->setMaxDepth(3);

	foreach ($it as $fileInfo) {
        if ($fileInfo->isFile()) {
            switch ($it->getSubPath()) {
                case 'application/service':
                    createFileTest($fileInfo);
                    break;
                case 'domain/service':
                    createFileTest($fileInfo);
                    break;
                case 'domain/exception':
                    createFileTest($fileInfo);
                    break;
                case 'infrastructure/adapter':
                    createFileTest($fileInfo);
                    break;
                default:
                    break;
            }
        }
    }

    function createFileTest($fileInfo)
    {
        global $pathProject;
        global $colors;
        $directory = 'tests';

        chdir(dirname($pathProject, 1));

        echo "\n".$colors->getColoredString("Verificando que exista la carpeta 'tests' en el proyecto.", "dark_gray", "light_gray");
        sleep(1);echo $colors->getColoredString(".", "dark_gray", "light_gray");
        sleep(1);echo $colors->getColoredString(".", "dark_gray", "light_gray") . "\n";

        if (!file_exists($directory)) {
            mkdir($directory);
            chmod($directory, 0777);
            echo $colors->getColoredString("Carpeta 'tests' creado satisfactoriamente.", "yellow", "green") . "\n\n";
            sleep(1);
        } else {
            echo $colors->getColoredString("La carpeta 'tests' ya existe.", "yellow") . "\n\n";
            sleep(1);
        }

        $pathTests = getcwd() . '/' . $directory;

        if ($fileInfo->getExtension() == 'php') {
            $fileTest = $fileInfo->getBasename('.php') . 'Test.' . $fileInfo->getExtension();
            $fileTest = $pathTests . '/domain/service/' . $fileTest;

            if (!file_exists(dirname($fileTest))) {
                mkdir(dirname($fileTest), 0777, true);
                chmod(dirname($fileTest), 0777);
                echo $colors->getColoredString("Carpeta de destino '" . str_replace(getcwd(), '', dirname($fileTest)) . "' creada.", "green") . "\n";
                sleep(1);
            } else {
                echo $colors->getColoredString("Carpeta de destino '" . str_replace(getcwd(), '', dirname($fileTest)) . "' ya existe.", "light_red") . "\n";
                sleep(1);
            }

            if (!file_exists($fileTest)) {
                touch($fileTest);
                chmod($fileTest, 0777);
                echo $colors->getColoredString("Archivo '" . str_replace(getcwd(), '', $fileTest) . "' creado correctamente.", "green") . "\n";
                sleep(1);
            } else {
                echo $colors->getColoredString("Archivo para pruebas unitarias '" . str_replace(getcwd(), '', $fileTest) . "' ya existe.", "light_red") . "\n";
                sleep(1);
            }
            echo "\n".$colors->getColoredString("__________________", "light_green") . "\n\n";
            echo $colors->getColoredString("****SUCCESSFUL****", "light_green") . "\n";
            echo $colors->getColoredString("__________________", "light_green") . "\n\n";
        }
    }

class Colors
{
    private $foreground_colors = array();
    private $background_colors = array();

    /**
     * colors constructor.
     */
    public function __construct() {
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    /**
     * @param $string
     * @param null $foreground_color
     * @param null $background_color
     * @return string
     */
    public function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }

        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    /**
     * @return array
     */
    public function getForegroundColors() {
        return array_keys($this->foreground_colors);
    }

    /**
     * @return array
     */
    public function getBackgroundColors() {
        return array_keys($this->background_colors);
    }
}
