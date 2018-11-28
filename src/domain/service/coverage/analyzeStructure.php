<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 25/11/18
 * Time: 07:53 PM
 */

namespace app\domain\service\coverage;
require __DIR__ . '/createEnvironment.php';

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class analyzeStructure
{
    private $pathProject;
    private $create;

    /**
     * analyzeStructure constructor.
     */
    public function __construct()
    {
        $this->pathProject = "/app/app/Search";
        $this->create = new createEnvironment;
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
            $this->create->createFolder();

            foreach ($it as $fileInfo) {
                if ($fileInfo->isFile()) {
                    switch (strtolower($it->getSubPath())) {
                        case 'application/service':
                            $this->create->createEnvironment($fileInfo, '/application/service/');
                            break;
                        case 'application/handler/query':
                            $this->create->createEnvironment($fileInfo, '/application/handler/query/');
                            break;
                        case 'domain/service':
                            $this->create->createEnvironment($fileInfo, '/domain/service/');
                            break;
                        case 'domain/exception':
                            $this->create->createEnvironment($fileInfo, '/domain/exception/');
                            break;
                        default:
                            break;
                    }
                }
            }
            $this->create->messageSuccess();
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }
}