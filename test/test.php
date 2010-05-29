#!/usr/bin/env php
<?php

class CMP {
const EQUAL = 1;
const NEQUAL = 0;
const ERROR = -1;
}

function printUsage($msg = '')
{
    if ($msg)
        echo $msg."\n\n";

    echo "Usage: ".basename(__FILE__)." \n".
         "  help \n".
         "  store [foafURL] [--targetdir=DIR]\n".
         "  single [foafURL] [--expecteddir=DIR]\n".
         "  profiling [foafURL] [--expecteddir=DIR]\n".
            "\n";
}

 $command = getarg(1);

 $params = array_slice($argv, 1);

 function getarg($argi)
 {
     global $argv;
     return isset($argv[$argi]) ? $argv[$argi] : NULL;
 }

 function URL2FileName($url) {

   $fname = strtr($url, array(
        '/' => '_s_',
        ':' => '_c_',
        '?' => '_q_',
        '&' => '_a_',
        '_' => '__',
        '=' => '_e_',
        '#' => '_h_'
    ));

    return $fname.".expected";
 }

 function FileName2URL($fname) {
     $pinfo = pathinfo(basename($fname));
     if ('expected' != $pinfo['extension'])
     {
         return NULL;
     }
     $url = strtr($pinfo['filename'], array(
        '_s_' => '/',
        '_c_' => ':',
        '_q_' => '?',
        '_a_' => '&',
        '__' => '_',
        '_e_' => '=',
        '_h_' => '#'
    ));

    return $url;
 }

 function http_get($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
 }

 function get_files_in_directory($dir='.', $pattern = '*')
 {
    $files = array();
     foreach (new DirectoryIterator($dir) as $fileInfo) {
        if($fileInfo->isFile() && preg_match($pattern, $fileInfo->getFilename()))
                $files[] = $fileInfo->getFilename();
    }
    return $files;
 }

 function compareToExpected($foafURL,$expectedFoafPath)  {

     // get remote content
     $t = microtime(true);
     $result['actual'] = http_get($foafURL);
     $result['sElapsed'] = microtime(true) - $t;

     // compare it to the stored expected content if exists
     if (file_exists($expectedFoafPath)) {
        $result['expected'] = file_get_contents($expectedFoafPath);
        $result['nEquals'] = (strcmp($result['expected'] , $result['actual']) == 0) ? CMP::EQUAL : CMP::NEQUAL;
     }
     else
          $result['nEquals'] = CMP::ERROR;

     return $result;
 }

 function executeStore($foafURL, $targetDir = NULL) {
    if (1 > strlen($foafURL))
        $foafURL = 'http://foaf.me/melvincarvalho';
    if (!$targetDir)
        $targetDir = dirname(__FILE__);

    $response = http_get($foafURL);
    file_put_contents($targetDir."/".URL2FileName($foafURL), $response);
 }

 function executeSingleTest($foafURL, $targetDir = NULL) {
     if (1 > strlen($foafURL))
        $foafURL = 'http://foaf.me/melvincarvalho';
     if (!$targetDir)
        $targetDir = dirname(__FILE__);
     $compareResult = compareToExpected($foafURL, $targetDir."/".URL2FileName($foafURL));
     echo "GET ".$foafURL." completed in ".$compareResult['sElapsed']." s.\n";
     echo "Comparing ".$foafURL." with expected content";

     switch ($compareResult['nEquals']) {
         case CMP::EQUAL :
            echo " [OK]\n";
         break;
         case CMP::NEQUAL :
            echo " [FAILED]\n";
            file_put_contents('expected.tmp', $compareResult['expected']."\n");
            file_put_contents('actual.tmp', $compareResult['actual']."\n");
            echo `diff expected.tmp actual.tmp`."\n";
         break;
         default:
             echo " [MISSING EXPECTED]";
     }

 }

 function stddev($values) {
     if (count($values) < 2)
         return 0;
     $mean = array_sum($values) / count($values);
     foreach ($values as $value) {
         $sq[] = ($value - $mean)*($value - $mean);
     }
     return sqrt(array_sum($sq)/(count($values) - 1));
 }

 function displayStats($testStats) {

     echo  $testStats['total']." requests:".
            "Success(".$testStats[CMP::EQUAL]."),".
            "Mismatch(".$testStats[CMP::NEQUAL]."),".
            "Error(".$testStats[CMP::ERROR].")\n";
     echo  "Average response time ".$testStats['sAvgElapsed']." (s)\n";
     echo  "Max response time     ".$testStats['sMaxElapsed']." (s)\n";
     echo  "Min response time     ".$testStats['sMinElapsed']." (s)\n";
     echo  "Std deviation         ".$testStats['sStdDevElapsed']." (s)\n";

 }

 function computeTimeStats($testStats)
 {
     $testStats['sAvgElapsed'] = array_sum($testStats['sElapsed']) / $testStats['total'];
     $testStats['sMaxElapsed'] = max($testStats['sElapsed']);
     $testStats['sMinElapsed'] = min($testStats['sElapsed']);
     $testStats['sStdDevElapsed'] = stddev($testStats['sElapsed']);
     return $testStats;
 }

 function executeProfiling($testCountPerURL = 3) {

     if (!$testCountPerURL)
         $testCountPerURL = 3;

    foreach (get_files_in_directory('.','/.*\.expected/') as $fname) {
        echo "Testing if ".FileName2URL($fname)." equals to the expected sample...";

        $testStats[CMP::EQUAL] = 0; $testStats[CMP::NEQUAL] = 0; $testStats[CMP::ERROR] = 0;
        $testStats['total'] = $testCountPerURL;
        for($testNo = 0; $testNo < $testCountPerURL; $testNo++) {
            $result = compareToExpected(FileName2URL($fname),$fname);
            $testStats[$result['nEquals']]++;
            $testStats['sElapsed'][] = $result['sElapsed'];
        }

        $testStats = computeTimeStats($testStats);

        if ($testCountPerURL == $testStats[CMP::EQUAL]) {
           echo "[OK]\n";
        }
        else
        {
           echo "[FAILED]\n";
        }
        displayStats($testStats);
    }
 }

switch ($command) {
    case 'store':
        executeStore(getarg(2), getarg(3));
        break;
    
    case 'single':
        executeSingleTest(getarg(2), getarg(3));
        break;
    case 'help':
        printUsage();
        break;
    case 'profiling':
        executeProfiling(getarg(2));
        break;
    default:
        executeProfiling();
        exit;
}

?>
