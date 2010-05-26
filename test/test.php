#!/usr/bin/env php
<?php

function printUsage($msg = '')
{
    if ($msg)
        echo $msg."\n\n";

    echo "Usage: ".basename(__FILE__)." \n".
         "  store [foafURL] [--targetdir=DIR]\n".
         "  single [foafURL] [--expecteddir=DIR]\n".
            "\n";
}

 if ($argc < 2) {
     printUsage();
     exit;
 }

 $command = $argv[1];
 $params = array_slice($argv, 1);

 function getarg($argno) {
     isset ($argv[$argno]) ? $argv[$argno] : NULL;
 }
 
 function URL2FileName($url) {
     $url = str_replace('http://', '', $url);
     $url = str_replace('https://', '', $url);
     $url = str_replace('/', '_', $url);
     $url.= '.foaf';
     return $url;
 }

 function executeStore($foafURL, $targetDir = NULL) {
    if (!$foafURL)
        $foafURL = 'http://foaf.me/melvincarvalho';
    if (!$targetDir)
        $targetDir = dirname(__FILE__);
     
    $ch = curl_init($foafURL);
    $hFoafFile = fopen( $targetDir."/".URL2FileName($foafURL), 'w' );

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FILE, $hFoafFile );

    curl_exec($ch);
    curl_close($ch);

    fclose($hFoafFile);
 }

 function executeSingleTest($foafURL, $targetDir = NULL) {

     if (!$foafURL)
        $foafURL = 'http://foaf.me/melvincarvalho';
     if (!$targetDir)
        $targetDir = dirname(__FILE__);
     
     // get remote foaf file
     $ch = curl_init($foafURL);
     curl_setopt($ch, CURLOPT_HEADER, 0);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     $foafFile = curl_exec($ch);
     curl_close($ch);

     $expectedFoafFile = file_get_contents($targetDir."/".URL2FileName($foafURL));

     $output_msg = "Comparing ".$foafURL." with expected content";
     
     if ($foafFile == $expectedFoafFile) {
         echo $output_msg." [OK]\n";
         return TRUE;
     } else {
         echo $output_msg." [FAILED]\n";
         file_put_contents('expected.tmp', $expectedFoafFile."\n");
         file_put_contents('actual.tmp', $foafFile."\n");
         echo `diff expected.tmp actual.tmp`."\n";

         return FALSE;
     }

 }

switch ($command) {
    case 'store':
        executeStore(getarg(2), getarg(3));
        break;
    
    case 'single':
        executeSingleTest(getarg(2), getarg(3));
        break;

    default:
        printUsage('Unknown command');
        exit;
}

?>
