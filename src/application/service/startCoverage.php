<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 26/11/18
 * Time: 12:29 AM
 */

require __DIR__ . '/../../domain/service/coverage/analyzeStructure.php';

use app\domain\service\coverage\analyzeStructure;

class startCoverage
{
    /**
     *
     */
    public function start()
    {
        $structure = new analyzeStructure;
        $structure->analyzeStructure();
    }
}