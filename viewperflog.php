<?php
include_once 'Logger.php';
include_once 'config.php';
$perflogdir = $GLOBALS['config']['debug_out_dir'];
echo "Scanning ".$perflogdir." directory for performance logs...\n";
if (0 < count($files = scandir($perflogdir))) {

    sort($files,SORT_STRING);

    $files = array_reverse($files);

    foreach ($files as $file) {

        if (strstr($file, 'perflog')) {
            echo $file."\n\n";
            echo file_get_contents($perflogdir.'/'.$file);
            echo "\n==================================================================\n\n";
        }
    }

}
?>
