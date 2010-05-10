<?php
//-----------------------------------------------------------------------------------------------------------------------------------
//
// Filename   : Logger.php
// Date       : 5th May 2010
// Version    : 0.1
//
// Copyright 2008-2010 foaf.me
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
//
// "Everything should be made as simple as possible, but no simpler."
// -- Albert Einstein
//
//-----------------------------------------------------------------------------------------------------------------------------------
/**
 * @author Laszlo Torok
 */
class Logger {
    const DEBUG_MODE = 'debug_mode';
    const DEBUG_OUT_DIR = 'debug_out_dir';
    
    private $enabled;
    private $directory_output;
    private $logfile;
    private $loghandle;
    private $lastmsgts = 0;
    private $messages = array();

    private static $instance = NULL;

    public function  __construct($dir_out = '/tmp/', $enabled = false) {
        $this->enabled = $enabled;
        $this->directory_output = $dir_out;
        $ts = time();
        $logfilebase = $__SERVER['SERVER_NAME'] ? $__SERVER['SERVER_NAME'] : 'perflog';
        $this->logfile = $this->directory_output.$logfilebase.'.'.$ts.'.log';
        $this->loghandle = fopen($this->logfile, 'w');
    }
    public function logWithTs($message)
    {
        if ($this->enabled) {
            $ts = microtime(true);
            $time_elapsed = $this->lastmsgts ? ($ts - $this->lastmsgts) : 0 ;
            $this->lastmsgts = $ts;

            $this->messages[] = array(
                'text'=> $message,
                'ts' => $time_elapsed
                );
        }
    }

    public function  __destruct() {
        foreach ($this->messages as $msg) {
             fwrite($this->loghandle, $msg['text']."\t\t".((float)$msg['ts']*1000)." msec\n");
        }
        fclose($this->loghandle);
    }

    public static function getLogger() {
        if (NULL == self::$instance) {
            self::$instance = new Logger(
                         $GLOBALS['config'][self::DEBUG_OUT_DIR],
                         $GLOBALS['config'][self::DEBUG_MODE]);
        }
        return self::$instance;
    }

    private function diff_microtime($mt_old,$mt_new)
    {
        list($old_usec, $old_sec) = explode(' ',$mt_old);
        list($new_usec, $new_sec) = explode(' ',$mt_new);
        $old_mt = ((float)$old_usec + (float)$old_sec);
        $new_mt = ((float)$new_usec + (float)$new_sec);
        return $new_mt - $old_mt;
    }

}
?>
