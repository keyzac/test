<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 26/11/18
 * Time: 12:29 AM
 */

require __DIR__ . '/../../domain/service/coverage/createStructure.php';

use app\domain\service\coverage\createStructure;

class startCoverage
{
    /**
     *
     */
    public function start()
    {
        $structure = new createStructure;
        $structure->analyzeStructure();
    }
}