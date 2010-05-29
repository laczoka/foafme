#!/usr/bin/env php
<?php

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
     $result['msElapsed'] = microtime(true) - $t;

     // compare it to the stored expected content if exists
     if (file_exists($expectedFoafPath)) {
        $result['expected'] = file_get_contents($expectedFoafPath);
        $result['nEquals'] = (strcmp($result['expected'] , $result['actual']) == 0) ? 1 : 0;
     }
     else
          $result['nEquals'] = -1;

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
     echo "GET ".$foafURL." completed in ".$compareResult['msElapsed']." ms.\n";
     echo "Comparing ".$foafURL." with expected content";

     switch ($compareResult['nEquals']) {
         case 1:
            echo " [OK]\n";
         break;
         case 0:
            echo " [FAILED]\n";
            file_put_contents('expected.tmp', $compareResult['expected']."\n");
            file_put_contents('actual.tmp', $compareResult['actual']."\n");
            echo `diff expected.tmp actual.tmp`."\n";
         break;
         default:
             echo " [MISSING EXPECTED]";
     }

 }

 function executeProfiling()
 {
    foreach (get_files_in_directory('.','/.*\.expected/') as $fname) {
        echo "Testing if ".FileName2URL($fname)." equals to the expected sample...";

        $result = compareToExpected(FileName2URL($fname),$fname);
      
        switch ($result['nEquals']) {
            case 1:
                echo "[OK]\n";
            break;
            case 0:
                echo "[FAILED]\n";
            break;
            default:
                echo "[DUBIOUS]\n";
        }

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
    default:
        executeProfiling();
        exit;
}

?>
